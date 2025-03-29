<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikeStores - SAE 401</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="/SAE401/www/css/style.css">
</head>
<body>

<header>
   <nav class="navbar navbar-expand-lg" style="background-color: #333; color: white;">
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
                <li class="nav-item"><a class="nav-link text-white" href="/SAE401/catalogue">Bikes</a></li>
                <?php if(isset($_SESSION['employee'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown">
                            Products management
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                            <li><a class="dropdown-item" href="/SAE401/brands">Brand</a></li>
                            <li><a class="dropdown-item" href="/SAE401/categories">Category</a></li>
                            <li><a class="dropdown-item" href="/SAE401/shops">Shops</a></li>
                            <li><a class="dropdown-item" href="/SAE401/products">Products</a></li>
                            <li><a class="dropdown-item" href="/SAE401/stocks">Stocks</a></li>
                        </ul>
                    </li>
                    <?php if(isset($_SESSION['employee']['employee_role']) && ($_SESSION['employee']['employee_role'] === 'chef' || $_SESSION['employee']['employee_role'] === 'it')): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="/SAE401/employees">Employees</a></li>
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

<main class="container py-4">

<?php
// Affichage des messages d'erreur
if(isset($_SESSION["error"]) && $_SESSION["error"] != ""){
    echo "<div class='alert alert-danger text-center'><p>{$_SESSION['error']}</p></div>";
    $_SESSION["error"] = "";
}
?>
</main>