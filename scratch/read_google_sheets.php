<?php
require_once __DIR__ . '/../app/config.php';

use App\GoogleSheet;

$sheet = new GoogleSheet();

echo "=== GURU ===\n";
print_r($sheet->get('Guru'));

echo "=== MAPEL ===\n";
print_r($sheet->get('Mapel'));

echo "=== KELAS ===\n";
print_r($sheet->get('Kelas'));

echo "=== JAM ===\n";
print_r($sheet->get('Jam'));

echo "=== TAHUN PELAJARAN ===\n";
print_r($sheet->get('TahunPelajaran'));
