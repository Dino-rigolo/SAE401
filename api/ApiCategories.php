<?php
/**
 * Categories API Controller
 * 
 * Handles CRUD operations for bike categories:
 * - GET: Retrieve all categories or single category by ID
 * - POST: Create new category
 * - PUT: Update existing category
 * - DELETE: Remove category
 * 
 * @package BikeStore\API
 * @version 1.0
 */

// Set HTTP headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// Include required files
require_once '../vendor/autoload.php';
require_once '../src/Entity/Categories.php';
require_once '../bootstrap.php';

use Entity\Categories;

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
 * 
 * GET requests are allowed without authentication
 * Other methods require valid API key
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
 * @example GET /api/ApiCategories.php?action=getall
 * Get all categories
 * 
 * @example GET /api/ApiCategories.php?action=getbyid&id=1
 * Get category by ID
 * 
 * @example POST /api/ApiCategories.php?action=create
 * Create new category with JSON body: {"category_name": "New Category"}
 * 
 * @example PUT /api/ApiCategories.php?action=update&id=1
 * Update category with JSON body: {"category_name": "Updated Category"}
 * 
 * @example DELETE /api/ApiCategories.php?action=delete&id=1
 * Delete category by ID
 */
switch ($request_method) {
    case 'GET':
        /**
         * Handle GET requests
         * 
         * @param string $action Request action (getall|getbyid)
         * @param int $id Category ID for getbyid action
         * @return json Response with category data or error message
         */
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getall") {
                $categories = $entityManager->getRepository(Categories::class)->findAll();
                $categoriesArray = array_map(function($category) {
                    return [
                        'category_id' => $category->getCategoryId(),
                        'category_name' => $category->getCategoryName()
                    ];
                }, $categories);
                echo json_encode($categoriesArray);
            } elseif (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getbyid" && !empty($_REQUEST["id"])) {
                $category = $entityManager->find(Categories::class, $_REQUEST["id"]);
                if ($category) {
                    echo json_encode([
                        'category_id' => $category->getCategoryId(),
                        'category_name' => $category->getCategoryName()
                    ]);
                } else {
                    throw new Exception('Category not found.');
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
         * Handle POST requests
         * 
         * @param string $action Must be 'create'
         * @param array $data JSON body with category_name
         * @return json Response with success or error message
         */
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "create") {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['category_name'])) {
                    $category = new Categories();
                    $category->setCategoryName($data['category_name']);
                    $entityManager->persist($category);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Category created']);
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
        /**
         * Handle PUT requests
         * 
         * @param string $action Must be 'update'
         * @param int $id Category ID to update
         * @param array $data JSON body with category_name
         * @return json Response with success or error message
         */
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "update" && !empty($_REQUEST["id"])) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['category_name'])) {
                    $category = $entityManager->find(Categories::class, $_REQUEST["id"]);
                    if ($category) {
                        $category->setCategoryName($data['category_name']);
                        $entityManager->flush();
                        echo json_encode(['message' => 'Category updated']);
                    } else {
                        throw new Exception('Category not found.');
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
         * Handle DELETE requests
         * 
         * @param string $action Must be 'delete'
         * @param int $id Category ID to delete
         * @return json Response with success or error message
         */
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "delete" && !empty($_REQUEST["id"])) {
                $category = $entityManager->find(Categories::class, $_REQUEST["id"]);
                if ($category) {
                    $entityManager->remove($category);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Category deleted']);
                } else {
                    throw new Exception('Category not found.');
                }
            } else {
                throw new Exception('Invalid action.');
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
//https://clafoutis.alwaysdata.net/SAE401/api/ApiCategories.php?action=getall"

//https://clafoutis.alwaysdata.net/SAE401/api/ApiCategories.php?action=getbyid&id=1"

// "https://clafoutis.alwaysdata.net/SAE401/api/ApiCategories.php?action=create" -d '{"category_name": "New Category"}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiCategories.php?action=update&id=1" -d '{"category_name": "Updated Category"}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiCategories.php?action=delete&id=1"
?>