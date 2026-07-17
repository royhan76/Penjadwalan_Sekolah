<?php
// scratch/read-excel-structure.php
require_once dirname(__DIR__) . '/app/config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = dirname(__DIR__) . '/jadwal.xlsx';

if (!file_exists($filePath)) {
    die("File jadwal.xlsx tidak ditemukan!\n");
}

echo "Loading jadwal.xlsx...\n";
$spreadsheet = IOFactory::load($filePath);

echo "Daftar sheet dalam file:\n";
$sheetNames = $spreadsheet->getSheetNames();
foreach ($sheetNames as $idx => $name) {
    echo "[$idx] Sheet Name: $name\n";
}

echo "\n--- Membaca Struktur Sheet Pertama: " . $sheetNames[0] . " ---\n";
$sheet = $spreadsheet->getSheet(0);
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

echo "Ukuran grid: A1 to $highestColumn$highestRow\n\n";

echo "Membaca 15 baris pertama:\n";
for ($row = 1; $row <= min(15, $highestRow); $row++) {
    $rowValues = [];
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $cellVal = $sheet->getCell($col . $row)->getValue();
        $rowValues[] = $cellVal === null ? '' : $cellVal;
    }
    echo "Row $row: " . implode(" | ", array_map(fn($v) => (string)$v, $rowValues)) . "\n";
}
