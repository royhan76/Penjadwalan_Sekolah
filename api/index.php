<?php
// api/index.php - Vercel entry point
// All requests are routed here by vercel.json

// Project root = satu level di atas folder api/
$projectRoot = dirname(__DIR__);

// Set working directory ke project root
chdir($projectRoot);

header('Content-Type: text/html; charset=UTF-8');

// Pre-define BASE_PATH sebelum config.php di-load
// supaya dirname(__DIR__) di config.php tidak salah path
if (!defined('BASE_PATH'))      define('BASE_PATH',      $projectRoot);
if (!defined('APP_PATH'))       define('APP_PATH',       $projectRoot . '/app');
if (!defined('PUBLIC_PATH'))    define('PUBLIC_PATH',    $projectRoot . '/public');
if (!defined('TEMPLATES_PATH')) define('TEMPLATES_PATH', $projectRoot . '/templates');

// Load main router
require_once $projectRoot . '/public/index.php';
