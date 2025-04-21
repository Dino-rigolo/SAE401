<?php
/**
 * Login View
 * 
 * Displays the login form with email and password fields
 * Handles session messages and remember me functionality
 * 
 * @package BikeStore\Views
 * @author 
 * @version 1.0
 */

/**
 * Start session if not already started
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Include header template
 */
include_once('www/header.inc.php');

/**
 * @var string $success Success message from session if present
 */
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm mt-5 login-card">
                <div class="card-header text-center bg-dark text-white">
                    <h1 class="card-title h3 mb-0">Identify yourself</h1>
                </div>
                <div class="card-body p-4 bg-light">
                    <?php
                    /**
                     * Display success message if present in session
                     * and remove it after display
                     */
                    if (isset($_SESSION['success'])) {
                        echo "<div class='alert alert-success text-center'>{$_SESSION['success']}</div>";
                        unset($_SESSION['success']);
                    }
                    ?>

                    <!-- Login form -->
                    <form action="/SAE401/connexion" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control rounded-pill" name="email" id="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control rounded-pill" name="password" id="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success rounded-pill">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
/**
 * Include footer template
 */
include_once('www/footer.inc.php');
?>