<?php
require_once dirname(__DIR__) . '/app/config.php';
requireLogin();

use App\GoogleSheet;
use App\Services\ExportService;

$sheet   = new GoogleSheet();
$service = new ExportService($sheet);

$kelasId = $_GET['kelas_id'] ?? '';
$tpId    = $_GET['tp_id'] ?? '';
$type    = $_GET['type'] ?? 'single';

if (empty($kelasId) && $type === 'single') {
    flash('error', 'Pilih kelas terlebih dahulu.');
    redirect('/jadwal.php');
}

if (empty($tpId)) {
    flash('error', 'Tahun pelajaran tidak dipilih.');
    redirect('/jadwal.php');
}

if ($type === 'original') {
    $service->downloadOriginalLayout($tpId);
} elseif ($type === 'all') {
    $service->downloadAll($tpId);
} else {
    $service->download($kelasId, $tpId);
}
