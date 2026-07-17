<?php
// app/config.php - Application Bootstrap & Configuration

if (!defined('BASE_PATH'))     define('BASE_PATH',      dirname(__DIR__));
if (!defined('APP_PATH'))      define('APP_PATH',       BASE_PATH . '/app');
if (!defined('PUBLIC_PATH'))   define('PUBLIC_PATH',    BASE_PATH . '/public');
if (!defined('TEMPLATES_PATH'))define('TEMPLATES_PATH', BASE_PATH . '/templates');

// Load .env if not already in environment
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Autoload
require_once BASE_PATH . '/vendor/autoload.php';

// Helper function
function env(string $key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

function redirect(string $url): never {
    header("Location: $url");
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $key, string $message = ''): string {
    if ($message) {
        $_SESSION['flash'][$key] = $message;
        return '';
    }
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
