<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru — Penjadwalan Sekolah</title>
    <meta name="description" content="Kelola data guru sekolah">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app-body">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<div class="page-bg"><div class="bg-orb orb-top-right"></div></div>

<main class="main-content">
    <div class="page-header animate-fadeInDown">
        <div>
            <h1 class="page-title">👨‍🏫 Data Guru</h1>
            <p class="page-subtitle">Kelola daftar guru pengajar</p>
        </div>
        <button class="btn btn-primary" id="btnTambah" onclick="openModal('add')">
            <svg viewBox="0 0 24 24" fill="none" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Tambah Guru
        </button>
    </div>

    <div id="alertBox"></div>

    <div class="table-card glass-card animate-fadeInUp">
        <div class="table-toolbar">
            <input type="search" id="tableSearch" class="form-control search-input" placeholder="🔍 Cari guru...">
            <span class="table-count"><?= count($data) ?> guru terdaftar</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID</th>
                        <th>Nama Guru</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                    <tr><td colspan="5" class="empty-row">Belum ada data guru. Klik "Tambah Guru" untuk memulai.</td></tr>
                    <?php else: ?>
                    <?php foreach ($data as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><span class="id-badge"><?= htmlspecialchars($row['ID']) ?></span></td>
                        <td><strong><?= htmlspecialchars($row['Nama']) ?></strong></td>
                        <td>
                            <span class="status-badge <?= strtolower($row['Status']) === 'aktif' ? 'badge-success' : 'badge-danger' ?>">
                                <?= htmlspecialchars($row['Status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn btn-sm btn-edit" title="Edit"
                                    onclick="openModal('edit', <?= htmlspecialchars(json_encode(['row' => $row['_row'], 'id' => $row['ID'], 'nama' => $row['Nama'], 'status' => $row['Status']])) ?>)">
                                    ✏️
                                </button>
                                <button class="btn btn-sm btn-delete" title="Hapus"
                                    onclick="confirmDelete(<?= $row['_row'] ?>, '<?= htmlspecialchars($row['Nama']) ?>')">
                                    🗑️
                                </button>
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

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="modal glass-card animate-fadeInUp" id="mainModal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Guru</h3>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form id="mainForm" onsubmit="submitForm(event)">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="row" id="fieldRow">
            <input type="hidden" name="id" id="fieldId">
            <input type="hidden" name="action" id="fieldAction" value="store">

            <div class="form-group">
                <label class="form-label" for="fieldNama">Nama Guru <span class="required">*</span></label>
                <input type="text" id="fieldNama" name="nama" class="form-control" placeholder="Contoh: Budi Santoso, S.Pd." required>
            </div>

            <div class="form-group">
                <label class="form-label" for="fieldStatus">Status</label>
                <select id="fieldStatus" name="status" class="form-control form-select">
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="submitText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation -->
<div class="modal-overlay" id="deleteOverlay" onclick="closeDelete(event)">
    <div class="modal glass-card" id="deleteModal" style="max-width: 420px">
        <div class="modal-header">
            <h3 class="modal-title">⚠️ Konfirmasi Hapus</h3>
            <button class="modal-close" onclick="closeDelete()">✕</button>
        </div>
        <div class="modal-body">
            <p>Yakin ingin menghapus guru <strong id="deleteName"></strong>?</p>
            <p class="text-muted small">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeDelete()">Batal</button>
            <button class="btn btn-danger" id="confirmDeleteBtn" onclick="doDelete()">Hapus</button>
        </div>
    </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
const ENDPOINT = '/guru.php';
let deleteRow = null;

function openModal(mode, data = null) {
    document.getElementById('fieldAction').value = mode === 'edit' ? 'update' : 'store';
    document.getElementById('modalTitle').textContent = mode === 'edit' ? 'Edit Guru' : 'Tambah Guru';
    document.getElementById('submitText').textContent = mode === 'edit' ? 'Perbarui' : 'Simpan';
    
    if (data) {
        document.getElementById('fieldRow').value = data.row;
        document.getElementById('fieldId').value = data.id;
        document.getElementById('fieldNama').value = data.nama;
        document.getElementById('fieldStatus').value = data.status;
    } else {
        document.getElementById('mainForm').reset();
        document.getElementById('fieldRow').value = '';
        document.getElementById('fieldId').value = '';
    }
    
    document.getElementById('modalOverlay').classList.add('show');
    setTimeout(() => document.getElementById('fieldNama').focus(), 100);
}

function closeModal(e) {
    if (e && e.target !== document.getElementById('modalOverlay')) return;
    document.getElementById('modalOverlay').classList.remove('show');
}

function confirmDelete(row, name) {
    deleteRow = row;
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteOverlay').classList.add('show');
}

function closeDelete(e) {
    if (e && e.target !== document.getElementById('deleteOverlay')) return;
    document.getElementById('deleteOverlay').classList.remove('show');
}

async function submitForm(e) {
    e.preventDefault();
    const form = document.getElementById('mainForm');
    const btn = document.getElementById('submitBtn');
    
    btn.disabled = true;
    btn.innerHTML = '<span>Menyimpan...</span>';
    
    try {
        const resp = await fetch(ENDPOINT, { method: 'POST', body: new FormData(form) });
        const text = await resp.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch(e) {
            showAlert('danger', 'Server Error: ' + text);
            return;
        }
        showAlert(result.success ? 'success' : 'danger', result.message);
        
        if (result.success) {
            closeModal();
            setTimeout(() => location.reload(), 800);
        }
    } catch(err) {
        showAlert('danger', 'Kesalahan jaringan: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span id="submitText">Simpan</span>';
    }
}

async function doDelete() {
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Menghapus...';
    
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('row', deleteRow);
        fd.append('csrf_token', '<?= csrf_token() ?>');
        
        const resp = await fetch(ENDPOINT, { method: 'POST', body: fd });
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
    } finally {
        btn.disabled = false;
        btn.textContent = 'Hapus';
    }
}

// Keyboard shortcut
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        closeDelete();
    }
});
</script>
</body>
</html>
