<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIBookLookupController extends Controller
{
    public function lookup(Request $request)
    {
        $request->validate(['title' => 'required|string|max:300']);

        $title   = trim($request->input('title'));
        $apiKey  = config('services.anthropic.key');

        // Check if the book already exists in the library
        $existing = Book::where('title', 'LIKE', "%{$title}%")
            ->where('is_condemned', false)
            ->first();

        // Call Claude to get book details
        $autoFill           = null;
        $suggestedJustification = null;

        if ($apiKey) {
            try {
                $detailsPrompt = "You are a library assistant. Given the book title \"{$title}\", return ONLY a JSON object (no markdown, no extra text) with these fields: author (string), publisher (string), isbn (string or null), publication_year (integer or null), subject (string). Example: {\"author\":\"John Doe\",\"publisher\":\"Oxford\",\"isbn\":\"978-0-19-853453-1\",\"publication_year\":2022,\"subject\":\"Computer Science\"}";

                $response = Http::withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 300,
                    'messages'   => [
                        ['role' => 'user', 'content' => $detailsPrompt],
                    ],
                ]);

                if ($response->successful()) {
                    $text = $response->json('content.0.text', '');
                    $text = trim(preg_replace('/```json|```/i', '', $text));
                    $autoFill = json_decode($text, true);
                }

                // If book exists, generate justification
                if ($existing) {
                    $justPrompt = "A library patron wants to request the book \"{$title}\" which already exists in the library catalog. Write exactly 2 sentences justifying why an additional copy or updated edition would benefit the library. Be concise and professional.";

                    $jResponse = Http::withHeaders([
                        'x-api-key'         => $apiKey,
                        'anthropic-version' => '2023-06-01',
                        'content-type'      => 'application/json',
                    ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
                        'model'      => 'claude-haiku-4-5-20251001',
                        'max_tokens' => 200,
                        'messages'   => [
                            ['role' => 'user', 'content' => $justPrompt],
                        ],
                    ]);

                    if ($jResponse->successful()) {
                        $suggestedJustification = trim($jResponse->json('content.0.text', ''));
                    }
                }
            } catch (\Exception $e) {
                // Gracefully fall through — return what we have
            }
        }

        return response()->json([
            'auto_fill'              => $autoFill,
            'exists_in_library'      => $existing !== null,
            'existing_book'          => $existing ? [
                'id'     => $existing->id,
                'title'  => $existing->title,
                'copies' => $existing->copies,
            ] : null,
            'suggested_justification' => $suggestedJustification,
        ]);
    }
}
