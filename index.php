<?php

use iutnc\nrv\dispatch\Dispatcher;

require_once 'vendor/autoload.php';

session_start();
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = 'default'; 
}

\iutnc\nrv\repository\NrvRepository::setConfig(__DIR__ . '/config.db.ini');
$dispatcher = new Dispatcher($action);

$dispatcher->run();