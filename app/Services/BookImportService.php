<?php

namespace App\Services;

use App\Models\Book;
use App\Models\CollectionType;
use App\Models\Course;
use App\Services\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookImportService
{
    /**
     * Parse and validate an uploaded CSV or Excel file.
     */
    public function preview(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt', 'xlsx'])) {
            return [
                'valid' => false,
                'errors' => [['row' => 0, 'field' => 'file', 'message' => 'Unsupported file format. Please upload a CSV (.csv) or Excel (.xlsx) file.']],
            ];
        }

        try {
            $rawRows = match ($extension) {
                'xlsx' => $this->parseXlsx($file->getRealPath()),
                default => $this->parseCsv($file->getRealPath()),
            };
        } catch (\Throwable $e) {
            return [
                'valid' => false,
                'errors' => [['row' => 0, 'field' => 'file', 'message' => 'Failed to parse file: ' . $e->getMessage()]],
            ];
        }

        if (empty($rawRows)) {
            return [
                'valid' => false,
                'errors' => [['row' => 0, 'field' => 'file', 'message' => 'The uploaded file is empty or contains only header columns.']],
            ];
        }

        // Validate rows and aggregate duplicates
        $validation = $this->validateAndNormalize($rawRows);

        return $validation;
    }

    /**
     * Perform the actual import of normalized books into the database.
     */
    public function executeImport(array $groupedBooks, ?string $adminUser = 'Admin'): array
    {
        return DB::transaction(function () use ($groupedBooks, $adminUser) {
            $importedCount = 0;
            $totalCopiesAdded = 0;

            $collectionMap = CollectionType::whereNull('archived_at')->pluck('id', 'name')->toArray();
            $defaultCollId = !empty($collectionMap) ? reset($collectionMap) : null;

            foreach ($groupedBooks as $item) {
                $collName = $item['collection'] ?? 'General';
                $collId = $collectionMap[$collName] ?? $defaultCollId;

                // Create or find existing book if merging
                $copies = max(1, (int) ($item['copies'] ?? 1));

                $barcode = $item['barcode'] ?? null;
                if (!$barcode) {
                    $barcode = $this->generateNextBarcode($collName);
                }

                $book = Book::create([
                    'title'              => $item['title'],
                    'author'             => $item['author'] ?? 'Unknown',
                    'isbn'               => $item['isbn'] ?? null,
                    'accession_number'   => $item['accession_number'] ?? null,
                    'loc_number'         => $item['call_number'] ?? $item['loc_number'] ?? null,
                    'barcode'            => $barcode,
                    'collection'         => $collName,
                    'collection_type_id' => $collId,
                    'shelf_location'     => $item['shelf_location'] ?? null,
                    'publisher'          => $item['publisher'] ?? null,
                    'publication_year'   => $item['publication_year'] ?? null,
                    'edition'            => $item['edition'] ?? null,
                    'copies'             => $copies,
                    'subject'            => $item['subject'] ?? null,
                    'status'             => 'available',
                    'is_new_acquisition' => true,
                ]);

                $importedCount++;
                $totalCopiesAdded += $copies;

                AuditLogger::log('book_imported', 'Imported book "' . $book->title . '" with ' . $copies . ' copies (Acc: ' . ($book->accession_number ?? 'N/A') . ')', [
                    'book_id'          => $book->id,
                    'affected_book'    => $book->title,
                    'accession_number' => $book->accession_number,
                    'copies'           => $copies,
                    'imported_by'      => $adminUser,
                ], 'books');
            }

            return [
                'success'            => true,
                'imported_titles'    => $importedCount,
                'total_copies_added' => $totalCopiesAdded,
                'message'            => "Successfully imported {$importedCount} title(s) ({$totalCopiesAdded} total copies).",
            ];
        });
    }

    /**
     * Validate each row strictly; reject if any error exists.
     */
    protected function validateAndNormalize(array $rawRows): array
    {
        $errors = [];
        $normalized = [];
        $seenAccessionsInFile = [];
        $existingAccessionsInDb = Book::whereNotNull('accession_number')->pluck('title', 'accession_number')->toArray();

        foreach ($rawRows as $idx => $row) {
            $rowNum = $idx + 2; // account for header (1-indexed)

            $title = trim($row['title'] ?? '');
            if ($title === '') {
                $errors[] = [
                    'row'     => $rowNum,
                    'field'   => 'title',
                    'message' => 'Book Title is required.',
                ];
                continue;
            }

            $accNum = trim($row['accession_number'] ?? '');
            if ($accNum !== '') {
                if (isset($seenAccessionsInFile[$accNum])) {
                    $errors[] = [
                        'row'     => $rowNum,
                        'field'   => 'accession_number',
                        'message' => "Duplicate accession number \"{$accNum}\" already used in row {$seenAccessionsInFile[$accNum]}.",
                    ];
                } else {
                    $seenAccessionsInFile[$accNum] = $rowNum;
                }

                if (isset($existingAccessionsInDb[$accNum])) {
                    $errors[] = [
                        'row'     => $rowNum,
                        'field'   => 'accession_number',
                        'message' => "Accession number \"{$accNum}\" already exists in the catalog for book \"{$existingAccessionsInDb[$accNum]}\".",
                    ];
                }
            }

            $isbn = trim($row['isbn'] ?? '');
            if ($isbn !== '') {
                $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);
                if (strlen($cleanIsbn) !== 10 && strlen($cleanIsbn) !== 13) {
                    $errors[] = [
                        'row'     => $rowNum,
                        'field'   => 'isbn',
                        'message' => "Invalid ISBN format \"{$isbn}\". Must be 10 or 13 digits.",
                    ];
                }
            }

            $pubYear = trim($row['publication_year'] ?? '');
            if ($pubYear !== '' && (!is_numeric($pubYear) || (int) $pubYear < 1000 || (int) $pubYear > (int) date('Y') + 5)) {
                $errors[] = [
                    'row'     => $rowNum,
                    'field'   => 'publication_year',
                    'message' => "Invalid publication year \"{$pubYear}\".",
                ];
            }

            $copies = isset($row['copies']) && is_numeric($row['copies']) ? max(1, (int) $row['copies']) : 1;

            $normalized[] = [
                'row_index'        => $rowNum,
                'title'            => $title,
                'author'           => trim($row['author'] ?? '') ?: 'Unknown',
                'isbn'             => $isbn ?: null,
                'accession_number' => $accNum ?: null,
                'collection'       => trim($row['collection'] ?? '') ?: 'General',
                'shelf_location'   => trim($row['shelf_location'] ?? '') ?: null,
                'call_number'      => trim($row['call_number'] ?? '') ?: null,
                'publisher'        => trim($row['publisher'] ?? '') ?: null,
                'publication_year' => $pubYear ?: null,
                'edition'          => trim($row['edition'] ?? '') ?: null,
                'copies'           => $copies,
                'subject'          => trim($row['subject'] ?? '') ?: null,
            ];
        }

        if (!empty($errors)) {
            return [
                'valid'       => false,
                'errors'      => $errors,
                'total_rows'  => count($rawRows),
                'error_count' => count($errors),
            ];
        }

        // Group rows that share the same Title and ISBN (or Title and Author) into one book record with multiple copies
        $grouped = [];
        foreach ($normalized as $bookItem) {
            $key = strtolower(trim($bookItem['title'])) . '|' . strtolower(trim($bookItem['isbn'] ?? $bookItem['author']));
            if (!isset($grouped[$key])) {
                $grouped[$key] = $bookItem;
            } else {
                // Increment copies
                $grouped[$key]['copies'] += $bookItem['copies'];
                // Keep multiple accession numbers if distinct
                if (!empty($bookItem['accession_number']) && !str_contains($grouped[$key]['accession_number'] ?? '', $bookItem['accession_number'])) {
                    $grouped[$key]['accession_number'] = ($grouped[$key]['accession_number'] ? $grouped[$key]['accession_number'] . ', ' : '') . $bookItem['accession_number'];
                }
            }
        }

        return [
            'valid'                => true,
            'errors'               => [],
            'total_rows'           => count($rawRows),
            'unique_titles_count'  => count($grouped),
            'total_copies_count'   => array_sum(array_column(array_values($grouped), 'copies')),
            'grouped_books'        => array_values($grouped),
            'preview_sample'       => array_slice(array_values($grouped), 0, 10),
        ];
    }

    /**
     * Parse CSV files with header mapping.
     */
    protected function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException("Unable to open CSV file.");
        }

        // Detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, "\t") ? "\t" : (str_contains($firstLine, ';') ? ';' : ',');

        // Read header
        $headerRaw = fgetcsv($handle, 0, $delimiter);
        if (!$headerRaw) {
            fclose($handle);
            return [];
        }

        // Clean UTF-8 BOM
        $headerRaw[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headerRaw[0]);
        $headers = array_map([$this, 'normalizeHeader'], $headerRaw);

        $rows = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (empty(array_filter($data, fn($v) => trim($v) !== ''))) {
                continue; // skip empty line
            }
            $row = [];
            foreach ($headers as $idx => $headerKey) {
                if ($headerKey) {
                    $row[$headerKey] = isset($data[$idx]) ? trim($data[$idx]) : '';
                }
            }
            if (!empty($row)) {
                $rows[] = $row;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Pure PHP XLSX (PKZip + XML) parser without requiring ext-zip.
     */
    protected function parseXlsx(string $path): array
    {
        $zipData = file_get_contents($path);
        if (!$zipData) {
            throw new \RuntimeException("Unable to read Excel file contents.");
        }

        $entries = $this->extractZipEntries($zipData);

        // Read Shared Strings
        $sharedStrings = [];
        if (isset($entries['xl/sharedStrings.xml'])) {
            $xml = simplexml_load_string($entries['xl/sharedStrings.xml']);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // Read Sheet 1
        $sheetXmlContent = $entries['xl/worksheets/sheet1.xml'] ?? $entries['xl/worksheets/sheet0.xml'] ?? null;
        if (!$sheetXmlContent) {
            // Find first sheet XML
            foreach ($entries as $filename => $content) {
                if (preg_match('#xl/worksheets/sheet\d+\.xml#i', $filename)) {
                    $sheetXmlContent = $content;
                    break;
                }
            }
        }

        if (!$sheetXmlContent) {
            throw new \RuntimeException("No worksheet data found in Excel workbook.");
        }

        $sheetXml = simplexml_load_string($sheetXmlContent);
        if (!$sheetXml || !isset($sheetXml->sheetData->row)) {
            return [];
        }

        $parsedRows = [];
        foreach ($sheetXml->sheetData->row as $r) {
            $rowCells = [];
            foreach ($r->c as $c) {
                $type = (string) $c['t'];
                $val = (string) $c->v;
                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                }
                $rowCells[] = trim($val);
            }
            $parsedRows[] = $rowCells;
        }

        if (empty($parsedRows)) {
            return [];
        }

        // First row is header
        $headerRaw = array_shift($parsedRows);
        $headers = array_map([$this, 'normalizeHeader'], $headerRaw);

        $result = [];
        foreach ($parsedRows as $data) {
            if (empty(array_filter($data, fn($v) => trim($v) !== ''))) {
                continue;
            }
            $row = [];
            foreach ($headers as $idx => $headerKey) {
                if ($headerKey) {
                    $row[$headerKey] = isset($data[$idx]) ? trim($data[$idx]) : '';
                }
            }
            if (!empty($row)) {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Map header column string to database field name.
     */
    protected function normalizeHeader(string $header): ?string
    {
        $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $header)));

        $map = [
            'title'            => ['title', 'book_title', 'name', 'pamagat'],
            'author'           => ['author', 'authors', 'writer', 'creator', 'may_akda'],
            'isbn'             => ['isbn', 'isbn_10', 'isbn_13', 'isbn10', 'isbn13', 'standard_number'],
            'accession_number' => ['accession_number', 'accession', 'acc_no', 'acc_num', 'accession_no'],
            'collection'       => ['collection', 'collection_type', 'category', 'section'],
            'shelf_location'   => ['shelf_location', 'shelf', 'location', 'rack'],
            'call_number'      => ['call_number', 'call_no', 'loc_number', 'loc_class'],
            'publisher'        => ['publisher', 'publishing_house', 'press'],
            'publication_year' => ['publication_year', 'year', 'pub_year', 'year_published', 'date_published'],
            'edition'          => ['edition', 'ed'],
            'copies'           => ['copies', 'copy_count', 'quantity', 'qty'],
            'subject'          => ['subject', 'subjects', 'topic', 'genre', 'tags'],
        ];

        foreach ($map as $field => $synonyms) {
            if ($clean === $field || in_array($clean, $synonyms, true)) {
                return $field;
            }
            foreach ($synonyms as $syn) {
                if (str_contains($clean, $syn)) {
                    return $field;
                }
            }
        }

        return null;
    }

    /**
     * Extract files from raw zip bytes using standard PKZip spec + gzinflate.
     */
    protected function extractZipEntries(string $zipData): array
    {
        $entries = [];
        $pos = 0;
        $len = strlen($zipData);

        while ($pos < $len - 4) {
            $sig = substr($zipData, $pos, 4);
            if ($sig !== "\x50\x4b\x03\x04") { // Local File Header signature
                break;
            }

            $method = unpack('v', substr($zipData, $pos + 8, 2))[1];
            $compSize = unpack('V', substr($zipData, $pos + 18, 4))[1];
            $nameLen = unpack('v', substr($zipData, $pos + 26, 2))[1];
            $extraLen = unpack('v', substr($zipData, $pos + 28, 2))[1];

            $name = substr($zipData, $pos + 30, $nameLen);
            $dataStart = $pos + 30 + $nameLen + $extraLen;
            $data = substr($zipData, $dataStart, $compSize);

            if ($method === 0) { // Stored
                $entries[$name] = $data;
            } elseif ($method === 8 && function_exists('gzinflate')) { // Deflated
                $decompressed = @gzinflate($data);
                if ($decompressed !== false) {
                    $entries[$name] = $decompressed;
                }
            }

            $pos = $dataStart + $compSize;
        }

        return $entries;
    }

    /**
     * Helper to compute next barcode sequence.
     */
    protected function generateNextBarcode(string $collection): string
    {
        $prefix = match ($collection) {
            'Filipiniana' => 'FIL',
            'Library of Congress' => 'LoC',
            'Thesis Collection' => 'THS',
            'Fictions' => 'FIC',
            'Special Collections' => 'SPC',
            default => 'GEN',
        };

        $lastBook = Book::where('barcode', 'LIKE', $prefix . '-%')
            ->orderByRaw('LENGTH(barcode) DESC, barcode DESC')
            ->first();

        $nextNum = 1;
        if ($lastBook && preg_match('/-(\d+)$/', $lastBook->barcode, $m)) {
            $nextNum = ((int) $m[1]) + 1;
        }

        return $prefix . '-' . $nextNum;
    }
}
