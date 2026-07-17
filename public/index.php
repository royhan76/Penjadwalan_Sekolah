<?php
// public/index.php - Main router

require_once dirname(__DIR__) . '/app/config.php';

header('Content-Type: text/html; charset=UTF-8');

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove trailing slash (except root)
$uri = ($uri !== '/') ? rtrim($uri, '/') : '/';

// ─── Serve static assets directly (for local dev with PHP built-in server) ───
$publicPath = __DIR__;
$staticFile = $publicPath . $uri;

if ($uri !== '/' && file_exists($staticFile) && is_file($staticFile)) {
    // Serve static files with correct MIME type
    $ext = strtolower(pathinfo($staticFile, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile($staticFile);
        exit;
    }
}

// ─── Default redirect ─────────────────────────────────────────────────────────
if ($uri === '' || $uri === '/') {
    if (isLoggedIn()) {
        redirect('/dashboard.php');
    } else {
        redirect('/login.php');
    }
}

// ─── Page routing ─────────────────────────────────────────────────────────────
$page = basename($uri);

$allowedPages = [
    'login.php', 'dashboard.php', 'guru.php', 'mapel.php', 'kelas.php',
    'jam.php', 'tahun.php', 'jadwal.php', 'export.php', 'logout.php',
    'setup.php', 'generate-template.php',
];

if (in_array($page, $allowedPages) && file_exists(__DIR__ . '/' . $page)) {
    include __DIR__ . '/' . $page;
} else {
    http_response_code(404);
    include __DIR__ . '/views/404.view.php';
}
