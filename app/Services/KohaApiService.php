<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class KohaApiService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(Setting::getValue('koha_base_url', config('koha.base_url', '')), '/');
        $this->apiKey  = Setting::getValue('koha_api_key', config('koha.api_key', ''));
        $this->timeout = (int) config('koha.timeout', 15);
    }

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->apiKey);
    }

    private function http()
    {
        return Http::withHeaders([
            'x-koha-embed' => '',
            'Authorization' => 'Basic ' . base64_encode($this->apiKey),
        ])->timeout($this->timeout);
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Koha base URL or API key is not configured.'];
        }

        try {
            $response = $this->http()->get("{$this->baseUrl}/api/v1/biblios?_per_page=1");
            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connection successful.'];
            }
            return ['success' => false, 'message' => "HTTP {$response->status()}: " . $response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function fetchBibliographicRecords(int $limit = 100): array
    {
        $response = $this->http()->get("{$this->baseUrl}/api/v1/biblios", [
            '_per_page' => $limit,
        ]);
        $response->throw();
        return $response->json() ?? [];
    }

    public function importBook(array $kohaRecord): Book
    {
        return Book::updateOrCreate(
            ['isbn' => $kohaRecord['isbn'] ?? null],
            [
                'title'            => $kohaRecord['title'] ?? 'Unknown Title',
                'author'           => $kohaRecord['author'] ?? null,
                'edition'          => $kohaRecord['edition'] ?? null,
                'publication_year' => $kohaRecord['copyrightdate'] ?? null,
                'isbn'             => $kohaRecord['isbn'] ?? null,
                'status'           => 'available',
            ]
        );
    }

    public function syncCirculation(): array
    {
        $response = $this->http()->get("{$this->baseUrl}/api/v1/checkouts", ['_per_page' => 500]);
        $response->throw();
        return $response->json() ?? [];
    }

    public function fetchPatrons(int $limit = 100): array
    {
        $response = $this->http()->get("{$this->baseUrl}/api/v1/patrons", ['_per_page' => $limit]);
        $response->throw();
        return $response->json() ?? [];
    }
}
