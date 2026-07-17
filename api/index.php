<?php
// api/index.php - Vercel entry point
// Routes all requests to public/index.php

// Ensure correct working directory
chdir(dirname(__DIR__));

// Load the main public router
require_once __DIR__ . '/../public/index.php';
