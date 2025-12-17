<!-- LEAFLET CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- EDITADDRESS MODAL -->
<div id="addressModal" class="modal-overlay" style="display: none;">
    <div class="modal-content glass-effect">
        <h3>Edit Address</h3>
        <p class="subtitle">Exclusive to South Caloocan</p>
        <form id="addressForm">
            <div class="form-group">
                <label>Street / House No.</label>
                <input type="text" name="street" class="modal-input" placeholder="Enter street address" required>
            </div>
            
            <div class="form-group">
                <label>Region</label>
                <select name="region" id="regionSelect" class="modal-input" required>
                    <option value="" disabled selected>Select Region</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label>Province</label>
                    <select name="province" id="provinceSelect" class="modal-input" required>
                        <option value="" disabled selected>Select Province</option>
                    </select>
                </div>
                <div class="form-group half">
                    <label>City</label>
                    <select name="city" id="citySelect" class="modal-input" required>
                        <option value="" disabled selected>Select City</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Barangay</label>
                <select name="barangay" id="barangaySelect" class="modal-input" required>
                    <option value="" disabled selected>Select Barangay</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Pin Location</label>
                <div id="map" style="height: 200px; width: 100%; border-radius: 8px;"></div>
                <p class="small-text">Click on the map to pin your location within South Caloocan.</p>
                <input type="hidden" name="latitude" id="latInput">
                <input type="hidden" name="longitude" id="lngInput">
            </div>

            <div class="modal-actions">
                <button type="button" id="closeAddressModal" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary-custom" id="saveAddressBtn">Save Address</button>
            </div>
        </form>
    </div>
</div>