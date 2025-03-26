<?php 
// Vérifier si la session est déjà démarrée avant de la démarrer
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Conserver les var_dump pour le débogage
 var_dump($_POST); // Voir les données postées
 var_dump($_SESSION); // Voir l'état de la session

include_once('www/header.inc.php');
?>
<h1>Identify yourself</h1>

<?php
// Affichage des messages d'erreur/succès
if (isset($_SESSION['error'])) {
    echo "<div class='error'>{$_SESSION['error']}</div>";
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo "<div class='success'>{$_SESSION['success']}</div>";
    unset($_SESSION['success']);
}
?>

<form action="https://clafoutis.alwaysdata.net/SAE401/connexion" method="POST">
    <div>
        <label for="email">Email</label>
        <input type="text" name="email" id="email" required>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <div>
        <label for="remember">Remember me <input id="rememberChkBox" type="checkbox" name="remember"></label>
    </div>

    <button type="submit">Login</button>
</form>

<?php 
include_once('www/footer.inc.php');
?>