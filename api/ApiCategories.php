<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once '../vendor/autoload.php'; // Assurez-vous que l'autoload de Composer est inclus
require_once '../src/Entity/Categories.php';
require_once '../bootstrap.php'; // Fichier pour initialiser Doctrine

use Entity\Categories;

define('API_KEY', 'e8f1997c763');

$request_method = $_SERVER["REQUEST_METHOD"];

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

switch ($request_method) {
    case 'GET':
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