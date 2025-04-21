<?php
/**
 * Employees Management View
 * 
 * Displays and manages employees for each store:
 * - Lists employees by store
 * - Allows adding new employees
 * - Provides edit and delete functionality
 * - Handles role-based access control
 * 
 * @package BikeStore\Views
 * @author Your Name
 * @version 1.0
 */

/**
 * Include header template and required CSS
 */
include_once('www/header.inc.php');
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">';

/**
 * @var array $employees Global array containing all employees data
 * @var array $stores Global array containing all stores data
 */
$employees = $GLOBALS['employees_data'];
$stores = $GLOBALS['stores_data'];

/**
 * Filter stores based on user role
 * Chiefs can only see their own store
 * IT staff can see all stores
 * 
 * @var int|null $storeFilter Store ID for filtering, null for IT staff
 */
$storeFilter = ($_SESSION['employee']['employee_role'] === 'chief') 
    ? $_SESSION['employee']['store_id'] 
    : null;

?>

<!-- Success messages section -->
<div class="container-fluid my-5">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Employee added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['update']) && $_GET['update'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Employee modified successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['delete']) && $_GET['delete'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Employee deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col text-center">
            <h1 class="fw-bold">Employees</h1>
        </div>
    </div>

    <?php 
    foreach ($stores as $store):
        if ($storeFilter === null || $store['store_id'] == $storeFilter):
    ?>
    <div class="row mt-5">
        <div class="col-md-10 offset-md-1">
            <h5 class="text-center mb-3">Employees of <?php echo htmlspecialchars($store['store_name']); ?></h5>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php
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
        endif; 
    endforeach; 
    ?>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <span id="deleteItemName"></span> ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Oui</button>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * JavaScript Documentation
 * 
 * @event DOMContentLoaded
 * Initializes delete functionality:
 * - Sets up event listeners for delete buttons
 * - Handles confirmation modal
 * - Processes delete requests to API
 * 
 * @function deleteEmployee
 * Sends DELETE request to API endpoint
 * @param {string} id Employee ID
 * @param {string} type Entity type (always 'employees')
 * @returns {Promise} API response
 * 
 * @api DELETE /SAE401/api/employees/{id}
 * Requires API key in headers
 */
?>

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

    /**
     * Loads filter options from the API
     * 
     * Fetches brand, category, and model year data from the product API 
     * and populates the corresponding dropdown menus
     * 
     * @returns {void}
     * @throws {Error} When API request fails
     */
    function loadFilters() {
        fetch('/SAE401/api/ApiProducts.php?action=getfilters', {
            headers: {
                'Api': 'e8f1997c763'  // API key for authentication
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.brands) {
                    const brandSelect = document.getElementById('brand');
                    data.brands.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand.id;
                        option.textContent = brand.name;
                        brandSelect.appendChild(option);
                    });
                }

                if (data.categories) {
                    const categorySelect = document.getElementById('category');
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });
                }

                if (data.model_years) {
                    const yearSelect = document.getElementById('model-year');
                    data.model_years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        yearSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading filters:', error));
    }

    /**
     * Loads products based on selected filters
     * 
     * Fetches products from API with applied filters (brand, category, year, price)
     * and displays them in the product container as cards
     * 
     * @returns {void}
     * @throws {Error} When API request fails
     */
    function loadProducts() {
        const brand = document.getElementById('brand').value;
        const category = document.getElementById('category').value;
        const modelYear = document.getElementById('model-year').value;

        let minPrice = document.getElementById('min-price').value;
        let maxPrice = document.getElementById('max-price').value;

        // Normalize decimal separator
        if (minPrice) minPrice = minPrice.replace(',', '.');
        if (maxPrice) maxPrice = maxPrice.replace(',', '.');

        console.log("Applied filters:", {
            brand: brand,
            category: category,
            model_year: modelYear,
            min_price: minPrice,
            max_price: maxPrice
        });

        const params = new URLSearchParams();
        
        params.append('action', 'getall');

        if (brand) params.append('brand', brand);
        if (category) params.append('category', category);
        if (modelYear) params.append('model_year', modelYear);
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        
        console.log("Request URL:", `/SAE401/api/ApiProducts.php?${params.toString()}`);

        const container = document.getElementById('product-container');
        container.innerHTML = `
            <div class="col-12 text-center">
                <div class="spinner-border text-success" role="status"></div>
                <p>Loading products...</p>
            </div>
        `;

        fetch(`/SAE401/api/ApiProducts.php?${params.toString()}`, {
            headers: {
                'Api': 'e8f1997c763'  // API key for authentication
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                container.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center"><p>No products found.</p></div>';
                    return;
                }

                data.forEach(product => {
                    const productHtml = `
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm h-100">
                                <img src="/SAE401/www/images/velosport.jpg" class="card-img-top" alt="${product.name || 'No name'}">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-truncate">${product.product_name || 'No name'}</h5>
                                    <p class="text-muted mb-1">${product.brand_name || 'Unknown brand'} - ${product.category_name || 'Unknown category'}</p>
                                    <p class="card-text">Year: ${product.model_year || 'N/A'}</p>
                                    <p class="fw-bold text-success">${product.list_price !== undefined && product.list_price !== null ? Number(product.list_price).toFixed(2) : 'N/A'}€</p>
                                </div>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', productHtml);
                });
            })
            .catch(error => {
                console.error('Error loading products:', error);
                container.innerHTML = '<div class="col-12 text-center text-danger"><p>Error loading products: ' + error.message + '</p></div>';
            });
    }

    /**
     * Validates price input to ensure it contains only valid currency format
     * 
     * @param {Event} e - Input event
     * @returns {void}
     */
    function validatePriceInput(e) {
        let value = this.value.replace(/[^0-9,.]/g, '');
        
        // Handle multiple decimal separators
        let decimalCount = (value.match(/[,.]/g) || []).length;
        if (decimalCount > 1) {
            let parts = value.split(/[,.]/);
            value = parts[0] + ',' + parts.slice(1).join('');
        }
        
        // Limit to 2 decimal places
        if (value.indexOf(',') !== -1 || value.indexOf('.') !== -1) {
            let parts = value.split(/[,.]/);
            if (parts[1] && parts[1].length > 2) {
                parts[1] = parts[1].substring(0, 2);
                value = parts[0] + ',' + parts[1];
            }
        }
        
        this.value = value;
    }

    // Initialize page when DOM is loaded
    document.addEventListener('DOMContentLoaded', function () {
        // Load initial data
        loadFilters();
        loadProducts();

        // Set up price input validation
        document.getElementById('min-price').addEventListener('input', validatePriceInput);
        document.getElementById('max-price').addEventListener('input', validatePriceInput);

        // Set up filter change listeners
        document.getElementById('filter-form').addEventListener('change', function () {
            loadProducts();
        });
        document.getElementById('min-price').addEventListener('blur', loadProducts);
        document.getElementById('max-price').addEventListener('blur', loadProducts);
    });
</script>

<?php
/**
 * Include footer template
 */
include_once('www/footer.inc.php');
?>