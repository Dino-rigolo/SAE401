<?php 
include_once('www/header.inc.php');

if (!isset($_SESSION['employee'])) {
    header('Location: /SAE401/connexion');
    exit;
}

$employee = $_SESSION['employee'];
$store_name = 'Non assigné';
if (isset($employee['store_id']) && !empty($employee['store_id'])) {
    $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/stores/{$employee['store_id']}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $store = json_decode($response, true);
    curl_close($ch);
    
    if (isset($store['store_name'])) {
        $store_name = $store['store_name'];
    }
}
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col text-center">
            <h1 class="fw-bold">Informations de l'employé</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <h5 class="card-title">Identité</h5>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Nom :</div>
                            <div class="col-md-8"><?php echo htmlspecialchars($employee['employee_name']); ?></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="card-title">Contact</h5>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Email :</div>
                            <div class="col-md-8"><?php echo htmlspecialchars($employee['employee_email']); ?></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5 class="card-title">Fonction</h5>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Rôle :</div>
                            <div class="col-md-8">
                                <?php 
                                $role_display = [
                                    'it' => 'Équipe IT',
                                    'sales' => 'Commercial',
                                    'chief' => 'Chef de magasin',
                                    'employee' => 'Employé'
                                ];
                                echo isset($role_display[$employee['employee_role']]) ? 
                                    htmlspecialchars($role_display[$employee['employee_role']]) : 
                                    htmlspecialchars($employee['employee_role']);
                                ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Magasin :</div>
                            <div class="col-md-8"><?php echo htmlspecialchars($store_name); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include_once('www/footer.inc.php');
?>