<?php
// app/Controllers/JadwalController.php - Schedule Grid & Management

namespace App\Controllers;

use App\GoogleSheet;
use App\Services\ValidatorService;

class JadwalController
{
    private GoogleSheet $sheet;
    private ValidatorService $validator;

    public function __construct(GoogleSheet $sheet)
    {
        $this->sheet = $sheet;
        $this->validator = new ValidatorService($sheet);
        requireLogin();
    }

    public function index(): void
    {
        try {
            $kelas     = $this->sheet->get('Kelas');
            $guru      = $this->sheet->get('Guru');
            $mapel     = $this->sheet->get('Mapel');
            $jam       = $this->sheet->get('Jam');
            $tp        = $this->sheet->get('TahunPelajaran');
            
            // Find active TP
            $aktiveTp = array_filter($tp, fn($t) => strtolower($t['Aktif'] ?? '') === 'ya');
            $aktiveTp = array_values($aktiveTp);
            $currentTp = $aktiveTp[0] ?? ($tp[0] ?? null);
            
            // Selected kelas
            $selectedKelasId = $_GET['kelas_id'] ?? ($kelas[0]['ID'] ?? null);
            $selectedTpId    = $_GET['tp_id'] ?? ($currentTp['ID'] ?? null);
            
            // Get jadwal for selected class and tp
            $jadwalAll = $this->sheet->get('Jadwal');
            $jadwal = array_filter($jadwalAll, fn($j) => 
                $j['KelasID'] === $selectedKelasId && 
                $j['TahunPelajaranID'] === $selectedTpId
            );
            
            // Build lookup: [hari][jamId] => jadwal
            $lookup = [];
            foreach ($jadwal as $j) {
                $lookup[$j['Hari']][$j['JamID']] = $j;
            }
            
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            
            include PUBLIC_PATH . '/views/jadwal.view.php';
        } catch (\Exception $e) {
            $error = $e->getMessage();
            include PUBLIC_PATH . '/views/error.view.php';
        }
    }

    public function store(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $data = [
            'hari'              => trim($_POST['hari'] ?? ''),
            'jam_id'            => trim($_POST['jam_id'] ?? ''),
            'kelas_id'          => trim($_POST['kelas_id'] ?? ''),
            'guru_id'           => trim($_POST['guru_id'] ?? ''),
            'mapel_id'          => trim($_POST['mapel_id'] ?? ''),
            'tahun_pelajaran_id' => trim($_POST['tahun_pelajaran_id'] ?? ''),
        ];

        $validation = $this->validator->validateSchedule($data);
        if (!$validation['valid']) {
            jsonResponse(['success' => false, 'message' => $validation['message']]);
        }

        $id = $this->sheet->nextId('Jadwal');
        $ok = $this->sheet->insert('Jadwal', [
            $id,
            $data['tahun_pelajaran_id'],
            $data['hari'],
            $data['jam_id'],
            $data['kelas_id'],
            $data['guru_id'],
            $data['mapel_id'],
        ]);

        if ($ok) {
            // Return enriched data for UI update
            $guru  = $this->sheet->findBy('Guru', 'ID', $data['guru_id']);
            $mapel = $this->sheet->findBy('Mapel', 'ID', $data['mapel_id']);
            
            jsonResponse([
                'success'   => true,
                'message'   => 'Jadwal berhasil ditambahkan.',
                'jadwal_id' => $id,
                'guru'      => $guru['Nama'] ?? '',
                'mapel'     => $mapel['Nama'] ?? '',
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal menyimpan jadwal.']);
        }
    }

    public function update(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $excludeId = trim($_POST['id'] ?? '');
        $row = (int)($_POST['row'] ?? 0);
        
        $data = [
            'hari'              => trim($_POST['hari'] ?? ''),
            'jam_id'            => trim($_POST['jam_id'] ?? ''),
            'kelas_id'          => trim($_POST['kelas_id'] ?? ''),
            'guru_id'           => trim($_POST['guru_id'] ?? ''),
            'mapel_id'          => trim($_POST['mapel_id'] ?? ''),
            'tahun_pelajaran_id' => trim($_POST['tahun_pelajaran_id'] ?? ''),
        ];

        $validation = $this->validator->validateSchedule($data, $excludeId);
        if (!$validation['valid']) {
            jsonResponse(['success' => false, 'message' => $validation['message']]);
        }

        $ok = $this->sheet->update('Jadwal', $row, [
            $excludeId,
            $data['tahun_pelajaran_id'],
            $data['hari'],
            $data['jam_id'],
            $data['kelas_id'],
            $data['guru_id'],
            $data['mapel_id'],
        ]);

        if ($ok) {
            $guru  = $this->sheet->findBy('Guru', 'ID', $data['guru_id']);
            $mapel = $this->sheet->findBy('Mapel', 'ID', $data['mapel_id']);
            
            jsonResponse([
                'success' => true,
                'message' => 'Jadwal berhasil diperbarui.',
                'guru'    => $guru['Nama'] ?? '',
                'mapel'   => $mapel['Nama'] ?? '',
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Gagal memperbarui jadwal.']);
        }
    }

    public function delete(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        $row = (int)($_POST['row'] ?? 0);
        if (!$row) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid.']);
        }

        $ok = $this->sheet->delete('Jadwal', $row);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Jadwal berhasil dihapus.'] 
            : ['success' => false, 'message' => 'Gagal menghapus jadwal.']
        );
    }

    /**
     * Get cell data for editing
     */
    public function getCell(): void
    {
        $hari     = $_GET['hari'] ?? '';
        $jamId    = $_GET['jam_id'] ?? '';
        $kelasId  = $_GET['kelas_id'] ?? '';
        $tpId     = $_GET['tp_id'] ?? '';
        
        $jadwal = $this->sheet->get('Jadwal');
        
        foreach ($jadwal as $j) {
            if ($j['Hari'] === $hari && $j['JamID'] === $jamId && 
                $j['KelasID'] === $kelasId && $j['TahunPelajaranID'] === $tpId) {
                jsonResponse(['success' => true, 'data' => $j]);
            }
        }
        
        jsonResponse(['success' => false, 'data' => null]);
    }

    /**
     * Import Excel schedules and master data
     */
    public function import(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            jsonResponse(['success' => false, 'message' => 'Gagal mengunggah file. Silakan coba lagi.']);
        }

        $tmpFile = $_FILES['file']['tmp_name'];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();

            // 1. Parse Tahun Pelajaran from cell A3
            $tpCell = $sheet->getCell('A3')->getValue();
            $tpName = '';
            if ($tpCell && preg_match('/TAHUN PELAJARAN\s+([0-9\/]+)/i', $tpCell, $matches)) {
                $tpName = trim($matches[1]);
            } else {
                $tpName = '2026/2027'; // fallback
            }

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

            if (empty($codeMap)) {
                jsonResponse(['success' => false, 'message' => 'Tidak dapat menemukan daftar Guru & Mapel di kolom Y-AB. Pastikan format file sesuai template.']);
            }

            // Define blocks
            $dayBlocks = [
                [
                    'header_row' => 6,
                    'schedule_rows' => [7 => '1', 8 => '2', 9 => '3', 10 => '4', 12 => '5', 13 => '6', 14 => '7', 16 => '8', 17 => '9'],
                    'days' => [
                        'Sabtu' => ['start_col' => 3, 'end_col' => 8],
                        'Ahad' => ['start_col' => 10, 'end_col' => 16],
                        'Senin' => ['start_col' => 18, 'end_col' => 23],
                    ]
                ],
                [
                    'header_row' => 20,
                    'schedule_rows' => [21 => '1', 22 => '2', 23 => '3', 24 => '4', 26 => '5', 27 => '6', 28 => '7', 30 => '8', 31 => '9'],
                    'days' => [
                        'Selasa' => ['start_col' => 3, 'end_col' => 8],
                        'Rabu' => ['start_col' => 10, 'end_col' => 16],
                        'Kamis' => ['start_col' => 18, 'end_col' => 23],
                    ]
                ]
            ];

            // Parse schedules
            $parsedSchedules = [];
            $allClassesFound = [];
            $allJamsFound = [];

            foreach ($dayBlocks as $block) {
                $headerRow = $block['header_row'];
                $rows = $block['schedule_rows'];

                foreach ($block['days'] as $day => $cols) {
                    $startCol = $cols['start_col'];
                    $endCol = $cols['end_col'];

                    for ($c = $startCol; $c <= $endCol; $c++) {
                        $classVal = $sheet->getCell([$c, $headerRow])->getValue();
                        if ($classVal === null || trim($classVal) === '') {
                            continue;
                        }
                        $className = trim($classVal);
                        $allClassesFound[$className] = true;

                        foreach ($rows as $r => $jamId) {
                            $cellVal = $sheet->getCell([$c, $r])->getValue();

                            // Parse time range to populate Jam table
                            $timeVal = $sheet->getCell([2, $r])->getValue(); // Column B
                            if ($timeVal && trim($timeVal) !== '') {
                                $allJamsFound[$jamId] = trim($timeVal);
                            }

                            if ($cellVal !== null && trim($cellVal) !== '') {
                                $val = trim($cellVal);

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
                                    // Activity cell (UPACARA, PRAMUKA, etc.)
                                    // Search teacher name in row below
                                    $teacherVal = '';
                                    $rowBelow = $r + 1;
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

            if (empty($parsedSchedules)) {
                jsonResponse(['success' => false, 'message' => 'Tidak ada jadwal pelajaran yang berhasil di-parse.']);
            }

            // 3. Load database data for lookup
            $tpList = $this->sheet->get('TahunPelajaran');
            $guruList = $this->sheet->get('Guru');
            $mapelList = $this->sheet->get('Mapel');
            $kelasList = $this->sheet->get('Kelas');
            $jamList = $this->sheet->get('Jam');

            // Build lookups
            $tpCache = [];
            foreach ($tpList as $t) {
                $tpCache[trim($t['Nama'])] = $t;
            }

            $guruCache = [];
            foreach ($guruList as $g) {
                $guruCache[strtolower(trim($g['Nama']))] = $g['ID'];
            }

            $mapelCache = [];
            foreach ($mapelList as $m) {
                $mapelCache[strtolower(trim($m['Nama']))] = $m['ID'];
            }

            $kelasCache = [];
            foreach ($kelasList as $k) {
                $kelasCache[strtolower(trim($k['Nama']))] = $k['ID'];
            }

            $jamCache = [];
            foreach ($jamList as $j) {
                $jamCache[$j['ID']] = $j['ID'];
            }

            // 4. Resolve/Create Tahun Pelajaran
            $tpId = '';
            if (isset($tpCache[$tpName])) {
                $tpId = $tpCache[$tpName]['ID'];
            } else {
                $tpId = $this->sheet->nextId('TahunPelajaran');
                // Deactivate other years
                foreach ($tpList as $t) {
                    if (strtolower($t['Aktif'] ?? '') === 'ya') {
                        $this->sheet->update('TahunPelajaran', $t['_row'], [$t['ID'], $t['Nama'], $t['Semester'], 'Tidak']);
                    }
                }
                $this->sheet->insert('TahunPelajaran', [$tpId, $tpName, '1', 'Ya']);
                $tpCache[$tpName] = ['ID' => $tpId, 'Nama' => $tpName, 'Semester' => '1', 'Aktif' => 'Ya'];
            }

            // 5. Resolve/Create Guru, Mapel, Kelas, Jam
            $newGurus = [];
            $newMapels = [];
            $newKelas = [];
            $newJams = [];

            // Add from codeMap
            $nextGuruId = (int)$this->sheet->nextId('Guru');
            $nextMapelId = (int)$this->sheet->nextId('Mapel');
            foreach ($codeMap as $code => $data) {
                $gName = $data['guru'];
                $gNameLower = strtolower($gName);
                if (!isset($guruCache[$gNameLower])) {
                    $newGurus[] = [$nextGuruId, $gName, 'Aktif'];
                    $guruCache[$gNameLower] = (string)$nextGuruId;
                    $nextGuruId++;
                }

                $mName = $data['mapel'];
                $mNameLower = strtolower($mName);
                if (!isset($mapelCache[$mNameLower])) {
                    $newMapels[] = [$nextMapelId, $mName, $data['jam']];
                    $mapelCache[$mNameLower] = (string)$nextMapelId;
                    $nextMapelId++;
                }
            }

            // Add from custom activities
            foreach ($parsedSchedules as $s) {
                $gName = $s['guru'];
                $gNameLower = strtolower($gName);
                if ($gName !== '-' && !isset($guruCache[$gNameLower])) {
                    $newGurus[] = [$nextGuruId, $gName, 'Aktif'];
                    $guruCache[$gNameLower] = (string)$nextGuruId;
                    $nextGuruId++;
                }

                $mName = $s['mapel'];
                $mNameLower = strtolower($mName);
                if (!isset($mapelCache[$mNameLower])) {
                    $newMapels[] = [$nextMapelId, $mName, 0];
                    $mapelCache[$mNameLower] = (string)$nextMapelId;
                    $nextMapelId++;
                }
            }

            // Resolve Classes with normalization
            $nextKelasId = (int)$this->sheet->nextId('Kelas');
            foreach (array_keys($allClassesFound) as $cName) {
                $normalizedName = $cName;
                if ($cName === '11 MIPA' && isset($kelasCache['11 miipa'])) {
                    $normalizedName = '11 MIIPA';
                }

                $cNameLower = strtolower($normalizedName);
                if (!isset($kelasCache[$cNameLower])) {
                    $newKelas[] = [$nextKelasId, $normalizedName];
                    $kelasCache[$cNameLower] = (string)$nextKelasId;
                    $nextKelasId++;
                }
            }

            // Resolve Jam times
            foreach ($allJamsFound as $jId => $timeStr) {
                if (!isset($jamCache[$jId])) {
                    $parts = explode('-', $timeStr);
                    $mulai = isset($parts[0]) ? str_replace('.', ':', trim($parts[0])) : '';
                    $selesai = isset($parts[1]) ? str_replace('.', ':', trim($parts[1])) : '';
                    $newJams[] = [$jId, "jam ke - " . $jId, $mulai, $selesai];
                    $jamCache[$jId] = $jId;
                }
            }

            // Write new master data to Google Sheets
            if (!empty($newGurus)) {
                foreach ($newGurus as $ng) {
                    $this->sheet->insert('Guru', $ng);
                }
            }
            if (!empty($newMapels)) {
                foreach ($newMapels as $nm) {
                    $this->sheet->insert('Mapel', $nm);
                }
            }
            if (!empty($newKelas)) {
                foreach ($newKelas as $nk) {
                    $this->sheet->insert('Kelas', $nk);
                }
            }
            if (!empty($newJams)) {
                foreach ($newJams as $nj) {
                    $this->sheet->insert('Jam', $nj);
                }
            }

            // 6. Overwrite Jadwal
            $existingJadwal = $this->sheet->get('Jadwal');
            $keptJadwal = array_filter($existingJadwal, fn($j) => $j['TahunPelajaranID'] !== $tpId);

            $newJadwalRows = [];
            $nextJadwalId = 1;

            foreach ($keptJadwal as $kj) {
                $newJadwalRows[] = [
                    $nextJadwalId,
                    $kj['TahunPelajaranID'],
                    $kj['Hari'],
                    $kj['JamID'],
                    $kj['KelasID'],
                    $kj['GuruID'],
                    $kj['MapelID']
                ];
                $nextJadwalId++;
            }

            foreach ($parsedSchedules as $ps) {
                $cName = $ps['class'];
                if ($cName === '11 MIPA' && isset($kelasCache['11 miipa'])) {
                    $cName = '11 MIIPA';
                }

                $cId = $kelasCache[strtolower($cName)] ?? '';
                $gId = $ps['guru'] !== '-' ? ($guruCache[strtolower($ps['guru'])] ?? '') : '';
                $mId = $mapelCache[strtolower($ps['mapel'])] ?? '';

                $newJadwalRows[] = [
                    $nextJadwalId,
                    $tpId,
                    $ps['day'],
                    $ps['jam_id'],
                    $cId,
                    $gId,
                    $mId
                ];
                $nextJadwalId++;
            }

            // Clear sheet schedules and write back new ones
            $this->sheet->clear('Jadwal!A2:G10000');
            if (!empty($newJadwalRows)) {
                $this->sheet->updateRange('Jadwal!A2:G' . (count($newJadwalRows) + 1), $newJadwalRows);
            }

            jsonResponse([
                'success' => true,
                'message' => 'Import sukses! Tahun Pelajaran ' . htmlspecialchars($tpName) . ' di-update. ' . count($newGurus) . ' guru baru, ' . count($newMapels) . ' mapel baru, ' . count($newKelas) . ' kelas baru terbuat, dan ' . count($parsedSchedules) . ' slot jadwal berhasil di-import.'
            ]);

        } catch (\Exception $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal meng-import: ' . $e->getMessage()]);
        }
    }
}

