<?php
// pages/driver/my_deliveries.php
?>
<div class="container-fluid pb-5 h-100 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-center mb-2 pt-2">
        <h2 class="h4 fw-bold mb-0">Current Delivery</h2>
        <span class="badge bg-success bg-opacity-10 text-success fw-bold border border-success px-3 py-1">In Progress</span>
    </div>

    <!-- Active Delivery Card (Takes up most space) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden flex-grow-1 d-flex flex-column mb-3">
        
        <!-- Map Container -->
        <div id="deliveryMap" class="position-relative bg-light" style="height: 50vh; width: 100%;">
            <!-- Map controls will be injected here by Leaflet -->
        </div>
        
        <!-- Delivery Details Panel (Bottom Sheet style) -->
        <div class="card-body p-3 d-flex flex-column bg-white" style="position: relative; z-index: 500; margin-top: -20px; border-radius: 20px 20px 0 0; box-shadow: 0 -4px 10px rgba(0,0,0,0.05);">
            <div class="align-self-center bg-secondary rounded-pill mb-3" style="width: 40px; height: 4px; opacity: 0.2;"></div>
            
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h3 class="h5 fw-bold text-dark mb-1">Order #ORD-123</h3>
                    <p class="text-muted small mb-0">Preparing for drop-off</p>
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-5 text-primary">₱450.00</div>
                    <div class="badge bg-warning text-dark">COD</div>
                </div>
            </div>

            <!-- Timeline Route (Simplified) -->
            <div class="d-flex align-items-center mb-4">
                <div class="d-flex flex-column align-items-center me-3">
                    <div class="rounded-circle border border-2 border-white ms-1" style="width: 12px; height: 12px; background: #e5e7eb;"></div>
                    <div style="width: 2px; height: 24px; background: #e5e7eb;"></div>
                    <div class="rounded-circle border border-2 border-white ms-1 shadow-sm" style="width: 16px; height: 16px; background: var(--success); box-shadow: 0 0 0 2px var(--success);"></div>
                </div>
                <div class="flex-grow-1">
                    <div class="mb-3 opacity-50">
                        <div class="fw-bold text-dark lh-1" style="font-size: 0.9rem;">Store (Leilife Cafe)</div>
                    </div>
                    <div>
                         <div class="fw-bold text-dark lh-1 fs-6">Unit 402, High Street South</div>
                         <small class="text-muted">BGC, Taguig</small>
                    </div>
                </div>
                <a href="tel:+639123456789" class="btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ph-fill ph-phone text-success fs-4"></i>
                </a>
            </div>
            
            <div class="mt-auto d-flex gap-2">
                 <button class="btn btn-success w-100 fw-bold shadow py-3">
                     <i class="ph-bold ph-check-circle me-2"></i> Complete Delivery
                 </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if map container exists
    if(document.getElementById('deliveryMap')) {
        // Init Map (Coordinates for BGC, Taguig roughly)
        // Store: 14.5505, 121.0500 (approx)
        // Customer: 14.5450, 121.0550 (approx)
        
        var map = L.map('deliveryMap').setView([14.5505, 121.0520], 15);

        // Add OSM Tile Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Custom Icons
        var storeIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/markers-default.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
        });
        
        var customerIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/markers-default.png', 
            // In a real app we'd change color, but default is fine for demo
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            className: 'hue-rotate-marker' // We can use CSS to shift hue for distinct color
        });

        // Add Markers
        var storeMarker = L.marker([14.5505, 121.0500]).addTo(map).bindPopup("<b>Leilife Cafe</b><br>Pickup Point").openPopup();
        var customerMarker = L.marker([14.5450, 121.0550]).addTo(map).bindPopup("<b>Customer</b><br>Drop-off Point");

        // Fit bounds to show both
        var group = new L.featureGroup([storeMarker, customerMarker]);
        map.fitBounds(group.getBounds().pad(0.2));
    }
});
</script>

<style>
/* CSS trick to make the customer marker green */
.hue-rotate-marker {
    filter: hue-rotate(140deg);
}
</style>
