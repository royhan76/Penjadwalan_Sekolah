# SDD Teknis - Sistem Penjadwalan Sekolah (PHP Native + Google Spreadsheet)

## 1. Tujuan

Membangun aplikasi penjadwalan sekolah berbasis PHP Native yang: -
Mengelola Guru, Kelas, Mata Pelajaran, Jam, Tahun Pelajaran. - Menyusun
jadwal tanpa bentrok. - Menyimpan data di Google Spreadsheet. -
Mengekspor jadwal ke Excel sesuai template. - Deploy di Vercel.

------------------------------------------------------------------------

# Arsitektur

User → PHP Native → Google Sheets API → Export Excel (PhpSpreadsheet)

Tidak menggunakan database SQL.

------------------------------------------------------------------------

# Struktur Proyek

    project/
    │
    ├── public/
    │   ├── index.php
    │   ├── login.php
    │   ├── dashboard.php
    │   └── export.php
    │
    ├── app/
    │   ├── Controllers/
    │   ├── Services/
    │   ├── Validators/
    │   ├── Helpers/
    │   └── GoogleSheet.php
    │
    ├── templates/
    │   └── jadwal.xlsx
    │
    ├── vendor/
    └── composer.json

------------------------------------------------------------------------

# FASE 1 - Analisis

Target: - Identifikasi seluruh data master. - Finalisasi format Excel. -
Definisikan seluruh aturan bentrok.

Output: - Dokumen kebutuhan. - Struktur spreadsheet.

Checklist: - Guru - Mata Pelajaran - Kelas - Hari - Jam - Tahun
Pelajaran - Semester

------------------------------------------------------------------------

# FASE 2 - Desain Spreadsheet

Buat Sheet:

## Guru

ID \| Nama \| Status

## Mapel

ID \| Nama \| Jam/Minggu

## Kelas

ID \| Nama

## Jam

ID \| Mulai \| Selesai

## Jadwal

Hari \| Jam \| Kelas \| Guru \| Mapel

Output: Spreadsheet siap dipakai sebagai database.

------------------------------------------------------------------------

# FASE 3 - Integrasi Google Sheets

Task: - Konfigurasi Service Account - Implementasi baca data - Tambah
data - Edit data - Hapus data

Output: Library Google Sheets siap dipakai.

------------------------------------------------------------------------

# FASE 4 - Master Data

Menu: - Guru - Kelas - Mapel - Jam Pelajaran - Tahun Pelajaran

CRUD penuh ke Google Spreadsheet.

------------------------------------------------------------------------

# FASE 5 - Engine Validasi

Rule wajib:

1.  Hari + Jam + Kelas unik.
2.  Hari + Jam + Guru unik.
3.  Jam mapel tidak melebihi alokasi.
4.  Jam pelajaran valid.
5.  Data wajib lengkap.

Jika gagal: - tampilkan pesan spesifik.

Output: Validator reusable.

------------------------------------------------------------------------

# FASE 6 - Modul Penjadwalan

UI berupa grid:

Jam x Hari

Klik sel kosong: - pilih guru - pilih mapel - simpan

Alur: 1. Validasi. 2. Simpan ke Spreadsheet. 3. Refresh grid.

------------------------------------------------------------------------

# FASE 7 - Dashboard

Widget: - Total Guru - Total Kelas - Total Mapel - Total Jadwal - Jadwal
belum lengkap

------------------------------------------------------------------------

# FASE 8 - Export Excel

Gunakan template.

Langkah: 1. Load template. 2. Isi identitas sekolah. 3. Isi seluruh
jadwal. 4. Pertahankan merge, border, warna. 5. Download XLSX.

------------------------------------------------------------------------

# FASE 9 - Pengujian

Uji: - Tambah jadwal normal. - Guru bentrok. - Kelas bentrok. - Alokasi
jam berlebih. - Export. - Edit. - Hapus.

------------------------------------------------------------------------

# FASE 10 - Deploy

Deploy: - Vercel - Environment Variable - Google Credential -
Spreadsheet ID

------------------------------------------------------------------------

# Struktur Service

GoogleSheetService - get() - insert() - update() - delete()

ValidatorService - validateTeacherConflict() - validateClassConflict() -
validateHourLimit()

ExportService - loadTemplate() - fillSchedule() - download()

------------------------------------------------------------------------

# Roadmap Pengerjaan

1.  Setup proyek
2.  Integrasi Google Sheets
3.  CRUD master data
4.  Validasi bentrok
5.  Grid penjadwalan
6.  Export Excel
7.  Testing
8.  Deploy

------------------------------------------------------------------------

# Definition of Done

-   Semua CRUD berjalan.
-   Tidak ada bentrok guru.
-   Tidak ada bentrok kelas.
-   Export identik dengan template.
-   Deploy di Vercel.
-   Seluruh data berasal dari Google Spreadsheet.
