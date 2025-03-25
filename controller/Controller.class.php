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
    
    /**
     * Constructeur du contrôleur
     */
    public function __construct() {
        global $entityManager;
        $this->entityManager = $entityManager;
        $this->request = $_SERVER['REQUEST_URI'];
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
        
        // Le premier segment est l'action principale
        $this->action = !empty($segments[0]) ? $segments[0] : 'home';
    }
    
    /**
     * Exécute l'action demandée
     */
    public function execute() {
        try {
            switch ($this->action) {
                case 'home':
                    $this->showHome();
                    break;
                    
                case 'catalogue':
                    $this->showCatalogue();
                    break;
                    
                case 'connexion':
                    $this->showConnexion();
                    break;
                    
                case 'employees':
                    $this->showEmployees();
                    break;
                    
                case 'information':
                    $this->showInformation();
                    break;
                    
                // Routes pour l'API
                case 'api':
                    $this->handleApiRequest();
                    break;
                    
                default:
                    throw new ControllerException("Page non trouvée", 404);
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Affiche la page d'accueil
     */
    private function showHome() {
        require_once __DIR__ . '/../view/ViewHome.php';
        $view = new ViewHome();
        $view->display();
    }
    
    /**
     * Affiche la page du catalogue
     */
    private function showCatalogue() {
        require_once __DIR__ . '/../view/ViewCatalogue.php';
        $view = new ViewCatalogue($this->entityManager);
        $view->display();
    }
    
    /**
     * Affiche la page de connexion
     */
    private function showConnexion() {
        require_once __DIR__ . '/../view/ViewConnexion.php';
        $view = new ViewConnexion();
        $view->display();
    }
    
    /**
     * Affiche la page des employés
     */
    private function showEmployees() {
        require_once __DIR__ . '/../view/ViewEmployees.php';
        $view = new ViewEmployees($this->entityManager);
        $view->display();
    }
    
    /**
     * Affiche la page d'information
     */
    private function showInformation() {
        require_once __DIR__ . '/../view/ViewInformation.php';
        $view = new ViewInformation();
        $view->display();
    }
    
    /**
     * Gère les requêtes vers l'API
     */
    private function handleApiRequest() {
        // Cette fonction n'est pas utilisée directement car les requêtes API 
        // sont gérées par le fichier .htaccess qui redirige vers les fichiers API appropriés
        throw new ControllerException("Cette URL n'est pas valide. Les requêtes API devraient être redirigées via .htaccess.", 400);
    }
    
    /**
     * Gère les erreurs
     */
    private function handleError(Exception $e) {
        $statusCode = ($e instanceof ControllerException) ? $e->getCode() : 500;
        http_response_code($statusCode);
        
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage(),
            'code' => $statusCode
        ]);
    }
}

// Instanciation et exécution du contrôleur
$controller = new Controller();
$controller->execute();