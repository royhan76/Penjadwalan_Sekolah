<?php
// app/Controllers/MasterController.php - CRUD for all master data

namespace App\Controllers;

use App\GoogleSheet;

class MasterController
{
    private GoogleSheet $sheet;

    public function __construct(GoogleSheet $sheet)
    {
        $this->sheet = $sheet;
        requireLogin();
    }

    // ===========================
    // GURU
    // ===========================

    public function guruIndex(): void
    {
        $data = $this->sheet->get('Guru');
        $title = 'Data Guru';
        $page = 'guru';
        include PUBLIC_PATH . '/views/master/guru.view.php';
    }

    public function guruStore(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $nama   = trim($_POST['nama'] ?? '');
        $status = trim($_POST['status'] ?? 'Aktif');
        
        if (empty($nama)) {
            jsonResponse(['success' => false, 'message' => 'Nama guru wajib diisi.']);
        }
        
        $id = $this->sheet->nextId('Guru');
        $ok = $this->sheet->insert('Guru', [$id, $nama, $status]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Guru berhasil ditambahkan.'] 
            : ['success' => false, 'message' => 'Gagal menambahkan guru. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function guruUpdate(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row    = (int)($_POST['row'] ?? 0);
        $id     = trim($_POST['id'] ?? '');
        $nama   = trim($_POST['nama'] ?? '');
        $status = trim($_POST['status'] ?? 'Aktif');
        
        if (!$row || empty($nama)) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid.']);
        }
        
        $ok = $this->sheet->update('Guru', $row, [$id, $nama, $status]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Guru berhasil diperbarui.'] 
            : ['success' => false, 'message' => 'Gagal memperbarui guru. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function guruDelete(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row = (int)($_POST['row'] ?? 0);
        if (!$row) {
            jsonResponse(['success' => false, 'message' => 'Baris tidak valid.']);
        }
        
        $ok = $this->sheet->delete('Guru', $row);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Guru berhasil dihapus.'] 
            : ['success' => false, 'message' => 'Gagal menghapus guru. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    // ===========================
    // MAPEL
    // ===========================

    public function mapelIndex(): void
    {
        $data = $this->sheet->get('Mapel');
        $title = 'Mata Pelajaran';
        $page = 'mapel';
        include PUBLIC_PATH . '/views/master/mapel.view.php';
    }

    public function mapelStore(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $nama = trim($_POST['nama'] ?? '');
        $jam  = (int)($_POST['jam_perminggu'] ?? 0);
        
        if (empty($nama)) {
            jsonResponse(['success' => false, 'message' => 'Nama mapel wajib diisi.']);
        }
        
        $id = $this->sheet->nextId('Mapel');
        $ok = $this->sheet->insert('Mapel', [$id, $nama, $jam]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Mapel berhasil ditambahkan.'] 
            : ['success' => false, 'message' => 'Gagal menambahkan mapel. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function mapelUpdate(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row  = (int)($_POST['row'] ?? 0);
        $id   = trim($_POST['id'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $jam  = (int)($_POST['jam_perminggu'] ?? 0);
        
        if (!$row || empty($nama)) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid.']);
        }
        
        $ok = $this->sheet->update('Mapel', $row, [$id, $nama, $jam]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Mapel berhasil diperbarui.'] 
            : ['success' => false, 'message' => 'Gagal memperbarui mapel. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function mapelDelete(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row = (int)($_POST['row'] ?? 0);
        if (!$row) {
            jsonResponse(['success' => false, 'message' => 'Baris tidak valid.']);
        }
        
        $ok = $this->sheet->delete('Mapel', $row);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Mapel berhasil dihapus.'] 
            : ['success' => false, 'message' => 'Gagal menghapus mapel. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    // ===========================
    // KELAS
    // ===========================

    public function kelasIndex(): void
    {
        $data = $this->sheet->get('Kelas');
        $title = 'Data Kelas';
        $page = 'kelas';
        include PUBLIC_PATH . '/views/master/kelas.view.php';
    }

    public function kelasStore(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $nama = trim($_POST['nama'] ?? '');
        
        if (empty($nama)) {
            jsonResponse(['success' => false, 'message' => 'Nama kelas wajib diisi.']);
        }
        
        $id = $this->sheet->nextId('Kelas');
        $ok = $this->sheet->insert('Kelas', [$id, $nama]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Kelas berhasil ditambahkan.'] 
            : ['success' => false, 'message' => 'Gagal menambahkan kelas. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function kelasUpdate(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row  = (int)($_POST['row'] ?? 0);
        $id   = trim($_POST['id'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        
        if (!$row || empty($nama)) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid.']);
        }
        
        $ok = $this->sheet->update('Kelas', $row, [$id, $nama]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Kelas berhasil diperbarui.'] 
            : ['success' => false, 'message' => 'Gagal memperbarui kelas. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function kelasDelete(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row = (int)($_POST['row'] ?? 0);
        if (!$row) {
            jsonResponse(['success' => false, 'message' => 'Baris tidak valid.']);
        }
        
        $ok = $this->sheet->delete('Kelas', $row);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Kelas berhasil dihapus.'] 
            : ['success' => false, 'message' => 'Gagal menghapus kelas. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    // ===========================
    // JAM PELAJARAN
    // ===========================

    public function jamIndex(): void
    {
        $data = $this->sheet->get('Jam');
        $title = 'Jam Pelajaran';
        $page = 'jam';
        include PUBLIC_PATH . '/views/master/jam.view.php';
    }

    public function jamStore(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $label  = trim($_POST['label'] ?? '');
        $mulai  = trim($_POST['mulai'] ?? '');
        $selesai = trim($_POST['selesai'] ?? '');
        
        if (empty($label) || empty($mulai) || empty($selesai)) {
            jsonResponse(['success' => false, 'message' => 'Semua field jam wajib diisi.']);
        }
        
        $id = $this->sheet->nextId('Jam');
        $ok = $this->sheet->insert('Jam', [$id, $label, $mulai, $selesai]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Jam pelajaran berhasil ditambahkan.'] 
            : ['success' => false, 'message' => 'Gagal menambahkan jam pelajaran. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function jamUpdate(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row     = (int)($_POST['row'] ?? 0);
        $id      = trim($_POST['id'] ?? '');
        $label   = trim($_POST['label'] ?? '');
        $mulai   = trim($_POST['mulai'] ?? '');
        $selesai = trim($_POST['selesai'] ?? '');
        
        if (!$row || empty($label)) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid.']);
        }
        
        $ok = $this->sheet->update('Jam', $row, [$id, $label, $mulai, $selesai]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Jam berhasil diperbarui.'] 
            : ['success' => false, 'message' => 'Gagal memperbarui jam. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function jamDelete(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row = (int)($_POST['row'] ?? 0);
        if (!$row) {
            jsonResponse(['success' => false, 'message' => 'Baris tidak valid.']);
        }
        
        $ok = $this->sheet->delete('Jam', $row);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Jam berhasil dihapus.'] 
            : ['success' => false, 'message' => 'Gagal menghapus jam. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    // ===========================
    // TAHUN PELAJARAN
    // ===========================

    public function tahunIndex(): void
    {
        $data = $this->sheet->get('TahunPelajaran');
        $title = 'Tahun Pelajaran';
        $page = 'tahun';
        include PUBLIC_PATH . '/views/master/tahun.view.php';
    }

    public function tahunStore(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $nama     = trim($_POST['nama'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $aktif    = ($_POST['aktif'] ?? '') === '1' ? 'Ya' : 'Tidak';
        
        if (empty($nama) || empty($semester)) {
            jsonResponse(['success' => false, 'message' => 'Nama dan semester wajib diisi.']);
        }
        
        // If set as active, deactivate others
        if ($aktif === 'Ya') {
            $allTp = $this->sheet->get('TahunPelajaran');
            foreach ($allTp as $tp) {
                if ($tp['Aktif'] === 'Ya') {
                    $this->sheet->update('TahunPelajaran', $tp['_row'], [
                        $tp['ID'], $tp['Nama'], $tp['Semester'], 'Tidak'
                    ]);
                }
            }
        }
        
        $id = $this->sheet->nextId('TahunPelajaran');
        $ok = $this->sheet->insert('TahunPelajaran', [$id, $nama, $semester, $aktif]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Tahun pelajaran berhasil ditambahkan.'] 
            : ['success' => false, 'message' => 'Gagal menambahkan tahun pelajaran. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function tahunUpdate(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row      = (int)($_POST['row'] ?? 0);
        $id       = trim($_POST['id'] ?? '');
        $nama     = trim($_POST['nama'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $aktif    = ($_POST['aktif'] ?? '') === '1' ? 'Ya' : 'Tidak';
        
        if (!$row || empty($nama)) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid.']);
        }
        
        // If set as active, deactivate others first
        if ($aktif === 'Ya') {
            $allTp = $this->sheet->get('TahunPelajaran');
            foreach ($allTp as $tp) {
                if ($tp['Aktif'] === 'Ya' && $tp['ID'] !== $id) {
                    $this->sheet->update('TahunPelajaran', $tp['_row'], [
                        $tp['ID'], $tp['Nama'], $tp['Semester'], 'Tidak'
                    ]);
                }
            }
        }
        
        $ok = $this->sheet->update('TahunPelajaran', $row, [$id, $nama, $semester, $aktif]);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Tahun pelajaran berhasil diperbarui.'] 
            : ['success' => false, 'message' => 'Gagal memperbarui tahun pelajaran. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }

    public function tahunDelete(): void
    {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'Token tidak valid.'], 403);
        }
        
        $row = (int)($_POST['row'] ?? 0);
        if (!$row) {
            jsonResponse(['success' => false, 'message' => 'Baris tidak valid.']);
        }
        
        $ok = $this->sheet->delete('TahunPelajaran', $row);
        
        jsonResponse($ok 
            ? ['success' => true, 'message' => 'Tahun pelajaran berhasil dihapus.'] 
            : ['success' => false, 'message' => 'Gagal menghapus tahun pelajaran. Detail: ' . ($this->sheet->lastError ?? 'Unknown error')]
        );
    }
}
