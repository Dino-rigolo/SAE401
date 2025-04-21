<?php
/**
 * Product Management View
 * 
 * Provides a comprehensive interface for managing products and related entities:
 * - Lists all items in a paginated table
 * - Supports filtering and sorting
 * - Provides add/edit/delete functionality
 * - Handles multiple entity types (products, brands, categories, stocks)
 * 
 * @package BikeStore\Views
 * @author Your Name
 * @version 1.0
 */

/**
 * Include required files and stylesheets
 */
include_once('www/header.inc.php');
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">';

/**
 * Initialize view variables
 * 
 * @var string $page_type Type of entity being managed (brands|categories|products|stocks)
 * @var string $title Display title for the current entity type
 * @var array $data Collection of entities to display
 * @var array $structure Form and table structure configuration
 * @var array $select_options Options for select inputs
 */
$page_type = isset($_GET['type']) ? $_GET['type'] : 'brands';
$title = $GLOBALS['product_title'];
$data = $GLOBALS['product_data'];
$structure = $GLOBALS['product_structure'];
$select_options = $GLOBALS['product_select_options'];

/**
 * Set up current structure based on page type
 * 
 * @var array $current_structure Structure configuration for current entity type
 * @var array $columns Table column headers
 * @var array $fields Data fields to display
 * @var string $id_field Primary key field name
 * @var array $form_fields Form input field configurations
 */
$current_structure = isset($structure[$page_type]) ? $structure[$page_type] : $structure['brands'];
$columns = $current_structure['columns'];
$fields = $current_structure['fields'];
$id_field = $current_structure['id_field'];
$form_fields = $current_structure['form_fields'];

/**
 * JavaScript Documentation
 * 
 * @function handleDelete
 * Handles item deletion:
 * - Shows confirmation modal
 * - Sends DELETE request to API
 * - Handles success/error responses
 * 
 * @function initializeCollapse
 * Initializes collapsible sections:
 * - Manages collapse icons
 * - Handles show/hide events
 * 
 * @event DOMContentLoaded
 * Sets up event listeners and initializes UI components
 * 
 * @api DELETE /SAE401/api/{type}/{id}
 * Endpoint for deleting items
 * Requires API key in headers
 */
?>

<div class="container-fluid my-5">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo ucfirst($page_type); ?> ajouté avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['update']) && $_GET['update'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo ucfirst($page_type); ?> modifié avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['delete']) && $_GET['delete'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo ucfirst($page_type); ?> supprimé avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="row mb-4">
        <div class="col text-center">
            <h1 class="fw-bold"><?php echo $title; ?></h1>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
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
                                                    data-name="<?php echo htmlspecialchars($item['product_name'] ?? 'cet élément'); ?>" 
                                                    data-type="<?php echo $page_type; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" style="fill: #dc3545;">
                                                        <path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path>
                                                    </svg>
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
            <div class="card shadow-sm">
                <div class="card-header bg-light collapse-header" data-bs-toggle="collapse" data-bs-target="#addFormCollapse" aria-expanded="true" aria-controls="addFormCollapse" style="cursor: pointer;">
                    <h5 class="text-center mb-0">
                        <span>Add a <?php echo rtrim($title, 's'); ?></span>
                        <i class="fas fa-chevron-down ms-2 collapse-icon"></i>
                    </h5>
                </div>
                <div class="collapse" id="addFormCollapse">
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
                                    <?php elseif ($field['name'] === 'store_id'): ?>
                                        <select id="store_id" name="store_id" class="form-select" required>
                                            <option value="">Select Store</option>
                                            <?php foreach ($select_options['store_id'] as $store): ?>
                                                <option value="<?php echo $store['store_id']; ?>"><?php echo $store['store_name']; ?></option>
                                            <?php endforeach; ?>
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
    </div>
</div>

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


    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }


    document.addEventListener('DOMContentLoaded', function() {

        const collapseHeaders = document.querySelectorAll('.collapse-header');
        

        collapseHeaders.forEach(header => {
            const targetId = header.getAttribute('data-bs-target');
            const collapseElement = document.querySelector(targetId);
            const icon = header.querySelector('.collapse-icon');
            
            if (collapseElement) {
                collapseElement.addEventListener('show.bs.collapse', function() {
                    if (icon) icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                });
                
                collapseElement.addEventListener('hide.bs.collapse', function() {
                    if (icon) icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                });
            }
        });
    });
</script>

<?php
include_once('www/footer.inc.php');
?>