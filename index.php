<?php
/**
 * BikeStore Application Entry Point
 * 
 * Main application file that:
 * - Initializes error reporting and session handling
 * - Sets up development environment
 * - Routes requests to appropriate controllers
 * - Handles base URL redirection
 * 
 * @package BikeStore
 * @author Your Name
 * @version 1.0
 */

/**
 * Configure error reporting for development
 * Displays all errors and startup errors
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Initialize session handling
 * Creates session test value for debugging
 */
session_start();

$_SESSION['test'] = 'test_session_' . time();
error_log("Session test définie: " . $_SESSION['test']);

/**
 * Include required controller classes
 * Sets up MVC architecture components
 */
require_once("controller/Controller.class.php");
include_once("controller/ControllerException.class.php");

/**
 * Handle base URL redirection
 * Redirects root requests to home page
 * 
 * @var string $_SERVER['REQUEST_URI'] Current request URI
 */    
if (!isset($_GET['action']) && $_SERVER['REQUEST_URI'] == '/SAE401/' || $_SERVER['REQUEST_URI'] == '/SAE401') {
    header('Location: /SAE401/home');
    exit;
}

/**
 * Initialize and execute main controller
 * 
 * @var Controller $controller Main application controller
 */
$controller = new Controller($_GET);
$controller->execute();
?>

