<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once '../vendor/autoload.php'; // Assurez-vous que l'autoload de Composer est inclus
require_once '../src/Entity/Brands.php';
require_once '../bootstrap.php'; // Fichier pour initialiser Doctrine

define('API_KEY', 'e8f1997c763');

use Entity\Brands;

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
                $brands = $entityManager->getRepository(Brands::class)->findAll();
                $brandsArray = array_map(function($brand) {
                    return [
                        'brand_id' => $brand->getBrandId(),
                        'brand_name' => $brand->getBrandName()
                        // Ajoutez d'autres propriétés si votre entité Brands en possède
                    ];
                }, $brands);
                echo json_encode($brandsArray);
            } elseif (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getbyid" && !empty($_REQUEST["id"])) {
                $brand = $entityManager->find(Brands::class, $_REQUEST["id"]);
                if ($brand) {
                    echo json_encode([
                        'brand_id' => $brand->getBrandId(), // Ajout de l'ID
                        'brand_name' => $brand->getBrandName()
                    ]);
                } else {
                    throw new Exception('Brand not found.');
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
                
                // Log pour débogage
                error_log("Données reçues dans API: " . json_encode($data));
                
                if (isset($data['brand_name'])) {
                    $brand = new Brands();
                    $brand->setBrandName($data['brand_name']);
                    $entityManager->persist($brand);
                    $entityManager->flush();
                    
                    echo json_encode(['message' => 'Brand created successfully']);
                    http_response_code(201); // Created
                } else {
                    throw new Exception('Brand name is required.');
                }
            } else {
                throw new Exception('Invalid action.');
            }
        } catch (Exception $e) {
            http_response_code(400); // Bad Request
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case 'PUT':
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "update" && !empty($_REQUEST["id"])) {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['brand_name'])) {
                    $brand = $entityManager->find(Brands::class, $_REQUEST["id"]);
                    if ($brand) {
                        $brand->setBrandName($data['brand_name']);
                        $entityManager->flush();
                        echo json_encode(['message' => 'Brand updated successfully']);
                    } else {
                        throw new Exception('Brand not found');
                    }
                } else {
                    throw new Exception('Brand name is required');
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
                $brand = $entityManager->find(Brands::class, $_REQUEST["id"]);
                if ($brand) {
                    $entityManager->remove($brand);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Brand deleted']);
                } else {
                    throw new Exception('Brand not found pour delete.');
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
//https://clafoutis.alwaysdata.net/SAE401/api/ApiBrands.php?action=getall"

//https://clafoutis.alwaysdata.net/SAE401/api/ApiBrands.php?action=getbyid&id=1"

// "https://clafoutis.alwaysdata.net/SAE401/api/ApiBrands.php?action=create" -d '{"brand_name": "New Brand"}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiBrands.php?action=update&id=1" -d '{"brand_name": "Updated Brand"}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiBrands.php?action=delete&id=1"
?>