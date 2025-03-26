<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once '../vendor/autoload.php'; // Assurez-vous que l'autoload de Composer est inclus
require_once '../src/Entity/Employees.php';
require_once '../src/Entity/Stores.php';
require_once '../bootstrap.php'; // Fichier pour initialiser Doctrine

use Entity\Employees;
use Entity\Stores;

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
            // Vérifie si un paramètre "action" est présent dans la requête
            if (!empty($_REQUEST["action"])) {
    
                // Récupérer tous les employés
                if ($_REQUEST["action"] == "getall") {
                    $employees = $entityManager->getRepository(Employees::class)->findAll();
                
                // Récupérer un employé par son ID
                } elseif ($_REQUEST["action"] == "getbyid" && !empty($_REQUEST["id"])) {
                    $employees = [$entityManager->find(Employees::class, $_REQUEST["id"])];
                
                // Récupérer tous les employés d'un magasin spécifique
                } elseif ($_REQUEST["action"] == "getbystore" && !empty($_REQUEST["store_id"])) {
                    $employees = $entityManager->getRepository(Employees::class)->findBy(['store' => $_REQUEST["store_id"]]);
                
                // Récupérer tous les employés selon leur rôle (employee, chief, it)
                } elseif ($_REQUEST["action"] == "getbyrole" && !empty($_REQUEST["role"])) {
                    $employees = $entityManager->getRepository(Employees::class)->findBy(['employee_role' => $_REQUEST["role"]]);
                
                // Récupérer un employé via son email (utile pour l'authentification)
                }elseif ($_REQUEST["action"] == "getbyemail" && !empty($_REQUEST["email"])) {
                    // Récupérer tous les employés pour vérifier
                    $allEmployees = $entityManager->getRepository(Employees::class)->findAll();
                    error_log("Tous les employés disponibles:");
                    foreach ($allEmployees as $emp) {
                        error_log("ID: " . $emp->getEmployeeId() . ", Email: " . $emp->getEmployeeEmail());
                    }
                    
                    
                    // CORRECTION : Vérifier le nom exact de la colonne dans votre entité
                    // Si votre entité utilise 'employee_email', mais la base de données utilise un autre nom
                    try {
                        // Tentative avec findOneBy
                        $employee = $entityManager->getRepository(Employees::class)->findOneBy(['employee_email' => strtolower($_REQUEST["email"])]);
                        
                        // Si ça ne fonctionne pas, essayer avec createQueryBuilder
                        if (!$employee) {
                            error_log("Tentative avec QueryBuilder");
                            $qb = $entityManager->createQueryBuilder();
                            $qb->select('e')
                               ->from(Employees::class, 'e')
                               ->where('e.employee_email = :email')
                               ->setParameter('email', $_REQUEST["email"]);
                            
                            $query = $qb->getQuery();
                            $employee = $query->getOneOrNullResult();
                        }
                        
                        // Log pour vérifier si l'employé est trouvé
                        error_log("Employé trouvé: " . ($employee ? "Oui" : "Non"));
                        
                        if ($employee) {
                            $response = json_encode([
                                'employee_id' => $employee->getEmployeeId(),
                                'employee_name' => $employee->getEmployeeName(),
                                'employee_email' => $employee->getEmployeeEmail(),
                                'employee_password' => $employee->getEmployeePassword(),
                                'employee_role' => $employee->getEmployeeRole(),
                                'store_id' => $employee->getStore() ? $employee->getStore()->getStoreId() : null
                            ]);
                            error_log("Réponse JSON: " . $response);
                            echo $response;
                        } else {
                            $response = json_encode(['error' => 'Employee not found']);
                            error_log("Réponse d'erreur: " . $response);
                            echo $response;
                        }
                    } catch (Exception $e) {
                        error_log("Exception lors de la recherche par email: " . $e->getMessage());
                        echo json_encode(['error' => 'Error searching employee: ' . $e->getMessage()]);
                    }
                    exit();
                
                // Vérifier si un email existe déjà (éviter les doublons)
                } elseif ($_REQUEST["action"] == "emailexists" && !empty($_REQUEST["email"])) {
                    $employee = $entityManager->getRepository(Employees::class)->findOneBy(['employee_email' => $_REQUEST["email"]]);
                    echo json_encode(['exists' => $employee !== null]); // Retourne true ou false
                    exit();
                
                // Trier les employés selon un champ (ex: par nom ou rôle)
                } elseif ($_REQUEST["action"] == "getsorted" && !empty($_REQUEST["sort"])) {
                    $orderBy = [$_REQUEST["sort"] => 'ASC']; // Trie en ordre croissant
                    $employees = $entityManager->getRepository(Employees::class)->findBy([], $orderBy);
                
                } else {
                    throw new Exception('Invalid action.'); // Gère les actions non reconnues
                }
    
                // Transforme les objets employés en tableaux associatifs pour les convertir en JSON
                $employeeData = array_map(function ($employee) {
                    return [
                        'employee_id' => $employee->getEmployeeId(),
                        'store_id' => $employee->getStore() ? $employee->getStore()->getStoreId() : null,
                        'employee_name' => $employee->getEmployeeName(),
                        'employee_email' => $employee->getEmployeeEmail(),
                        'employee_role' => $employee->getEmployeeRole()
                    ];
                }, $employees);
    
                // Renvoie les données des employés en format JSON
                echo json_encode($employeeData);
            
            } else {
                throw new Exception('No action provided.'); // Si aucune action n'est spécifiée
            }
        
        } catch (Exception $e) {
            // Capture et affiche les erreurs sous forme de JSON
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
    case 'POST':
        try {
            if (!empty($_REQUEST["action"]) && $_REQUEST["action"] == "create") {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['employee_name'], $data['employee_email'], $data['employee_password'], $data['employee_role'])) {
                    $employee = new Employees();
                    $employee->setEmployeeName($data['employee_name']);
                    $employee->setEmployeeEmail($data['employee_email']);
                    $employee->setEmployeePassword($data['employee_password']);
                    $employee->setEmployeeRole($data['employee_role']);
                    if (isset($data['store_id'])) {
                        $store = $entityManager->find(Stores::class, $data['store_id']);
                        if ($store) {
                            $employee->setStore($store);
                        } else {
                            throw new Exception('Store not found.');
                        }
                    }
                    $entityManager->persist($employee);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Employee created']);
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
                if (isset($data['employee_name'], $data['employee_email'], $data['employee_password'], $data['employee_role'])) {
                    $employee = $entityManager->find(Employees::class, $_REQUEST["id"]);
                    if ($employee) {
                        $employee->setEmployeeName($data['employee_name']);
                        $employee->setEmployeeEmail($data['employee_email']);
                        $employee->setEmployeePassword($data['employee_password']);
                        $employee->setEmployeeRole($data['employee_role']);
                        if (isset($data['store_id'])) {
                            $store = $entityManager->find(Stores::class, $data['store_id']);
                            if ($store) {
                                $employee->setStore($store);
                            } else {
                                throw new Exception('Store not found.');
                            }
                        }
                        $entityManager->flush();
                        echo json_encode(['message' => 'Employee updated']);
                    } else {
                        throw new Exception('Employee not found.');
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
                $employee = $entityManager->find(Employees::class, $_REQUEST["id"]);
                if ($employee) {
                    $entityManager->remove($employee);
                    $entityManager->flush();
                    echo json_encode(['message' => 'Employee deleted']);
                } else {
                    throw new Exception('Employee not found pour delete.');
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
//https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=getall"

//https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=getbyid&id=1"

// "https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=create" -d '{"employee_name": "John Doe", "employee_email": "john.doe@example.com", "employee_password": "password123", "employee_role": "Manager", "store_id": 1}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=update&id=1" -d '{"employee_name": "John Doe", "employee_email": "john.doe@example.com", "employee_password": "newpassword123", "employee_role": "Manager", "store_id": 1}' -H "Content-Type: application/json"

//"https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=delete&id=1"
?>