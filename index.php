<?php

use iutnc\nrv\dispatch\Dispatcher;


// Auto loader
require_once 'vendor/autoload.php';

session_start();
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = 'default'; 
}


//Set configuration
try {
    \iutnc\nrv\repository\NrvRepository::setConfig(__DIR__ . '/config.db.ini');
} catch (Exception $e) {
    echo 'Erreur lors de la configuration de la base de données : ' . htmlspecialchars($e->getMessage());
    exit;
}
$dispatcher = new Dispatcher($action);

$dispatcher->run();