<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once '../vendor/autoload.php'; 
require_once '../src/Entity/Stocks.php';
require_once '../src/Entity/Products.php';
require_once '../bootstrap.php';

use Entity\Stocks;
use Entity\Products;

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
                $stocks = $entityManager->getRepository(Stocks::class)->findAll();
                $stockData = array_map(function($stock) {
                    return [
                        'stock_id' => $stock->getStockId(),
                        'product_id' => $stock->getProduct()->getProductId(),
                        'quantity' => $stock->getQuantity()
                    ];
                }, $stocks);
                echo json_encode($stockData);
            } elseif (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getbyid" && !empty($_REQUEST["id"])) {
                $stock = $entityManager->find(Stocks::class, $_REQUEST["id"]);
                if ($stock) {
                    echo json_encode([
                        'stock_id' => $stock->getStockId(),
                        'product_id' => $stock->getProduct()->getProductId(),
                        'quantity' => $stock->getQuantity()
                    ]);
                } else {
                    throw new Exception('Stock not found.');
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
                if (isset($data['product_id'], $data['quantity'])) {
                    $stock = new Stocks();
                    $product = $entityManager->find(Products::class, $data['product_id']);
                    if ($product) {
                        $stock->setProduct($product);
                    } else {
                        throw new Exception('Product not found.');
                    }
                    $stock->setQuantity($data['quantity']);
                    $entityManager->persist($stock);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Stock created']);
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

// "https://clafoutis.alwaysdata.net/SAE401/api/ApiStocks.php?action=create" -d '{"product_id": 1, "quantity": 100}' -H "Content-Type: application/json" -H "Authorization: Bearer e8f1997c763"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiStocks.php?action=update&id=1" -d '{"product_id": 1, "quantity": 150}' -H "Content-Type: application/json" -H "Authorization: Bearer e8f1997c763"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiStocks.php?action=delete&id=1" -H "Authorization: Bearer e8f1997c763"
?>