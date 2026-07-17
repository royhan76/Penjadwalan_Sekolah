<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/../jadwal.xlsx';

if (!file_exists($filePath)) {
    die("File not found: $filePath\n");
}

$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();

$highestRow = min($sheet->getHighestRow(), 150);
$highestColumn = $sheet->getHighestColumn();
$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

echo "Row Range: 1 to $highestRow\n";
for ($row = 1; $row <= $highestRow; $row++) {
    $rowData = [];
    $hasData = false;
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $val = $sheet->getCell([$col, $row])->getValue();
        if ($val !== null && $val !== '') {
            $hasData = true;
        }
        $rowData[$col] = $val;
    }
    
    if ($hasData) {
        // Let's format and print only columns that have data, with column letters
        $formatted = [];
        foreach ($rowData as $colIdx => $val) {
            if ($val !== null && $val !== '') {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $formatted[] = "$colLetter: $val";
            }
        }
        echo "Row $row: " . implode(" | ", $formatted) . "\n";
    }
}
