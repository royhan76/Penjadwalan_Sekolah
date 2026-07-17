<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error — Penjadwalan Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
<?php include __DIR__ . '/partials/navbar.php'; ?>
<main class="main-content">
    <div class="error-page glass-card">
        <div class="error-icon">⚠️</div>
        <h1>Terjadi Kesalahan</h1>
        <p class="error-message"><?= htmlspecialchars($error ?? 'Kesalahan tidak diketahui.') ?></p>
        <div class="error-hint">
            <p>Kemungkinan penyebab:</p>
            <ul>
                <li>Google credentials belum dikonfigurasi</li>
                <li>Spreadsheet ID tidak valid</li>
                <li>Service Account tidak memiliki akses ke spreadsheet</li>
            </ul>
        </div>
        <div class="error-actions">
            <a href="/dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
            <a href="/setup.php" class="btn btn-ghost">Inisialisasi Sheet</a>
        </div>
    </div>
</main>
</body></html>
