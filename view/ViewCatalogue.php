<?php
/**
 * Product Catalog View
 * 
 * Displays the product catalog with filtering capabilities for bikes
 * Including brand, category, model year and price range filters
 * 
 * @package BikeStore\Views
 * @author 
 * @version 1.0
 */

// Include header
include_once('www/header.inc.php');

/**
 * JavaScript Functions Documentation
 * 
 * @function loadFilters
 * Fetches and populates filter options from the API
 * - Brands dropdown
 * - Categories dropdown
 * - Model years dropdown
 * 
 * @function loadProducts
 * Fetches and displays products based on selected filters
 * - Handles price formatting
 * - Displays loading state
 * - Renders product cards
 * - Handles error states
 * 
 * @event DOMContentLoaded
 * Initializes the page:
 * - Loads initial filters
 * - Loads initial products
 * - Sets up event listeners for filter changes
 * - Sets up input validation for price fields
 */
?>

<div class="container my-5">
    <h1 class="text-center fw-bold mb-4">Bikes</h1>

    <!-- Filtres -->
    <div class="card shadow-sm p-4 bg-light mb-4">
        <form id="filter-form" class="row g-3">
            <div class="col-md-3">
                <label for="brand" class="form-label fw-bold">Brand</label>
                <select id="brand" name="brand" class="form-select">
                    <option value="">All</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label fw-bold">Category</label>
                <select id="category" name="category" class="form-select">
                    <option value="">All</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="model-year" class="form-label fw-bold">Model Year</label>
                <select id="model-year" name="model-year" class="form-select">
                    <option value="">All</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Price</label>
                <div class="d-flex align-items-center">
                    <input type="text" id="min-price" name="min-price" class="form-control me-2" placeholder="Min" pattern="[0-9]+([,.][0-9]{1,2})?" inputmode="decimal">
                    <input type="text" id="max-price" name="max-price" class="form-control" placeholder="Max" pattern="[0-9]+([,.][0-9]{1,2})?" inputmode="decimal">
                </div>
            </div>
        </form>
    </div>

    <div id="product-container" class="row g-4">
        <div class="col-12 text-center">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading products...</p>
        </div>
    </div>
</div>

<script>
    /**
     * Loads filter options from the API
     * 
     * @returns {void}
     * @throws {Error} When API request fails
     */
    function loadFilters() {
        fetch('/SAE401/api/ApiProducts.php?action=getfilters')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
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
            .catch(error => console.error('Erreur lors du chargement des filtres:', error));
    }

    /**
     * Loads products based on selected filters
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

        if (minPrice) minPrice = minPrice.replace(',', '.');
        if (maxPrice) maxPrice = maxPrice.replace(',', '.');

        console.log("Filtres appliqués:", {
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
        
        console.log("URL de requête:", `/SAE401/api/ApiProducts.php?${params.toString()}`);

        const container = document.getElementById('product-container');
        container.innerHTML = `
            <div class="col-12 text-center">
                <div class="spinner-border text-success" role="status"></div>
                <p>Loading products...</p>
            </div>
        `;

        fetch(`/SAE401/api/ApiProducts.php?${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
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
                console.error('Erreur lors du chargement des produits:', error);
                container.innerHTML = '<div class="col-12 text-center text-danger"><p>Error loading products: ' + error.message + '</p></div>';
            });
    }

    // Initialize page when DOM is loaded
    document.addEventListener('DOMContentLoaded', function () {
        loadFilters();
        loadProducts();

        document.getElementById('min-price').addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9,.]/g, '');
            
            let decimalCount = (value.match(/[,.]/g) || []).length;
            if (decimalCount > 1) {
                let parts = value.split(/[,.]/);
                value = parts[0] + ',' + parts.slice(1).join('');
            }
            
            if (value.indexOf(',') !== -1 || value.indexOf('.') !== -1) {
                let parts = value.split(/[,.]/);
                if (parts[1] && parts[1].length > 2) {
                    parts[1] = parts[1].substring(0, 2);
                    value = parts[0] + ',' + parts[1];
                }
            }
            
            this.value = value;
        });
        
        document.getElementById('max-price').addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9,.]/g, '');
            let decimalCount = (value.match(/[,.]/g) || []).length;
            if (decimalCount > 1) {
                let parts = value.split(/[,.]/);
                value = parts[0] + ',' + parts.slice(1).join('');
            }
            if (value.indexOf(',') !== -1 || value.indexOf('.') !== -1) {
                let parts = value.split(/[,.]/);
                if (parts[1] && parts[1].length > 2) {
                    parts[1] = parts[1].substring(0, 2);
                    value = parts[0] + ',' + parts[1];
                }
            }
            this.value = value;
        });

        document.getElementById('filter-form').addEventListener('change', function () {
            loadProducts();
        });
        document.getElementById('min-price').addEventListener('blur', loadProducts);
        document.getElementById('max-price').addEventListener('blur', loadProducts);
    });
</script>

<?php
include_once('www/footer.inc.php');
?>
