<?php 
include_once('www/header.inc.php');

// Charger FontAwesome pour les icônes
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">';

// Récupérer le type depuis $_GET (déjà défini par le contrôleur)
$page_type = isset($_GET['type']) ? $_GET['type'] : 'brands';

// Utiliser les données passées par le contrôleur
$title = $GLOBALS['product_title'];
$data = $GLOBALS['product_data'];
$structure = $GLOBALS['product_structure'];
$select_options = $GLOBALS['product_select_options'];

// Récupérer la structure pour le type actuel
$current_structure = isset($structure[$page_type]) ? $structure[$page_type] : $structure['brands'];
$columns = $current_structure['columns'];
$fields = $current_structure['fields'];
$id_field = $current_structure['id_field'];
$form_fields = $current_structure['form_fields'];
?>

<div class="container my-5">
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo ucfirst($page_type); ?> ajouté avec succès !
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
    <div class="row mb-4">
        <div class="col text-center">
            <h1 class="fw-bold"><?php echo $title; ?></h1>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-10 offset-md-1">
            <h5 class="text-center mb-3">List of <?php echo strtolower($title); ?></h5>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light sticky-top" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <th><?php echo $column; ?></th>
                                    <?php endforeach; ?>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data)): ?>
                                    <?php foreach ($data as $item): ?>
                                        <tr>
                                            <?php foreach ($fields as $field): ?>
                                                <td>
                                                    <?php 
                                                    if ($field === 'list_price') {
                                                        echo '$' . number_format($item[$field] ?? 0, 2);
                                                    } else {
                                                        echo htmlspecialchars($item[$field] ?? 'N/A'); 
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="text-center">
                                                <a href="#" class="icon-action delete-btn" 
                                                        data-id="<?php echo $item[$id_field]; ?>" 
                                                        data-type="<?php echo $page_type; ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" style="fill: #dc3545;transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg>
                                                </a>
                                                <a href="/SAE401/modif<?php echo $page_type; ?>/<?php echo $item[$id_field]; ?>" 
                                                   class="icon-action ms-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" style="fill: #0d6efd;transform: ;msFilter:;"><path d="M8.707 19.707 18 10.414 13.586 6l-9.293 9.293a1.003 1.003 0 0 0-.263.464L3 21l5.242-1.03c.176-.044.337-.135.465-.263zM21 7.414a2 2 0 0 0 0-2.828L19.414 3a2 2 0 0 0-2.828 0L15 4.586 19.414 9 21 7.414z"></path></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?php echo count($columns) + 1; ?>" class="text-center">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-6 offset-md-3">
            <h5 class="text-center mb-3">Add a <?php echo rtrim($title, 's'); ?></h5>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/SAE401/add' . $page_type); ?>" method="POST">
                        <?php foreach ($form_fields as $field): ?>
                            <div class="mb-3">
                                <label for="<?php echo $field['name']; ?>" class="form-label"><?php echo $field['label']; ?></label>
                                
                                <?php if ($field['type'] === 'select'): ?>
                                    <select id="<?php echo $field['name']; ?>" name="<?php echo $field['name']; ?>" class="form-select" required>
                                        <option value="">Select <?php echo $field['label']; ?></option>
                                        <?php if (isset($select_options[$field['name']])): ?>
                                            <?php foreach ($select_options[$field['name']] as $option): ?>
                                                <?php 
                                                $option_value = $option[$field['name']];
                                                $option_label = '';
                                                
                                                // Déterminer le label à afficher selon le type de select
                                                if ($field['source'] === 'brands') {
                                                    $option_label = $option['brand_name'];
                                                } elseif ($field['source'] === 'categories') {
                                                    $option_label = $option['category_name'];
                                                } elseif ($field['source'] === 'shops') {
                                                    $option_label = $option['store_name'];
                                                } elseif ($field['source'] === 'products') {
                                                    $option_label = $option['product_name'];
                                                }
                                                ?>
                                                <option value="<?php echo $option_value; ?>"><?php echo $option_label; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                <?php elseif ($field['type'] === 'textarea'): ?>
                                    <textarea id="<?php echo $field['name']; ?>" name="<?php echo $field['name']; ?>" class="form-control" 
                                        placeholder="Enter <?php echo strtolower($field['label']); ?>" required></textarea>
                                <?php else: ?>
                                    <input type="<?php echo $field['type']; ?>" 
                                        id="<?php echo $field['name']; ?>" 
                                        name="<?php echo $field['name']; ?>" 
                                        class="form-control" 
                                        placeholder="Enter <?php echo strtolower($field['label']); ?>" 
                                        <?php echo isset($field['step']) ? 'step="' . $field['step'] . '"' : ''; ?>
                                        <?php if ($field['name'] === 'model_year'): ?>
                                            min="1950" 
                                            max="<?php echo date('Y') + 10; ?>"
                                        <?php endif; ?>
                                        required>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-success">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php 
    // Afficher la section des employés uniquement pour les magasins et si l'utilisateur a les droits
    if ($page_type === 'shops' && isset($_SESSION['employee']) && 
        ($_SESSION['employee']['employee_role'] === 'chief' || $_SESSION['employee']['employee_role'] === 'it')):
        
        // Si l'utilisateur est chief, il ne peut gérer que son magasin
        $storeFilter = ($_SESSION['employee']['employee_role'] === 'chief') 
            ? $_SESSION['employee']['store_id'] 
            : null;
        
        // Parcourir tous les magasins (pour IT) ou seulement le magasin de l'utilisateur (pour chief)
        foreach ($data as $store):
            if ($storeFilter === null || $store['store_id'] == $storeFilter):
    ?>
    <div class="row mt-5">
        <div class="col-md-10 offset-md-1">
            <h5 class="text-center mb-3">Employees of <?php echo htmlspecialchars($store['store_name']); ?></h5>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php
                    // Récupérer les employés de ce magasin via l'API
                    $ch = curl_init("https://clafoutis.alwaysdata.net/SAE401/api/employees?action=getbystore&store_id=" . $store['store_id']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec($ch);
                    $storeEmployees = json_decode($response, true);
                    curl_close($ch);
                    
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
                                                    data-type="employees"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" style="fill: #dc3545;transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg>
                                                </a>
                                                <a href="/SAE401/modifemployees/<?php echo $employee['employee_id']; ?>" 
                                                   class="icon-action ms-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" style="fill: #0d6efd;transform: ;msFilter:;"><path d="M8.707 19.707 18 10.414 13.586 6l-9.293 9.293a1.003 1.003 0 0 0-.263.464L3 21l5.242-1.03c.176-.044.337-.135.465-.263zM21 7.414a2 2 0 0 0 0-2.828L19.414 3a2 2 0 0 0-2.828 0L15 4.586 19.414 9 21 7.414z"></path></svg>
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
    endif; // Fin de la condition pour afficher la section des employés
    ?>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this item? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Configurer la modal de suppression
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const type = this.getAttribute('data-type');
                confirmDeleteBtn.href = `/SAE401/delete${type}/${id}`;
            });
        });
    });
</script>

<?php 
include_once('www/footer.inc.php');
?>