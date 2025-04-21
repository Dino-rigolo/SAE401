<?php
/**
 * Stocks API Controller
 * 
 * Handles CRUD operations for product stocks in stores:
 * - GET: Retrieve stocks information
 * - POST: Create new stock entry
 * - PUT: Update existing stock
 * - DELETE: Remove stock entry
 * 
 * @package BikeStore\API
 * @author 
 * @version 1.0
 */

/**
 * Set HTTP headers for API access
 */
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// Include required files
require_once '../vendor/autoload.php';
require_once '../src/Entity/Stocks.php';
require_once '../src/Entity/Products.php';
require_once '../src/Entity/Stores.php';
require_once '../bootstrap.php';

use Entity\Stores;
use Entity\Stocks;
use Entity\Products;

/**
 * API authentication key
 * @var string
 */
define('API_KEY', 'e8f1997c763');

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
 * @example GET /api/ApiStocks.php?action=getall
 * Get all stocks with store and product details
 * 
 * @example GET /api/ApiStocks.php?action=getbyid&id=1
 * Get stock by ID with store and product details
 * 
 * @example POST /api/ApiStocks.php?action=create
 * Create new stock with JSON body:
 * {
 *   "store_id": 1,
 *   "product_id": 1,
 *   "quantity": 100
 * }
 * 
 * @example PUT /api/ApiStocks.php?action=update&id=1
 * Update stock with JSON body:
 * {
 *   "product_id": 1,
 *   "quantity": 150
 * }
 * 
 * @example DELETE /api/ApiStocks.php?action=delete&id=1
 * Delete stock by ID
 */
switch ($request_method) {
    case 'GET':
        /**
         * Handle GET requests for stocks
         * 
         * @param string $action Request action (getall|getbyid)
         * @param int $id Stock ID for getbyid action
         * @return json Response with stock data or error message
         */
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getall") {
                $stocks = $entityManager->getRepository(Stocks::class)->findAll();
                $stockData = array_map(function($stock) {
                    return [
                        'stock_id' => $stock->getStockId(),
                        'store_id' => $stock->getStore() ? $stock->getStore()->getStoreId() : null,
                        'store_name' => $stock->getStore() ? $stock->getStore()->getStoreName() : 'N/A',
                        'product_id' => $stock->getProduct() ? $stock->getProduct()->getProductId() : null,
                        'product_name' => $stock->getProduct() ? $stock->getProduct()->getProductName() : 'N/A',
                        'quantity' => $stock->getQuantity()
                    ];
                }, $stocks);
                echo json_encode($stockData);
            } elseif (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getbyid" && !empty($_REQUEST["id"])) {
                $stock = $entityManager->find(Stocks::class, $_REQUEST["id"]);
                if ($stock) {
                    echo json_encode([
                        'stock_id' => $stock->getStockId(),
                        'store_id' => $stock->getStore() ? $stock->getStore()->getStoreId() : null,
                        'store_name' => $stock->getStore() ? $stock->getStore()->getStoreName() : 'N/A',
                        'product_id' => $stock->getProduct() ? $stock->getProduct()->getProductId() : null,
                        'product_name' => $stock->getProduct() ? $stock->getProduct()->getProductName() : 'N/A',
                        'quantity' => $stock->getQuantity()
                    ]);
                } else {
                    throw new Exception('Stock not found');
                }
            } else {
                throw new Exception('Invalid action.');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case 'POST':
        /**
         * Handle POST requests for stock creation
         * 
         * @param array $data JSON body containing:
         *      - store_id: int Store identifier
         *      - product_id: int Product identifier
         *      - quantity: int Stock quantity
         * @return json Response with success or error message
         */
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "create") {
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Log des données reçues
                error_log("Données reçues pour la création de stock : " . json_encode($data));
                
                if (isset($data['store_id'], $data['product_id'], $data['quantity'])) {
                    // Récupérer le magasin
                    $store = $entityManager->find(Stores::class, $data['store_id']);
                    if (!$store) {
                        throw new Exception("Store with ID " . $data['store_id'] . " not found");
                    }

                    // Récupérer le produit
                    $product = $entityManager->find(Products::class, $data['product_id']);
                    if (!$product) {
                        throw new Exception("Product with ID " . $data['product_id'] . " not found");
                    }
                    
                    // Créer et persister le stock
                    $stock = new Stocks();
                    $stock->setStore($store);
                    $stock->setProduct($product);
                    $stock->setQuantity($data['quantity']);

                    // Log avant persistance
                    error_log("Stock avant persistance : " . json_encode([
                        'store' => $stock->getStore()->getStoreName(),
                        'product' => $stock->getProduct()->getProductName(),
                        'quantity' => $stock->getQuantity()
                    ]));

                    $entityManager->persist($stock);
                    $entityManager->flush();

                    // Log après persistance
                    error_log("Stock ajouté avec succès");
                    
                    echo json_encode(['message' => 'Stock created successfully']);
                } else {
                    throw new Exception('Missing required fields: store_id, product_id, or quantity');
                }
            } else {
                throw new Exception('Invalid action.');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case 'PUT':
        /**
         * Handle PUT requests for stock updates
         * 
         * @param int $id Stock ID to update
         * @param array $data JSON body with updated stock data
         * @return json Response with success or error message
         */
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "update" && !empty($_REQUEST["id"])) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['product_id'], $data['quantity'])) {
                    $stock = $entityManager->find(Stocks::class, $_REQUEST["id"]);
                    if ($stock) {
                        $product = $entityManager->find(Products::class, $data['product_id']);
                        if ($product) {
                            $stock->setProduct($product);
                        } else {
                            throw new Exception('Product not found.');
                        }
                        $stock->setQuantity($data['quantity']);
                        $entityManager->flush();
                        echo json_encode(['message' => 'Stock updated']);
                    } else {
                        throw new Exception('Stock not found.');
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
        /**
         * Handle DELETE requests for stock removal
         * 
         * @param int $id Stock ID to delete
         * @return json Response with success or error message
         */
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "delete" && !empty($_REQUEST["id"])) {
                $stock = $entityManager->find(Stocks::class, $_REQUEST["id"]);
                if ($stock) {
                    $entityManager->remove($stock);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Stock deleted']);
                } else {
                    throw new Exception('Stock not found pour delete.');
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
//https://clafoutis.alwaysdata.net/SAE401/api/ApiStocks.php?action=getall"

//https://clafoutis.alwaysdata.net/SAE401/api/ApiStocks.php?action=getbyid&id=1"

// "https://clafoutis.alwaysdata.net/SAE401/api/ApiStocks.php?action=create" -d '{"store_id":1", product_id": 1, "quantity": 100}' -H "Content-Type: application/json" -H "Authorization: Bearer e8f1997c763"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiStocks.php?action=update&id=1" -d '{"product_id": 1, "quantity": 150}' -H "Content-Type: application/json" -H "Authorization: Bearer e8f1997c763"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiStocks.php?action=delete&id=1" -H "Authorization: Bearer e8f1997c763"
?>