<?php

require_once 'ControllerException.class.php';
require_once __DIR__ . '/../bootstrap.php';

/**
 * Main Controller class for the BikeStore application
 * 
 * Handles all HTTP requests, authentication, and business logic
 * for the BikeStore management system
 * 
 * @package BikeStore
 * @author 
 * @version 1.0
 */
class Controller {
    /** @var EntityManager Doctrine entity manager instance */
    private $entityManager;
    
    /** @var string Current request URI */
    private $request;
    
    /** @var string Current action to execute */
    private $action;
    
    /** @var string User email from request */
    private $email;
    
    /** @var string User password from request */
    private $password;
    
    /** @var int Entity ID from request */
    private $id;
    
    /** @var string User role */
    private $role;

    /**
     * Controller constructor
     * 
     * Initializes the controller, starts session if needed,
     * checks authentication cookie and sets up request parameters
     * 
     * @param array $request_params Request parameters
     */
    public function __construct($request_params = []) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['employee'])) {
            $this->checkAuthCookie();
        }
        
        global $entityManager;
        $this->entityManager = $entityManager;
        $this->request = $_SERVER['REQUEST_URI'];
        
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
     * Checks if user has a valid authentication cookie
     * 
     * Validates the auth cookie, fetches user data from API
     * and creates a new session if cookie is valid
     * 
     * @return void
     * @throws Exception If cookie validation fails
     */
    private function checkAuthCookie() {
        error_log("RECEIVED COOKIES: " . print_r($_COOKIE, true));
        if (isset($_COOKIE['sae401_auth'])) {
            try {
                error_log("Checking authentication cookie");
                $cookieData = json_decode(base64_decode($_COOKIE['sae401_auth']), true);
                
                          ", email=" . isset($cookieData['email']) . 
                          ", expires=" . isset($cookieData['expires']) . 
                          ", hash=" . isset($cookieData['hash']);
                if (!isset($cookieData['id']) || !isset($cookieData['email']) || 
                    !isset($cookieData['expires']) || !isset($cookieData['hash'])) {
                    error_log("Incomplete cookie, deleting");
                    setcookie('sae401_auth', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'domain' => '.clafoutis.alwaysdata.net',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'None'
                    ]);
                    return;
                }
                
                if ($cookieData['expires'] < time()) {
                    error_log("Expired cookie, deleting");
                    setcookie('sae401_auth', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'domain' => '.clafoutis.alwaysdata.net',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'None'
                    ]);
                    return;
                }
                
                $apiUrl = "https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=getbyid&id=" . urlencode($cookieData['id']);
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $employee = json_decode($response, true);
                error_log("API Response: " . json_encode($employee));
                
                if (!$employee || isset($employee['error']) || 
                    !isset($employee['employee_id']) || !isset($employee['employee_password'])) {
                    error_log("Invalid employee data, deleting cookie");
                    setcookie('sae401_auth', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'domain' => '.clafoutis.alwaysdata.net',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'None'
                    ]);
                    return;
                }
                
                $expectedHash = hash('sha256', $employee['employee_id'] . $employee['employee_password'] . 'SAE401_SALT');
                
                if ($cookieData['hash'] === $expectedHash) {
                    $_SESSION['employee'] = [
                        'employee_id' => $employee['employee_id'],
                        'employee_name' => $employee['employee_name'],
                        'employee_email' => $employee['employee_email'],
                        'employee_role' => $employee['employee_role'],
                        'store_id' => isset($employee['store_id']) ? $employee['store_id'] : null
                    ];
                    
                    $cookieData['expires'] = time() + (30 * 24 * 60 * 60);
                    $cookieValue = base64_encode(json_encode($cookieData));
                    
                    setcookie(
                        'sae401_auth', 
                        $cookieValue,
                        [
                            'expires' => time() + (30 * 24 * 60 * 60),
                            'path' => '/',
                            'domain' => '.clafoutis.alwaysdata.net',
                            'secure' => true,
                            'httponly' => true, 
                            'samesite' => 'None'
                        ]
                    );
                    error_log("Automatic reconnection successful for: " . $employee['employee_email']);
                } else {
                    error_log("Invalid hash, deleting cookie");
                    setcookie('sae401_auth', '', [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'domain' => '.clafoutis.alwaysdata.net',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'None'
                    ]);
                }
            } catch (Exception $e) {
                error_log("Error in checkAuthCookie: " . $e->getMessage());
                setcookie('sae401_auth', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '.clafoutis.alwaysdata.net',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'None'
                ]);
            }
        }
    }
    
    private function parseRequest() {
        $parsedUrl = parse_url($this->request);
        $path = $parsedUrl['path'];
        
        $basePath = '/SAE401/';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        $segments = explode('/', trim($path, '/'));
        
        if (!isset($this->action)) {
            if (!empty($segments[0])) {
                $this->action = $segments[0];
            } else {
                $this->action = 'home';
            }
        }
    }

    /**
     * Main execution method
     * 
     * Routes the request to appropriate handler methods
     * based on action and user permissions
     * 
     * @return void
     * @throws ControllerException For invalid requests or unauthorized access
     */
    public function execute() {
        try {
            $this->action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'default';
            
            if (preg_match('/^add(brands|categories|shops|products|stocks)$/', $this->action)) {
                $this->handleAdd();
                return;
            }
            
            if ($this->action === 'addemployees') {
                $this->addEmployees();
                return;
            }
            
            if (preg_match('/^modif(brands|categories|shops|products|stocks|employees)$/', $this->action)) {
                $this->handleModif();
                return;
            }
            
            if (preg_match('/^update(brands|categories|shops|products|stocks|employees)$/', $this->action)) {
                $this->handleUpdate();
                return;
            }
            
            if (preg_match('/^delete(brands|categories|shops|products|stocks|employees)$/', $this->action)) {
                $this->handleDelete();
                return;
            }
            
            $isConnected = isset($_SESSION['employee']);
            $isEmployee = $isConnected && isset($_SESSION['employee']);
            $isChef = $isConnected && isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'chief';
            $isIT = $isConnected && isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'it';
            
            $publicPages = ['home', 'catalogue', 'connexion', 'shop', 'terms'];
            
            if (!$isConnected && !in_array($this->action, $publicPages)) {
                throw new ControllerException("You must be logged in to access this page", 401);
            }
            
            if (!$isConnected) {
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
                        throw new ControllerException("Page not found", 404);
                }
            } else {
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
                            throw new ControllerException("Access forbidden", 403);
                        }
                        $this->showModifProduct();
                        break;
                        
                    case 'employees':
                        if (!$isChef && !$isIT) {
                            throw new ControllerException("Access forbidden", 403);
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
                                    $this->showProducts();
                                    break;
                            }
                        } else {
                            $this->showProducts();
                        }
                        break;
                    case 'stocks':
                        $this->showStocks();
                        break;
                    case 'terms':
                        $this->showTerms();
                        break;
                    case 'api-docs':
                        // Redirect to Swagger API documentation
                        header('Location: /SAE401/docs swagger/index.html');
                        exit;
                        break;
                        
                    default:
                        throw new ControllerException("Page not found", 404);
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
     * Handles the login process
     * 
     * Validates credentials against the API, creates session
     * and sets authentication cookie if "remember me" is checked
     * 
     * @return void
     * @throws Exception For invalid credentials or API errors
     */
    private function doLogin() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = isset($_POST['email']) ? $_POST['email'] : '';
                $password = isset($_POST['password']) ? $_POST['password'] : '';
                $remember = isset($_POST['remember']) ? true : false;
                
                if (empty($email) || empty($password)) {
                    throw new Exception("Please provide an email address and a password");
                }
                
                $apiUrl = "https://clafoutis.alwaysdata.net/SAE401/api/ApiEmployees.php?action=getbyemail&email=" . urlencode($email);
                
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                
                if (curl_errno($ch)) {
                    throw new Exception("API connection error");
                }
                
                curl_close($ch);
                
                $employee = json_decode($response, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Response processing error");
                }
                
                if (isset($employee['error'])) {
                    throw new Exception("Incorrect email or password ");
                }
                
                if (!isset($employee['employee_password'])) {
                    throw new Exception("Incomplete employee data");
                }
                
                if ($password === $employee['employee_password']) {
                    $_SESSION['employee'] = [
                        'employee_id' => $employee['employee_id'],
                        'employee_name' => $employee['employee_name'],
                        'employee_email' => $employee['employee_email'],
                        'employee_role' => $employee['employee_role'],
                        'store_id' => isset($employee['store_id']) ? $employee['store_id'] : null
                    ];
                    
                    if ($remember) {
                        $cookieData = [
                            'id' => $employee['employee_id'],
                            'email' => $employee['employee_email'],
                            'expires' => time() + (30 * 24 * 60 * 60),
                            'hash' => hash('sha256', $employee['employee_id'] . $employee['employee_password'] . 'SAE401_SALT')
                        ];
                        
                        $cookieValue = base64_encode(json_encode($cookieData));
                        
                        setcookie(
                            'sae401_auth', 
                            $cookieValue,
                            [
                                'expires' => time() + (30 * 24 * 60 * 60),
                                'path' => '/',
                                'domain' => '.clafoutis.alwaysdata.net',
                                'secure' => true,
                                'httponly' => true,
                                'samesite' => 'None'
                            ]
                        );
                        
                        error_log("Cookie set: " . print_r($_COOKIE, true));
                    }
                    
                    $_SESSION['success'] = "Successful connection";
                    
                    header('Location: /SAE401/home');
                    exit;
                } else {
                    throw new Exception("incorrect email or password");
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /SAE401/connexion');
            exit;
        }
    }

    /**
     * Handles user logout
     * 
     * Removes authentication cookie and destroys session
     * 
     * @return void
     */
    private function doLogout() {
    setcookie('sae401_auth', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '.clafoutis.alwaysdata.net',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
    
    session_unset();
    session_destroy();
    
    header('Location: /SAE401/home');
    exit;
    }
    
    private function showHome() {
        require_once __DIR__ . '/../view/ViewHome.php';
    }

    private function showCatalogue() {
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/products");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $products = json_decode($response, true);
        curl_close($ch);
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/categories");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $categories = json_decode($response, true);
        curl_close($ch);
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/brands");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $brands = json_decode($response, true);
        curl_close($ch);
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/ApiProducts.php?action=getfilters");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $filters = json_decode($response, true);
        curl_close($ch);
        require_once __DIR__ . '/../view/ViewCatalogue.php';
    }
    
    private function showConnexion() {
        require_once __DIR__ . '/../view/ViewConnexion.php';
    }
    
    private function showShop() {
        if (!isset($this->id)) {
            throw new ControllerException("Missing store ID", 400);
        }
        
        $apiUrl = "https://clafoutis.alwaysdata.net/SAE401/api/ApiStores.php?action=getbyid&id=" . urlencode($this->id);
        
        error_log("Attempt to access the API: " . $apiUrl);
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        
        if ($response === false) {
            error_log("cURL Error: " . curl_error($ch));
            throw new ControllerException("API connection error", 500);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        error_log("HTTP Response Code: " . $httpCode);
        
        curl_close($ch);
        
        $store = json_decode($response, true);
        
        if (isset($store['error'])) {
            error_log("API Error: " . $store['error']);
            throw new ControllerException($store['error'], 404);
        }
     
        require_once __DIR__ . '/../view/ViewShop.php';
    }
    
    private function showInformation() {
        require_once __DIR__ . '/../view/ViewInformation.php';
    }
    
    private function showProduct() {
        if (!isset($this->id)) {
            throw new ControllerException("Missing product ID", 400);
        }
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/products/{$this->id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $product = json_decode($response, true);
        curl_close($ch);
        
        if (empty($product)) {
            throw new ControllerException("Product not found", 404);
        }
        
        require_once __DIR__ . '/../view/ViewProduct.php';
    }
    
    private function showModifProduct() {
        if (!isset($this->id)) {
            throw new ControllerException("Missing product ID", 400);
        }
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/products/{$this->id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $product = json_decode($response, true);
        curl_close($ch);
        
        if (empty($product)) {
            throw new ControllerException("Product not found", 404);
        }
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/categories");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $categories = json_decode($response, true);
        curl_close($ch);
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/brands");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $brands = json_decode($response, true);
        curl_close($ch);
        
        require_once __DIR__ . '/../view/ViewModifProduct.php';
    }
    
    private function showEmployees() {
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/employees");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $employees = json_decode($response, true);
        curl_close($ch);
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/stores");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $stores = json_decode($response, true);
        curl_close($ch);
        
        $GLOBALS['employees_data'] = $employees;
        $GLOBALS['stores_data'] = $stores;
        
        require_once __DIR__ . '/../view/ViewEmployees.php';
    }
    /**
     * Displays the Terms & Conditions page
     * 
     * @return void
     */
    private function showTerms() {
        require_once __DIR__ . '/../view/ViewTermsConditions.php';
    }
    
    private function handleError(ControllerException $e) {
        $statusCode = $e->getCode();
        http_response_code($statusCode);
        
        $errorMessage = $e->getMessage();
        
        require_once __DIR__ . '/../view/ViewError.php';
        $errorView = new ViewError($statusCode, $errorMessage);
        $errorView->display();
    }

    /**
     * Shows product listing page
     * 
     * Displays products with filtering and sorting options
     * 
     * @param string $type Type of products to display (brands|categories|shops|products|stocks)
     * @return void
     */
    private function showProductsPage($type) {
        $_GET['type'] = $type;
        
        $titles = [
            'brands' => 'Brands',
            'categories' => 'Categories',
            'shops' => 'Shops',
            'products' => 'Products',
            'stocks' => 'Stocks'
        ];
        
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
        
        $api_endpoints = [
            'brands' => 'https://clafoutis.alwaysdata.net/SAE401/api/brands',
            'categories' => 'https://clafoutis.alwaysdata.net/SAE401/api/categories',
            'shops' => 'https://clafoutis.alwaysdata.net/SAE401/api/stores',
            'products' => 'https://clafoutis.alwaysdata.net/SAE401/api/products',
            'stocks' => 'https://clafoutis.alwaysdata.net/SAE401/api/stocks'
        ];
        
        if ($type === 'stocks') {
            error_log("Attempting to fetch stocks from: " . $api_endpoints[$type]);
        }

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

        if ($type === 'stocks') {
            error_log("Stocks API response: " . substr($response, 0, 200) . "...");
            error_log("HTTP code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE));
            error_log("JSON error: " . json_last_error_msg());
        }
        
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
        
        $GLOBALS['product_data'] = $data;
        $GLOBALS['product_structure'] = $structure;
        $GLOBALS['product_select_options'] = $select_options;
        $GLOBALS['product_title'] = isset($titles[$type]) ? $titles[$type] : 'Brands';
        
        if ($type === 'stocks') {
            foreach ($data as $key => $stock) {
                if (!isset($stock['store_name'])) {
                    $data[$key]['store_name'] = 'Not specified';
                }
                if (!isset($stock['product_name'])) {
                    $data[$key]['product_name'] = 'Not specified';
                }
                if (!isset($stock['quantity'])) {
                    $data[$key]['quantity'] = 0;
                }
            }
        } elseif ($type === 'stocks') {
            $data = [];
            error_log("API Stocks returned invalid data");
        }

        require_once __DIR__ . '/../view/ViewProduct.php';
    }

    private function showBrands() {
        $this->showProductsPage('brands');
    }

    private function showCategories() {
        $this->showProductsPage('categories');
    }

    private function showShops() {
        $this->showProductsPage('shops');
    }

    private function showProducts() {
        $this->showProductsPage('products');
    }

    private function showStocks() {
        $this->showProductsPage('stocks');
    }

    /**
     * Handles addition of new entities
     * 
     * Processes POST requests to add new brands, categories,
     * shops, products or stocks
     * 
     * @return void
     * @throws ControllerException For validation or API errors
     */
    private function handleAdd() {
        if (!isset($_SESSION['employee'])) {
            throw new ControllerException("You must be logged in to perform this action", 401);
        }
        
        $urlParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $action = $urlParts[1] ?? '';
        $type = str_replace('add', '', $action);
        
        $validTypes = ['brands', 'categories', 'shops', 'products', 'stocks'];
        if (!in_array($type, $validTypes)) {
            throw new ControllerException("Invalid element type", 400);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ControllerException("Unauthorised method", 405);
        }
        
        $apiUrls = [
            'brands' => 'https://clafoutis.alwaysdata.net/SAE401/api/brands',
            'categories' => 'https://clafoutis.alwaysdata.net/SAE401/api/categories',
            'shops' => 'https://clafoutis.alwaysdata.net/SAE401/api/stores',
            'products' => 'https://clafoutis.alwaysdata.net/SAE401/api/products',
            'stocks' => 'https://clafoutis.alwaysdata.net/SAE401/api/stocks'
        ];
        
        $data = $_POST;

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
    
    if (!isset($data['store_id']) || !isset($data['product_id']) || !isset($data['quantity'])) {
        throw new ControllerException("Incomplete data for adding stock", 400);
    }
    
    error_log("Sending stock data: store_id=" . $data['store_id'] . ", product_id=" . $data['product_id'] . ", quantity=" . $data['quantity']);
}

error_log("Data sent to the API: " . json_encode($data));

$apiUrl = $apiUrls[$type];
if ($type === 'stocks') {
    $apiUrl .= '?action=create';
    
    error_log("Stock data sent: " . json_encode([
        'store_id' => $data['store_id'],
        'product_id' => $data['product_id'],
        'quantity' => $data['quantity']
    ]));
} else {
    $apiUrl .= '?action=create';
}

error_log("Full API URL: " . $apiUrl);
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Api: e8f1997c763'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        error_log("API Response ($type): " . $response);
        error_log("HTTP Code: " . $httpCode);
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new ControllerException("Error when adding: " . ($error['error'] ?? "Unknown error"), 400);
        }
        
        header("Location: /SAE401/products?type=$type&success=1");
        exit;
    }

    private function addEmployees() {
        if (!isset($_SESSION['employee'])) {
            throw new ControllerException("Unauthorized", 401);
        }
        
        $userRole = $_SESSION['employee']['employee_role'];
        $userStoreId = $_SESSION['employee']['store_id'] ?? null;
        $targetStoreId = isset($_POST['store_id']) ? (int)$_POST['store_id'] : null;
        
        $hasPermission = false;
        if ($userRole === 'it') {
            $hasPermission = true;
        } else if ($userRole === 'chief' && $userStoreId === $targetStoreId) {
            $hasPermission = true;
        }
        
        if (!$hasPermission) {
            throw new ControllerException("You do not have the rights to add an employee to this shop", 403);
        }
        
        if (!isset($_POST['employee_name']) || !isset($_POST['employee_email']) || 
            !isset($_POST['employee_password']) || !isset($_POST['employee_role']) || 
            !isset($_POST['store_id'])) {
            throw new ControllerException("Incomplete data", 400);
        }
        
        $validRoles = ['employee'];
        if ($userRole === 'it') {
            $validRoles[] = 'chief';
        }
        
        if (!in_array($_POST['employee_role'], $validRoles)) {
            throw new ControllerException("Unauthorised role", 400);
        }
        
        $data = [
            'employee_name' => htmlspecialchars($_POST['employee_name']),
            'employee_email' => htmlspecialchars($_POST['employee_email']),
            'employee_password' => $_POST['employee_password'],
            'employee_role' => $_POST['employee_role'],
            'store_id' => (int)$_POST['store_id']
        ];
        
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/employees?action=create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Api: e8f1997c763' 
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new ControllerException("Error when adding: " . ($error['error'] ?? "Unknown error"), 400);
        }
        
        header("Location: /SAE401/employees?success=1");
        exit;
    }

    /**
     * Handles entity updates
     * 
     * Processes PUT requests to update existing entities
     * 
     * @return void
     * @throws ControllerException For validation or API errors
     */
    private function handleUpdate() {
        if (!isset($_SESSION['employee'])) {
            throw new ControllerException("You must be logged in to perform this action", 401);
        }
        
        $urlParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $action = $urlParts[1] ?? ''; 
        $type = str_replace('update', '', $action); 
        
        $validTypes = ['brands', 'categories', 'shops', 'products', 'stocks', 'employees'];
        if (!in_array($type, $validTypes)) {
            throw new ControllerException("Invalid element type", 400);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new ControllerException("Unauthorised method", 405);
        }
        
        $data = $_POST;
        
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
            throw new ControllerException("Missing ID for update", 400);
        }
        
        $apiUrls = [
            'brands' => "https://clafoutis.alwaysdata.net/SAE401/api/brands/{$id}?action=update",
            'categories' => "https://clafoutis.alwaysdata.net/SAE401/api/categories/{$id}?action=update",
            'shops' => "https://clafoutis.alwaysdata.net/SAE401/api/stores/{$id}?action=update",
            'products' => "https://clafoutis.alwaysdata.net/SAE401/api/products/{$id}?action=update",
            'stocks' => "https://clafoutis.alwaysdata.net/SAE401/api/stocks/{$id}?action=update",
            'employees' => "https://clafoutis.alwaysdata.net/SAE401/api/employees/{$id}?action=update"
        ];
        
        error_log("Data sent to the API for update: " . json_encode($data));
        
        $ch = curl_init($apiUrls[$type]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Api: e8f1997c763'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        error_log("API Response ($type): " . $response);
        error_log("HTTP Code: " . $httpCode);
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new ControllerException("Error during update: " . ($error['error'] ?? "Unknown error"), 400);
        }
        
        header("Location: /SAE401/products?type=$type&update=1");
        exit;
    }

    private function handleModif() {
        $urlParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        
        if (count($urlParts) < 3) {
            throw new ControllerException("Bad request", 400);
        }
        
        $action = $urlParts[1];
        $type = str_replace('modif', '', $action);
        
        $id = isset($this->id) ? $this->id : (isset($urlParts[2]) ? $urlParts[2] : null);

        if (empty($id)) {
            throw new ControllerException("Missing ID", 400);
        }
        
        $validTypes = ['brands', 'categories', 'shops', 'products', 'stocks', 'employees'];
        if (!in_array($type, $validTypes)) {
            throw new ControllerException("Invalid element type", 400);
        }
        
        $apiEndpoints = [
            'brands' => "https://clafoutis.alwaysdata.net/SAE401/api/brands/{$id}",
            'categories' => "https://clafoutis.alwaysdata.net/SAE401/api/categories/{$id}",
            'shops' => "https://clafoutis.alwaysdata.net/SAE401/api/stores/{$id}",
            'products' => "https://clafoutis.alwaysdata.net/SAE401/api/products/{$id}",
            'stocks' => "https://clafoutis.alwaysdata.net/SAE401/api/stocks/{$id}",
            'employees' => "https://clafoutis.alwaysdata.net/SAE401/api/employees/{$id}"
        ];
        
        $ch = curl_init($apiEndpoints[$type]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $item = json_decode($response, true);

error_log("Data retrieved for $type: " . json_encode($item));

if (empty($item) || isset($item['error'])) {
    throw new ControllerException("Element not found or API error", 404);
}

error_log("API Response for $type/$id: " . $response);
error_log("Data decoded: " . json_encode($item));

if ($type === 'products' && (!isset($item['brand_id']) || !isset($item['category_id']))) {
    error_log("WARNING: Product is missing brand_id or category_id");
}
else if ($type === 'stocks' && (!isset($item['store_id']) || !isset($item['product_id']))) {
    error_log("WARNING: Stock is missing store_id or product_id");
}
        
        $structure = $this->getStructure();
        
        $select_options = [];
        
        $api_endpoints = [
            'brands' => 'https://clafoutis.alwaysdata.net/SAE401/api/brands',
            'categories' => 'https://clafoutis.alwaysdata.net/SAE401/api/categories',
            'shops' => 'https://clafoutis.alwaysdata.net/SAE401/api/stores',
            'products' => 'https://clafoutis.alwaysdata.net/SAE401/api/products',
            'employees' => 'https://clafoutis.alwaysdata.net/SAE401/api/employees'
        ];
        
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
        
        $GLOBALS['page_type'] = $type;
        $GLOBALS['item_data'] = $item;
        $GLOBALS['product_structure'] = $structure;
        $GLOBALS['product_select_options'] = $select_options;
        $GLOBALS['product_title'] = isset($this->getTitles()[$type]) ? $this->getTitles()[$type] : 'Element';
        
        require_once __DIR__ . '/../view/ViewModifProduct.php';
    }

    /**
     * Gets data structure for different entity types
     * 
     * Defines columns, fields and form structure for each entity type
     * 
     * @return array Multidimensional array of entity structures
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
        if (!isset($_SESSION['employee'])) {
            throw new ControllerException("You must be logged in to perform this action", 401);
        }

        $urlParts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $action = $urlParts[1];
        $id = $urlParts[2] ?? null;

        $type = str_replace('delete', '', $action);

        error_log("Type: $type, ID: $id");

        $validTypes = ['brands', 'categories', 'shops', 'products', 'stocks', 'employees'];
        if (!in_array($type, $validTypes)) {
            throw new ControllerException("Invalid element type", 400);
        }

        if (empty($id)) {
            throw new ControllerException("Missing ID for deletion", 400);
        }

        $apiUrls = [
            'brands' => "https://clafoutis.alwaysdata.net/SAE401/api/brands/{$id}?action=delete",
            'categories' => "https://clafoutis.alwaysdata.net/SAE401/api/categories/{$id}?action=delete",
            'shops' => "https://clafoutis.alwaysdata.net/SAE401/api/stores/{$id}?action=delete",
            'products' => "https://clafoutis.alwaysdata.net/SAE401/api/products/{$id}?action=delete",
            'stocks' => "https://clafoutis.alwaysdata.net/SAE401/api/stocks/{$id}?action=delete",
            'employees' => "https://clafoutis.alwaysdata.net/SAE401/api/employees/{$id}?action=delete"
        ];

        error_log("API URL: " . $apiUrls[$type]);

        $ch = curl_init($apiUrls[$type]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        error_log("API response: " . $response);
        error_log("HTTP Code: " . $httpCode);

        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new ControllerException("Deletion error : " . ($error['error'] ?? "Unknown error"), 400);
        }

        header("Location: /SAE401/products?type={$type}&delete=1");
        exit;
    }

    private function showModifEmployees() {
        if (!isset($this->id)) {
            throw new ControllerException("Missing employee ID", 400);
        }
        
        // Récupérer les informations de l'employé
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/employees/{$this->id}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $employee = json_decode($response, true);
        curl_close($ch);
        
        if (empty($employee)) {
            throw new ControllerException("Employee not found", 404);
        }
        
        // Récupérer la liste des magasins pour les admins IT
        $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/stores");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $stores = json_decode($response, true);
        curl_close($ch);
        
        // Passer les données à la vue
        $GLOBALS['employee_data'] = $employee;
        $GLOBALS['stores_data'] = $stores;
        
        // Rediriger vers la vue spécifique pour les employés
        require_once __DIR__ . '/../view/ViewModifEmployee.php';
    }
}