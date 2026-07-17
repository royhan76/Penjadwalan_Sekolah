<?php
// api/index.php - Vercel entry point
// Routes all requests to public/index.php

// Set correct working directory to project root
$projectRoot = dirname(__DIR__);
chdir($projectRoot);

// Define BASE_PATH explicitly agar tidak bergantung pada chdir
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $projectRoot);
}

// Load the main public router
require_once $projectRoot . '/public/index.php';
