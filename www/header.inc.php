<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikeStores - SAE 301</title>
    <link rel="stylesheet" href="www/css/header.css">

  <!--connection-->
    <!-- <link href="www/css/connection.css" rel="stylesheet">-->
    <link href="www/css/styles.css" rel="stylesheet"> 
</head>
<body>
<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
  <symbol id="people-circle" viewBox="0 0 16 16">
    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
  </symbol>
</svg>

<header>
   <nav class="py-2 border-bottom">
    <div class="container d-flex flex-wrap">
    <a href="index.php"><img src="www/images/logo.svg" alt="logo minuit" class="logominuit"></a>
    <?php
      if(isset($_SESSION["compte"])){
        if($_SESSION["compte"] instanceof Employee){ // Si le compte est un employée
          if($_SESSION["compte"]->isSalesEmployee()){ //Si c'est un vendeur
            echo "<ul class=\"nav me-auto\">  
            <li class=\"nav-item\"><a href=\"index.php?action=GenderRanking\" class=\"nav-link link-body-emphasis px-2 active\" aria-current=\"page\">CLASSEMENT GENRE</a></li>
            <li class=\"nav-item\"><a href=\"index.php?action=AgentResume\" class=\"nav-link link-body-emphasis px-2\">RESUMÉ</a></li>
            <li class=\"nav-item\"><a href=\"index.php?action=CountryStats\" class=\"nav-link link-body-emphasis px-2\">CLASSEMENT PAYS</a></li>

            </ul>";
          }elseif($_SESSION["compte"]->isItStaff()){
            echo "<ul class=\"nav me-auto\">
            <li class=\"nav-item\"><a href=\"index.php?action=ListCustomer\" class=\"nav-link link-body-emphasis px-2 active\" aria-current=\"page\">LISTE DES COMPTES</a></li>
            <li class=\"nav-item\"><a href=\"index.php?action=ModifAllPlaylist\" class=\"nav-link link-body-emphasis px-2\">PLAYLISTS</a></li>
          </ul>";
          }
          echo "<div class=\"iconeblock\">
          <a href=\"#\" class=\"d-block link-body-emphasis text-decoration-none dropdown-toggle\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
            <svg class=\"rounded-circle\" width=\"32\" height=\"32\"><use xlink:href=\"#people-circle\"/></svg>
          </a>
          <ul class=\"dropdown-menu text-small shadow\">
            <li><a class=\"dropdown-item\" href=\"index.php?action=ViewInfo\">Compte</a></li>
            <li><hr class=\"dropdown-divider\"></li>
            <li><a class=\"dropdown-item\" href=\"index.php?action=deconnection\">Déconnexion</a></li>
          </ul>";
        }else if($_SESSION["compte"] instanceof Customer) {// Si le compte n'est pas un employée
          echo "<ul class=\"nav me-auto\">
          <li class=\"nav-item\"><a href=\"index.php?action=ViewCatalogue\" class=\"nav-link link-body-emphasis px-2 active\" aria-current=\"page\">CATALOGUE</a></li>
          <li class=\"nav-item\"><a href=\"index.php?action=ViewMusicClient\" class=\"nav-link link-body-emphasis px-2\">MES MUSIQUES</a></li>
        </ul>";
        echo "<div class=\"iconeblock\">
              <a href=\"#\" class=\"d-block link-body-emphasis text-decoration-none dropdown-toggle\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                <svg class=\"rounded-circle\" width=\"32\" height=\"32\"><use xlink:href=\"#people-circle\"/></svg>
              </a>
              <ul class=\"dropdown-menu text-small shadow\">
                <li><a class=\"dropdown-item\" href=\"index.php?action=ViewInfo\">Compte</a></li>
                <li><a class=\"dropdown-item\" href=\"index.php?action=ViewPurchaseHistoryClient\">Historique d'achat</a></li>
                <li><hr class=\"dropdown-divider\"></li>
                <li><a class=\"dropdown-item\" href=\"index.php?action=deconnection\">Déconnexion</a></li>
              </ul>";
          }
        }
    ?>
    </div>
  </nav>
</header>
<?
if(isset($_SESSION["error"]) && $_SESSION["error"] != ""){
    echo "<div class=\"alert alert-danger\" role=\"alert\">{$_SESSION["error"]}</div>";
    $_SESSION["error"] = "";
}



?>