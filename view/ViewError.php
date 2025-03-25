<?php
/**
 * ViewError - Vue pour afficher les erreurs
 * 
 * Cette classe gère l'affichage des messages d'erreur
 * pour différents codes d'erreur HTTP
 */
class ViewError {
    private $statusCode;
    private $errorMessage;
    
    /**
     * Constructeur
     * 
     * @param int $statusCode Code d'erreur HTTP (404, 500, etc.)
     * @param string $errorMessage Message d'erreur à afficher
     */
    public function __construct($statusCode, $errorMessage) {
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
    }
    
    /**
     * Affiche la page d'erreur
     */
    public function display() {
        // Titre de la page d'erreur en fonction du code HTTP
        $errorTitle = $this->getErrorTitle($this->statusCode);
        
        // Inclure l'en-tête de la page
        include_once 'www/header.inc.php';
        
        // Afficher le contenu de la page d'erreur
        ?>
        <div class="error-container">
            <div class="error-card">
                <h1>Erreur <?php echo $this->statusCode; ?></h1>
                <h2><?php echo $errorTitle; ?></h2>
                <p class="error-message"><?php echo $this->errorMessage; ?></p>
                <a href="/SAE401/home" class="btn btn-primary">Retour à l'accueil</a>
            </div>
        </div>
        <?php
        
        // Inclure le pied de page
        include_once 'www/footer.inc.php';
    }
    
    /**
     * Retourne un titre explicatif pour le code d'erreur HTTP
     * 
     * @param int $statusCode Code d'erreur HTTP
     * @return string Titre explicatif
     */
    private function getErrorTitle($statusCode) {
        switch ($statusCode) {
            case 400:
                return "Requête incorrecte";
            case 401:
                return "Non autorisé";
            case 403:
                return "Accès interdit";
            case 404:
                return "Page non trouvée";
            case 500:
                return "Erreur interne du serveur";
            default:
                return "Une erreur est survenue";
        }
    }
}
?>