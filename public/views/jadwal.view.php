<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran — Penjadwalan Sekolah</title>
    <meta name="description" content="Grid jadwal pelajaran interaktif">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
<?php include __DIR__ . '/partials/navbar.php'; ?>
<div class="page-bg"><div class="bg-orb orb-top-right"></div><div class="bg-orb orb-bottom-left"></div></div>

<main class="main-content">
    <div class="page-header animate-fadeInDown">
        <div>
            <h1 class="page-title">📅 Jadwal Pelajaran</h1>
            <p class="page-subtitle">Klik sel kosong untuk menambah jadwal • Klik sel terisi untuk mengedit/hapus</p>
        </div>
        <div class="header-actions">
            <?php if ($selectedTpId): ?>
            <a href="/export.php?tp_id=<?= htmlspecialchars($selectedTpId) ?>&type=original" 
               class="btn btn-success" target="_blank" title="Export seluruh kelas dalam format grid asli (seperti jadwal.xlsx)">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18" style="stroke: currentColor; stroke-dasharray: none; stroke-linecap: round; stroke-linejoin: round;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2"/>
                    <polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="2"/>
                    <line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2"/>
                </svg>
                Export Format Asli (Lengkap)
            </a>
            <?php endif; ?>

            <?php if ($selectedKelasId && $selectedTpId): ?>
            <a href="/export.php?kelas_id=<?= htmlspecialchars($selectedKelasId) ?>&tp_id=<?= htmlspecialchars($selectedTpId) ?>&type=single" 
               class="btn btn-ghost" target="_blank" title="Export jadwal kelas yang sedang aktif">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18" style="stroke: currentColor; stroke-dasharray: none; stroke-linecap: round; stroke-linejoin: round;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2"/>
                    <polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="2"/>
                    <line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2"/>
                </svg>
                Export Kelas Ini
            </a>
            <?php endif; ?>

            <button class="btn btn-primary" onclick="openImportModal()">
                <svg viewBox="0 0 24 24" fill="none" width="18" height="18" style="stroke: currentColor; stroke-dasharray: none; stroke-linecap: round; stroke-linejoin: round;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2"/>
                    <polyline points="17 8 12 3 7 8" stroke="currentColor" stroke-width="2"/>
                    <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2"/>
                </svg>
                Import Excel
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar glass-card animate-fadeInDown" style="--delay: 0.1s">
        <form method="GET" action="/jadwal.php" class="filter-form">
            <div class="filter-group">
                <label class="filter-label">Tahun Pelajaran</label>
                <select name="tp_id" class="form-control form-select" onchange="this.form.submit()">
                    <?php foreach ($tp as $t): ?>
                    <option value="<?= htmlspecialchars($t['ID']) ?>" <?= $t['ID'] === $selectedTpId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['Nama']) ?> — Sem <?= htmlspecialchars($t['Semester']) ?>
                        <?= strtolower($t['Aktif'] ?? '') === 'ya' ? ' ✅' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Kelas</label>
                <select name="kelas_id" class="form-control form-select" onchange="this.form.submit()">
                    <?php foreach ($kelas as $k): ?>
                    <option value="<?= htmlspecialchars($k['ID']) ?>" <?= $k['ID'] === $selectedKelasId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['Nama']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-stats">
                <span class="filter-badge">
                    <?= count($jadwal) ?> slot terisi
                </span>
            </div>
        </form>
    </div>

    <div id="alertBox"></div>

    <!-- Schedule Grid -->
    <div class="schedule-wrapper animate-fadeInUp" style="--delay: 0.2s">
        <?php if (empty($jam)): ?>
        <div class="glass-card empty-state-big">
            <div class="empty-icon">🕐</div>
            <h3>Belum ada jam pelajaran</h3>
            <p>Tambahkan jam pelajaran terlebih dahulu sebelum membuat jadwal.</p>
            <a href="/jam.php" class="btn btn-primary">Tambah Jam Pelajaran</a>
        </div>
        <?php elseif (empty($kelas)): ?>
        <div class="glass-card empty-state-big">
            <div class="empty-icon">🏫</div>
            <h3>Belum ada kelas</h3>
            <p>Tambahkan data kelas terlebih dahulu.</p>
            <a href="/kelas.php" class="btn btn-primary">Tambah Kelas</a>
        </div>
        <?php else: ?>
        <div class="schedule-scroll">
            <table class="schedule-table" id="scheduleTable">
                <thead>
                    <tr>
                        <th class="jam-header">Jam / Hari</th>
                        <?php foreach ($hariList as $hari): ?>
                        <th class="day-header">
                            <span class="day-name"><?= $hari ?></span>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jam as $j): ?>
                    <tr>
                        <td class="jam-cell">
                            <div class="jam-label"><?= htmlspecialchars($j['Label']) ?></div>
                            <div class="jam-time"><?= htmlspecialchars($j['Mulai']) ?>–<?= htmlspecialchars($j['Selesai']) ?></div>
                        </td>
                        <?php foreach ($hariList as $hari): ?>
                        <?php 
                        $jadwalCell = $lookup[$hari][$j['ID']] ?? null;
                        $guruData = $jadwalCell ? array_filter($guru, fn($g) => $g['ID'] === $jadwalCell['GuruID']) : [];
                        $guruData = array_values($guruData);
                        $mapelData = $jadwalCell ? array_filter($mapel, fn($m) => $m['ID'] === $jadwalCell['MapelID']) : [];
                        $mapelData = array_values($mapelData);
                        ?>
                        <td class="schedule-cell <?= $jadwalCell ? 'has-schedule' : 'empty-cell' ?>"
                            data-hari="<?= htmlspecialchars($hari) ?>"
                            data-jam-id="<?= htmlspecialchars($j['ID']) ?>"
                            data-kelas-id="<?= htmlspecialchars($selectedKelasId) ?>"
                            data-tp-id="<?= htmlspecialchars($selectedTpId) ?>"
                            <?php if ($jadwalCell): ?>
                            data-jadwal='<?= htmlspecialchars(json_encode($jadwalCell)) ?>'
                            <?php endif; ?>
                            onclick="handleCellClick(this)">
                            <?php if ($jadwalCell): ?>
                            <div class="cell-content">
                                <div class="cell-mapel"><?= htmlspecialchars($mapelData[0]['Nama'] ?? '-') ?></div>
                                <div class="cell-guru"><?= htmlspecialchars($guruData[0]['Nama'] ?? '-') ?></div>
                            </div>
                            <?php else: ?>
                            <div class="cell-add">
                                <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
                                    <line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Legend -->
    <div class="schedule-legend animate-fadeInUp" style="--delay: 0.3s">
        <div class="legend-item"><div class="legend-box filled"></div><span>Ada Jadwal</span></div>
        <div class="legend-item"><div class="legend-box empty"></div><span>Kosong (klik untuk tambah)</span></div>
    </div>
</main>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="modal glass-card schedule-modal" id="mainModal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Jadwal</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-context" id="modalContext"></div>
        <form id="scheduleForm" onsubmit="submitSchedule(event)">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" id="sAction" value="store">
            <input type="hidden" name="hari" id="sHari">
            <input type="hidden" name="jam_id" id="sJamId">
            <input type="hidden" name="kelas_id" id="sKelasId">
            <input type="hidden" name="tahun_pelajaran_id" id="sTpId">
            <input type="hidden" name="id" id="sId">
            <input type="hidden" name="row" id="sRow">

            <div class="form-group">
                <label class="form-label">Guru <span class="required">*</span></label>
                <select id="sGuru" name="guru_id" class="form-control form-select" required>
                    <option value="">-- Pilih Guru --</option>
                    <?php foreach ($guru as $g): ?>
                    <?php if (strtolower($g['Status'] ?? 'aktif') === 'aktif'): ?>
                    <option value="<?= htmlspecialchars($g['ID']) ?>"><?= htmlspecialchars($g['Nama']) ?></option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Mata Pelajaran <span class="required">*</span></label>
                <select id="sMapel" name="mapel_id" class="form-control form-select" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php foreach ($mapel as $m): ?>
                    <option value="<?= htmlspecialchars($m['ID']) ?>"><?= htmlspecialchars($m['Nama']) ?> (<?= htmlspecialchars($m['JamPerminggu']) ?> jam/minggu)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Batal</button>
                <div style="display:flex;gap:0.5rem">
                    <button type="button" class="btn btn-danger" id="btnDelete" style="display:none" onclick="deleteSchedule()">🗑️ Hapus</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span id="submitText">Simpan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div class="modal-overlay" id="importModalOverlay" onclick="closeImportModal(event)">
    <div class="modal glass-card schedule-modal" id="importModal" style="max-width: 500px">
        <div class="modal-header">
            <h3 class="modal-title">📥 Import Jadwal Excel</h3>
            <button class="modal-close" onclick="closeImportModal()">✕</button>
        </div>
        <div class="modal-context">
            <p style="margin-bottom: 0.75rem; font-size: 0.9rem; opacity: 0.85; line-height: 1.4">
                Pilih file Excel (<code>.xlsx</code>) yang memiliki format layout grid jadwal sekolah asli. Sistem akan membaca:
            </p>
            <ul style="margin-left: 1.25rem; font-size: 0.85rem; opacity: 0.8; line-height: 1.4; margin-bottom: 1rem">
                <li>Tahun Pelajaran dari cell A3 (misal: <code>2026/2027</code>).</li>
                <li>Daftar Guru & Mapel di kolom Y-AB (KODE, NAMA GURU, MATA PELAJARAN).</li>
                <li>Seluruh jadwal kelas berdasarkan kode di kolom C-H, J-P, R-W.</li>
            </ul>
            <p style="font-size: 0.85rem; color: #ffb703; font-weight: 500; margin-bottom: 1rem">
                ⚠️ PERINGATAN: Jadwal yang ada pada Tahun Pelajaran tersebut di Google Sheet akan ditimpa (di-overwrite) dengan data baru dari file Excel ini.
            </p>
        </div>
        <form id="importForm" onsubmit="submitImport(event)" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="import">
            
            <div class="form-group">
                <label class="form-label">File Excel (.xlsx) <span class="required">*</span></label>
                <input type="file" name="file" accept=".xlsx" class="form-control" required style="padding: 0.5rem">
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeImportModal()">Batal</button>
                <button type="submit" class="btn btn-primary" id="importSubmitBtn">
                    <span>Mulai Import</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
const ENDPOINT = '/jadwal.php';
let currentCell = null;
let isEditMode = false;

function handleCellClick(cell) {
    currentCell = cell;
    const hari    = cell.dataset.hari;
    const jamId   = cell.dataset.jamId;
    const kelasId = cell.dataset.kelasId;
    const tpId    = cell.dataset.tpId;
    const jadwal  = cell.dataset.jadwal ? JSON.parse(cell.dataset.jadwal) : null;
    
    // Fill hidden fields
    document.getElementById('sHari').value    = hari;
    document.getElementById('sJamId').value   = jamId;
    document.getElementById('sKelasId').value = kelasId;
    document.getElementById('sTpId').value    = tpId;
    
    // Context info
    const kelasName = '<?= htmlspecialchars($selectedKelasId) ?>';
    document.getElementById('modalContext').innerHTML = `
        <div class="context-tags">
            <span class="context-tag">${hari}</span>
            <span class="context-tag">${cell.querySelector('.jam-label') ? '' : ''}Jam ID: ${jamId}</span>
        </div>
    `;
    
    if (jadwal) {
        // Edit mode
        isEditMode = true;
        document.getElementById('sAction').value = 'update';
        document.getElementById('sId').value     = jadwal.ID;
        document.getElementById('sRow').value    = jadwal._row;
        document.getElementById('sGuru').value   = jadwal.GuruID;
        document.getElementById('sMapel').value  = jadwal.MapelID;
        document.getElementById('modalTitle').textContent  = '✏️ Edit Jadwal';
        document.getElementById('submitText').textContent  = 'Perbarui';
        document.getElementById('btnDelete').style.display = 'flex';
    } else {
        // Add mode
        isEditMode = false;
        document.getElementById('sAction').value = 'store';
        document.getElementById('sId').value     = '';
        document.getElementById('sRow').value    = '';
        document.getElementById('sGuru').value   = '';
        document.getElementById('sMapel').value  = '';
        document.getElementById('modalTitle').textContent  = '➕ Tambah Jadwal';
        document.getElementById('submitText').textContent  = 'Simpan';
        document.getElementById('btnDelete').style.display = 'none';
    }
    
    document.getElementById('modalOverlay').classList.add('show');
    setTimeout(() => document.getElementById('sGuru').focus(), 100);
}

function closeModal(e) {
    if (e && e.target !== document.getElementById('modalOverlay')) return;
    document.getElementById('modalOverlay').classList.remove('show');
}

async function submitSchedule(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span>Menyimpan...</span>';
    
    try {
        const resp = await fetch(ENDPOINT, { method: 'POST', body: new FormData(document.getElementById('scheduleForm')) });
        const text = await resp.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch(e) {
            showAlert('danger', 'Server Error: ' + text);
            return;
        }
        
        if (result.success) {
            showAlert('success', result.message);
            closeModal();
            setTimeout(() => location.reload(), 600);
        } else {
            showAlert('danger', result.message);
        }
    } catch(err) {
        showAlert('danger', 'Kesalahan jaringan: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span id="submitText">' + (isEditMode ? 'Perbarui' : 'Simpan') + '</span>';
    }
}

async function deleteSchedule() {
    if (!confirm('Yakin hapus jadwal ini?')) return;
    
    const jadwal = JSON.parse(currentCell.dataset.jadwal || '{}');
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('row', jadwal._row);
    fd.append('csrf_token', '<?= csrf_token() ?>');
    
    try {
        const resp = await fetch(ENDPOINT, { method: 'POST', body: fd });
        const text = await resp.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch(e) {
            showAlert('danger', 'Server Error: ' + text);
            return;
        }
        showAlert(result.success ? 'success' : 'danger', result.message);
        if (result.success) { closeModal(); setTimeout(() => location.reload(), 600); }
    } catch(err) {
        showAlert('danger', 'Kesalahan jaringan: ' + err.message);
    }
}

function openImportModal() {
    document.getElementById('importModalOverlay').classList.add('show');
}

function closeImportModal(e) {
    if (e && e.target !== document.getElementById('importModalOverlay')) return;
    document.getElementById('importModalOverlay').classList.remove('show');
}

async function submitImport(e) {
    e.preventDefault();
    const btn = document.getElementById('importSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span>Mengimport...</span>';
    
    const formData = new FormData(document.getElementById('importForm'));
    
    try {
        const resp = await fetch(ENDPOINT, {
            method: 'POST',
            body: formData
        });
        const text = await resp.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch(e) {
            showAlert('danger', 'Server Error: ' + text);
            return;
        }
        
        if (result.success) {
            showAlert('success', result.message);
            closeImportModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', result.message);
        }
    } catch(err) {
        showAlert('danger', 'Kesalahan jaringan: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>Mulai Import</span>';
    }
}

document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') {
        closeModal(); 
        closeImportModal();
    }
});
</script>
</body>
</html>
