<?php
require_once dirname(__DIR__) . '/app/config.php';
use App\Controllers\MasterController;
use App\GoogleSheet;

$sheet = new GoogleSheet();
$ctrl  = new MasterController($sheet);
$action = $_POST['action'] ?? $_GET['action'] ?? 'index';

match($action) {
    'store'  => $ctrl->jamStore(),
    'update' => $ctrl->jamUpdate(),
    'delete' => $ctrl->jamDelete(),
    default  => $ctrl->jamIndex(),
};
