<?php

    session_start();
    session_regenerate_id();
    require_once("controller/Controller.class.php");
    include_once("controller/ControllerException.class.php");
    
    // Si aucune action n'est spécifiée dans l'URL, rediriger vers home
    if (!isset($_GET['action']) && $_SERVER['REQUEST_URI'] == '/SAE401/' || $_SERVER['REQUEST_URI'] == '/SAE401') {
        header('Location: /SAE401/home');
        exit;
    }
    
    $controller = new Controller($_GET);
    $controller->execute();
?>