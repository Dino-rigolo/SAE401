<?php

require_once 'ControllerException.class.php';
require_once __DIR__ . '/../bootstrap.php';

/**
 * Classe Controller principale
 * 
 * Gère toutes les requêtes entrantes et redirige vers les bonnes vues ou appelle les services API
 */
class Controller {
    private $entityManager;
    private $request;
    private $action;
    private $email;
    private $password;
    private $id;
    private $role;
    
    /**
     * Constructeur du contrôleur
     */
    public function __construct($request_params = []) {
        global $entityManager;
        $this->entityManager = $entityManager;
        $this->request = $_SERVER['REQUEST_URI'];
        
        // Récupère les paramètres de la requête depuis $request_params
        if (isset($request_params['action'])) {
            $this->action = $request_params['action'];
        }
        if (isset($request_params['email'])) {
            $this->email = $request_params['email'];
        }
        if (isset($request_params['password'])) {
            $this->password = $request_params['password'];
        }
        if (isset($request_params['id'])) {
            $this->id = $request_params['id'];
        }
        
        $this->parseRequest();
    }
    
    /**
     * Parse la requête entrante
     */
    private function parseRequest() {
        // Extraire l'action de l'URL
        $parsedUrl = parse_url($this->request);
        $path = $parsedUrl['path'];
        
        // Supprimer le préfixe de base si nécessaire
        $basePath = '/SAE401/';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Diviser le chemin en segments
        $segments = explode('/', trim($path, '/'));
        
        // Le premier segment est l'action principale si pas déjà défini
        if (!isset($this->action)) {
            if (!empty($segments[0])) {
                $this->action = $segments[0];
            } else {
                // Si aucune action n'est spécifiée, rediriger vers la page d'accueil
                $this->action = 'home';
            }
        }
    }
    
    /**
     * Exécute l'action demandée
     */
    public function execute() {
        // Pour le débogage
        error_log("Action : " . $this->action);
        error_log("Méthode HTTP : " . $_SERVER['REQUEST_METHOD']);
        
        try {
            // Vérifier si l'utilisateur est connecté
            $isConnected = isset($_SESSION['employee']);
            $isEmployee = $isConnected && isset($_SESSION['employee']);
            $isChef = $isConnected && isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'chef';
            $isIT = $isConnected && isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'it';
            
            // Pages accessibles sans connexion
            $publicPages = ['home', 'catalogue', 'connexion', 'shop'];
            
            // Si l'utilisateur n'est pas connecté et tente d'accéder à une page protégée
            if (!$isConnected && !in_array($this->action, $publicPages)) {
                throw new ControllerException("Vous devez être connecté pour accéder à cette page", 401);
            }
            
            // Traitement des actions selon l'état de connexion
            if (!$isConnected) {
                // Actions pour utilisateurs non connectés
                switch ($this->action) {
                    case 'home':
                        $this->showHome();
                        break;
                        
                    case 'catalogue':
                        $this->showCatalogue();
                        break;
                        
                    case 'connexion':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($this->email) && isset($this->password)) {
                            $this->doLogin();
                        } else {
                            $this->showConnexion();
                        }
                        break;
                        
                    case 'shop':
                        $this->showShop();
                        break;
                        
                    default:
                        throw new ControllerException("Page non trouvée", 404);
                }
            } else {
                // Actions pour utilisateurs connectés
                switch ($this->action) {
                    case 'home':
                        $this->showHome();
                        break;
                        
                    case 'catalogue':
                        $this->showCatalogue();
                        break;
                        
                    case 'shop':
                        $this->showShop();
                        break;
                        
                    case 'deconnexion':
                        $this->doLogout();
                        break;
                        
                    case 'information':
                        $this->showInformation();
                        break;
                        
                    case 'product':
                        $this->showProduct();
                        break;
                        
                    case 'modifProduct':
                        if (!$isEmployee) {
                            throw new ControllerException("Accès non autorisé", 403);
                        }
                        $this->showModifProduct();
                        break;
                        
                    case 'employees':
                        if (!$isChef && !$isIT) {
                            throw new ControllerException("Accès non autorisé", 403);
                        }
                        $this->showEmployees();
                        break;
                        
                    default:
                        throw new ControllerException("Page non trouvée", 404);
                }
            }
        } catch (ControllerException $e) {
            $this->handleError($e);
        } catch (Exception $e) {
            $this->handleError(new ControllerException($e->getMessage(), 500));
        }
    }
    
    /**
     * Effectue la connexion de l'utilisateur
     */
    private function doLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            // Pour le débogage
            error_log("Tentative de connexion avec : $email");
            
            // Appel à l'API Employees pour vérifier les informations
            $apiUrl = "http://localhost/SAE401/api/employees?action=getbyemail&email=" . urlencode($email);
            error_log("Appel API : $apiUrl");
            
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            error_log("Réponse API : $response");
            
            $employee = json_decode($response, true);
            
            // Vérifier les informations d'identification
            if ($employee && password_verify($password, $employee['employee_password'])) {
                // Connexion réussie
                $_SESSION['employee'] = $employee;
                $_SESSION['success'] = "Connexion réussie";
                
                // Redirection vers la page d'accueil
                header('Location: /SAE401/home');
                exit;
            } else {
                // Echec de la connexion
                $_SESSION['error'] = "Email ou mot de passe incorrect";
                header('Location: /SAE401/connexion');
                exit;
            }
        }
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    private function doLogout() {
        session_unset();
        session_destroy();
        header('Location: /SAE401/home');
        exit;
    }
    
    /**
     * Affiche la page d'accueil
     */
    private function showHome() {
        require_once __DIR__ . '/../view/ViewHome.php';
    }
    
    /**
     * Affiche la page du catalogue
     */
    private function showCatalogue() {
        // Récupérer les produits via l'API
        $ch = curl_init("http://localhost/SAE401/api/products");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $products = json_decode($response, true);
        curl_close($ch);
        
        // Récupérer les catégories via l'API
        $ch = curl_init("http://localhost/SAE401/api/categories");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $categories = json_decode($response, true);
        curl_close($ch);
        
        // Récupérer les marques via l'API
        $ch = curl_init("http://localhost/SAE401/api/brands");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $brands = json_decode($response, true);
        curl_close($ch);
        
        require_once __DIR__ . '/../view/ViewCatalogue.php';
    }
    
    /**
     * Affiche la page de connexion
     */
    private function showConnexion() {
        require_once __DIR__ . '/../view/ViewConnexion.php';
    }
    
    /**
     * Affiche la page de la boutique
     */
    private function showShop() {
        // Récupérer les produits via l'API
        $ch = curl_init("http://localhost/SAE401/api/products");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $products = json_decode($response, true);
        curl_close($ch);
        
        require_once __DIR__ . '/../view/ViewShop.php';
    }
    
    /**
     * Affiche la page d'information
     */
    private function showInformation() {
        require_once __DIR__ . '/../view/ViewInformation.php';
    }
    
    /**
     * Affiche la page d'un produit
     */
    private function showProduct() {
        if (!isset($this->id)) {
            throw new ControllerException("ID du produit manquant", 400);
        }
        
        // Récupérer le produit via l'API
        $ch = curl_init("http://localhost/SAE401/api/products/{$this->id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $product = json_decode($response, true);
        curl_close($ch);
        
        if (empty($product)) {
            throw new ControllerException("Produit non trouvé", 404);
        }
        
        require_once __DIR__ . '/../view/ViewProduct.php';
    }
    
    /**
     * Affiche la page de modification d'un produit
     */
    private function showModifProduct() {
        if (!isset($this->id)) {
            throw new ControllerException("ID du produit manquant", 400);
        }
        
        // Récupérer le produit via l'API
        $ch = curl_init("http://localhost/SAE401/api/products/{$this->id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $product = json_decode($response, true);
        curl_close($ch);
        
        if (empty($product)) {
            throw new ControllerException("Produit non trouvé", 404);
        }
        
        // Récupérer les catégories via l'API
        $ch = curl_init("http://localhost/SAE401/api/categories");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $categories = json_decode($response, true);
        curl_close($ch);
        
        // Récupérer les marques via l'API
        $ch = curl_init("http://localhost/SAE401/api/brands");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $brands = json_decode($response, true);
        curl_close($ch);
        
        require_once __DIR__ . '/../view/ViewModifProduct.php';
    }
    
    /**
     * Affiche la page des employés
     */
    private function showEmployees() {
        // Récupérer les employés via l'API
        $ch = curl_init("http://localhost/SAE401/api/employees");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $employees = json_decode($response, true);
        curl_close($ch);
        
        $isIT = isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'it';
        
        require_once __DIR__ . '/../view/ViewEmployees.php';
    }
    
    /**
     * Gère les erreurs
     */
    private function handleError(ControllerException $e) {
        $statusCode = $e->getCode();
        http_response_code($statusCode);
        
        $errorMessage = $e->getMessage();
        
        // Afficher la page d'erreur
        require_once __DIR__ . '/../view/ViewError.php';
    }
}

// Instanciation et exécution du contrôleur
$controller = new Controller();
$controller->execute();