<?php
// app/Services/ValidatorService.php - Validation Engine

namespace App\Services;

use App\GoogleSheet;

class ValidatorService
{
    private GoogleSheet $sheet;

    public function __construct(GoogleSheet $sheet)
    {
        $this->sheet = $sheet;
    }

    /**
     * Rule 1: Hari + Jam + Guru harus unik (guru tidak boleh ngajar dua kelas bersamaan)
     */
    public function validateTeacherConflict(string $hari, string $jamId, string $guruId, ?string $excludeId = null): array
    {
        $jadwal = $this->sheet->get('Jadwal');
        
        foreach ($jadwal as $row) {
            if ($excludeId && $row['ID'] === $excludeId) continue;
            
            if ($row['Hari'] === $hari && $row['JamID'] === $jamId && $row['GuruID'] === $guruId) {
                $guru = $this->sheet->findBy('Guru', 'ID', $guruId);
                $namaGuru = $guru['Nama'] ?? "Guru ID $guruId";
                return [
                    'valid' => false,
                    'message' => "❌ Konflik: Guru <strong>$namaGuru</strong> sudah mengajar di jam ini pada hari $hari."
                ];
            }
        }
        
        return ['valid' => true];
    }

    /**
     * Rule 2: Hari + Jam + Kelas harus unik (kelas tidak boleh ada dua pelajaran bersamaan)
     */
    public function validateClassConflict(string $hari, string $jamId, string $kelasId, ?string $excludeId = null): array
    {
        $jadwal = $this->sheet->get('Jadwal');
        
        foreach ($jadwal as $row) {
            if ($excludeId && $row['ID'] === $excludeId) continue;
            
            if ($row['Hari'] === $hari && $row['JamID'] === $jamId && $row['KelasID'] === $kelasId) {
                $kelas = $this->sheet->findBy('Kelas', 'ID', $kelasId);
                $namaKelas = $kelas['Nama'] ?? "Kelas ID $kelasId";
                return [
                    'valid' => false,
                    'message' => "❌ Konflik: Kelas <strong>$namaKelas</strong> sudah ada jadwal di jam ini pada hari $hari."
                ];
            }
        }
        
        return ['valid' => true];
    }

    /**
     * Rule 3: Jam mapel tidak boleh melebihi alokasi jam per minggu
     */
    public function validateHourLimit(string $mapelId, string $kelasId, string $tahunPelajaranId, ?string $excludeId = null): array
    {
        $mapel = $this->sheet->findBy('Mapel', 'ID', $mapelId);
        if (!$mapel) {
            return ['valid' => false, 'message' => '❌ Mata pelajaran tidak ditemukan.'];
        }
        
        $alokasi = (int)($mapel['JamPerminggu'] ?? 0);
        if ($alokasi <= 0) {
            return ['valid' => true]; // No limit set
        }
        
        $jadwal = $this->sheet->get('Jadwal');
        $count = 0;
        
        foreach ($jadwal as $row) {
            if ($excludeId && $row['ID'] === $excludeId) continue;
            
            if ($row['MapelID'] === $mapelId && 
                $row['KelasID'] === $kelasId && 
                $row['TahunPelajaranID'] === $tahunPelajaranId) {
                $count++;
            }
        }
        
        if ($count >= $alokasi) {
            $namaMapel = $mapel['Nama'] ?? "Mapel ID $mapelId";
            return [
                'valid' => false,
                'message' => "❌ Alokasi jam <strong>$namaMapel</strong> sudah penuh ($count/$alokasi jam per minggu)."
            ];
        }
        
        return ['valid' => true];
    }

    /**
     * Validate all required fields are present
     */
    public function validateRequired(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                return [
                    'valid' => false,
                    'message' => "❌ Field <strong>$field</strong> wajib diisi."
                ];
            }
        }
        return ['valid' => true];
    }

    /**
     * Run all schedule validations at once
     */
    public function validateSchedule(array $data, ?string $excludeId = null): array
    {
        // Check required fields
        $required = $this->validateRequired($data, ['hari', 'jam_id', 'kelas_id', 'guru_id', 'mapel_id', 'tahun_pelajaran_id']);
        if (!$required['valid']) return $required;

        // Check teacher conflict
        $teacherCheck = $this->validateTeacherConflict($data['hari'], $data['jam_id'], $data['guru_id'], $excludeId);
        if (!$teacherCheck['valid']) return $teacherCheck;

        // Check class conflict
        $classCheck = $this->validateClassConflict($data['hari'], $data['jam_id'], $data['kelas_id'], $excludeId);
        if (!$classCheck['valid']) return $classCheck;

        // Check hour limit
        $hourCheck = $this->validateHourLimit($data['mapel_id'], $data['kelas_id'], $data['tahun_pelajaran_id'], $excludeId);
        if (!$hourCheck['valid']) return $hourCheck;

        return ['valid' => true];
    }
}
