<?php
/**
 * Stores API Controller
 * 
 * Handles CRUD operations for store locations:
 * - GET: Retrieve stores information
 * - POST: Create new store
 * - PUT: Update existing store
 * - DELETE: Remove store
 * 
 * @package BikeStore\API
 * @author Your Name
 * @version 1.0
 */

/**
 * Set HTTP headers for API access
 */
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once '../vendor/autoload.php'; // Assurez-vous que l'autoload de Composer est inclus
require_once '../src/Entity/Stores.php';
require_once '../bootstrap.php'; // Fichier pour initialiser Doctrine

use Entity\Stores;

/**
 * API authentication key
 * @var string
 */
define('API_KEY', 'e8f1997c763');

/**
 * Current HTTP request method
 * @var string
 */
$request_method = $_SERVER["REQUEST_METHOD"];

/**
 * Validates API key from request headers
 * GET requests are exempt from authentication
 * 
 * @return void
 * @throws Exception If API key is missing or invalid
 */
function validateApiKey() {
    $headers = getallheaders();
    if($_SERVER["REQUEST_METHOD"]=="GET"){
    return;
    }
    if (!isset($headers['Api'])||$headers['Api'] != API_KEY) {
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['message' => 'Method Not Allowed']);
        exit();
    }   
}
validateApiKey();

$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
$request = explode('/', trim($path_info, '/'));

/**
 * Process the request based on HTTP method
 * 
 * @api
 * @example GET /api/ApiStores.php?action=getall
 * Get all stores
 * 
 * @example GET /api/ApiStores.php?action=getbyid&id=1
 * Get store by ID
 * 
 * @example POST /api/ApiStores.php?action=create
 * Create new store with JSON body:
 * {
 *   "store_name": "New Store",
 *   "phone": "123-456-7890",
 *   "email": "store@example.com",
 *   "street": "123 Main St",
 *   "city": "Anytown",
 *   "state": "CA",
 *   "zip_code": "12345"
 * }
 * 
 * @example PUT /api/ApiStores.php?action=update&id=1
 * Update store with same JSON structure as POST
 * 
 * @example DELETE /api/ApiStores.php?action=delete&id=1
 * Delete store by ID
 */
switch ($request_method) {
    case 'GET':
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getall") {
                $stores = $entityManager->getRepository(Stores::class)->findAll();
                $storeData = array_map(function($store) {
                    return [
                        'store_id' => $store->getStoreId(),
                        'store_name' => $store->getStoreName(),
                        'phone' => $store->getPhone(),
                        'email' => $store->getEmail(),
                        'street' => $store->getStreet(),
                        'city' => $store->getCity(),
                        'state' => $store->getState(),
                        'zip_code' => $store->getZipCode()
                    ];
                }, $stores);
                echo json_encode($storeData);
            } elseif (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getbyid" && !empty($_REQUEST["id"])) {
                $store = $entityManager->find(Stores::class, $_REQUEST["id"]);
                if ($store) {
                    echo json_encode([
                        'store_id' => $store->getStoreId(),
                        'store_name' => $store->getStoreName(),
                        'phone' => $store->getPhone(),
                        'email' => $store->getEmail(),
                        'street' => $store->getStreet(),
                        'city' => $store->getCity(),
                        'state' => $store->getState(),
                        'zip_code' => $store->getZipCode()
                    ]);
                } else {
                    throw new Exception('Store not found.');
                }
            } else {
                throw new Exception('Invalid action.');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case 'POST':
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "create") {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['store_name'], $data['phone'], $data['email'], $data['street'], $data['city'], $data['state'], $data['zip_code'])) {
                    $store = new Stores();
                    $store->setStoreName($data['store_name']);
                    $store->setPhone($data['phone']);
                    $store->setEmail($data['email']);
                    $store->setStreet($data['street']);
                    $store->setCity($data['city']);
                    $store->setState($data['state']);
                    $store->setZipCode($data['zip_code']);
                    $entityManager->persist($store);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Store created']);
                } else {
                    throw new Exception('Invalid input');
                }
            } else {
                throw new Exception('Invalid action.');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case 'PUT':
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "update" && !empty($_REQUEST["id"])) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['store_name'], $data['phone'], $data['email'], $data['street'], $data['city'], $data['state'], $data['zip_code'])) {
                    $store = $entityManager->find(Stores::class, $_REQUEST["id"]);
                    if ($store) {
                        $store->setStoreName($data['store_name']);
                        $store->setPhone($data['phone']);
                        $store->setEmail($data['email']);
                        $store->setStreet($data['street']);
                        $store->setCity($data['city']);
                        $store->setState($data['state']);
                        $store->setZipCode($data['zip_code']);
                        $entityManager->flush();
                        echo json_encode(['message' => 'Store updated']);
                    } else {
                        throw new Exception('Store not found.');
                    }
                } else {
                    throw new Exception('Invalid input');
                }
            } else {
                throw new Exception('Invalid action.');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case 'DELETE':
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "delete" && !empty($_REQUEST["id"])) {
                $store = $entityManager->find(Stores::class, $_REQUEST["id"]);
                if ($store) {
                    $entityManager->remove($store);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Store deleted']);
                } else {
                    throw new Exception('Store not found pour delete.');
                }
            } else {
                throw new Exception('Invalid action pour delete.');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}

// Exemples d'utilisation:
//https://clafoutis.alwaysdata.net/SAE401/api/ApiStores.php?action=getall"

//https://clafoutis.alwaysdata.net/SAE401/api/ApiStores.php?action=getbyid&id=1"

// "https://clafoutis.alwaysdata.net/SAE401/api/ApiStores.php?action=create" -d '{"store_name": "New Store", "phone": "123-456-7890", "email": "store@example.com", "street": "123 Main St", "city": "Anytown", "state": "CA", "zip_code": "12345"}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiStores.php?action=update&id=1" -d '{"store_name": "Updated Store", "phone": "123-456-7890", "email": "store@example.com", "street": "123 Main St", "city": "Anytown", "state": "CA", "zip_code": "12345"}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiStores.php?action=delete&id=1"
?>