<?php 
include_once('www/header.inc.php');
?>
<!-- Section principale -->
<div class="intro">
    <img src="/SAE401/www/images/hommevelo.jpg" alt="image of a bike" class="intro-image">
    <div class="intro-text">
        <h1>BikeStores</h1>
        <h2>Ride Beyond Limits</h2>
        <button class="intro-button">Our bikes</button>
    </div>
</div>

<!-- Section des magasins -->
<h2 class="section-title">The BikeStores shops</h2>
<div class="map-container">
    <!-- <img src="/SAE401/www/images/map.png" alt="Map of BikeStores shops" class="map-image"> -->
</div>

<!-- Liste des magasins -->
<h2 class="section-title">List of shops</h2>
<div class="shop-list">
    <?php 
    for ($i = 0; $i < 6; $i++) {
        echo '<div class="shop-item">
                <p><strong>Nom de la boutique</strong></p>
                <p>9046 rue d’Arga Bera</p>
                <p>64440 LARUNS</p>
              </div>';
    }
    ?>
</div>

<?php 
include_once('www/footer.inc.php');
?>