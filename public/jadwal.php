<?php
require_once dirname(__DIR__) . '/app/config.php';
use App\Controllers\JadwalController;
use App\GoogleSheet;

$sheet = new GoogleSheet();
$ctrl  = new JadwalController($sheet);
$action = $_POST['action'] ?? $_GET['action'] ?? 'index';

match($action) {
    'store'    => $ctrl->store(),
    'update'   => $ctrl->update(),
    'delete'   => $ctrl->delete(),
    'get_cell' => $ctrl->getCell(),
    'import'   => $ctrl->import(),
    default    => $ctrl->index(),
};
