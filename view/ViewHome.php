<?php
/**
 * Home Page View
 * 
 * Displays the main landing page of the BikeStore application featuring:
 * - Welcome banner with call-to-action
 * - Interactive map showing all store locations
 * - List of all stores with details
 * 
 * @package BikeStore\Views
 * @author Your Name
 * @version 1.0
 */

/**
 * Include header template
 */
include_once('www/header.inc.php');
?>

<div class="intro text-center text-white" style="background-color: #333; padding: 50px 0;">
    <h2>Welcome to</h2>
    <h1>BikeStores</h1>
    <h2>Ride Beyond Limits</h2>
    <a href="/SAE401/catalogue" class="btn btn-success rounded-pill mt-3 px-4 py-2">Our bikes</a>
</div>

<h2 class="text-center my-4" style="color: #333;">The BikeStores shops</h2>
<div class="container">
    <div class="card shadow mb-5" style="border: none;">
        <div class="card-body p-0">
            <div id="map" style="height: 500px;"></div>
        </div>
    </div>
</div>


<h2 class="text-center my-4" style="color: #333;">List of shops</h2>
<div class="container">
    <div class="row g-4" id="shop-container">

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
    /**
     * BikeStores Map Interface
     * 
     * Provides an interactive map showing store locations and user position
     * Includes functionality for geocoding addresses and displaying store information
     * 
     * @module BikeStoresMap
     * @author Your Name
     * @version 1.0
     */

    /** 
     * Global map instance
     * @type {L.Map|null}
     */
    var map; 
    
    /**
     * Initialize map and load stores when window is fully loaded
     * 
     * @function
     * @listens window.load
     */
    window.addEventListener('load', function() {
        if (typeof L === 'undefined') {
            console.error("Leaflet isn't loaded correctly");
            document.getElementById("map").innerHTML = "Error loading map";
            return;
        }
        
        console.log("Initializing map");

        /**
         * Initialize Leaflet map with France as the center point
         * 
         * @type {L.Map}
         */
        map = L.map('map', {
            center: [46.603354, 1.888334], // France center coordinates
            zoom: 6,
            zoomControl: true
        });
        
        /**
         * Add tile layer to map using CartoDB basemap
         * 
         * @type {L.TileLayer}
         */
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);
        
        console.log("Map initialized");
        
        /**
         * Force map to recalculate its size after a short delay
         * Necessary for maps in tabs or hidden containers
         */
        setTimeout(function() {
            map.invalidateSize(true);
            console.log("Map resized");
        }, 500);
        
        // Load all store locations
        loadShops();
        
        /**
         * Get user's current location and display it on the map
         * Centers the map on user's position when available
         */
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                /**
                 * Success callback for geolocation
                 * 
                 * @param {GeolocationPosition} position - User's position
                 */
                function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    console.log("User position:", lat, lng);

                    /**
                     * Custom icon for user location
                     * 
                     * @type {L.Icon}
                     */
                    var userIcon = L.icon({
                        iconUrl: '/SAE401/www/images/user-marker.png',
                        iconSize: [32, 32], 
                        iconAnchor: [16, 32], 
                        popupAnchor: [0, -32] 
                    });

                    /**
                     * User location marker
                     * 
                     * @type {L.Marker}
                     */
                    var userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
                    userMarker.bindPopup("<b>Your location</b>").openPopup();
                    
                    // Center map on user's position
                    map.setView([lat, lng], 8);
                },
                /**
                 * Error callback for geolocation
                 * 
                 * @param {GeolocationPositionError} error - Geolocation error
                 */
                function(error) {
                    console.error("Geolocation error:", error);
                }
            );
        }
    });
    
    /**
     * Loads all bike shops from the API and displays them on the map and in the list
     * Uses geocoding to convert addresses to coordinates
     * 
     * @function
     * @returns {void}
     */
    function loadShops() {
        console.log("Loading stores");
        $.ajax({
            url: "/SAE401/api/ApiStores.php?action=getall",
            type: "GET",
            dataType: "json",
            /**
             * Success callback for API call
             * 
             * @param {Array} data - Store data from API
             */
            success: function(data) {
                console.log("Stores received:", data);
                $("#shop-container").empty();
                
                // Handle empty data
                if (!data || data.length === 0 || (data.error && data.error === 'No stores found')) {
                    $("#shop-container").html('<div class="col-12"><div class="alert alert-info text-center">No stores available</div></div>');
                    return;
                }
                
                /**
                 * Process each store to display on map and in list
                 * 
                 * @param {number} index - Array index
                 * @param {Object} store - Store information
                 * @param {number} store.store_id - Store ID
                 * @param {string} store.store_name - Store name
                 * @param {string} store.street - Street address
                 * @param {string} store.zip_code - Postal code
                 * @param {string} store.city - City name
                 */
                $.each(data, function(index, store) {
                    // Create store card for list display
                    var storeHtml = '<div class="col-lg-4 col-md-6 col-sm-12">' +
                        '<div class="card h-100 transition-card">' +
                        '<div class="card-body">' +
                        '<a href="/SAE401/shop/' + store.store_id + '" class="text-decoration-none text-dark">' +
                        '<h5 class="card-title">' + (store.store_name || 'Unknown name') + '</h5>' +
                        '<p class="card-text">' + (store.street || 'Unknown address') + '</p>' +
                        '<p class="card-text">' + (store.zip_code || '') + ' ' + (store.city || '') + '</p>' +
                        '</a>' +
                        '</div>' +
                        '<div class="card-footer bg-transparent">' +
                        '<a href="/SAE401/shop/' + store.store_id + '" class="btn btn-outline-primary w-100">View details</a>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    
                    // Add store card to container
                    $("#shop-container").append(storeHtml);
                    
                    // Add store to map if map is initialized
                    if (map) {
                        /**
                         * Geocode store address to get coordinates
                         * Uses Nominatim OpenStreetMap service
                         */
                        var address = encodeURIComponent((store.street || '') + ', ' + (store.zip_code || '') + ' ' + (store.city || ''));
                        console.log("Geocoding for:", store.store_name, "Address:", address);                 
                        
                        $.getJSON('https://nominatim.openstreetmap.org/search?format=json&q=' + address, function(results) {
                            if (results && results.length > 0) {
                                console.log("Geocoding successful for:", store.store_name, results[0]);
                                
                                var lat = parseFloat(results[0].lat);
                                var lon = parseFloat(results[0].lon);
                                
                                if (!isNaN(lat) && !isNaN(lon)) {
                                    /**
                                     * Custom icon for store markers
                                     * 
                                     * @type {L.Icon}
                                     */
                                    var storeIcon = L.icon({
                                        iconUrl: '/SAE401/www/images/store-marker.png',
                                        iconSize: [32, 32],
                                        iconAnchor: [16, 32],
                                        popupAnchor: [0, -32]
                                    });

                                    /**
                                     * Create and add store marker to map
                                     * 
                                     * @type {L.Marker}
                                     */
                                    var storeMarker = L.marker([lat, lon], { icon: storeIcon }).addTo(map);
                                    
                                    // Create popup with store information
                                    storeMarker.bindPopup(
                                        '<div class="text-center">' +
                                        '<h5>' + store.store_name + '</h5>' +
                                        '<p>' + store.street + '<br>' + store.zip_code + ' ' + store.city + '</p>' +
                                        '<a href="/SAE401/shop/' + store.store_id + '" class="btn btn-sm btn-success">View the shop</a>' +
                                        '</div>'
                                    );
                                } else {
                                    console.warn("Invalid coordinates for:", store.store_name);
                                }
                            } else {
                                console.warn("Geocoding failed for:", store.store_name);
                            }
                        }).fail(function(jqXHR, textStatus, errorThrown) {
                            console.error("Geocoding error for:", store.store_name, errorThrown);
                        });
                    } else {
                        console.error("Map is not initialized");
                    }
                });
            },
            /**
             * Error callback for API call
             * 
             * @param {jqXHR} xhr - jQuery XHR object
             * @param {string} status - Status text
             * @param {string} error - Error message
             */
            error: function(xhr, status, error) {
                console.error("Error loading stores:", status, error);
                $("#shop-container").html('<div class="col-12"><div class="alert alert-danger text-center">Error loading stores</div></div>');
            }
        });
    }
</script>

<?php
/**
 * Include footer template
 */
include_once('www/footer.inc.php');
?>