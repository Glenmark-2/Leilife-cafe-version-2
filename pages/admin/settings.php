<?php
// Settings page
?>
<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Settings</p>
    </div>
    <button type="submit" form="settingsForm" class="btn-primary-custom btns" id="saveSettingsBtn">Save All Changes</button>
</div>

<div class="settings-container">
    <form id="settingsForm">
        <div class="settings-tabs">
            <button type="button" class="tab-btn active" data-tab="general">General</button>
            <button type="button" class="tab-btn" data-tab="operations">Operations</button>
            <button type="button" class="tab-btn" data-tab="delivery">Delivery</button>
            <button type="button" class="tab-btn" data-tab="payments">Payments</button>
            <button type="button" class="tab-btn" data-tab="system">System</button>
        </div>

        <div class="tab-content active" id="general">
            <div class="settings-section">
                <h3>Store Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Store Name</label>
                        <input type="text" name="store_name" id="store_name" placeholder="Leilife Cafe & Resto">
                    </div>
                    <div class="form-group">
                        <label>Slogan</label>
                        <input type="text" name="store_slogan" id="store_slogan" placeholder="Premium Coffee & Fine Dining">
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="contact_phone" id="contact_phone">
                    </div>
                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" id="contact_email">
                    </div>
                    <div class="form-group full-width">
                        <label>Physical Address</label>
                        <textarea name="physical_address" id="physical_address" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h3>Social Media</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Facebook URL</label>
                        <input type="url" name="facebook_link" id="facebook_link" placeholder="https://facebook.com/leilife">
                    </div>
                    <div class="form-group">
                        <label>Instagram URL</label>
                        <input type="url" name="instagram_link" id="instagram_link" placeholder="https://instagram.com/leilife">
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="operations">
            <div class="settings-section">
                <h3>Store Status</h3>
                <div class="form-group toggle-group">
                    <div class="toggle-info">
                        <strong>Live Store Status</strong>
                        <p>When closed, customers can browse but not order.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="is_store_open" id="is_store_open">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="settings-section">
                <h3>Opening Hours</h3>
                <div id="openingHoursContainer">
                    <!-- Populated by JS -->
                </div>
            </div>

            <div class="settings-section">
                <h3>Order Capacity</h3>
                <div class="form-group">
                    <label>Simultaneous Order Limit</label>
                    <input type="number" name="order_limit" id="order_limit" min="1">
                    <p class="help-text">Maximum active orders allowed at once.</p>
                </div>
            </div>
        </div>

        <div class="tab-content" id="delivery">
            <div class="settings-section">
                <h3>Delivery Fees</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Standard Delivery Fee (₱)</label>
                        <input type="number" name="delivery_fee" id="delivery_fee" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Free Delivery Threshold (₱)</label>
                        <input type="number" name="free_delivery_threshold" id="free_delivery_threshold" step="0.01">
                        <p class="help-text">Set to 0 to disable free delivery.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="payments">
            <div class="settings-section">
                <h3>Payment Methods</h3>
                <div class="form-group toggle-group">
                    <div class="toggle-info">
                        <strong>Cash on Delivery (COD)</strong>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_cod" id="enable_cod">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="form-group toggle-group">
                    <div class="toggle-info">
                        <strong>E-Wallet (GCash via PayMongo)</strong>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_gcash" id="enable_gcash">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="settings-section paymongo-config">
                <h3>PayMongo API Credentials</h3>
                <div class="form-group">
                    <label>Public Key</label>
                    <input type="password" name="paymongo_public_key" id="paymongo_public_key">
                </div>
                <div class="form-group">
                    <label>Secret Key</label>
                    <input type="password" name="paymongo_secret_key" id="paymongo_secret_key">
                </div>
                <p class="warning-text"><i class="bi bi-shield-lock"></i> Sensitive keys are hidden. Use with caution.</p>
            </div>
        </div>

        <div class="tab-content" id="system">
            <div class="settings-section">
                <h3>Receipt Customization</h3>
                <div class="form-group full-width">
                    <label>Receipt Footer Message</label>
                    <textarea name="receipt_footer" id="receipt_footer" rows="2" placeholder="Thank you for your purchase!"></textarea>
                    <p class="help-text">This message appears at the bottom of the POS receipt.</p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Unsaved Changes Modal -->
<div class="modal fade" id="unsavedChangesModal" tabindex="-1" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content text-center" style="border-radius: 20px; padding: 40px; padding-bottom: 30px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-body p-0">
                <div class="d-flex justify-content-center mb-4">
                    <div style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid #fbbc05; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 45px; font-weight: bold; color: #fbbc05; line-height: 1; margin-top: -5px;">!</span>
                    </div>
                </div>
                <h3 class="fw-bold mb-3" style="color: #333; font-size: 1.6rem;">Unsaved Changes</h3>
                <p class="text-secondary mb-4" style="font-size: 1.1rem; line-height: 1.5; padding: 0 10px;">
                    You have made changes to the store settings.<br>
                    Are you sure you want to leave without saving them?
                </p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="btn bg-white rounded-pill px-4" data-bs-dismiss="modal" style="font-weight: 500; font-size: 1.1rem; color: #333; border: 1px solid #ddd; padding-top: 10px; padding-bottom: 10px; width: 140px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">Stay</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="discardChangesBtn" style="font-weight: 500; font-size: 1.1rem; background-color: #df364c; border: none; padding-top: 10px; padding-bottom: 10px; width: 150px; box-shadow: 0 2px 5px rgba(223,54,76,0.2);">Leave Page</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Warning Modal -->
<div class="modal fade" id="paymentWarningModal" tabindex="-1" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content text-center" style="border-radius: 20px; padding: 40px; padding-bottom: 30px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-body p-0">
                <div class="d-flex justify-content-center mb-4">
                    <div style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid #fbbc05; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 45px; font-weight: bold; color: #fbbc05; line-height: 1; margin-top: -5px;">!</span>
                    </div>
                </div>
                <h3 class="fw-bold mb-3" style="color: #333; font-size: 1.6rem;">Action Required</h3>
                <p class="text-secondary mb-4" style="font-size: 1.1rem; line-height: 1.5; padding: 0 10px;">
                    At least one payment method (COD or E-Wallet) must be enabled to save the settings.
                </p>
                <div class="d-flex justify-content-center mt-4">
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal" style="font-weight: 500; font-size: 1.1rem; width: 140px; background-color: #333; border: none; padding-top: 10px; padding-bottom: 10px;">Got it</button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/Leilife_2nd/css/admin/settings.css">
<script src="/Leilife_2nd/scripts/admin/settings.js"></script>