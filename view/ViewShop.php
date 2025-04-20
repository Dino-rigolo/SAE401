<?php 
include_once('www/header.inc.php');
?>

<div class="container my-5">
    <h1 class="text-center fw-bold mb-4"><?php echo htmlspecialchars($store['store_name'] ?? 'Shop Details'); ?></h1>
    
    <?php if (empty($store)): ?>
        <div class="alert alert-danger">
            Unable to retrieve shop information.
        </div>
    <?php else: ?>
        <div class="card shadow-sm p-4 bg-light mb-4">
            <h2 class="h5 fw-bold mb-3">Contact the shop</h2>
            <div class="d-flex align-items-center mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
            </svg>
                <a href="tel:<?php echo htmlspecialchars($store['phone'] ?? ''); ?>" class="text-decoration-none">
                    <?php echo htmlspecialchars($store['phone'] ?? 'Not specified'); ?>
                </a>
            </div>
            <div class="d-flex align-items-center mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shop" viewBox="0 0 16 16">
                <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5M4 15h3v-5H4zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm3 0h-2v3h2z"/>
                </svg>
                <span>
                    <?php echo htmlspecialchars($store['street'] ?? ''); ?>
                    <?php echo htmlspecialchars(($store['zip_code'] ?? '') . ' ' . ($store['city'] ?? '')); ?>
                </span>
            </div>
            <div class="d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/>
                </svg>
                <a href="mailto:<?php echo htmlspecialchars($store['email'] ?? ''); ?>" class="text-decoration-none">
                    <?php echo htmlspecialchars($store['email'] ?? 'Not specified'); ?>
                </a>
            </div>
        </div>
        <div class="card shadow-sm p-4 bg-light mb-4">
            <h2 class="h5 fw-bold mb-3">A word of welcome</h2>
            <div class="d-flex align-items-center">
                <p>The entire <?php echo htmlspecialchars($store['store_name'] ?? 'BikeStores'); ?>  team is delighted to be able to serve you! We are all passionate about mountain sports and are here to advise you. You can order all <?php echo htmlspecialchars($store['store_name'] ?? 'BikeStores'); ?>  products from us or from home (even those we don't have and even the bulkiest ones) and have them delivered to your shop within 48 hours ! Delivery is free!</p>       
            </div> 
        </div>  
    <?php endif; ?>
</div>

<?php 
include_once('www/footer.inc.php');
?>