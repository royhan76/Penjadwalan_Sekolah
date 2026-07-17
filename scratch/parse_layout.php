<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/../jadwal.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();

echo "Analyzing schedule blocks...\n";

// Let's scan rows 5 and 19 for days
$days_positions = [];
foreach ([5, 19] as $rowNum) {
    for ($col = 1; $col <= 24; $col++) {
        $val = $sheet->getCell([$col, $rowNum])->getValue();
        if ($val !== null && trim($val) !== '' && !in_array(trim($val), ['JAM KE', 'Waktu'])) {
            $days_positions[] = [
                'day' => trim($val),
                'row' => $rowNum,
                'col' => $col
            ];
        }
    }
}

echo "Days found and their starting columns:\n";
foreach ($days_positions as $dp) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dp['col']);
    echo "- Day: {$dp['day']} | Row: {$dp['row']} | Col: {$colLetter} ({$dp['col']})\n";
}

// For each day, let's see which classes are listed under it
// The classes are listed in the row below the day row (row + 1)
foreach ($days_positions as $dp) {
    $dayRow = $dp['row'];
    $classRow = $dayRow + 1;
    $startCol = $dp['col'];
    
    // Find classes for this day. They continue to the right until we hit a divider or empty/day/master column.
    // The next day is at $dp_next['col'] or divider.
    // Let's scan from $startCol onwards.
    echo "\nClasses for {$dp['day']}:\n";
    for ($c = $startCol; $c <= $startCol + 10; $c++) {
        $cellVal = $sheet->getCell([$c, $classRow])->getValue();
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
        if ($cellVal === null || trim($cellVal) === '') {
            // Check if there is scheduled data in this column. Sometimes classes headers are merged, or it's a spacer.
            // Let's check if the column has values in subsequent rows.
            $hasData = false;
            for ($r = $classRow + 1; $r <= $classRow + 10; $r++) {
                $v = $sheet->getCell([$c, $r])->getValue();
                if ($v !== null && trim($v) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if (!$hasData) {
                echo "  Col $colLetter ({$c}): [EMPTY COLUMN]\n";
                break;
            }
            echo "  Col $colLetter ({$c}): [Merged/No Class Header]\n";
        } else {
            echo "  Col $colLetter ({$c}): " . trim($cellVal) . "\n";
        }
    }
}
