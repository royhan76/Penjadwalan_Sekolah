<?php
// scratch/inspect-all-google-sheets.php
require_once dirname(__DIR__) . '/app/config.php';
use App\GoogleSheet;

$sheet = new GoogleSheet();
$sheets = ['Guru', 'Mapel', 'Kelas', 'Jam', 'TahunPelajaran'];

foreach ($sheets as $sName) {
    echo "=== Sheet Google: $sName ===\n";
    $rows = $sheet->get($sName);
    if (empty($rows)) {
        echo "(Kosong)\n";
    } else {
        foreach (array_slice($rows, 0, 10) as $idx => $row) {
            echo "Row $idx: " . json_encode($row) . "\n";
        }
    }
    echo "\n";
}
