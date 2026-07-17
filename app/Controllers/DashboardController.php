<?php
// app/Controllers/DashboardController.php

namespace App\Controllers;

use App\GoogleSheet;

class DashboardController
{
    private GoogleSheet $sheet;

    public function __construct(GoogleSheet $sheet)
    {
        $this->sheet = $sheet;
    }

    public function index(): void
    {
        requireLogin();
        
        try {
            $guru   = $this->sheet->get('Guru');
            $kelas  = $this->sheet->get('Kelas');
            $mapel  = $this->sheet->get('Mapel');
            $jadwal = $this->sheet->get('Jadwal');
            $jam    = $this->sheet->get('Jam');
            $tp     = $this->sheet->get('TahunPelajaran');
            
            // Find active tahun pelajaran
            $aktiveTp = array_filter($tp, fn($t) => strtolower($t['Aktif'] ?? '') === 'ya');
            $aktiveTp = array_values($aktiveTp);
            $currentTp = $aktiveTp[0] ?? ($tp[0] ?? null);
            
            // Statistics
            $stats = [
                'total_guru'   => count($guru),
                'total_kelas'  => count($kelas),
                'total_mapel'  => count($mapel),
                'total_jadwal' => count($jadwal),
                'total_jam'    => count($jam),
            ];
            
            // Completion status per kelas
            $completionData = [];
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            $totalPossiblePerKelas = count($hariList) * count($jam);
            
            foreach ($kelas as $k) {
                $jadwalKelas = array_filter($jadwal, fn($j) => 
                    $j['KelasID'] === $k['ID'] && 
                    ($currentTp ? $j['TahunPelajaranID'] === $currentTp['ID'] : true)
                );
                $filled = count($jadwalKelas);
                $percent = $totalPossiblePerKelas > 0 
                    ? round(($filled / $totalPossiblePerKelas) * 100) 
                    : 0;
                
                $completionData[] = [
                    'kelas'    => $k['Nama'],
                    'filled'   => $filled,
                    'total'    => $totalPossiblePerKelas,
                    'percent'  => $percent,
                    'complete' => $percent >= 80,
                ];
            }
            
            // Guru workload
            $guruWorkload = [];
            foreach ($guru as $g) {
                $jadwalGuru = array_filter($jadwal, fn($j) => $j['GuruID'] === $g['ID']);
                $guruWorkload[] = [
                    'nama' => $g['Nama'],
                    'jam'  => count($jadwalGuru),
                ];
            }
            
            // Sort by jam desc
            usort($guruWorkload, fn($a, $b) => $b['jam'] - $a['jam']);
            $guruWorkload = array_slice($guruWorkload, 0, 5); // top 5
            
            include PUBLIC_PATH . '/views/dashboard.view.php';
        } catch (\Exception $e) {
            $error = $e->getMessage();
            include PUBLIC_PATH . '/views/error.view.php';
        }
    }
}
