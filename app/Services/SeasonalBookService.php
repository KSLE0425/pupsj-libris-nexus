<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeasonalBookService
{
    public const DEFAULT_CONFIG = [
        'mode' => 'auto', // 'auto' (date-detected) or 'manual'
        'manual_season_key' => '1st_semester',
        'seasons' => [
            '1st_semester' => [
                'key'         => '1st_semester',
                'name'        => '1st Semester (Aug – Dec)',
                'theme'       => 'Academic Foundations & Core Curricula',
                'description' => 'Focus on foundational textbooks, IT, computer science, accounting, business administration, and core university curricula.',
                'start_month' => 8,
                'start_day'   => 1,
                'end_month'   => 12,
                'end_day'     => 15,
                'keywords'    => ['programming', 'software', 'data', 'accounting', 'business', 'management', 'computer', 'mathematics', 'algorithms', 'engineering'],
                'subjects'    => ['Computer software', 'Business Administration', 'Marketing Strategy', 'Algorithms', 'Reliability', 'Coding theory'],
            ],
            'mid_year_holiday' => [
                'key'         => 'mid_year_holiday',
                'name'        => 'Mid-Year & Holiday Season (Dec – Jan)',
                'theme'       => 'Filipiniana, Literature, Culture & Leisure Reading',
                'description' => 'Highlight cultural heritage, Philippine literature, classics, self-improvement, history, and light reading.',
                'start_month' => 12,
                'start_day'   => 16,
                'end_month'   => 1,
                'end_day'     => 15,
                'keywords'    => ['filipiniana', 'rizal', 'history', 'literature', 'fiction', 'philippines', 'culture', 'novel', 'poetry', 'biography'],
                'subjects'    => ['Filipiniana', 'Fictions', 'Philippine History', 'Literature', 'General Reading'],
            ],
            '2nd_semester' => [
                'key'         => '2nd_semester',
                'name'        => '2nd Semester (Jan – May)',
                'theme'       => 'Advanced Specializations, Capstone & Thesis',
                'description' => 'Emphasis on specialized departmental courses, research methodologies, capstone projects, and special collections.',
                'start_month' => 1,
                'start_day'   => 16,
                'end_month'   => 5,
                'end_day'     => 31,
                'keywords'    => ['thesis', 'research', 'special', 'design', 'architecture', 'consumer', 'strategy', 'finance', 'analytics', 'network'],
                'subjects'    => ['Special Collections', 'Thesis Collection', 'Consumer Behavior', 'Agile software development', 'Advanced Computing'],
            ],
            'summer_break' => [
                'key'         => 'summer_break',
                'name'        => 'Summer & Special Term (Jun – Jul)',
                'theme'       => 'Summer Reading, Certification & Review Materials',
                'description' => 'Curated collection of certification guides, board examination review materials, accelerated summer classes, and creative leisure.',
                'start_month' => 6,
                'start_day'   => 1,
                'end_month'   => 7,
                'end_day'     => 31,
                'keywords'    => ['review', 'guide', 'examination', 'handbook', 'practical', 'introduction', 'summer', 'general', 'learning'],
                'subjects'    => ['Library of Congress', 'General Collections', 'Reference', 'Study Guides'],
            ],
        ],
    ];

    /**
     * Retrieve the seasonal configuration from database settings.
     */
    public static function getConfig(): array
    {
        $raw = Setting::getValue('seasonal_books_config');
        if (!$raw) {
            return self::DEFAULT_CONFIG;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (!is_array($decoded) || empty($decoded['seasons'])) {
            return self::DEFAULT_CONFIG;
        }

        return array_replace_recursive(self::DEFAULT_CONFIG, $decoded);
    }

    /**
     * Save configuration to settings.
     */
    public static function saveConfig(array $config): void
    {
        Setting::setValue('seasonal_books_config', json_encode($config), 'catalog');
    }

    /**
     * Get the active season based on mode (auto detection vs manual override).
     */
    public static function getActiveSeason(?Carbon $now = null): array
    {
        $config = self::getConfig();
        $now = $now ?? now('Asia/Manila');

        if (($config['mode'] ?? 'auto') === 'manual' && !empty($config['manual_season_key'])) {
            $key = $config['manual_season_key'];
            if (isset($config['seasons'][$key])) {
                $season = $config['seasons'][$key];
                $season['active_mode'] = 'manual';
                return $season;
            }
        }

        // Auto-detection by date
        $month = $now->month;
        $day = $now->day;

        foreach ($config['seasons'] as $key => $season) {
            $sm = (int) $season['start_month'];
            $sd = (int) $season['start_day'];
            $em = (int) $season['end_month'];
            $ed = (int) $season['end_day'];

            if ($sm <= $em) {
                // Same calendar year interval (e.g. 1/16 to 5/31)
                $inRange = ($month > $sm || ($month === $sm && $day >= $sd)) &&
                           ($month < $em || ($month === $em && $day <= $ed));
            } else {
                // Spans across New Year (e.g. 12/16 to 1/15)
                $inRange = ($month > $sm || ($month === $sm && $day >= $sd)) ||
                           ($month < $em || ($month === $em && $day <= $ed));
            }

            if ($inRange) {
                $season['active_mode'] = 'auto';
                return $season;
            }
        }

        // Fallback default
        $fallback = $config['seasons']['1st_semester'] ?? reset($config['seasons']);
        $fallback['active_mode'] = 'auto';
        return $fallback;
    }

    /**
     * Get seasonal book suggestions with scoring and explanations.
     */
    public static function getSuggestedBooks(int $limit = 12): Collection
    {
        $season = self::getActiveSeason();
        $keywords = $season['keywords'] ?? [];
        $subjects = $season['subjects'] ?? [];

        // Base query: available, unarchived, uncondemned books
        $query = Book::where('status', '!=', 'archived')
            ->where('is_condemned', false);

        $books = $query->get();

        // Get borrowing stats for books in this season window historically
        $now = now('Asia/Manila');
        $seasonalUsages = BookUsage::whereMonth('time_in', $now->month)
            ->select('book_id', DB::raw('count(*) as season_borrows'))
            ->groupBy('book_id')
            ->pluck('season_borrows', 'book_id');

        // Score each book
        $scored = $books->map(function ($book) use ($keywords, $subjects, $season, $seasonalUsages, $now) {
            $score = 0;
            $reasons = [];

            // Subject exact match
            foreach ($subjects as $s) {
                if (!empty($book->subject) && stripos($book->subject, $s) !== false) {
                    $score += 30;
                    $reasons[] = "Curriculum match: {$s}";
                    break;
                }
                if (!empty($book->collection) && stripos($book->collection, $s) !== false) {
                    $score += 25;
                    $reasons[] = "Featured in {$book->collection}";
                    break;
                }
            }

            // Keyword match across title, author, subject
            $searchBlob = strtolower(($book->title ?? '') . ' ' . ($book->subject ?? '') . ' ' . ($book->description ?? ''));
            $matchedKeywords = [];
            foreach ($keywords as $kw) {
                if (str_contains($searchBlob, strtolower($kw))) {
                    $score += 15;
                    $matchedKeywords[] = $kw;
                }
            }
            if (!empty($matchedKeywords)) {
                $reasons[] = "Seasonal theme: " . implode(', ', array_slice($matchedKeywords, 0, 2));
            }

            // Historical seasonal popularity
            $borrows = $seasonalUsages[$book->id] ?? 0;
            if ($borrows > 0) {
                $score += min(30, $borrows * 10);
                $reasons[] = "Popular in {$now->format('F')} ({$borrows} borrows)";
            }

            // Availability bonus
            if ($book->copies > 0) {
                $score += 10;
            }

            // New acquisitions bonus
            if ($book->created_at && $book->created_at->diffInDays() <= 90) {
                $score += 10;
                $reasons[] = "Recent Acquisition";
            }

            $book->seasonal_score = $score;
            $book->seasonal_reason = !empty($reasons) ? implode(' • ', array_unique($reasons)) : 'Recommended for ' . $season['name'];
            $book->season_name = $season['name'];

            return $book;
        });

        return $scored->sortByDesc('seasonal_score')->take($limit)->values();
    }
}
