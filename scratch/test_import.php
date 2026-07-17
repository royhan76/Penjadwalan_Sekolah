<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__ . '/../jadwal.xlsx';

if (!file_exists($filePath)) {
    die("File not found: $filePath\n");
}

$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();

// 1. Parse Tahun Pelajaran
$tpCell = $sheet->getCell('A3')->getValue();
$tpName = '';
if ($tpCell && preg_match('/TAHUN PELAJARAN\s+([0-9\/]+)/i', $tpCell, $matches)) {
    $tpName = trim($matches[1]);
} else {
    $tpName = '2026/2027'; // fallback
}
echo "Parsed Tahun Pelajaran: $tpName\n";

// 2. Parse Master Codes (columns Y, Z, AA, AB)
$codeMap = [];
for ($row = 5; $row <= 100; $row++) {
    $code = $sheet->getCell("Y$row")->getValue();
    $guru = $sheet->getCell("Z$row")->getValue();
    $mapel = $sheet->getCell("AA$row")->getValue();
    $jam = $sheet->getCell("AB$row")->getValue();
    
    if ($code !== null && trim($code) !== '' && $guru !== null && trim($guru) !== '') {
        $codeMap[trim($code)] = [
            'guru' => trim($guru),
            'mapel' => trim($mapel),
            'jam' => $jam ? (int)$jam : 0
        ];
    }
}
echo "Parsed " . count($codeMap) . " master codes. Examples:\n";
$exCount = 0;
foreach ($codeMap as $code => $data) {
    echo "- $code => Guru: {$data['guru']} | Mapel: {$data['mapel']} | Jam/Minggu: {$data['jam']}\n";
    if (++$exCount >= 5) break;
}

// 3. Define the Day Blocks
$dayBlocks = [
    // Block 1 (Header row = 6, schedule rows = [7,8,9,10, 12,13,14, 16,17])
    [
        'header_row' => 6,
        'schedule_rows' => [
            7  => '1',
            8  => '2',
            9  => '3',
            10 => '4',
            12 => '5',
            13 => '6',
            14 => '7',
            16 => '8',
            17 => '9',
        ],
        'days' => [
            'Sabtu' => [
                'start_col' => 3, // C
                'end_col' => 8,   // H
            ],
            'Ahad' => [
                'start_col' => 10, // J
                'end_col' => 16,   // P
            ],
            'Senin' => [
                'start_col' => 18, // R
                'end_col' => 23,   // W
            ],
        ]
    ],
    // Block 2 (Header row = 20, schedule rows = [21,22,23,24, 26,27,28, 30,31])
    [
        'header_row' => 20,
        'schedule_rows' => [
            21 => '1',
            22 => '2',
            23 => '3',
            24 => '4',
            26 => '5',
            27 => '6',
            28 => '7',
            30 => '8',
            31 => '9',
        ],
        'days' => [
            'Selasa' => [
                'start_col' => 3, // C
                'end_col' => 8,   // H
            ],
            'Rabu' => [
                'start_col' => 10, // J
                'end_col' => 16,   // P
            ],
            'Kamis' => [
                'start_col' => 18, // R
                'end_col' => 23,   // W
            ],
        ]
    ]
];

// Let's parse all schedule entries
$parsedSchedules = [];

foreach ($dayBlocks as $block) {
    $headerRow = $block['header_row'];
    $rows = $block['schedule_rows'];
    
    foreach ($block['days'] as $day => $cols) {
        $startCol = $cols['start_col'];
        $endCol = $cols['end_col'];
        
        // Loop columns for classes
        for ($c = $startCol; $c <= $endCol; $c++) {
            $classVal = $sheet->getCell([$c, $headerRow])->getValue();
            if ($classVal === null || trim($classVal) === '') {
                continue; // Skip spacer columns (like U)
            }
            $className = trim($classVal);
            
            // Loop through schedule rows
            foreach ($rows as $r => $jamId) {
                $cellVal = $sheet->getCell([$c, $r])->getValue();
                if ($cellVal !== null && trim($cellVal) !== '') {
                    $val = trim($cellVal);
                    
                    // Is this a code or a direct activity name?
                    if (isset($codeMap[$val])) {
                        $parsedSchedules[] = [
                            'day' => $day,
                            'jam_id' => $jamId,
                            'class' => $className,
                            'code' => $val,
                            'guru' => $codeMap[$val]['guru'],
                            'mapel' => $codeMap[$val]['mapel']
                        ];
                    } else {
                        // It is a direct text like "UPACARA" or "PRAMUKA" or "TAHLIL"
                        // For teacher: check the row below it (if it's not a Jam row and has text)
                        $teacherVal = '';
                        $rowBelow = $r + 1;
                        // Let's see if the row below is not a Jam row, is within limits, and has value
                        if (!isset($rows[$rowBelow]) && $rowBelow <= 34) {
                            $belowVal = $sheet->getCell([$c, $rowBelow])->getValue();
                            if ($belowVal !== null && trim($belowVal) !== '' && !str_contains(strtolower($belowVal), 'istirahat')) {
                                $teacherVal = trim($belowVal);
                            }
                        }
                        
                        $parsedSchedules[] = [
                            'day' => $day,
                            'jam_id' => $jamId,
                            'class' => $className,
                            'code' => '',
                            'guru' => $teacherVal ?: '-',
                            'mapel' => $val
                        ];
                    }
                }
            }
        }
    }
}

echo "\nParsed Total Schedules: " . count($parsedSchedules) . "\n";
echo "First 15 parsed schedule entries:\n";
for ($i = 0; $i < min(15, count($parsedSchedules)); $i++) {
    $s = $parsedSchedules[$i];
    echo "- Day: {$s['day']} | Jam: {$s['jam_id']} | Class: {$s['class']} | Mapel: {$s['mapel']} | Guru: {$s['guru']} (Code: {$s['code']})\n";
}
