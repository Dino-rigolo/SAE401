<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once '../bootstrap.php'; // Ce fichier initialise déjà Doctrine

// Supprimez toutes les importations redondantes des entités
// Ne gardez que ces imports de classes
use Entity\Products;
use Entity\Brands;
use Entity\Categories;

define('API_KEY', 'e8f1997c763');

$request_method = $_SERVER["REQUEST_METHOD"];

// Fonction simplifiée pour valider l'API Key
function validateApiKey() {
    if($_SERVER["REQUEST_METHOD"] == "GET") return;
    
    $headers = getallheaders();
    if (!isset($headers['Api']) || $headers['Api'] != API_KEY) {
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
            // Gestion de l'action getall simplifiée
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getall") {
                global $entityManager;
                
                // Simplification de la requête avec un seul QueryBuilder
                $qb = $entityManager->createQueryBuilder();
                $qb->select('p')
                ->from('Entity\Products', 'p')  // Changez 'src\Entity\Products' en 'Entity\Products'
                ->leftJoin('p.brand', 'b')
                ->leftJoin('p.category', 'c');
                
                // Ajout des filtres seulement s'ils sont définis
                if (!empty($_REQUEST["brand"])) {
                    $qb->andWhere('b.brand_id = :brand')
                       ->setParameter('brand', $_REQUEST["brand"]);
                }
                
                if (!empty($_REQUEST["category"])) {
                    $qb->andWhere('c.category_id = :category')
                       ->setParameter('category', $_REQUEST["category"]);
                }
                
                if (!empty($_REQUEST["model_year"])) {
                    $qb->andWhere('p.model_year = :model_year')
                       ->setParameter('model_year', $_REQUEST["model_year"]);
                }
                
                if (!empty($_REQUEST["min_price"])) {
                    $qb->andWhere('p.list_price >= :min_price')
                       ->setParameter('min_price', $_REQUEST["min_price"]);
                }
                
                if (!empty($_REQUEST["max_price"])) {
                    $qb->andWhere('p.list_price <= :max_price')
                       ->setParameter('max_price', $_REQUEST["max_price"]);
                }
                
                // Exécution de la requête
                error_log("Paramètres reçus dans getall: " . json_encode($_REQUEST));
                $query = $qb->getQuery();
                $sql = $query->getSQL();
                $params = $query->getParameters();
                error_log("SQL: " . $sql);
                error_log("Paramètres: " . json_encode($params->map(function($p) { return $p->getValue(); })->toArray()));
                                
                $products = $qb->getQuery()->getResult();
                
                // Transformation simplifiée des objets en tableau JSON
                $productData = array_map(function($product) {
                    return [
                        'product_id' => $product->getProductId(),
                        'product_name' => $product->getProductName(),
                        'brand_id' => $product->getBrand()->getBrandId(),
                        'brand_name' => $product->getBrand()->getBrandName(),
                        'category_id' => $product->getCategory()->getCategoryId(),
                        'category_name' => $product->getCategory()->getCategoryName(),
                        'model_year' => $product->getModelYear(),
                        'list_price' => $product->getListPrice()
                    ];
                }, $products);
                
                echo json_encode($productData);
                exit();
            } 
            // Gestion de getfilters simplifiée
            elseif ($_REQUEST["action"] == "getfilters") {
                header('Content-Type: application/json');
                try {
                    // Utilisez directement SQL pour éviter les problèmes de doublon de classe
                    $conn = $entityManager->getConnection();
                    
                    // Récupérer les marques sans doublons - sélectionne la première occurrence de chaque nom
                    $brandsSql = "SELECT MIN(brand_id) as id, brand_name as name 
                                 FROM brands 
                                 GROUP BY brand_name 
                                 ORDER BY name";
                    $brands = $conn->executeQuery($brandsSql)->fetchAllAssociative();
                    
                    // Récupérer les catégories sans doublons
                    $categoriesSql = "SELECT MIN(category_id) as id, category_name as name 
                                     FROM categories 
                                     GROUP BY category_name 
                                     ORDER BY name";
                    $categories = $conn->executeQuery($categoriesSql)->fetchAllAssociative();
                    
                    // Récupérer les années modèles
                    $yearsSql = "SELECT DISTINCT model_year FROM products ORDER BY model_year DESC";
                    $yearsResult = $conn->executeQuery($yearsSql)->fetchAllAssociative();
                    $years = array_column($yearsResult, 'model_year');
                    
                    echo json_encode([
                        'brands' => $brands,
                        'categories' => $categories,
                        'model_years' => $years
                    ]);
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                exit();
            }
            elseif (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "getbyid" && !empty($_REQUEST["id"])) {
                $product = $entityManager->find(Products::class, $_REQUEST["id"]);
                if ($product) {
                    echo json_encode([
                        'product_id' => $product->getProductId(),
                        'product_name' => $product->getProductName(),
                        'brand_id' => $product->getBrand() ? $product->getBrand()->getBrandId() : null,
                        'brand_name' => $product->getBrand() ? $product->getBrand()->getBrandName() : 'N/A', // Ajouté 
                        'category_id' => $product->getCategory() ? $product->getCategory()->getCategoryId() : null,
                        'category_name' => $product->getCategory() ? $product->getCategory()->getCategoryName() : 'N/A', // Ajouté
                        'model_year' => $product->getModelYear(),
                        'list_price' => $product->getListPrice()
                    ]);
                } else {
                    throw new Exception('Product not found');
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
                    throw new Exception('Product not found');
                }
            } else {
                throw new Exception('Invalid action');
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