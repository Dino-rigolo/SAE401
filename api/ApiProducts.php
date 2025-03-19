<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once '../vendor/autoload.php'; // Assurez-vous que l'autoload de Composer est inclus
require_once '../src/Entity/Categories.php';
require_once '../src/Entity/Products.php';
require_once '../src/Entity/Brands.php';
require_once '../bootstrap.php'; // Fichier pour initialiser Doctrine

use Entity\Products;
use Entity\Brands;
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
                $products = $entityManager->getRepository(Products::class)->findAll();
                $productNames = array_map(function($product) {
                    return $product->getProductName();
                }, $products);
                echo json_encode($productNames);
            } elseif (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getbyid" && !empty($_REQUEST["id"])) {
                $product = $entityManager->find(Products::class, $_REQUEST["id"]);
                if ($product) {
                    echo json_encode([
                        'product_name' => $product->getProductName(),
                        'brand_id' => $product->getBrand()->getBrandId(),
                        'category_id' => $product->getCategory()->getCategoryId(),
                        'model_year' => $product->getModelYear(),
                        'list_price' => $product->getListPrice()
                    ]);
                } else {
                    throw new Exception('Product not found.');
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
                if (isset($data['product_name'], $data['brand_id'], $data['category_id'], $data['model_year'], $data['list_price'])) {
                    $product = new Products();
                    $product->setProductName($data['product_name']);
                    $brand = $entityManager->find(Brands::class, $data['brand_id']);
                    if ($brand) {
                        $product->setBrand($brand);
                    } else {
                        throw new Exception('Brand not found.');
                    }
                    $category = $entityManager->find(Categories::class, $data['category_id']);
                    if ($category) {
                        $product->setCategory($category);
                    } else {
                        throw new Exception('category not found.');
                    }
                    $product->setModelYear($data['model_year']);
                    $product->setListPrice($data['list_price']);
                    $entityManager->persist($product);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Product created']);
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
                if (isset($data['product_name'], $data['brand_id'], $data['category_id'], $data['model_year'], $data['list_price'])) {
                    $product = $entityManager->find(Products::class, $_REQUEST["id"]);
                    if ($product) {
                        $product->setProductName($data['product_name']);
                        $brand = $entityManager->find(Brands::class, $data['brand_id']);
                        if ($brand) {
                            $product->setBrand($brand);
                        } else {
                            throw new Exception('Brand not found.');
                        }
                        $category = $entityManager->find(Categories::class, $data['category_id']);
                        if ($category) {
                            $product->setCategory($category);
                        } else {
                            throw new Exception('category not found.');
                        }
                        $product->setModelYear($data['model_year']);
                        $product->setListPrice($data['list_price']);
                        $entityManager->flush();
                        echo json_encode(['message' => 'Product updated']);
                    } else {
                        throw new Exception('Product not found.');
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
                $product = $entityManager->find(Products::class, $_REQUEST["id"]);
                if ($product) {
                    $entityManager->remove($product);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Product deleted']);
                } else {
                    throw new Exception('Product not found pour delete.');
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
//https://clafoutis.alwaysdata.net/SAE401/api/ApiProducts.php?action=getall"

//https://clafoutis.alwaysdata.net/SAE401/api/ApiProducts.php?action=getbyid&id=1"

// "https://clafoutis.alwaysdata.net/SAE401/api/ApiProducts.php?action=create" -d '{"product_name": "New Product", "brand_id": 1, "category_id": 1, "model_year": 2025, "list_price": 100.00}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiProducts.php?action=update&id=1" -d '{"product_name": "Updated Product", "brand_id": 1, "category_id": 1, "model_year": 2025, "list_price": 150.00}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiProducts.php?action=delete&id=1"
?>