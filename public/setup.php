<?php
// public/setup.php - One-time setup: Initialize Google Sheets structure

require_once dirname(__DIR__) . '/app/config.php';
requireLogin();

use App\GoogleSheet;

$sheet = new GoogleSheet();
$results = $sheet->initSheets();

echo '<pre>';
echo "=== Setup Spreadsheet ===\n\n";
foreach ($results as $name => $status) {
    $icon = $status === 'created' ? '✅ Created' : '⚠️  Already exists';
    echo "$name: $icon\n";
}
echo "\n✅ Setup selesai! <a href='/dashboard.php'>Kembali ke Dashboard</a>";
echo '</pre>';
