<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tahun Pelajaran — Penjadwalan Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="page-bg"><div class="bg-orb orb-top-right"></div></div>
<main class="main-content">
    <div class="page-header animate-fadeInDown">
        <div><h1 class="page-title">📅 Tahun Pelajaran</h1><p class="page-subtitle">Kelola tahun pelajaran & semester</p></div>
        <button class="btn btn-primary" onclick="openModal('add')">
            <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Tambah Tahun
        </button>
    </div>
    <div id="alertBox"></div>
    <div class="table-card glass-card animate-fadeInUp">
        <div class="table-toolbar">
            <input type="search" id="tableSearch" class="form-control search-input" placeholder="🔍 Cari tahun pelajaran...">
            <span class="table-count"><?= count($data) ?> data</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table" id="dataTable">
                <thead><tr><th>No</th><th>ID</th><th>Nama</th><th>Semester</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php if (empty($data)): ?>
                    <tr><td colspan="6" class="empty-row">Belum ada data. Klik "Tambah Tahun".</td></tr>
                    <?php else: ?>
                    <?php foreach ($data as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><span class="id-badge"><?= htmlspecialchars($row['ID']) ?></span></td>
                        <td><strong><?= htmlspecialchars($row['Nama']) ?></strong></td>
                        <td>Semester <?= htmlspecialchars($row['Semester']) ?></td>
                        <td>
                            <span class="status-badge <?= strtolower($row['Aktif']) === 'ya' ? 'badge-success' : 'badge-muted' ?>">
                                <?= strtolower($row['Aktif']) === 'ya' ? '✅ Aktif' : 'Tidak Aktif' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn btn-sm btn-edit" onclick="openModal('edit', <?= htmlspecialchars(json_encode(['row' => $row['_row'], 'id' => $row['ID'], 'nama' => $row['Nama'], 'semester' => $row['Semester'], 'aktif' => $row['Aktif']])) ?>)">✏️</button>
                                <button class="btn btn-sm btn-delete" onclick="confirmDelete(<?= $row['_row'] ?>, '<?= htmlspecialchars($row['Nama']) ?>')">🗑️</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="modal glass-card">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Tahun Pelajaran</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form id="mainForm" onsubmit="submitForm(event)">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="row" id="fieldRow">
            <input type="hidden" name="id" id="fieldId">
            <input type="hidden" name="action" id="fieldAction" value="store">
            <div class="form-group">
                <label class="form-label">Nama Tahun Pelajaran <span class="required">*</span></label>
                <input type="text" id="fieldNama" name="nama" class="form-control" placeholder="Contoh: 2024/2025" required>
            </div>
            <div class="form-group">
                <label class="form-label">Semester <span class="required">*</span></label>
                <select id="fieldSemester" name="semester" class="form-control form-select" required>
                    <option value="">-- Pilih Semester --</option>
                    <option value="1">Semester 1 (Ganjil)</option>
                    <option value="2">Semester 2 (Genap)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="toggle-label">
                    <input type="checkbox" id="fieldAktif" name="aktif" value="1" class="toggle-input">
                    <span class="toggle-switch"></span>
                    <span class="toggle-text">Jadikan Aktif</span>
                </label>
                <p class="form-hint">Hanya satu tahun pelajaran yang bisa aktif sekaligus</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary" id="submitBtn"><span id="submitText">Simpan</span></button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="deleteOverlay" onclick="closeDelete(event)">
    <div class="modal glass-card" style="max-width:420px">
        <div class="modal-header"><h3 class="modal-title">⚠️ Konfirmasi Hapus</h3><button class="modal-close" onclick="closeDelete()">✕</button></div>
        <div class="modal-body"><p>Yakin hapus <strong id="deleteName"></strong>?</p></div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeDelete()">Batal</button>
            <button class="btn btn-danger" onclick="doDelete()">Hapus</button>
        </div>
    </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
const ENDPOINT = '/tahun.php';
let deleteRow = null;
function openModal(mode, data = null) {
    document.getElementById('fieldAction').value = mode === 'edit' ? 'update' : 'store';
    document.getElementById('modalTitle').textContent = mode === 'edit' ? 'Edit Tahun Pelajaran' : 'Tambah Tahun Pelajaran';
    document.getElementById('submitText').textContent = mode === 'edit' ? 'Perbarui' : 'Simpan';
    if (data) { 
        document.getElementById('fieldRow').value = data.row; 
        document.getElementById('fieldId').value = data.id; 
        document.getElementById('fieldNama').value = data.nama;
        document.getElementById('fieldSemester').value = data.semester;
        document.getElementById('fieldAktif').checked = data.aktif === 'Ya';
    } else { document.getElementById('mainForm').reset(); }
    document.getElementById('modalOverlay').classList.add('show');
    setTimeout(() => document.getElementById('fieldNama').focus(), 100);
}
function closeModal(e) { if (e && e.target !== document.getElementById('modalOverlay')) return; document.getElementById('modalOverlay').classList.remove('show'); }
function confirmDelete(row, name) { deleteRow = row; document.getElementById('deleteName').textContent = name; document.getElementById('deleteOverlay').classList.add('show'); }
function closeDelete(e) { if (e && e.target !== document.getElementById('deleteOverlay')) return; document.getElementById('deleteOverlay').classList.remove('show'); }
async function submitForm(e) {
    e.preventDefault(); const btn = document.getElementById('submitBtn'); btn.disabled = true;
    try { 
        const resp = await fetch(ENDPOINT, {method:'POST', body: new FormData(document.getElementById('mainForm'))}); 
        const text = await resp.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch(e) {
            showAlert('danger', 'Server Error: ' + text);
            return;
        }
        showAlert(result.success ? 'success' : 'danger', result.message); 
        if (result.success) { closeModal(); setTimeout(() => location.reload(), 800); } 
    } catch(err) {
        showAlert('danger', 'Kesalahan jaringan: ' + err.message);
    } finally { btn.disabled = false; }
}
async function doDelete() {
    const fd = new FormData(); fd.append('action','delete'); fd.append('row', deleteRow); fd.append('csrf_token','<?= csrf_token() ?>');
    try {
        const resp = await fetch(ENDPOINT, {method:'POST', body: fd}); 
        const text = await resp.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch(e) {
            showAlert('danger', 'Server Error: ' + text);
            closeDelete();
            return;
        }
        showAlert(result.success ? 'success' : 'danger', result.message); 
        closeDelete(); 
        if (result.success) setTimeout(() => location.reload(), 800);
    } catch(err) {
        showAlert('danger', 'Kesalahan jaringan: ' + err.message);
        closeDelete();
    }
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeDelete(); } });
</script>
</body></html>
