<?php
/**
 * Error View Class
 * 
 * Handles the display of error messages for different HTTP error codes
 * Provides a consistent error page layout across the application
 * 
 * @package BikeStore\Views
 * @author Your Name
 * @version 1.0
 */
class ViewError {
    /** @var int HTTP status code for the error */
    private $statusCode;
    
    /** @var string Custom error message to display */
    private $errorMessage;
    
    /**
     * Constructor
     * 
     * Initializes a new error view with status code and message
     * 
     * @param int    $statusCode    HTTP error code (404, 500, etc.)
     * @param string $errorMessage  Detailed error message to display
     */
    public function __construct($statusCode, $errorMessage) {
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
    }
    
    /**
     * Displays the error page
     * 
     * Renders a complete error page including header and footer
     * with error details and a return to home button
     * 
     * @return void
     */
    public function display() {
        $errorTitle = $this->getErrorTitle($this->statusCode);
        
        include_once 'www/header.inc.php';
        ?>
        <div class="error-container">
            <div class="error-card">
                <h1>Error <?php echo $this->statusCode; ?></h1>
                <h2><?php echo $errorTitle; ?></h2>
                <p class="error-message"><?php echo $this->errorMessage; ?></p>
                <a href="/SAE401/home" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
        <?php
        include_once 'www/footer.inc.php';
    }
    
    /**
     * Gets a descriptive title for an HTTP error code
     * 
     * Maps HTTP status codes to human-readable error titles
     * 
     * @param int $statusCode HTTP error code
     * @return string Human-readable error title
     */
    private function getErrorTitle($statusCode) {
        switch ($statusCode) {
            case 400:
                return "Bad Request";
            case 401:
                return "Unauthorized";
            case 403:
                return "Access Forbidden";
            case 404:
                return "Page Not Found";
            case 500:
                return "Internal Server Error";
            default:
                return "An Error Occurred";
        }
    }
}
?>