<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Sistem Penjadwalan Sekolah</title>
    <meta name="description" content="Dashboard overview sistem penjadwalan sekolah">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="page-bg">
    <div class="bg-orb orb-top-right"></div>
    <div class="bg-orb orb-bottom-left"></div>
</div>

<main class="main-content">
    <div class="page-header animate-fadeInDown">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Selamat datang, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong> 👋</p>
        </div>
        <?php if ($currentTp): ?>
        <div class="tp-badge glass-card">
            <span class="badge-dot active-dot"></span>
            <span><?= htmlspecialchars($currentTp['Nama']) ?> — Semester <?= htmlspecialchars($currentTp['Semester']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card glass-card animate-fadeInUp" style="--delay: 0.1s">
            <div class="stat-icon icon-blue">
                <svg viewBox="0 0 24 24" fill="none" width="24" height="24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Guru</span>
                <span class="stat-value counter" data-target="<?= $stats['total_guru'] ?>"><?= $stats['total_guru'] ?></span>
            </div>
            <a href="/guru.php" class="stat-action">Lihat semua →</a>
        </div>

        <div class="stat-card glass-card animate-fadeInUp" style="--delay: 0.15s">
            <div class="stat-icon icon-purple">
                <svg viewBox="0 0 24 24" fill="none" width="24" height="24">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Kelas</span>
                <span class="stat-value counter" data-target="<?= $stats['total_kelas'] ?>"><?= $stats['total_kelas'] ?></span>
            </div>
            <a href="/kelas.php" class="stat-action">Lihat semua →</a>
        </div>

        <div class="stat-card glass-card animate-fadeInUp" style="--delay: 0.2s">
            <div class="stat-icon icon-green">
                <svg viewBox="0 0 24 24" fill="none" width="24" height="24">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Mata Pelajaran</span>
                <span class="stat-value counter" data-target="<?= $stats['total_mapel'] ?>"><?= $stats['total_mapel'] ?></span>
            </div>
            <a href="/mapel.php" class="stat-action">Lihat semua →</a>
        </div>

        <div class="stat-card glass-card animate-fadeInUp" style="--delay: 0.25s">
            <div class="stat-icon icon-orange">
                <svg viewBox="0 0 24 24" fill="none" width="24" height="24">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                    <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Jadwal</span>
                <span class="stat-value counter" data-target="<?= $stats['total_jadwal'] ?>"><?= $stats['total_jadwal'] ?></span>
            </div>
            <a href="/jadwal.php" class="stat-action">Kelola →</a>
        </div>
    </div>

    <!-- Content Row -->
    <div class="content-row">
        <!-- Completion Status -->
        <div class="content-card glass-card animate-fadeInUp" style="--delay: 0.3s">
            <div class="card-header">
                <h2 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Status Pengisian Jadwal per Kelas
                </h2>
            </div>
            <div class="card-body">
                <?php if (empty($completionData)): ?>
                <div class="empty-state">
                    <p>Belum ada data kelas. <a href="/kelas.php">Tambah kelas</a></p>
                </div>
                <?php else: ?>
                <div class="completion-list">
                    <?php foreach ($completionData as $item): ?>
                    <div class="completion-item">
                        <div class="completion-header">
                            <span class="completion-name"><?= htmlspecialchars($item['kelas']) ?></span>
                            <div class="completion-meta">
                                <span class="completion-count"><?= $item['filled'] ?>/<?= $item['total'] ?> slot</span>
                                <span class="completion-badge <?= $item['complete'] ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $item['percent'] ?>%
                                </span>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill <?= $item['complete'] ? 'fill-success' : 'fill-primary' ?>" 
                                 style="width: <?= $item['percent'] ?>%"
                                 data-width="<?= $item['percent'] ?>">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Guru Workload -->
        <div class="content-card glass-card animate-fadeInUp" style="--delay: 0.35s">
            <div class="card-header">
                <h2 class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Beban Mengajar Guru (Top 5)
                </h2>
            </div>
            <div class="card-body">
                <?php if (empty($guruWorkload)): ?>
                <div class="empty-state">
                    <p>Belum ada data guru. <a href="/guru.php">Tambah guru</a></p>
                </div>
                <?php else: ?>
                <div class="workload-list">
                    <?php $maxJam = max(array_column($guruWorkload, 'jam') ?: [1]); ?>
                    <?php foreach ($guruWorkload as $i => $gw): ?>
                    <div class="workload-item">
                        <div class="workload-rank"><?= $i + 1 ?></div>
                        <div class="workload-info">
                            <span class="workload-name"><?= htmlspecialchars($gw['nama']) ?></span>
                            <div class="progress-bar">
                                <div class="progress-fill fill-blue" 
                                     style="width: <?= $maxJam > 0 ? round(($gw['jam'] / $maxJam) * 100) : 0 ?>%">
                                </div>
                            </div>
                        </div>
                        <span class="workload-count"><?= $gw['jam'] ?> jam</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions animate-fadeInUp" style="--delay: 0.4s">
        <h2 class="section-title">Aksi Cepat</h2>
        <div class="action-grid">
            <a href="/setup.php" class="action-card glass-card">
                <div class="action-icon icon-blue">⚙️</div>
                <span>Inisialisasi Sheet</span>
            </a>
            <a href="/jadwal.php" class="action-card glass-card">
                <div class="action-icon icon-purple">📅</div>
                <span>Input Jadwal</span>
            </a>
            <a href="/guru.php" class="action-card glass-card">
                <div class="action-icon icon-green">👨‍🏫</div>
                <span>Tambah Guru</span>
            </a>
            <?php if ($currentTp && !empty($kelas)): ?>
            <a href="/export.php?tp_id=<?= htmlspecialchars($currentTp['ID']) ?>&kelas_id=<?= htmlspecialchars($kelas[0]['ID'] ?? '') ?>" class="action-card glass-card">
                <div class="action-icon icon-orange">📥</div>
                <span>Export Excel</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="/assets/js/app.js"></script>
</body>
</html>
