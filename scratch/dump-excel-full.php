<?php
// scratch/dump-excel-full.php
require_once dirname(__DIR__) . '/app/config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = dirname(__DIR__) . '/jadwal.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();

echo "--- DUMPING NON-EMPTY CELLS IN EXCEL ---\n";
for ($row = 1; $row <= 40; $row++) {
    $rowText = [];
    $hasContent = false;
    for ($col = 1; $col <= 20; $col++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $val = $sheet->getCell($colLetter . $row)->getValue();
        if ($val !== null && trim((string)$val) !== '') {
            $hasContent = true;
            $rowText[] = "$colLetter$row: '$val'";
        }
    }
    if ($hasContent) {
        echo "Row $row: " . implode(" | ", $rowText) . "\n";
    }
}
