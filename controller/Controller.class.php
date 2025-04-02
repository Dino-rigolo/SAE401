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
        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est déjà connecté via session
        if (!isset($_SESSION['employee'])) {
            // Sinon, vérifier s'il y a un cookie d'authentification
            $this->checkAuthCookie();
        }
        
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
     * Vérifie le cookie d'authentification
     */
    private function checkAuthCookie() {
        if (isset($_COOKIE['sae401_auth'])) {
            try {
                // Décoder les données du cookie
                $cookieData = json_decode(base64_decode($_COOKIE['sae401_auth']), true);
                
                // Vérifier que les données nécessaires sont présentes
                if (!isset($cookieData['id']) || !isset($cookieData['email']) || 
                    !isset($cookieData['expires']) || !isset($cookieData['hash'])) {
                    // Cookie invalide, le supprimer
                    setcookie('sae401_auth', '', time() - 3600, '/');
                    return;
                }
                
                // Vérifier si le cookie a expiré
                if ($cookieData['expires'] < time()) {
                    // Cookie expiré, le supprimer
                    setcookie('sae401_auth', '', time() - 3600, '/');
                    return;
                }
                
                // Récupérer les données de l'employé pour vérifier le hash
                $apiUrl = "https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=getbyid&id=" . urlencode($cookieData['id']);
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $employee = json_decode($response, true);
                
                // Vérifier que l'employé existe et que le hash correspond
                if (!$employee || isset($employee['error'])) {
                    setcookie('sae401_auth', '', time() - 3600, '/');
                    return;
                }
                
                // Recalculer le hash pour vérifier
                $expectedHash = hash('sha256', $employee['employee_id'] . $employee['employee_password'] . 'SAE401_SALT');
                
                // Si le hash correspond, connecter l'utilisateur
                if ($cookieData['hash'] === $expectedHash) {
                    $_SESSION['employee'] = [
                        'employee_id' => $employee['employee_id'],
                        'employee_name' => $employee['employee_name'],
                        'employee_email' => $employee['employee_email'],
                        'employee_role' => $employee['employee_role'],
                        'store_id' => isset($employee['store_id']) ? $employee['store_id'] : null
                    ];
                    
                    // Optionnel : renouveler le cookie pour prolonger la connexion
                    $cookieData['expires'] = time() + (30 * 24 * 60 * 60);
                    $cookieValue = base64_encode(json_encode($cookieData));
                    
                    setcookie(
                        'sae401_auth', 
                        $cookieValue, 
                        [
                            'expires' => time() + (30 * 24 * 60 * 60),
                            'path' => '/',
                            'domain' => '',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]
                    );
                } else {
                    // Hash invalide, supprimer le cookie
                    setcookie('sae401_auth', '', time() - 3600, '/');
                }
            } catch (Exception $e) {
                // En cas d'erreur, supprimer le cookie
                setcookie('sae401_auth', '', time() - 3600, '/');
            }
        }
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
        try {
            // Obtenir l'action depuis l'URL
            $this->action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'default';
            
            // Intercepter les actions d'ajout
            if (preg_match('/^add(brands|categories|shops|products|stocks)$/', $this->action)) {
                $this->handleAdd();
                return;
            }
            
            // Ajouter cette condition pour intercepter l'ajout d'employés
            if ($this->action === 'addemployees') {
                $this->addEmployees();
                return;
            }
            
            // Traiter les actions modif{type}
            if (preg_match('/^modif(brands|categories|shops|products|stocks|employees)$/', $this->action)) {
                $this->handleModif();
                return;
            }
            
            // Traiter les actions update{type}
            if (preg_match('/^update(brands|categories|shops|products|stocks|employees)$/', $this->action)) {
                $this->handleUpdate();
                return;
            }
            
            // Traiter les actions delete{type}
            if (preg_match('/^delete(brands|categories|shops|products|stocks|employees)$/', $this->action)) {
                $this->handleDelete();
                return;
            }
            
            // Vérifier si l'utilisateur est connecté
            $isConnected = isset($_SESSION['employee']);
            $isEmployee = $isConnected && isset($_SESSION['employee']);
            $isChef = $isConnected && isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'chef';
            $isIT = $isConnected && isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'it';
            
            // Pages accessibles sans connexion
            $publicPages = ['home', 'catalogue', 'connexion', 'shop', 'terms'];
            
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
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
                            $this->doLogin();
                        } else {
                            $this->showConnexion();
                        }
                        break;
                        
                    case 'shop':
                        $this->showShop();
                        break;
                        
                    case 'terms':
                        $this->showTerms();
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

                    case 'brands':
                        $this->showBrands();
                        break;
                    case 'categories':
                        $this->showCategories();
                        break;
                    case 'shops':
                        $this->showShops();
                        break;
                    case 'products':
                        if (isset($_GET['type'])) {
                            switch ($_GET['type']) {
                                case 'brands':
                                    $this->showBrands();
                                    break;
                                case 'categories':
                                    $this->showCategories();
                                    break;
                                case 'shops':
                                    $this->showShops();
                                    break;
                                case 'stocks':
                                    $this->showStocks();
                                    break;
                                default:
                                    $this->showProducts(); // Par défaut: afficher les produits
                                    break;
                            }
                        } else {
                            $this->showProducts(); // Si pas de type spécifié
                        }
                        break;
                    case 'stocks':
                        $this->showStocks();
                        break;
                        
                    default:
                        throw new ControllerException("Page non trouvée", 404);
                }
            }
            if (strpos($this->action, 'add') === 0) {
                $this->handleAdd();
                return;
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
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Récupérer les valeurs des champs du formulaire
                $email = isset($_POST['email']) ? $_POST['email'] : '';
                $password = isset($_POST['password']) ? $_POST['password'] : '';
                $remember = isset($_POST['remember']) ? true : false;
                
                if (empty($email) || empty($password)) {
                    throw new Exception("Veuillez fournir un email et un mot de passe");
                }
                
                // Appel à l'API
                $apiUrl = "https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=getbyemail&email=" . urlencode($email);
                
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                
                if (curl_errno($ch)) {
                    throw new Exception("Erreur de connexion à l'API");
                }
                
                curl_close($ch);
                
                // Traiter la réponse JSON
                $employee = json_decode($response, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Erreur de traitement de la réponse");
                }
                
                // Vérifier les données de l'employé
                if (isset($employee['error'])) {
                    throw new Exception("Email non trouvé");
                }
                
                if (!isset($employee['employee_password'])) {
                    throw new Exception("Données d'employé incomplètes");
                }
                
                // Vérifier le mot de passe
                if ($password === $employee['employee_password']) {
                    // Connexion réussie - initialiser la session
                    $_SESSION['employee'] = [
                        'employee_id' => $employee['employee_id'],
                        'employee_name' => $employee['employee_name'],
                        'employee_email' => $employee['employee_email'],
                        'employee_role' => $employee['employee_role'],
                        'store_id' => isset($employee['store_id']) ? $employee['store_id'] : null
                    ];
                    
                    // Si "Remember me" est coché, créer un cookie
                    if ($remember) {
                        // Créer les données à stocker dans le cookie
                        $cookieData = [
                            'id' => $employee['employee_id'],
                            'email' => $employee['employee_email'],
                            // Timestamp pour vérifier la date d'expiration
                            'expires' => time() + (30 * 24 * 60 * 60), // 30 jours
                            // Hash unique pour renforcer la sécurité
                            'hash' => hash('sha256', $employee['employee_id'] . $employee['employee_password'] . 'SAE401_SALT')
                        ];
                        
                        // Encoder et chiffrer les données
                        $cookieValue = base64_encode(json_encode($cookieData));
                        
                        // Créer le cookie qui expire dans 30 jours
                        setcookie(
                            'sae401_auth', 
                            $cookieValue, 
                            [
                                'expires' => time() + (30 * 24 * 60 * 60), // 30 jours
                                'path' => '/',
                                'domain' => '',
                                'secure' => true,    // Cookie uniquement via HTTPS
                                'httponly' => true,  // Non accessible via JavaScript
                                'samesite' => 'Lax' // Protection CSRF (Lax pour permettre les liens entrants)
                            ]
                        );
                    }
                    
                    $_SESSION['success'] = "Connexion réussie";
                    
                    // Redirection
                    header('Location: /SAE401/home');
                    exit;
                } else {
                    throw new Exception("Mot de passe incorrect");
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /SAE401/connexion');
            exit;
        }
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    private function doLogout() {
    // Supprimer le cookie d'authentification
    setcookie('sae401_auth', '', time() - 3600, '/');
    
    // Détruire la session
    session_unset();
    session_destroy();
    
    // Rediriger vers la page d'accueil
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
    $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/products");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $products = json_decode($response, true);
    curl_close($ch);
    
    // Récupérer les catégories via l'API
    $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/categories");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $categories = json_decode($response, true);
    curl_close($ch);
    
    // Récupérer les marques via l'API
    $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/brands");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $brands = json_decode($response, true);
    curl_close($ch);
    
    // Récupérer les filtres via l'API
    $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/ApiProducts.php?action=getfilters");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $filters = json_decode($response, true);
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
        if (!isset($this->id)) {
            throw new ControllerException("ID de la boutique manquant", 400);
        }
        
        // Utiliser le format d'URL correct pour l'API
        $apiUrl = "https://clafoutis.alwaysdata.net/SAE401/api/ApiStores.php?action=getbyid&id=" . urlencode($this->id);
        
        // Ajouter des logs pour le débogage
        error_log("Tentative d'accès à l'API: " . $apiUrl);
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        
        if ($response === false) {
            error_log("Erreur cURL: " . curl_error($ch));
            throw new ControllerException("Erreur de connexion à l'API", 500);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        error_log("Code de réponse HTTP: " . $httpCode);
        
        curl_close($ch);
        
        $store = json_decode($response, true);
        
        // Vérifier si la réponse contient une erreur
        if (isset($store['error'])) {
            error_log("Erreur API: " . $store['error']);
            throw new ControllerException($store['error'], 404);
        }
        
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
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/products/{$this->id}");
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
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/products/{$this->id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $product = json_decode($response, true);
        curl_close($ch);
        
        if (empty($product)) {
            throw new ControllerException("Produit non trouvé", 404);
        }
        
        // Récupérer les catégories via l'API
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/categories");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $categories = json_decode($response, true);
        curl_close($ch);
        
        // Récupérer les marques via l'API
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/brands");
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
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/employees");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $employees = json_decode($response, true);
        curl_close($ch);
        
        $isIT = isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'it';
        
        require_once __DIR__ . '/../view/ViewEmployees.php';
    }
    
    /**
     * Affiche la page des conditions d'utilisation
     */
    private function showTerms() {
        require_once __DIR__ . '/../view/ViewTermsConditions.php';
    }
    
    /**
     * Gère les erreurs
     */
    private function handleError(ControllerException $e) {
        $statusCode = $e->getCode();
        http_response_code($statusCode);
        
        $errorMessage = $e->getMessage();
        
        // Instancier et afficher la page d'erreur
        require_once __DIR__ . '/../view/ViewError.php';
        $errorView = new ViewError($statusCode, $errorMessage);
        $errorView->display();
    }

    /**
     * Méthode commune pour afficher la page des produits 
     * avec un type spécifique (brands, categories, etc.)
     */
    private function showProductsPage($type) {
        // Définir le type dans $_GET pour que la vue puisse l'utiliser
        $_GET['type'] = $type;
        
        // Titres en fonction du type
        $titles = [
            'brands' => 'Brands',
            'categories' => 'Categories',
            'shops' => 'Shops',
            'products' => 'Products',
            'stocks' => 'Stocks'
        ];
        
        // Structure des données pour chaque type
        $structure = [
            'brands' => [
                'columns' => ['ID', 'Brand Name'],
                'fields' => ['brand_id', 'brand_name'],
                'id_field' => 'brand_id',
                'form_fields' => [
                    ['name' => 'brand_name', 'label' => 'Brand Name', 'type' => 'text']
                ]
            ],
            'categories' => [
                'columns' => ['ID', 'Category Name'],
                'fields' => ['category_id', 'category_name'],
                'id_field' => 'category_id',
                'form_fields' => [
                    ['name' => 'category_name', 'label' => 'Category Name', 'type' => 'text']
                ]
            ],
            'shops' => [
                'columns' => ['ID', 'Store Name', 'Phone', 'Email', 'Address'],
                'fields' => ['store_id', 'store_name', 'phone', 'email', 'street'],
                'id_field' => 'store_id',
                'form_fields' => [
                    ['name' => 'store_name', 'label' => 'Store Name', 'type' => 'text'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'street', 'label' => 'Street Address', 'type' => 'text'],
                    ['name' => 'city', 'label' => 'City', 'type' => 'text'],
                    ['name' => 'state', 'label' => 'State', 'type' => 'text'],
                    ['name' => 'zip_code', 'label' => 'Zip Code', 'type' => 'text']
                ],
                // Ajoutez cette section pour la gestion des employés
                'employee_management' => true
            ],
            'products' => [
                'columns' => ['ID', 'Product Name', 'Brand', 'Category', 'Model Year', 'Price'],
                'fields' => ['product_id', 'product_name', 'brand_id', 'category_id', 'model_year', 'list_price'],
                'id_field' => 'product_id',
                'form_fields' => [
                    ['name' => 'product_name', 'label' => 'Product Name', 'type' => 'text'],
                    ['name' => 'brand_id', 'label' => 'Brand', 'type' => 'select', 'source' => 'brands'],
                    ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'source' => 'categories'],
                    ['name' => 'model_year', 'label' => 'Model Year', 'type' => 'number'],
                    ['name' => 'list_price', 'label' => 'Price', 'type' => 'number', 'step' => '0.01']
                ]
            ],
            'stocks' => [
                'columns' => ['ID', 'Store', 'Product', 'Quantity'],
                'fields' => ['stock_id', 'store_name', 'product_name', 'quantity'],
                'id_field' => 'stock_id',
                'form_fields' => [
                    ['name' => 'store_id', 'label' => 'Store', 'type' => 'select', 'source' => 'shops'],
                    ['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'source' => 'products'],
                    ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number']
                ]
            ]
        ];
        
        // API endpoints
        $api_endpoints = [
            'brands' => 'https://clafoutis.alwaysdata.net/SAE401/api/brands',
            'categories' => 'https://clafoutis.alwaysdata.net/SAE401/api/categories',
            'shops' => 'https://clafoutis.alwaysdata.net/SAE401/api/stores',
            'products' => 'https://clafoutis.alwaysdata.net/SAE401/api/products',
            'stocks' => 'https://clafoutis.alwaysdata.net/SAE401/api/stocks'
        ];
        
        // Juste avant d'initialiser le cURL pour les stocks
        if ($type === 'stocks') {
            error_log("Attempting to fetch stocks from: " . $api_endpoints[$type]);
        }

        // Récupérer les données via l'API
        $data = [];
        if (isset($api_endpoints[$type])) {
            $ch = curl_init($api_endpoints[$type]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            if ($response === false) {
                error_log('Erreur cURL : ' . curl_error($ch));
            } else {
                $data = json_decode($response, true);
                if ($data === null) {
                    error_log('Erreur JSON : ' . json_last_error_msg());
                }
            }
            curl_close($ch);
        }

        // Juste après avoir reçu la réponse pour les stocks
        if ($type === 'stocks') {
            error_log("Stocks API response: " . substr($response, 0, 200) . "...");  // Afficher les 200 premiers caractères
            error_log("HTTP code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE));
            error_log("JSON error: " . json_last_error_msg());
        }
        
        // Pour les champs de type select, récupérer les données source
        $select_options = [];
        $current_structure = isset($structure[$type]) ? $structure[$type] : $structure['brands'];
        $form_fields = $current_structure['form_fields'];
        
        foreach ($form_fields as $field) {
            if (isset($field['type']) && $field['type'] === 'select' && isset($field['source'])) {
                $source_type = $field['source'];
                if (isset($api_endpoints[$source_type])) {
                    $ch = curl_init($api_endpoints[$source_type]);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    if ($response !== false) {
                        $select_options[$field['name']] = json_decode($response, true);
                    }
                    curl_close($ch);
                }
            }
        }
        
        // Passer les données à la vue en tant que variables globales
        $GLOBALS['product_data'] = $data;
        $GLOBALS['product_structure'] = $structure;
        $GLOBALS['product_select_options'] = $select_options;
        $GLOBALS['product_title'] = isset($titles[$type]) ? $titles[$type] : 'Brands';
        
        // Dans Controller.class.php, dans la méthode showProductsPage, ajoutez ceci pour les stocks :
        if ($type === 'stocks') {
            // Vérifier et préparer les données des stocks
            foreach ($data as $key => $stock) {
                // Vérifier que toutes les clés nécessaires existent
                if (!isset($stock['store_name'])) {
                    $data[$key]['store_name'] = 'N/A';
                }
                if (!isset($stock['product_name'])) {
                    $data[$key]['product_name'] = 'N/A';
                }
                if (!isset($stock['quantity'])) {
                    $data[$key]['quantity'] = 0;
                }
            }
        } elseif ($type === 'stocks') {
            // Si $data n'est pas un tableau, initialiser à un tableau vide
            $data = [];
            error_log("API Stocks a retourné des données non valides");
        }

        require_once __DIR__ . '/../view/ViewProduct.php';
    }

    /**
     * Affiche la page des marques
     */
    private function showBrands() {
        $this->showProductsPage('brands');
    }

    /**
     * Affiche la page des catégories
     */
    private function showCategories() {
        $this->showProductsPage('categories');
    }

    /**
     * Affiche la page des boutiques
     */
    private function showShops() {
        $this->showProductsPage('shops');
    }

    /**
     * Affiche la page des produits
     */
    private function showProducts() {
        $this->showProductsPage('products');
    }

    /**
     * Affiche la page des stocks
     */
    private function showStocks() {
        $this->showProductsPage('stocks');
    }

    /**
     * Gère l'ajout d'un nouvel élément
     */
    private function handleAdd() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['employee'])) {
            throw new ControllerException("Vous devez être connecté pour effectuer cette action", 401);
        }
        
        // Récupérer le type d'élément à ajouter depuis l'URL
        $urlParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $action = $urlParts[1] ?? ''; // ex: addbrands
        $type = str_replace('add', '', $action); // ex: brands
        
        // Valider le type
        $validTypes = ['brands', 'categories', 'shops', 'products', 'stocks'];
        if (!in_array($type, $validTypes)) {
            throw new ControllerException("Type d'élément invalide", 400);
        }
        
        // Vérifier que les données POST sont présentes
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ControllerException("Méthode non autorisée", 405);
        }
        
        // Préparer l'URL de l'API
        $apiUrls = [
            'brands' => 'https://clafoutis.alwaysdata.net/SAE401/api/brands',
            'categories' => 'https://clafoutis.alwaysdata.net/SAE401/api/categories',
            'shops' => 'https://clafoutis.alwaysdata.net/SAE401/api/stores',
            'products' => 'https://clafoutis.alwaysdata.net/SAE401/api/products',
            'stocks' => 'https://clafoutis.alwaysdata.net/SAE401/api/stocks' // Changez cette URL
        ];
        
        // Préparer les données pour l'API
        $data = $_POST;

// S'assurer que les IDs sont au format numérique pour les stocks
if ($type === 'stocks') {
    if (isset($data['store_id'])) {
        $data['store_id'] = (int)$data['store_id'];
    }
    if (isset($data['product_id'])) {
        $data['product_id'] = (int)$data['product_id'];
    }
    if (isset($data['quantity'])) {
        $data['quantity'] = (int)$data['quantity'];
    }
    
    // Vérifier que les données nécessaires sont présentes
    if (!isset($data['store_id']) || !isset($data['product_id']) || !isset($data['quantity'])) {
        throw new ControllerException("Données incomplètes pour l'ajout de stock", 400);
    }
    
    // Ajouter des logs supplémentaires pour le débogage
    error_log("Envoi de données de stock: store_id=" . $data['store_id'] . ", product_id=" . $data['product_id'] . ", quantity=" . $data['quantity']);
}

// Log pour le débogage
error_log("Données envoyées à l'API: " . json_encode($data));

// Ajouter le paramètre action=create pour l'API
$apiUrl = $apiUrls[$type];
if ($type === 'stocks') {
    $apiUrl .= '?action=create';
    
    // Ajouter ces logs spécifiques pour les stocks
    error_log("Données de stock envoyées: " . json_encode([
        'store_id' => $data['store_id'],
        'product_id' => $data['product_id'],
        'quantity' => $data['quantity']
    ]));
} else {
    $apiUrl .= '?action=create';
}

// Enregistrer l'URL complète dans les logs
error_log("URL API complète: " . $apiUrl);
        
        // Envoyer à l'API
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Api: e8f1997c763' // Clé API
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Log pour le débogage
        error_log("API Response ($type): " . $response);
        error_log("HTTP Code: " . $httpCode);
        
        curl_close($ch);
        
        // Vérifier la réponse
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new ControllerException("Erreur lors de l'ajout: " . ($error['error'] ?? "Erreur inconnue"), 400);
        }
        
        // Redirection vers la liste avec message de succès
        header("Location: /SAE401/products?type=$type&success=1");
        exit;
    }

    /**
     * Ajoute un nouvel employé
     */
    private function addEmployees() {
        // Validation des droits d'accès
        if (!isset($_SESSION['employee'])) {
            throw new ControllerException("Accès non autorisé", 401);
        }
        
        $userRole = $_SESSION['employee']['employee_role'];
        $userStoreId = $_SESSION['employee']['store_id'] ?? null;
        $targetStoreId = isset($_POST['store_id']) ? (int)$_POST['store_id'] : null;
        
        // Vérifier les permissions
        $hasPermission = false;
        if ($userRole === 'it') {
            $hasPermission = true; // IT peut ajouter dans tous les magasins
        } else if ($userRole === 'chief' && $userStoreId === $targetStoreId) {
            $hasPermission = true; // Chief peut ajouter dans son magasin
        }
        
        if (!$hasPermission) {
            throw new ControllerException("Vous n'avez pas les droits pour ajouter un employé dans ce magasin", 403);
        }
        
        // Validation des données
        if (!isset($_POST['employee_name']) || !isset($_POST['employee_email']) || 
            !isset($_POST['employee_password']) || !isset($_POST['employee_role']) || 
            !isset($_POST['store_id'])) {
            throw new ControllerException("Données incomplètes", 400);
        }
        
        // Valider que le rôle est autorisé
        $validRoles = ['employee'];
        if ($userRole === 'it') {
            $validRoles[] = 'chief'; // IT peut ajouter des chiefs
        }
        
        if (!in_array($_POST['employee_role'], $validRoles)) {
            throw new ControllerException("Rôle non autorisé", 400);
        }
        
        // Préparation des données
        $data = [
            'employee_name' => htmlspecialchars($_POST['employee_name']),
            'employee_email' => htmlspecialchars($_POST['employee_email']),
            'employee_password' => $_POST['employee_password'], // Idéalement à hacher
            'employee_role' => $_POST['employee_role'],
            'store_id' => (int)$_POST['store_id']
        ];
        
        // Envoi à l'API
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/employees?action=create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Api: e8f1997c763' // Clé API
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Vérifier la réponse
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new ControllerException("Erreur lors de l'ajout: " . ($error['error'] ?? "Erreur inconnue"), 400);
        }
        
        // Redirection vers la liste des magasins
        header("Location: /SAE401/products?type=shops&success=1");
        exit;
    }

    /**
     * Gère la modification d'un élément
     */
    private function handleUpdate() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['employee'])) {
            throw new ControllerException("Vous devez être connecté pour effectuer cette action", 401);
        }
        
        // Récupérer le type d'élément à modifier depuis l'URL
        $urlParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $action = $urlParts[1] ?? ''; // update{type}
        $type = str_replace('update', '', $action); // Extrait 'brands' de 'updatebrands'
        
        // Valider le type
        $validTypes = ['brands', 'categories', 'shops', 'products', 'stocks', 'employees'];
        if (!in_array($type, $validTypes)) {
            throw new ControllerException("Type d'élément invalide", 400);
        }
        
        // Vérifier que les données POST sont présentes
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ControllerException("Méthode non autorisée", 405);
        }
        
        // Préparation des données pour l'API
        $data = $_POST;
        
        // Déterminer l'ID de l'élément à mettre à jour
        $id_fields = [
            'brands' => 'brand_id',
            'categories' => 'category_id',
            'shops' => 'store_id',
            'products' => 'product_id',
            'stocks' => 'stock_id',
            'employees' => 'employee_id'
        ];
        
        $id_field = $id_fields[$type] ?? '';
        $id = isset($data[$id_field]) ? $data[$id_field] : null;
        
        if (empty($id)) {
            throw new ControllerException("ID manquant pour la mise à jour", 400);
        }
        
        // Préparation de l'URL API
        $apiUrls = [
            'brands' => "https://clafoutis.alwaysdata.net/SAE401/api/brands/{$id}?action=update",
            'categories' => "https://clafoutis.alwaysdata.net/SAE401/api/categories/{$id}?action=update",
            'shops' => "https://clafoutis.alwaysdata.net/SAE401/api/stores/{$id}?action=update",
            'products' => "https://clafoutis.alwaysdata.net/SAE401/api/products/{$id}?action=update",
            'stocks' => "https://clafoutis.alwaysdata.net/SAE401/api/stocks/{$id}?action=update",
            'employees' => "https://clafoutis.alwaysdata.net/SAE401/api/employees/{$id}?action=update"
        ];
        
        // Log pour le débogage
        error_log("Données envoyées à l'API pour mise à jour: " . json_encode($data));
        
        // Envoyer à l'API
        $ch = curl_init($apiUrls[$type]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Api: e8f1997c763' // Clé API
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Log pour le débogage
        error_log("API Response ($type): " . $response);
        error_log("HTTP Code: " . $httpCode);
        
        curl_close($ch);
        
        // Vérifier la réponse
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new ControllerException("Erreur lors de la mise à jour: " . ($error['error'] ?? "Erreur inconnue"), 400);
        }
        
        // Redirection vers la liste avec message de succès
        header("Location: /SAE401/products?type=$type&update=1");
        exit;
    }

    /**
     * Méthode pour afficher le formulaire de modification
     */
    private function handleModif() {
        // Récupérer le type et l'ID dans l'URL
        $urlParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        
        if (count($urlParts) < 3) {
            throw new ControllerException("URL invalide", 400);
        }
        
        $action = $urlParts[1]; // modif{type}
        $type = str_replace('modif', '', $action); // Extraire 'brands' de 'modifbrands'
        
        $id = isset($this->id) ? $this->id : (isset($urlParts[2]) ? $urlParts[2] : null);

        if (empty($id)) {
            throw new ControllerException("ID manquant", 400);
        }
        
        // Valider le type
        $validTypes = ['brands', 'categories', 'shops', 'products', 'stocks', 'employees'];
        if (!in_array($type, $validTypes)) {
            throw new ControllerException("Type d'élément invalide", 400);
        }
        
        // Préparation de l'URL API
        $apiEndpoints = [
            'brands' => "https://clafoutis.alwaysdata.net/SAE401/api/brands/{$id}",
            'categories' => "https://clafoutis.alwaysdata.net/SAE401/api/categories/{$id}",
            'shops' => "https://clafoutis.alwaysdata.net/SAE401/api/stores/{$id}",
            'products' => "https://clafoutis.alwaysdata.net/SAE401/api/products/{$id}",
            'stocks' => "https://clafoutis.alwaysdata.net/SAE401/api/stocks/{$id}",
            'employees' => "https://clafoutis.alwaysdata.net/SAE401/api/employees/{$id}"
        ];
        
        // Récupérer les données de l'élément
        $ch = curl_init($apiEndpoints[$type]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $item = json_decode($response, true);

// Ajoutez ce log pour déboguer
error_log("Données récupérées pour $type : " . json_encode($item));

if (empty($item) || isset($item['error'])) {
    throw new ControllerException("Élément non trouvé ou erreur API", 404);
}

error_log("API Response for $type/$id: " . $response);
error_log("Data decoded: " . json_encode($item));

// Vérifier les données spécifiques pour chaque type
if ($type === 'products' && (!isset($item['brand_id']) || !isset($item['category_id']))) {
    error_log("WARNING: Product is missing brand_id or category_id");
    // Récupérer les données manquantes si possible
}
else if ($type === 'stocks' && (!isset($item['store_id']) || !isset($item['product_id']))) {
    error_log("WARNING: Stock is missing store_id or product_id");
    // Récupérer les données manquantes si possible
}
        
        // Structure pour les types d'éléments
        $structure = $this->getStructure();
        
        // Récupérer les options de sélection si nécessaire (pour les listes déroulantes)
        $select_options = [];
        
        // API endpoints pour les options de sélection
        $api_endpoints = [
            'brands' => 'https://clafoutis.alwaysdata.net/SAE401/api/brands',
            'categories' => 'https://clafoutis.alwaysdata.net/SAE401/api/categories',
            'shops' => 'https://clafoutis.alwaysdata.net/SAE401/api/stores',
            'products' => 'https://clafoutis.alwaysdata.net/SAE401/api/products',
            'employees' => 'https://clafoutis.alwaysdata.net/SAE401/api/employees'
        ];
        
        // Pour chaque champ de type "select", récupérer les options
        $form_fields = $structure[$type]['form_fields'];
        foreach ($form_fields as $field) {
            if (isset($field['type']) && $field['type'] === 'select' && isset($field['source'])) {
                $source_type = $field['source'];
                if (isset($api_endpoints[$source_type])) {
                    $ch = curl_init($api_endpoints[$source_type]);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    if ($response !== false) {
                        $select_options[$field['name']] = json_decode($response, true);
                    }
                    curl_close($ch);
                }
            }
        }
        
        // Passer les données à la vue
        $GLOBALS['page_type'] = $type;
        $GLOBALS['item_data'] = $item;
        $GLOBALS['product_structure'] = $structure;
        $GLOBALS['product_select_options'] = $select_options;
        $GLOBALS['product_title'] = isset($this->getTitles()[$type]) ? $this->getTitles()[$type] : 'Element';
        
        require_once __DIR__ . '/../view/ViewModifProduct.php';
    }

    /**
     * Retourne la structure des données pour chaque type
     */
    private function getStructure() {
        return [
            'brands' => [
                'columns' => ['ID', 'Brand Name'],
                'fields' => ['brand_id', 'brand_name'],
                'id_field' => 'brand_id',
                'form_fields' => [
                    ['name' => 'brand_name', 'label' => 'Brand Name', 'type' => 'text']
                ]
            ],
            'categories' => [
                'columns' => ['ID', 'Category Name'],
                'fields' => ['category_id', 'category_name'],
                'id_field' => 'category_id',
                'form_fields' => [
                    ['name' => 'category_name', 'label' => 'Category Name', 'type' => 'text']
                ]
            ],
            'shops' => [
                'columns' => ['ID', 'Store Name', 'Phone', 'Email', 'Address'],
                'fields' => ['store_id', 'store_name', 'phone', 'email', 'street'],
                'id_field' => 'store_id',
                'form_fields' => [
                    ['name' => 'store_name', 'label' => 'Store Name', 'type' => 'text'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'street', 'label' => 'Street Address', 'type' => 'text']
                ]
            ],
            'products' => [
                'columns' => ['ID', 'Product Name', 'Brand', 'Category', 'Model Year', 'Price'],
                'fields' => ['product_id', 'product_name', 'brand_id', 'category_id', 'model_year', 'list_price'],
                'id_field' => 'product_id',
                'form_fields' => [
                    ['name' => 'product_name', 'label' => 'Product Name', 'type' => 'text'],
                    ['name' => 'brand_id', 'label' => 'Brand', 'type' => 'select', 'source' => 'brands'],
                    ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'source' => 'categories'],
                    ['name' => 'model_year', 'label' => 'Model Year', 'type' => 'number'],
                    ['name' => 'list_price', 'label' => 'Price', 'type' => 'number', 'step' => '0.01']
                ]
            ],
            'stocks' => [
                'columns' => ['ID', 'Store', 'Product', 'Quantity'],
                'fields' => ['stock_id', 'store_id', 'product_id', 'quantity'],
                'id_field' => 'stock_id',
                'form_fields' => [
                    ['name' => 'store_id', 'label' => 'Store', 'type' => 'select', 'source' => 'shops'],
                    ['name' => 'product_id', 'label' => 'Product', 'type' => 'select', 'source' => 'products'],
                    ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number']
                ]
            ],
            'employees' => [
                'columns' => ['ID', 'Name', 'Email', 'Role', 'Store'],
                'fields' => ['employee_id', 'employee_name', 'employee_email', 'employee_role', 'store_id'],
                'id_field' => 'employee_id',
                'form_fields' => [
                    ['name' => 'employee_name', 'label' => 'Name', 'type' => 'text'],
                    ['name' => 'employee_email', 'label' => 'Email', 'type' => 'email'],
                    ['name' => 'employee_password', 'label' => 'Password', 'type' => 'password'],
                    ['name' => 'employee_role', 'label' => 'Role', 'type' => 'select', 'options' => [
                        ['value' => 'employee', 'label' => 'Employee'],
                        ['value' => 'chief', 'label' => 'Chief'],
                        ['value' => 'it', 'label' => 'IT']
                    ]],
                    ['name' => 'store_id', 'label' => 'Store', 'type' => 'select', 'source' => 'shops']
                ]
            ]
        ];
    }

    /**
     * Retourne les titres pour chaque type
     */
    private function getTitles() {
        return [
            'brands' => 'Brands',
            'categories' => 'Categories',
            'shops' => 'Shops',
            'products' => 'Products',
            'stocks' => 'Stocks',
            'employees' => 'Employees'
        ];
    }

    private function handleDelete() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['employee'])) {
            throw new ControllerException("Vous devez être connecté pour effectuer cette action", 401);
        }

        // Récupérer le type et l'ID depuis l'URL
        $urlParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $action = $urlParts[1]; // delete{type}
        $id = $urlParts[2] ?? null;

        $type = str_replace('delete', '', $action); // Extrait 'brands' de 'deletebrands'

        // Valider le type
        $validTypes = ['brands', 'categories', 'shops', 'products', 'stocks', 'employees'];
        if (!in_array($type, $validTypes)) {
            throw new ControllerException("Type d'élément invalide", 400);
        }

        if (empty($id)) {
            throw new ControllerException("ID manquant pour la suppression", 400);
        }

        // Préparer l'URL de l'API
        $apiUrls = [
            'brands' => "https://clafoutis.alwaysdata.net/SAE401/api/brands/{$id}?action=delete",
            'categories' => "https://clafoutis.alwaysdata.net/SAE401/api/categories/{$id}?action=delete",
            'shops' => "https://clafoutis.alwaysdata.net/SAE401/api/stores/{$id}?action=delete",
            'products' => "https://clafoutis.alwaysdata.net/SAE401/api/products/{$id}?action=delete",
            'stocks' => "https://clafoutis.alwaysdata.net/SAE401/api/stocks/{$id}?action=delete",
            'employees' => "https://clafoutis.alwaysdata.net/SAE401/api/employees/{$id}?action=delete"
        ];

        // Envoyer la requête à l'API
        $ch = curl_init($apiUrls[$type]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Vérifier la réponse
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new ControllerException("Erreur lors de la suppression : " . ($error['error'] ?? "Erreur inconnue"), 400);
        }

        // Rediriger vers la liste avec un message de succès
        header("Location: /SAE401/products?type={$type}&delete=1");
        exit;
    }
}

