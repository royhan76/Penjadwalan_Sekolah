<?php
// app/Services/ExportService.php - Excel Export using PhpSpreadsheet

namespace App\Services;

use App\GoogleSheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportService
{
    private GoogleSheet $sheet;
    private string $templatePath;

    public function __construct(GoogleSheet $sheet)
    {
        $this->sheet = $sheet;
        $this->templatePath = dirname(__DIR__, 2) . '/templates/jadwal.xlsx';
    }

    /**
     * Load template file
     */
    private function loadTemplate(): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        if (!file_exists($this->templatePath)) {
            // Create a default template if none exists
            return $this->createDefaultTemplate();
        }
        
        return IOFactory::load($this->templatePath);
    }

    /**
     * Create a default template if none provided
     */
    private function createDefaultTemplate(): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal Pelajaran');
        
        // Header sekolah
        $sheet->setCellValue('A1', 'JADWAL PELAJARAN');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        $sheet->setCellValue('A2', 'Tahun Pelajaran:');
        $sheet->setCellValue('B2', '');
        $sheet->setCellValue('A3', 'Semester:');
        $sheet->setCellValue('B3', '');
        
        return $spreadsheet;
    }

    /**
     * Fill schedule data into spreadsheet
     */
    public function fillSchedule(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, array $jadwalData, array $meta = []): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $ws = $spreadsheet->getActiveSheet();
        
        // Fill meta information if provided
        if (!empty($meta['tahun_pelajaran'])) {
            $ws->setCellValue('B2', $meta['tahun_pelajaran']);
        }
        if (!empty($meta['semester'])) {
            $ws->setCellValue('B3', $meta['semester']);
        }
        if (!empty($meta['kelas'])) {
            $ws->setCellValue('B4', $meta['kelas']);
        }
        
        // Define days and build grid
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $startRow = 6;
        
        // Headers: Jam | Senin | Selasa | ...
        $ws->setCellValue('A' . $startRow, 'Jam');
        foreach ($hari as $i => $h) {
            $col = chr(ord('B') + $i);
            $ws->setCellValue($col . $startRow, $h);
            $ws->getStyle($col . $startRow)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1e3a5f'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ]);
        }
        $ws->getStyle('A' . $startRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1e3a5f'],
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
        ]);
        
        // Get all jam pelajaran
        $jamList = $this->sheet->get('Jam');
        
        // Index jadwal for quick lookup: [hari][jamId] = [guru, mapel]
        $lookup = [];
        foreach ($jadwalData as $j) {
            $lookup[$j['Hari']][$j['JamID']] = $j;
        }
        
        // Fill rows
        foreach ($jamList as $ri => $jam) {
            $row = $startRow + 1 + $ri;
            $label = $jam['Label'] . ' (' . $jam['Mulai'] . '-' . $jam['Selesai'] . ')';
            $ws->setCellValue('A' . $row, $label);
            
            foreach ($hari as $i => $h) {
                $col = chr(ord('B') + $i);
                $jadwal = $lookup[$h][$jam['ID']] ?? null;
                
                if ($jadwal) {
                    $guru = $this->sheet->findBy('Guru', 'ID', $jadwal['GuruID']);
                    $mapel = $this->sheet->findBy('Mapel', 'ID', $jadwal['MapelID']);
                    
                    $cellText = ($mapel['Nama'] ?? '') . "\n" . ($guru['Nama'] ?? '');
                    $ws->setCellValue($col . $row, $cellText);
                    $ws->getStyle($col . $row)->getAlignment()->setWrapText(true);
                }
            }
        }
        
        // Apply borders to the grid
        if (!empty($jamList)) {
            $lastRow = $startRow + count($jamList);
            $lastCol = chr(ord('A') + count($hari));
            $range = "A{$startRow}:{$lastCol}{$lastRow}";
            
            $ws->getStyle($range)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'cccccc'],
                    ],
                ],
            ]);
        }
        
        // Auto-size columns
        foreach (range('A', chr(ord('A') + count($hari))) as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }
        
        return $spreadsheet;
    }

    /**
     * Generate and download the Excel file
     */
    public function download(string $kelasId, string $tahunPelajaranId): void
    {
        // Load all needed data
        $kelas = $this->sheet->findBy('Kelas', 'ID', $kelasId);
        $tp = $this->sheet->findBy('TahunPelajaran', 'ID', $tahunPelajaranId);
        
        $jadwal = $this->sheet->get('Jadwal');
        $jadwalKelas = array_filter($jadwal, fn($j) => 
            $j['KelasID'] === $kelasId && $j['TahunPelajaranID'] === $tahunPelajaranId
        );
        
        $spreadsheet = $this->loadTemplate();
        
        $meta = [
            'tahun_pelajaran' => $tp['Nama'] ?? 'N/A',
            'semester'        => $tp['Semester'] ?? 'N/A',
            'kelas'           => $kelas['Nama'] ?? 'N/A',
        ];
        
        $spreadsheet = $this->fillSchedule($spreadsheet, array_values($jadwalKelas), $meta);
        
        $filename = 'Jadwal_' . ($kelas['Nama'] ?? 'Kelas') . '_' . ($tp['Nama'] ?? '') . '.xlsx';
        $filename = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $filename);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Download full schedule (all kelas) per sheet
     */
    public function downloadAll(string $tahunPelajaranId): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // remove default sheet
        
        $tp = $this->sheet->findBy('TahunPelajaran', 'ID', $tahunPelajaranId);
        $kelasList = $this->sheet->get('Kelas');
        $jadwalAll = $this->sheet->get('Jadwal');
        
        foreach ($kelasList as $kelas) {
            $ws = $spreadsheet->createSheet();
            $ws->setTitle(substr($kelas['Nama'], 0, 31)); // max 31 chars for sheet title
            $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($ws));
            
            $jadwalKelas = array_filter($jadwalAll, fn($j) => 
                $j['KelasID'] === $kelas['ID'] && $j['TahunPelajaranID'] === $tahunPelajaranId
            );
            
            $tempSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $tempSpreadsheet->setActiveSheetIndex(0);
            $tempWs = $tempSpreadsheet->getActiveSheet();
            
            // Copy current worksheet reference
            $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($ws));
            $ws->setTitle(substr($kelas['Nama'], 0, 31));
            
            // Add header
            $ws->setCellValue('A1', 'JADWAL PELAJARAN - ' . strtoupper($kelas['Nama']));
            $ws->mergeCells('A1:H1');
            $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $ws->setCellValue('A2', 'Tahun Pelajaran: ' . ($tp['Nama'] ?? '') . ' | Semester: ' . ($tp['Semester'] ?? ''));
        }
        
        $filename = 'Jadwal_Semua_Kelas_' . ($tp['Nama'] ?? '') . '.xlsx';
        $filename = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $filename);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        
        $spreadsheet->setActiveSheetIndex(0);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Download schedule in its original multi-class side-by-side format (similar to the imported jadwal.xlsx)
     */
    public function downloadOriginalLayout(string $tahunPelajaranId): void
    {
        $spreadsheet = $this->loadTemplate();
        $ws = $spreadsheet->getActiveSheet();

        // 1. Update Tahun Pelajaran Title
        $tp = $this->sheet->findBy('TahunPelajaran', 'ID', $tahunPelajaranId);
        $tpName = $tp['Nama'] ?? '2026/2027';
        $ws->setCellValue('A3', 'TAHUN PELAJARAN ' . $tpName);

        // 2. Clear schedule ranges in memory
        $ranges = [
            'C7:E10', 'G7:H10', 'C12:E14', 'G12:H14', 'C16:E17', 'G16:H17', // Sabtu
            'J7:P10', 'J12:P14', 'J16:P17',                                 // Ahad
            'R7:T10', 'V7:W10', 'R12:T14', 'V12:W14', 'R16:T17', 'V16:W17', // Senin
            'C21:E24', 'G21:H24', 'C26:E28', 'G26:H28', 'C30:E31', 'G30:H31', // Selasa
            'J21:P24', 'J26:P28', 'J30:P31',                                 // Rabu
            'R21:T24', 'V21:W24', 'R26:T28', 'V26:W28', 'R30:T31', 'V30:W31'  // Kamis
        ];

        foreach ($ranges as $range) {
            [$start, $end] = explode(':', $range);
            $startCol = preg_replace('/[0-9]/', '', $start);
            $startRow = (int)preg_replace('/[A-Z]/i', '', $start);
            $endCol = preg_replace('/[0-9]/', '', $end);
            $endRow = (int)preg_replace('/[A-Z]/i', '', $end);

            $startColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startCol);
            $endColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($endCol);

            for ($r = $startRow; $r <= $endRow; $r++) {
                for ($c = $startColIdx; $c <= $endColIdx; $c++) {
                    $ws->setCellValue([$c, $r], null);
                }
            }
        }

        // 3. Scan columns Y, Z, AA of the sheet to build a Code map
        $codeMap = [];
        for ($row = 5; $row <= 100; $row++) {
            $code = $ws->getCell("Y$row")->getValue();
            $guruName = $ws->getCell("Z$row")->getValue();
            $mapelName = $ws->getCell("AA$row")->getValue();

            if ($code !== null && trim($code) !== '' && $guruName !== null && trim($guruName) !== '') {
                $key = strtolower(trim($guruName)) . '|' . strtolower(trim($mapelName));
                $codeMap[$key] = trim($code);
            }
        }

        // 4. Load database schedules and master lookups
        $jadwalAll = $this->sheet->get('Jadwal');
        $jadwal = array_filter($jadwalAll, fn($j) => $j['TahunPelajaranID'] === $tahunPelajaranId);

        $guruList = $this->sheet->get('Guru');
        $guruMap = [];
        foreach ($guruList as $g) {
            $guruMap[$g['ID']] = $g['Nama'];
        }

        $mapelList = $this->sheet->get('Mapel');
        $mapelMap = [];
        foreach ($mapelList as $m) {
            $mapelMap[$m['ID']] = $m['Nama'];
        }

        $kelasList = $this->sheet->get('Kelas');
        $kelasMap = [];
        foreach ($kelasList as $k) {
            $kelasMap[$k['ID']] = $k['Nama'];
        }

        // 5. Day blocks parameters
        $dayBlocks = [
            'Sabtu' => [
                'header_row' => 6, 'start_col' => 3, 'end_col' => 8,
                'rows' => [1 => 7, 2 => 8, 3 => 9, 4 => 10, 5 => 12, 6 => 13, 7 => 14, 8 => 16, 9 => 17]
            ],
            'Ahad' => [
                'header_row' => 6, 'start_col' => 10, 'end_col' => 16,
                'rows' => [1 => 7, 2 => 8, 3 => 9, 4 => 10, 5 => 12, 6 => 13, 7 => 14, 8 => 16, 9 => 17]
            ],
            'Senin' => [
                'header_row' => 6, 'start_col' => 18, 'end_col' => 23,
                'rows' => [1 => 7, 2 => 8, 3 => 9, 4 => 10, 5 => 12, 6 => 13, 7 => 14, 8 => 16, 9 => 17]
            ],
            'Selasa' => [
                'header_row' => 20, 'start_col' => 3, 'end_col' => 8,
                'rows' => [1 => 21, 2 => 22, 3 => 23, 4 => 24, 5 => 26, 6 => 27, 7 => 28, 8 => 30, 9 => 31]
            ],
            'Rabu' => [
                'header_row' => 20, 'start_col' => 10, 'end_col' => 16,
                'rows' => [1 => 21, 2 => 22, 3 => 23, 4 => 24, 5 => 26, 6 => 27, 7 => 28, 8 => 30, 9 => 31]
            ],
            'Kamis' => [
                'header_row' => 20, 'start_col' => 18, 'end_col' => 23,
                'rows' => [1 => 21, 2 => 22, 3 => 23, 4 => 24, 5 => 26, 6 => 27, 7 => 28, 8 => 30, 9 => 31]
            ]
        ];

        // 6. Write schedules back to cell coordinates
        foreach ($jadwal as $j) {
            $day = $j['Hari'];
            $jamId = (int)$j['JamID'];
            $kelasName = $kelasMap[$j['KelasID']] ?? '';
            $guruName = $guruMap[$j['GuruID']] ?? '';
            $mapelName = $mapelMap[$j['MapelID']] ?? '';

            if (!isset($dayBlocks[$day])) {
                continue;
            }

            $block = $dayBlocks[$day];
            $headerRow = $block['header_row'];
            $startCol = $block['start_col'];
            $endCol = $block['end_col'];

            // Find column for this class
            $colIdx = null;
            for ($c = $startCol; $c <= $endCol; $c++) {
                $hVal = $ws->getCell([$c, $headerRow])->getValue();
                if ($hVal !== null && trim($hVal) !== '') {
                    $hValStr = strtolower(trim($hVal));
                    $kNameStr = strtolower(trim($kelasName));

                    if ($hValStr === $kNameStr ||
                        ($kNameStr === '11 miipa' && $hValStr === '11 mipa') ||
                        ($kNameStr === '11 mipa' && $hValStr === '11 miipa')) {
                        $colIdx = $c;
                        break;
                    }
                }
            }

            if ($colIdx === null) {
                continue;
            }

            $rowIdx = $block['rows'][$jamId] ?? null;
            if ($rowIdx === null) {
                continue;
            }

            // Look up code
            $key = strtolower(trim($guruName)) . '|' . strtolower(trim($mapelName));
            if (isset($codeMap[$key])) {
                $cellText = $codeMap[$key];
            } else {
                $cellText = $mapelName;
                // For custom activities, write guru name below if it's not a schedule row
                $rowBelow = $rowIdx + 1;
                if ($guruName && $guruName !== '-' && !isset($block['rows'][$rowBelow])) {
                    $ws->setCellValue([$colIdx, $rowBelow], $guruName);
                }
            }

            $ws->setCellValue([$colIdx, $rowIdx], $cellText);
        }

        // 7. Output to browser
        $filename = 'Jadwal_Lengkap_' . preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $tpName) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}

