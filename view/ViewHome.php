<?php 
include_once('www/header.inc.php');
?>
<!-- Section principale -->
<div class="intro text-center text-white" style="background-color: #333; padding: 50px 0;">
    <h2>Welcome to</h2>
    <h1>BikeStores</h1>
    <h2>Ride Beyond Limits</h2>
    <a href="/SAE401/catalogue" class="btn btn-success rounded-pill mt-3 px-4 py-2">Our bikes</a>
</div>

<!-- Section des magasins -->
<h2 class="text-center my-4" style="color: #333;">The BikeStores shops</h2>
<div class="container">
    <div class="card shadow mb-5" style="border: none;">
        <div class="card-body p-0">
            <div id="map" style="height: 500px;"></div>
        </div>
    </div>
</div>

<!-- Liste des magasins -->
<h2 class="text-center my-4" style="color: #333;">List of shops</h2>
<div class="container">
    <div class="row g-4" id="shop-container">
        <!-- Les données des boutiques seront chargées ici par AJAX -->
        <div class="col-12 text-center">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Chargement des boutiques...</p>
        </div>
    </div>
</div>

<!-- Inclusion des bibliothèques -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script type="text/javascript">
    var map; // Variable globale pour la carte
    
    // Attendre que tout soit complètement chargé
    window.addEventListener('load', function() {
        if (typeof L === 'undefined') {
            console.error("Leaflet n'est pas chargé correctement");
            document.getElementById("map").innerHTML = "Erreur de chargement de la carte";
            return;
        }
        
        console.log("Initialisation de la carte");
        
        // Initialiser la carte
        map = L.map('map', {
            center: [46.603354, 1.888334],
            zoom: 6,
            zoomControl: true
        });
        
        // Ajouter les tuiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);
        
        console.log("Carte initialisée");
        
        // Forcer la mise à jour de la taille
        setTimeout(function() {
            map.invalidateSize(true);
            console.log("Carte redimensionnée");
        }, 500);
        
        // Charger les boutiques
        loadShops();
        
        // Géolocalisation
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    console.log("Position utilisateur:", lat, lng);
                    
                    // Définir une icône personnalisée pour l'utilisateur
                    var userIcon = L.icon({
                        iconUrl: '/SAE401/www/images/user-marker.png',
                        iconSize: [32, 32], // Taille de l'icône
                        iconAnchor: [16, 32], // Point de l'icône qui correspond à la position
                        popupAnchor: [0, -32] // Point d'où le popup s'ouvre
                    });

                    var userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
                    userMarker.bindPopup("<b>Votre position</b>").openPopup();
                    
                    map.setView([lat, lng], 8);
                },
                function(error) {
                    console.error("Erreur de géolocalisation:", error);
                }
            );
        }
    });
    
    // Fonction pour charger les boutiques depuis l'API
    function loadShops() {
        console.log("Chargement des boutiques");
        $.ajax({
            url: "/SAE401/api/ApiStores.php?action=getall",
            type: "GET",
            dataType: "json",
            success: function(data) {
                console.log("Boutiques reçues:", data);
                
                // Vider le conteneur
                $("#shop-container").empty();
                
                // Si aucune boutique n'est trouvée
                if (!data || data.length === 0 || (data.error && data.error === 'No stores found')) {
                    $("#shop-container").html('<div class="col-12"><div class="alert alert-info text-center">Aucune boutique disponible</div></div>');
                    return;
                }
                
                // Afficher chaque boutique dans la liste
                $.each(data, function(index, store) {
                    var storeHtml = '<div class="col-lg-4 col-md-6 col-sm-12">' +
                        '<div class="card h-100 transition-card">' +
                        '<div class="card-body">' +
                        '<a href="/SAE401/shop/' + store.store_id + '" class="text-decoration-none text-dark">' +
                        '<h5 class="card-title">' + (store.store_name || 'Nom inconnu') + '</h5>' +
                        '<p class="card-text">' + (store.street || 'Adresse inconnue') + '</p>' +
                        '<p class="card-text">' + (store.zip_code || '') + ' ' + (store.city || '') + '</p>' +
                        '</a>' +
                        '</div>' +
                        '<div class="card-footer bg-transparent">' +
                        '<a href="/SAE401/shop/' + store.store_id + '" class="btn btn-outline-primary w-100">Voir détails</a>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    
                    $("#shop-container").append(storeHtml);
                    
                    // Ajouter un marqueur seulement si la carte est initialisée
                    if (map) {
                        // Adresse pour la géolocalisation
                        var address = encodeURIComponent((store.street || '') + ', ' + (store.zip_code || '') + ' ' + (store.city || ''));
                        console.log("Géocodage pour:", store.store_name, "Adresse:", address);
                        
                        // Utiliser Nominatim (OpenStreetMap) pour le géocodage
                        $.getJSON('https://nominatim.openstreetmap.org/search?format=json&q=' + address, function(results) {
                            if (results && results.length > 0) {
                                console.log("Géocodage réussi pour:", store.store_name, results[0]);
                                
                                var lat = parseFloat(results[0].lat);
                                var lon = parseFloat(results[0].lon);
                                
                                if (!isNaN(lat) && !isNaN(lon)) {
                                    // Définir une icône personnalisée pour les boutiques
                                    var storeIcon = L.icon({
                                        iconUrl: '/SAE401/www/images/store-marker.png',
                                        iconSize: [32, 32],
                                        iconAnchor: [16, 32],
                                        popupAnchor: [0, -32]
                                    });

                                    var storeMarker = L.marker([lat, lon], { icon: storeIcon }).addTo(map);
                                    storeMarker.bindPopup(
                                        '<div class="text-center">' +
                                        '<h5>' + store.store_name + '</h5>' +
                                        '<p>' + store.street + '<br>' + store.zip_code + ' ' + store.city + '</p>' +
                                        '<a href="/SAE401/shop/' + store.store_id + '" class="btn btn-sm btn-primary">Voir la boutique</a>' +
                                        '</div>'
                                    );
                                } else {
                                    console.warn("Coordonnées invalides pour:", store.store_name);
                                }
                            } else {
                                console.warn("Géocodage échoué pour:", store.store_name);
                            }
                        }).fail(function(jqXHR, textStatus, errorThrown) {
                            console.error("Erreur de géocodage pour:", store.store_name, errorThrown);
                        });
                    } else {
                        console.error("La carte n'est pas initialisée");
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error("Erreur lors du chargement des boutiques:", status, error);
                $("#shop-container").html('<div class="col-12"><div class="alert alert-danger text-center">Erreur lors du chargement des boutiques</div></div>');
            }
        });
    }
</script>

<?php 
include_once('www/footer.inc.php');
?>