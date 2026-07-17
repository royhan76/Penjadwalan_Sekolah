<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/../jadwal.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();

$cols = range('C', 'W');

echo "=== INSPECTING BOTTOM ROWS OF BLOCK 1 (Rows 17-18) ===\n";
foreach ([17, 18] as $row) {
    echo "Row $row: ";
    foreach ($cols as $col) {
        $val = $sheet->getCell($col . $row)->getValue();
        if ($val !== null && trim($val) !== '') {
            echo "[$col: " . trim($val) . "] ";
        }
    }
    echo "\n";
}

echo "\n=== INSPECTING BOTTOM ROWS OF BLOCK 2 (Rows 31-35) ===\n";
foreach ([31, 32, 33, 34, 35] as $row) {
    echo "Row $row: ";
    foreach ($cols as $col) {
        $val = $sheet->getCell($col . $row)->getValue();
        if ($val !== null && trim($val) !== '') {
            echo "[$col: " . trim($val) . "] ";
        }
    }
    echo "\n";
}

echo "\n=== INSPECTING SIGNATURE AREA (Rows 39-41) ===\n";
foreach ([38, 39, 40, 41] as $row) {
    echo "Row $row: ";
    foreach ($cols as $col) {
        $val = $sheet->getCell($col . $row)->getValue();
        if ($val !== null && trim($val) !== '') {
            echo "[$col: " . trim($val) . "] ";
        }
    }
    echo "\n";
}
