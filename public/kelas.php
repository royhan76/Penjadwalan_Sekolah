<?php
require_once dirname(__DIR__) . '/app/config.php';
use App\Controllers\MasterController;
use App\GoogleSheet;

$sheet = new GoogleSheet();
$ctrl  = new MasterController($sheet);
$action = $_POST['action'] ?? $_GET['action'] ?? 'index';

match($action) {
    'store'  => $ctrl->kelasStore(),
    'update' => $ctrl->kelasUpdate(),
    'delete' => $ctrl->kelasDelete(),
    default  => $ctrl->kelasIndex(),
};
