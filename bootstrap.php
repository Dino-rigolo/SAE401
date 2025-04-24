<?php
/**
 * Database Bootstrap Configuration
 * 
 * Sets up Doctrine ORM configuration and database connection:
 * - Configures entity paths and development mode
 * - Establishes database connection with credentials
 * - Creates EntityManager instance for database operations
 * 
 * @package BikeStore\Config
 * @author Your Name
 * @version 1.0
 */

require_once "vendor/autoload.php";

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

/**
 * Doctrine ORM Configuration
 * 
 * @var array $paths Entity class paths
 * @var bool $isDevMode Development mode flag
 */
$paths = array(__DIR__."/src");
$isDevMode = true;

/**
 * Create Doctrine configuration with annotations
 * Enables development mode for detailed error messages
 * 
 * @var \Doctrine\ORM\Configuration $config
 */
$config = ORMSetup::createAnnotationMetadataConfiguration($paths, $isDevMode);

/**
 * Database connection configuration
 * Establishes connection to MySQL database
 * 
 * @var \Doctrine\DBAL\Connection $connection
 */
$connection = DriverManager::getConnection([
    'dbname' => '',
    'user' => '',
    'password' => '',
    'host' => '',
    'driver' => 'pdo_mysql',
], $config);

/**
 * Entity Manager instance
 * Central access point for database operations
 * 
 * @var \Doctrine\ORM\EntityManager $entityManager
 */
$entityManager = new EntityManager($connection, $config);
?>



