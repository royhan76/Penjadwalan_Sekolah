<?php
// scratch/dump-excel-rest.php
require_once dirname(__DIR__) . '/app/config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = dirname(__DIR__) . '/jadwal.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();

echo "--- SEARCHING LEGENDS AND CODES IN EXCEL ---\n";
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();
$highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

echo "Grid size: 1 to $highestRow rows, 1 to $highestColIndex columns ($highestColumn)\n\n";

for ($row = 35; $row <= $highestRow; $row++) {
    $rowText = [];
    $hasContent = false;
    for ($col = 1; $col <= $highestColIndex; $col++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $val = $sheet->getCell($colLetter . $row)->getValue();
        if ($val !== null && trim((string)$val) !== '') {
            // If it's a long text or contains a list
            $hasContent = true;
            $rowText[] = "$colLetter$row: '$val'";
        }
    }
    if ($hasContent) {
        // Output row if it has content
        echo "Row $row: " . implode(" | ", array_slice($rowText, 0, 8)) . "\n";
        if (count($rowText) > 8) {
            echo "   ... and " . (count($rowText) - 8) . " more columns\n";
        }
    }
}
