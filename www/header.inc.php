<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikeStores - SAE 401</title>
    <link rel="stylesheet" href="/SAE401/www/css/style.css">
</head>
<body>

<header>
   <nav>
    <div class="container">
        <a href="/SAE401/home"><img src="/SAE401/www/images/logobikestores.svg"  alt="logo BikeStores" class="logo"></a>
        
        <!-- Navigation principale -->
        <ul class="navigation">
            <!-- Pages accessibles à tous (connectés ou non) -->
            <li><a href="/SAE401/home">Bikes</a></li>
            
            <?php if(isset($_SESSION['employee'])): ?>
                <!-- Liens pour employés (sans rôle spécifique) -->
                <li class="dropdown">
                    <a href="#">Products management</a>
                    <ul class="dropdown-content">
                        <li><a href="/SAE401/brands">Brand</a></li>
                        <li><a href="/SAE401/categories">Category</a></li>
                        <li><a href="/SAE401/shops">Shops</a></li>
                        <li><a href="/SAE401/products">Products</a></li>
                        <li><a href="/SAE401/stocks">Stocks</a></li>
                    </ul>
                </li>
                
                <?php if(isset($_SESSION['employee']['employee_role']) && ($_SESSION['employee']['employee_role'] === 'chef' || $_SESSION['employee']['employee_role'] === 'it')): ?>
                    <!-- Liens supplémentaires pour les chefs et IT -->
                    <li><a href="/SAE401/employees">Employees</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        
        <!-- Partie droite de la navigation (connexion/compte) -->
        <div class="user-menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8 0-1.168.258-2.275.709-3.276.154.09.308.182.456.276.396.25.791.5 1.286.688.494.187 1.088.312 1.879.312.792 0 1.386-.125 1.881-.313s.891-.437 1.287-.687.792-.5 1.287-.688c.494-.187 1.088-.312 1.88-.312s1.386.125 1.88.313c.495.187.891.437 1.287.687s.792.5 1.287.688c.178.067.374.122.581.171.191.682.3 1.398.3 2.141 0 4.411-3.589 8-8 8z"></path><circle cx="8.5" cy="12.5" r="1.5"></circle><circle cx="15.5" cy="12.5" r="1.5"></circle></svg>

            <?php if(isset($_SESSION['employee'])): ?>
                <!-- Utilisateur connecté -->
                <div class="dropdown">
                    <a href="#">
                        <span><?= htmlspecialchars($_SESSION['employee']['employee_name']) ?></span>
                    </a>
                    <ul class="dropdown-content">
                        <li><a href="/SAE401/information">Your information</a></li>
                        <li><a href="/SAE401/deconnexion">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Utilisateur non connecté -->
                <a href="/SAE401/connexion" class="btn-connexion">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
  </nav>
</header>

<?php
// Affichage des messages d'erreur
if(isset($_SESSION["error"]) && $_SESSION["error"] != ""){
    echo "<div class=\"error-message\">{$_SESSION["error"]}</div>";
    $_SESSION["error"] = "";
}
?>