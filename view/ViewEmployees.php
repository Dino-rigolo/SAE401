<?php 
include_once('www/header.inc.php');

// Charger FontAwesome pour les icônes
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">';

// Utilisez les données passées par le contrôleur
$employees = $GLOBALS['employees_data'];
$stores = $GLOBALS['stores_data'];
?>

<div class="container-fluid my-5">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Employé ajouté avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['update']) && $_GET['update'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Employé modifié avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['delete']) && $_GET['delete'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Employé supprimé avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col text-center">
            <h1 class="fw-bold">Employees</h1>
        </div>
    </div>

    <?php 
    // Si l'utilisateur est chief, il ne peut gérer que son magasin
    $storeFilter = ($_SESSION['employee']['employee_role'] === 'chief') 
        ? $_SESSION['employee']['store_id'] 
        : null;
    
    // Parcourir tous les magasins (pour IT) ou seulement le magasin de l'utilisateur (pour chief)
    foreach ($stores as $store):
        if ($storeFilter === null || $store['store_id'] == $storeFilter):
    ?>
    <div class="row mt-5">
        <div class="col-md-10 offset-md-1">
            <h5 class="text-center mb-3">Employees of <?php echo htmlspecialchars($store['store_name']); ?></h5>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php
                    // Filtrer les employés pour ce magasin
                    $storeEmployees = array_filter($employees, function($emp) use ($store) {
                        return $emp['store_id'] == $store['store_id'];
                    });
                    
                    if (!empty($storeEmployees)):
                    ?>
                    <div class="table-container" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light sticky-top" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($storeEmployees as $employee): ?>
                                    <tr>
                                        <td><?php echo $employee['employee_id']; ?></td>
                                        <td><?php echo htmlspecialchars($employee['employee_name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['employee_email']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['employee_role']); ?></td>
                                        <td class="text-center">
                                            <?php if ($_SESSION['employee']['employee_role'] === 'it' || 
                                                    ($_SESSION['employee']['employee_role'] === 'chief' && 
                                                    $employee['employee_role'] !== 'chief' && 
                                                    $employee['employee_role'] !== 'it')): ?>
                                                <a href="#" class="icon-action delete-btn" 
                                                    data-id="<?php echo $employee['employee_id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($employee['employee_name']); ?>"
                                                    data-type="employees"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" style="fill: #dc3545;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg>
                                                </a>
                                                <a href="/SAE401/modifemployees/<?php echo $employee['employee_id']; ?>" 
                                                class="icon-action ms-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" style="fill: #0d6efd;"><path d="M8.707 19.707 18 10.414 13.586 6l-9.293 9.293a1.003 1.003 0 0 0-.263.464L3 21l5.242-1.03c.176-.044.337-.135.465-.263zM21 7.414a2 2 0 0 0 0-2.828L19.414 3a2 2 0 0 0-2.828 0L15 4.586 19.414 9 21 7.414z"></path></svg>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p class="text-center">No employees found for this store.</p>
                    <?php endif; ?>
                    
                    <!-- Formulaire d'ajout d'employé -->
                    <div class="mt-4">
                        <h6 class="text-center mb-3">Add New Employee</h6>
                        <form action="/SAE401/addemployees" method="POST" class="row g-3">
                            <input type="hidden" name="store_id" value="<?php echo $store['store_id']; ?>">
                            
                            <div class="col-md-6">
                                <label for="employee_name_<?php echo $store['store_id']; ?>" class="form-label">Name</label>
                                <input type="text" class="form-control" id="employee_name_<?php echo $store['store_id']; ?>" name="employee_name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="employee_email_<?php echo $store['store_id']; ?>" class="form-label">Email</label>
                                <input type="email" class="form-control" id="employee_email_<?php echo $store['store_id']; ?>" name="employee_email" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="employee_password_<?php echo $store['store_id']; ?>" class="form-label">Password</label>
                                <input type="password" class="form-control" id="employee_password_<?php echo $store['store_id']; ?>" name="employee_password" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="employee_role_<?php echo $store['store_id']; ?>" class="form-label">Role</label>
                                <select class="form-select" id="employee_role_<?php echo $store['store_id']; ?>" name="employee_role" required>
                                    <option value="employee">Employee</option>
                                    <?php if ($_SESSION['employee']['employee_role'] === 'it'): ?>
                                        <option value="chief">Chief</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success">Add Employee</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
        endif; // Fin de la condition de filtrage des magasins
    endforeach; // Fin de la boucle des magasins
    ?>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer <span id="deleteItemName"></span> ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Oui</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Configurer la modal de suppression
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteItemName = document.getElementById('deleteItemName');
        let deleteId, deleteType;

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                
                deleteId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name') || 'cet élément';
                deleteType = this.getAttribute('data-type');

                deleteItemName.textContent = name;
                
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });

        confirmDeleteBtn.addEventListener('click', function() {
            const url = `https://clafoutis.alwaysdata.net/SAE401/api/${deleteType}/${deleteId}?action=delete`;
            
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            deleteModal.hide();
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Api': 'e8f1997c763'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Erreur HTTP: ' + response.status);
                    });
                }
                return response.json();
            })
            .then(data => {
                window.location.href = `/SAE401/${deleteType}?delete=1`;
            })
            .catch(error => {
                alert(`Erreur: ${error.message}`);
                console.error('Erreur détaillée:', error);
            });
        });
    });
</script>

<?php 
include_once('www/footer.inc.php');
?>