<?php
/**
 * Footer Template
 * 
 * Provides the common footer structure for all pages:
 * - Copyright information
 * - Terms & Conditions link
 * - Bootstrap integration
 * 
 * @package BikeStore\Templates
 * @version 1.0
 * 
 * @var string $currentPath Current page path for active link highlighting
 */
?>

</main>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <!-- Legal Links Section -->
            <div class="col-md-3">
                <a href="/SAE401/terms" 
                   class="text-white text-decoration-none <?php echo ($currentPath === 'terms') ? 'active' : ''; ?>" aria-current="<?php echo ($currentPath === 'terms') ? 'page' : 'false'; ?>">
                    Terms & Conditions
                </a>
            </div>
            <div class="col-md-3">
                <a href="/SAE401/api-docs" class="text-white text-decoration-none">API Documentation</a>
            </div>
            <!-- Copyright Section -->
            <div class="col-md-6 text-md-end">
                <span>&copy; 2024 - 2025 BikeStores</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>