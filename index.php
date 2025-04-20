<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$_SESSION['test'] = 'test_session_' . time();
error_log("Session test définie: " . $_SESSION['test']);

require_once("controller/Controller.class.php");
include_once("controller/ControllerException.class.php");
    
if (!isset($_GET['action']) && $_SERVER['REQUEST_URI'] == '/SAE401/' || $_SERVER['REQUEST_URI'] == '/SAE401') {
    header('Location: /SAE401/home');
    exit;
}

$controller = new Controller($_GET);
$controller->execute();
?>

