<?php
/**
 * Header Template
 * 
 * Provides the common header structure for all pages including:
 * - HTML doctype and meta tags
 * - Bootstrap integration
 * - Navigation menu with dynamic highlighting
 * - User authentication status
 * - Error message display
 * 
 * @package BikeStore\Templates
 * @author Your Name
 * @version 1.0
 */

/**
 * Navigation variables
 * 
 * @var string $currentPage Current page from URL parameters
 * @var string $currentAction Current action from URL parameters
 * @var string $currentType Current product type from URL parameters
 * @var string $currentPath Current path from URL, stripped of parameters
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikeStores - SAE 401</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/SAE401/www/css/style.css">
</head>
<body>

<?php
/**
 * Process current URL to determine active navigation items
 * Extracts path information for menu highlighting
 */
$currentPage = isset($_GET['page']) ? $_GET['page'] : '';
$currentAction = isset($_GET['action']) ? $_GET['action'] : '';
$currentType = isset($_GET['type']) ? $_GET['type'] : '';
$uri = $_SERVER['REQUEST_URI'];
$basePath = '/SAE401/';
$currentPath = '';
if (strpos($uri, $basePath) === 0) {
    $currentPath = substr($uri, strlen($basePath));
    // Remove query parameters and trailing slashes
    $currentPath = preg_replace('/[\?\/].*$/', '', $currentPath);
}
?>

<header>
   <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #333; color: white;">
    <div class="container">
        <a class="navbar-brand text-white" href="/SAE401/home">
            <img src="/SAE401/www/images/logobikestores.svg" alt="logo BikeStores" class="logo" style="height: 40px;">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="color: white;"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation principale -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($currentPath === 'catalogue') ? 'active' : ''; ?>" href="/SAE401/catalogue">Bikes</a>
                </li>
                <?php if(isset($_SESSION['employee'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown">
                            Products management
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                            <li><a class="dropdown-item <?php echo ($currentPath === 'products' && $currentType === 'brands') ? 'active' : ''; ?>" href="/SAE401/products?type=brands">Brand</a></li>
                            <li><a class="dropdown-item <?php echo ($currentPath === 'products' && $currentType === 'categories') ? 'active' : ''; ?>" href="/SAE401/products?type=categories">Category</a></li>
                            <li><a class="dropdown-item <?php echo ($currentPath === 'products' && $currentType === 'shops') ? 'active' : ''; ?>" href="/SAE401/products?type=shops">Shops</a></li>
                            <li><a class="dropdown-item <?php echo ($currentPath === 'products' && $currentType === 'products') ? 'active' : ''; ?>" href="/SAE401/products?type=products">Products</a></li>
                            <li><a class="dropdown-item <?php echo ($currentPath === 'products' && $currentType === 'stocks') ? 'active' : ''; ?>" href="/SAE401/products?type=stocks">Stocks</a></li>
                        </ul>
                    </li>
                    <?php if(isset($_SESSION['employee']['employee_role']) && ($_SESSION['employee']['employee_role'] === 'chief' || $_SESSION['employee']['employee_role'] === 'it')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo ($currentPath === 'employees') ? 'active' : ''; ?>" href="/SAE401/employees">Employees</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <!-- Menu utilisateur -->
            <ul class="navbar-nav ms-auto">
                <?php if(!isset($_SESSION['employee'])): ?>
                    <li class="nav-item">
                        <a class="btn btn-success rounded-pill" href="/SAE401/connexion">Login</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['employee']['employee_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/SAE401/information">Your information</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/SAE401/deconnexion">Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
   </nav>
</header>
<main>
<?php
/**
 * Error Message Display
 * Shows error messages from session if they exist
 * Clears the message after display
 * 
 * @var string $_SESSION["error"] Error message to display
 */
if(isset($_SESSION["error"]) && $_SESSION["error"] != "") {
    echo "<main class='container py-4'>";
    echo "<div class='alert alert-danger text-center'><p>{$_SESSION['error']}</p></div>";
    echo "</main>";
    $_SESSION["error"] = "";
}
?>