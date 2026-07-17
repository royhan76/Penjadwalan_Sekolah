<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/../jadwal.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();

$cols = range('A', 'W');

echo "=== GRID VIEW OF ROWS 30 TO 33 ===\n";
for ($row = 30; $row <= 33; $row++) {
    $parts = [];
    foreach ($cols as $col) {
        $val = $sheet->getCell($col . $row)->getValue();
        $parts[] = "$col: " . ($val === null ? '[NULL]' : trim($val));
    }
    echo "Row $row: " . implode(" | ", $parts) . "\n\n";
}
