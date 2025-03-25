<?php 
// Au début du fichier
//session_start(); // Assurez-vous que la session est démarrée
var_dump($_POST); // Voir les données postées
var_dump($_SESSION); // Voir l'état de la session

include_once('www/header.inc.php');
?>
<h1>Identify yourself</h1>

<?php
// Code temporaire pour tester la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    // Employé de test (à remplacer par l'appel API réel)
    $testEmployee = [
        'employee_id' => 1,
        'employee_name' => 'Test User',
        'employee_email' => 'shannahsummer@bikestore.com',
        'employee_password' => password_hash('TVv(cB4mBEiC', PASSWORD_DEFAULT),
        'employee_role' => 'it'
    ];
    
    if ($_POST['email'] === $testEmployee['employee_email'] && 
        password_verify($_POST['password'], $testEmployee['employee_password'])) {
        $_SESSION['employee'] = $testEmployee;
        $_SESSION['success'] = "Connexion réussie !";
        echo "<script>alert('Connexion réussie !'); window.location.href='/SAE401/home';</script>";
        exit;
    } else {
        echo "<div class='error'>Email ou mot de passe incorrect</div>";
    }
}
?>

<form action="/SAE401/connexion" method="POST">
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