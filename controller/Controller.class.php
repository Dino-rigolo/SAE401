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
                'fields' => ['product_id', 'product_name', 'brand_name', 'category_name', 'model_year', 'list_price'],
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
            'stocks' => 'https://clafoutis.alwaysdata.net/SAE401/api/stocks'
        ];
        
        // Préparer les données pour l'API
        $data = $_POST;
        
        // Ajouter le paramètre action=create pour l'API
        $apiUrl = $apiUrls[$type] . '?action=create';
        
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
}

