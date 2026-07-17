# Sistem Penjadwalan Sekolah
> PHP Native + Google Sheets API + PhpSpreadsheet

Aplikasi penjadwalan sekolah berbasis web menggunakan PHP murni, Google Spreadsheet sebagai database, dan export Excel.

---

## 🚀 Cara Setup Lokal

### 1. Clone & Install Dependencies

```bash
git clone <repo>
cd sdd_penjadwalan
composer install
```

### 2. Konfigurasi `.env`

```bash
cp .env.example .env
```

Edit `.env`:
```env
GOOGLE_SERVICE_ACCOUNT_JSON=jadwal-sekolah-502704-38aa5ab2c1cb.json
SPREADSHEET_ID=your_spreadsheet_id_here
APP_NAME=Sistem Penjadwalan Sekolah
SESSION_SECRET=random_secret_minimum_32_chars
ADMIN_USERNAME=admin
ADMIN_PASSWORD=admin123
```

### 3. Google Service Account

1. Buat Service Account di [Google Cloud Console](https://console.cloud.google.com/)
2. Enable **Google Sheets API**
3. Download credentials JSON → simpan di root project
4. Share Spreadsheet ke email Service Account (Editor)

### 4. Jalankan Development Server

```bash
php -S localhost:8000 -t public
```

Buka: http://localhost:8000

### 5. Setup Spreadsheet

Buka http://localhost:8000/setup.php → akan otomatis buat sheet-sheet yang diperlukan.

### 6. Generate Template Excel

Buka http://localhost:8000/generate-template.php → akan buat `templates/jadwal.xlsx`.

---

## 📁 Struktur Project

```
sdd_penjadwalan/
├── api/
│   └── index.php              ← Vercel entry point
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── JadwalController.php
│   │   └── MasterController.php
│   ├── Services/
│   │   ├── ExportService.php
│   │   └── ValidatorService.php
│   ├── GoogleSheet.php
│   └── config.php
├── public/
│   ├── assets/
│   │   ├── css/app.css
│   │   └── js/app.js
│   ├── views/
│   │   ├── master/            ← CRUD views
│   │   ├── partials/navbar.php
│   │   ├── login.view.php
│   │   ├── dashboard.view.php
│   │   └── jadwal.view.php
│   ├── index.php              ← Main router
│   ├── login.php
│   ├── dashboard.php
│   ├── jadwal.php
│   ├── guru.php, kelas.php, mapel.php, jam.php, tahun.php
│   ├── export.php
│   └── setup.php
├── templates/
│   └── jadwal.xlsx            ← Template Excel
├── .env
├── composer.json
└── vercel.json
```

---

## 🔐 Login Default

| Field    | Value     |
|----------|-----------|
| Username | `admin`   |
| Password | `admin123`|

> Ubah di `.env` sebelum deploy production!

---

## 📊 Struktur Google Spreadsheet

| Sheet          | Kolom                                            |
|----------------|--------------------------------------------------|
| Guru           | ID, Nama, Status                                 |
| Mapel          | ID, Nama, JamPerminggu                           |
| Kelas          | ID, Nama                                         |
| Jam            | ID, Label, Mulai, Selesai                        |
| TahunPelajaran | ID, Nama, Semester, Aktif                        |
| Jadwal         | ID, TahunPelajaranID, Hari, JamID, KelasID, GuruID, MapelID |

---

## 🌐 Deploy ke Vercel

```bash
vercel --prod
```

Set Environment Variables di Vercel Dashboard:
- `SPREADSHEET_ID`
- `GOOGLE_SERVICE_ACCOUNT_JSON` (isi seluruh konten JSON)
- `SESSION_SECRET`
- `ADMIN_USERNAME`
- `ADMIN_PASSWORD`

---

## ✅ Fitur

- [x] Login / Logout
- [x] Dashboard dengan statistik
- [x] CRUD Guru, Kelas, Mapel, Jam, Tahun Pelajaran
- [x] Grid jadwal interaktif (klik untuk tambah/edit/hapus)
- [x] Validasi bentrok guru & kelas
- [x] Validasi alokasi jam per minggu
- [x] Export Excel per kelas
- [x] Export Excel semua kelas (multi-sheet)
- [x] Deploy Vercel
