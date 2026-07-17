<?php
require_once dirname(__DIR__) . '/app/config.php';
use App\Controllers\DashboardController;
use App\GoogleSheet;
$sheet = new GoogleSheet();
$ctrl = new DashboardController($sheet);
$ctrl->index();
