<?php
// scratch/dump-legend.php
require_once dirname(__DIR__) . '/app/config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = dirname(__DIR__) . '/jadwal.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();

echo "--- DUMPING LEGEND AREA (COLUMNS Y, Z, AA, AB, AC, AD) ---\n";
for ($row = 1; $row <= 60; $row++) {
    $vals = [];
    $hasVal = false;
    foreach (['Y', 'Z', 'AA', 'AB', 'AC', 'AD'] as $col) {
        $val = $sheet->getCell($col . $row)->getValue();
        if ($val !== null && trim((string)$val) !== '') {
            $hasVal = true;
        }
        $vals[$col] = $val === null ? '' : trim((string)$val);
    }
    if ($hasVal) {
        echo "Row $row: Y: '{$vals['Y']}' | Z: '{$vals['Z']}' | AA: '{$vals['AA']}' | AB: '{$vals['AB']}' | AC: '{$vals['AC']}' | AD: '{$vals['AD']}'\n";
    }
}
