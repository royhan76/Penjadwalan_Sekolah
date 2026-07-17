<?php
// app/GoogleSheet.php - Google Sheets Service

namespace App;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class GoogleSheet
{
    private Sheets $service;
    private string $spreadsheetId;
    public ?string $lastError = null;

    public function __construct()
    {
        $this->spreadsheetId = env('1kQ0zdK0WdtrEWl1jy_ypMoxEY6PsdEWK3_YVY3Q-xAM');
        // $this->spreadsheetId = env('SPREADSHEET_ID');

        
        $client = new Client();
        $client->setApplicationName(env('APP_NAME', 'Penjadwalan Sekolah'));
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAuthConfig($this->resolveCredentials());
        $client->setAccessType('offline');
        
        $this->service = new Sheets($client);
    }

    private function resolveCredentials(): array|string
    {
        // Check if JSON string is set as env var (for Vercel)
        $jsonString = env('GOOGLE_SERVICE_ACCOUNT_JSON');
        
        if ($jsonString && str_starts_with($jsonString, '{')) {
            return json_decode($jsonString, true);
        }
        
        // Check if it's a file path
        $basePath = dirname(__DIR__);
        $filePath = $basePath . '/' . ($jsonString ?: 'credentials.json');
        
        if (file_exists($filePath)) {
            return $filePath;
        }
        
        throw new \RuntimeException("Google credentials not found. Please set GOOGLE_SERVICE_ACCOUNT_JSON env variable.");
    }

    /**
     * Get all rows from a sheet
     */
    public function get(string $sheetName, string $range = 'A:Z'): array
    {
        try {
            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                "$sheetName!$range"
            );
            $values = $response->getValues() ?? [];
            
            if (empty($values)) return [];
            
            $headers = array_shift($values);
            $result = [];
            
            foreach ($values as $rowIndex => $row) {
                $item = [];
                foreach ($headers as $colIndex => $header) {
                    $item[$header] = $row[$colIndex] ?? '';
                }
                $item['_row'] = $rowIndex + 2; // actual spreadsheet row (1-based + header)
                $result[] = $item;
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::get error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get raw values without header mapping
     */
    public function getRaw(string $range): array
    {
        try {
            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                $range
            );
            return $response->getValues() ?? [];
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::getRaw error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Append a new row to a sheet
     */
    public function insert(string $sheetName, array $values): bool
    {
        try {
            $body = new ValueRange(['values' => [$values]]);
            $params = ['valueInputOption' => 'USER_ENTERED'];
            
            $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                "$sheetName!A:Z",
                $body,
                $params
            );
            return true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::insert error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a specific row
     */
    public function update(string $sheetName, int $row, array $values): bool
    {
        try {
            $body = new ValueRange(['values' => [$values]]);
            $params = ['valueInputOption' => 'USER_ENTERED'];
            
            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                "$sheetName!A$row",
                $body,
                $params
            );
            return true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear a range of cells in a sheet
     */
    public function clear(string $range): bool
    {
        try {
            $this->service->spreadsheets_values->clear(
                $this->spreadsheetId,
                $range,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
            return true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::clear error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a range of cells with multiple rows
     */
    public function updateRange(string $range, array $values): bool
    {
        try {
            $body = new ValueRange(['values' => $values]);
            $params = ['valueInputOption' => 'USER_ENTERED'];
            
            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );
            return true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::updateRange error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a specific row by clearing it then removing
     */
    public function delete(string $sheetName, int $row): bool
    {
        try {
            // Get sheet ID for batch update
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
            $sheetId = null;
            
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === $sheetName) {
                    $sheetId = $sheet->getProperties()->getSheetId();
                    break;
                }
            }
            
            if ($sheetId === null) {
                throw new \RuntimeException("Sheet '$sheetName' not found");
            }
            
            // Delete row using batchUpdate
            $requests = [
                new \Google\Service\Sheets\Request([
                    'deleteDimension' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'dimension' => 'ROWS',
                            'startIndex' => $row - 1, // 0-based
                            'endIndex' => $row,       // exclusive
                        ],
                    ],
                ]),
            ];
            
            $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => $requests,
            ]);
            
            $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $body);
            return true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new sheet tab in Google Sheets
     */
    public function createSheet(string $sheetName): bool
    {
        try {
            $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => [
                    [
                        'addSheet' => [
                            'properties' => [
                                'title' => $sheetName
                            ]
                        ]
                    ]
                ]
            ]);
            $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $body);
            return true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::createSheet error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Initialize all required sheets with headers
     */
    public function initSheets(): array
    {
        $sheets = [
            'Guru'         => ['ID', 'Nama', 'Status'],
            'Mapel'        => ['ID', 'Nama', 'JamPerminggu'],
            'Kelas'        => ['ID', 'Nama'],
            'Jam'          => ['ID', 'Label', 'Mulai', 'Selesai'],
            'TahunPelajaran' => ['ID', 'Nama', 'Semester', 'Aktif'],
            'Jadwal'       => ['ID', 'TahunPelajaranID', 'Hari', 'JamID', 'KelasID', 'GuruID', 'MapelID'],
        ];
        
        try {
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
            $existingSheetNames = [];
            foreach ($spreadsheet->getSheets() as $s) {
                $existingSheetNames[] = $s->getProperties()->getTitle();
            }
            
            $results = [];
            foreach ($sheets as $sheetName => $headers) {
                if (!in_array($sheetName, $existingSheetNames)) {
                    $created = $this->createSheet($sheetName);
                    if ($created) {
                        $this->insert($sheetName, $headers);
                        $results[$sheetName] = 'created';
                    } else {
                        $results[$sheetName] = 'failed to create: ' . ($this->lastError ?? 'unknown error');
                    }
                } else {
                    $results[$sheetName] = 'exists';
                }
            }
            return $results;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("GoogleSheet::initSheets error: " . $e->getMessage());
            return ['error' => 'Gagal mengambil informasi spreadsheet: ' . $e->getMessage()];
        }
    }

    /**
     * Find row by field value
     */
    public function findBy(string $sheetName, string $field, mixed $value): ?array
    {
        $rows = $this->get($sheetName);
        foreach ($rows as $row) {
            if (isset($row[$field]) && $row[$field] == $value) {
                return $row;
            }
        }
        return null;
    }

    /**
     * Generate next ID for a sheet
     */
    public function nextId(string $sheetName): string
    {
        $rows = $this->get($sheetName);
        if (empty($rows)) return '1';
        
        $ids = array_column($rows, 'ID');
        $numericIds = array_filter($ids, 'is_numeric');
        
        if (empty($numericIds)) return '1';
        return (string)(max($numericIds) + 1);
    }
}
