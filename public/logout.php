<?php
require_once dirname(__DIR__) . '/app/config.php';
use App\Controllers\AuthController;
$ctrl = new AuthController();
$ctrl->logout();
