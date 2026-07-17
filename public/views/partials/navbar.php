<?php
// public/views/partials/navbar.php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar glass-nav" id="mainNav">
    <div class="nav-brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            </svg>
        </div>
        <span class="brand-text">Penjadwalan <span class="gradient-text">Sekolah</span></span>
    </div>

    <div class="nav-toggle" id="navToggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
    </div>

    <ul class="nav-links" id="navLinks">
        <li>
            <a href="/dashboard.php" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                    <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                    <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                    <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="2"/>
                </svg>
                Dashboard
            </a>
        </li>
        <li class="nav-dropdown">
            <a href="#" class="nav-link <?= in_array($currentPage, ['guru','kelas','mapel','jam','tahun']) ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Master Data
                <svg viewBox="0 0 24 24" fill="none" width="14" height="14" class="chevron">
                    <polyline points="6 9 12 15 18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </a>
            <ul class="dropdown-menu glass-card">
                <li><a href="/guru.php" class="dropdown-item <?= $currentPage === 'guru' ? 'active' : '' ?>">👨‍🏫 Guru</a></li>
                <li><a href="/kelas.php" class="dropdown-item <?= $currentPage === 'kelas' ? 'active' : '' ?>">🏫 Kelas</a></li>
                <li><a href="/mapel.php" class="dropdown-item <?= $currentPage === 'mapel' ? 'active' : '' ?>">📚 Mata Pelajaran</a></li>
                <li><a href="/jam.php" class="dropdown-item <?= $currentPage === 'jam' ? 'active' : '' ?>">🕐 Jam Pelajaran</a></li>
                <li><a href="/tahun.php" class="dropdown-item <?= $currentPage === 'tahun' ? 'active' : '' ?>">📅 Tahun Pelajaran</a></li>
            </ul>
        </li>
        <li>
            <a href="/jadwal.php" class="nav-link <?= $currentPage === 'jadwal' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                    <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
                </svg>
                Jadwal
            </a>
        </li>
        <li>
            <a href="/logout.php" class="nav-link nav-logout">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <polyline points="16 17 21 12 16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Logout
            </a>
        </li>
    </ul>
</nav>
