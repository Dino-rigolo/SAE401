<?php 
include_once('www/header.inc.php');
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
                    <!-- Les options seront chargées dynamiquement -->
                </select>
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label fw-bold">Category</label>
                <select id="category" name="category" class="form-select">
                    <option value="">All</option>
                    <!-- Les options seront chargées dynamiquement -->
                </select>
            </div>
            <div class="col-md-2">
                <label for="model-year" class="form-label fw-bold">Model Year</label>
                <select id="model-year" name="model-year" class="form-select">
                    <option value="">All</option>
                    <!-- Les options seront chargées dynamiquement -->
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

    <!-- Produits -->
    <div id="product-container" class="row g-4">
        <!-- Les produits seront chargés dynamiquement ici -->
        <div class="col-12 text-center">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Chargement des produits...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Charger les filtres et les produits au chargement de la page
        loadFilters();
        loadProducts();

        // Validation des champs de prix
        document.getElementById('min-price').addEventListener('input', function(e) {
            // N'autorise que les chiffres, la virgule et le point
            let value = this.value.replace(/[^0-9,.]/g, '');
            
            // Assure qu'il n'y a qu'un seul séparateur décimal
            let decimalCount = (value.match(/[,.]/g) || []).length;
            if (decimalCount > 1) {
                let parts = value.split(/[,.]/);
                value = parts[0] + ',' + parts.slice(1).join('');
            }
            
            // Limite à 2 chiffres après la virgule
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
            // Même validation que pour min-price
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

        // Appliquer les filtres lors des changements
        document.getElementById('filter-form').addEventListener('change', function () {
            loadProducts();
        });
        
        // Appliquer les filtres également lors de la saisie dans les champs de prix
        document.getElementById('min-price').addEventListener('blur', loadProducts);
        document.getElementById('max-price').addEventListener('blur', loadProducts);
    });

    function loadFilters() {
        fetch('/SAE401/api/ApiProducts.php?action=getfilters')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Charger les marques
                if (data.brands) {
                    const brandSelect = document.getElementById('brand');
                    data.brands.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand.id;
                        option.textContent = brand.name;
                        brandSelect.appendChild(option);
                    });
                }

                // Charger les catégories
                if (data.categories) {
                    const categorySelect = document.getElementById('category');
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });
                }

                // Charger les années modèles
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

    function loadProducts() {
        const brand = document.getElementById('brand').value;
        const category = document.getElementById('category').value;
        const modelYear = document.getElementById('model-year').value;

        // Récupération et conversion des prix (virgule → point)
        let minPrice = document.getElementById('min-price').value;
        let maxPrice = document.getElementById('max-price').value;

        // Convertir les virgules en points pour l'API
        if (minPrice) minPrice = minPrice.replace(',', '.');
        if (maxPrice) maxPrice = maxPrice.replace(',', '.');

        // Débogage - afficher les valeurs des filtres
        console.log("Filtres appliqués:", {
            brand: brand,
            category: category,
            model_year: modelYear,
            min_price: minPrice,
            max_price: maxPrice
        });

        const params = new URLSearchParams();
        
        // Toujours inclure action=getall
        params.append('action', 'getall');

        if (brand) params.append('brand', brand);
        if (category) params.append('category', category);
        if (modelYear) params.append('model_year', modelYear);
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        
        // Débogage - afficher l'URL complète
        console.log("URL de requête:", `/SAE401/api/ApiProducts.php?${params.toString()}`);

        // Afficher un indicateur de chargement
        const container = document.getElementById('product-container');
        container.innerHTML = `
            <div class="col-12 text-center">
                <div class="spinner-border text-success" role="status"></div>
                <p>Chargement des produits...</p>
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
                    container.innerHTML = '<div class="col-12 text-center"><p>Aucun produit trouvé.</p></div>';
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
                container.innerHTML = '<div class="col-12 text-center text-danger"><p>Erreur lors du chargement des produits: ' + error.message + '</p></div>';
            });
    }
</script>

<?php
include_once('www/footer.inc.php');
?>
