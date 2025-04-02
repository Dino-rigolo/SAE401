<?php 
include_once('www/header.inc.php');

error_log("Loading ViewModifProduct.php for type: " . ($GLOBALS['page_type'] ?? 'unknown'));
error_log("Item data: " . json_encode($GLOBALS['item_data'] ?? 'not set'));

// Récupérer le type et l'ID depuis l'URL
$page_type = $GLOBALS['page_type'];
$item = $GLOBALS['item_data'];
$structure = $GLOBALS['product_structure'];
$select_options = $GLOBALS['product_select_options'];

// Récupérer la structure pour le type actuel
$current_structure = isset($structure[$page_type]) ? $structure[$page_type] : $structure['brands'];
$form_fields = $current_structure['form_fields'];
$id_field = $current_structure['id_field'];

error_log("Données de l'élément : " . json_encode($item));
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col text-center">
            <h1 class="fw-bold">Modify <?php echo rtrim($GLOBALS['product_title'], 's'); ?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/SAE401/update' . $page_type); ?>" method="POST">
                        <input type="hidden" name="<?php echo $id_field; ?>" value="<?php echo $item[$id_field]; ?>">
                        <input type="hidden" name="brand_id" value="<?php echo isset($item['brand_id']) ? htmlspecialchars($item['brand_id']) : ''; ?>">
                        
                        <?php foreach ($form_fields as $field): ?>
                            <div class="mb-3">
                                <label for="<?php echo $field['name']; ?>" class="form-label"><?php echo $field['label']; ?></label>
                                
                                <?php if ($field['type'] === 'select'): ?>
                                    <?php if ($field['name'] === 'brand_id'): ?>
                                        <?php if (isset($item['brand_id'])): ?>
                                            <select id="brand_id" name="brand_id" class="form-select" required>
                                                <?php foreach ($select_options['brand_id'] as $option): ?>
                                                    <option value="<?php echo $option['brand_id']; ?>" <?php echo ($item['brand_id'] == $option['brand_id']) ? 'selected' : ''; ?>>
                                                        <?php echo $option['brand_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <p class="text-danger">Brand ID is missing</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <select id="<?php echo $field['name']; ?>" name="<?php echo $field['name']; ?>" class="form-select" required>
                                            <?php if (isset($select_options[$field['name']])): ?>
                                                <?php foreach ($select_options[$field['name']] as $option): ?>
                                                    <?php 
                                                    $option_field_name = $field['name']; 
                                                    $option_value = isset($option[$option_field_name]) ? $option[$option_field_name] : null;
                                                    
                                                    // Déterminer le label à afficher selon le type de select
                                                    $option_label = 'Unknown';
                                                    if ($field['source'] === 'brands' && isset($option['brand_name'])) {
                                                        $option_label = $option['brand_name'];
                                                    } elseif ($field['source'] === 'categories' && isset($option['category_name'])) {
                                                        $option_label = $option['category_name'];
                                                    } elseif ($field['source'] === 'shops' && isset($option['store_name'])) {
                                                        $option_label = $option['store_name'];
                                                    } elseif ($field['source'] === 'products' && isset($option['product_name'])) {
                                                        $option_label = $option['product_name'];
                                                    }

                                                    // Vérifier si l'ID courant correspond à l'option
                                                    $is_selected = isset($item[$field['name']]) && $item[$field['name']] == $option_value;
                                                    ?>
                                                    <option value="<?php echo htmlspecialchars($option_value); ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($option_label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    <?php endif; ?>
                                <?php elseif ($field['type'] === 'textarea'): ?>
                                    <textarea id="<?php echo $field['name']; ?>" name="<?php echo $field['name']; ?>" class="form-control" required><?php echo htmlspecialchars($item[$field['name']] ?? ''); ?></textarea>
                                <?php else: ?>
                                    <input type="<?php echo $field['type']; ?>" 
                                        id="<?php echo $field['name']; ?>" 
                                        name="<?php echo $field['name']; ?>" 
                                        value="<?php echo htmlspecialchars($item[$field['name']] ?? ''); ?>"
                                        class="form-control" 
                                        <?php echo isset($field['step']) ? 'step="' . $field['step'] . '"' : ''; ?>
                                        <?php if ($field['name'] === 'model_year'): ?>
                                            min="1950" 
                                            max="<?php echo date('Y') + 10; ?>"
                                        <?php endif; ?>
                                        required>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/SAE401/products?type=<?php echo $page_type; ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include_once('www/footer.inc.php');
?>