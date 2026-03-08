<div class="container custom-width mt-4">
    <div class="row justify-content-center gy-4">
        <!-- Left Column -->
        <div class="col-12 col-md-8 col-lg-6">
            <!-- Contact Details -->
            <div class="container p-3 rounded-4 text-start p-4 bg-white">
                <h5>Contact Details</h5>
                <hr class="hr">
                <?php
                require_once __DIR__ . '/../backend/helpers/SessionManager.php';
                require_once __DIR__ . '/../backend/repositories/UserRepository.php';
                require_once __DIR__ . '/../backend/config/Database.php';

                $userData = null;
                $userAddress = null;
                if (SessionManager::isLoggedIn()) {
                    $userId = SessionManager::get('user_id');
                    $db = (new Database())->getConnection();
                    $userRepo = new UserRepository($db);

                    // Fetch user data
                    $query = "SELECT * FROM users WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute([':id' => $userId]);
                    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Fetch user address if exists
                    $addressQuery = "SELECT * FROM user_addresses WHERE user_id = :user_id";
                    $addressStmt = $db->prepare($addressQuery);
                    $addressStmt->execute([':user_id' => $userId]);
                    $userAddress = $addressStmt->fetch(PDO::FETCH_ASSOC);
                }
                ?>
                <div class="mb-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <p class="small m-0">Fullname</p>
                            <input type="text" class="form-control" id="contactName" placeholder="Full Name"
                                value="<?php echo $userData ? htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) : ''; ?>" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <p class="small m-0">Phone Number</p>
                            <input type="text" class="form-control" id="contactPhone" placeholder="Ex. 09123456789"
                                value="<?php echo $userData ? htmlspecialchars($userData['phone_number'] ?? '') : ''; ?>"
                                <?php echo ($userData && !empty($userData['phone_number'])) ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                </div>
                <!-- Hidden inputs for validation / submission -->
                <input type="hidden" id="contactEmail" value="<?php echo $userData ? htmlspecialchars($userData['email']) : ''; ?>">

                <?php
                $isPhoneReadonly = ($userData && !empty($userData['phone_number']));
                $btnText = $isPhoneReadonly ? 'Edit' : 'Save';
                $btnClass = $isPhoneReadonly ? 'btn-primary-custom' : 'btn-success';
                ?>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn <?php echo $btnClass; ?> edit-btn-size" id="editContactBtn"><?php echo $btnText; ?></button>
                </div>
            </div>

            <!-- Delivery Options -->
            <div class="container bg-white p-3 rounded-4 text-start mt-4 p-4">
                <h5>Delivery Options</h5>
                <hr class="hr">

                <?php
                $enable_pickup = isset($settings_header['enable_pickup']) ? (int)$settings_header['enable_pickup'] : 1;
                $enable_home_delivery = isset($settings_header['enable_home_delivery']) ? (int)$settings_header['enable_home_delivery'] : 1;
                ?>

                <!-- Pickup -->
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="deliveryOption" id="pickup" value="pickup"
                        <?php echo $enable_pickup ? 'checked' : 'disabled'; ?>>
                    <label class="form-check-label <?php echo !$enable_pickup ? 'text-muted' : ''; ?>" for="pickup">
                        Pick up <?php echo !$enable_pickup ? '(Unavailable)' : ''; ?>
                    </label>
                </div>

                <!-- Pickup Sub Option -->
                <div class="form-check ms-4" id="pickupAddress" style="<?php echo !$enable_pickup ? 'display: none;' : ''; ?>">
                    <input class="form-check-input" type="radio" name="pickupLocation" id="pickupLocation1" value="lunduyan" checked>
                    <label class="form-check-label" for="pickupLocation1">
                        Lunduyan Langaray Village, Barangay 14 Caloocan City
                    </label>
                </div>

                <!-- Home Delivery -->
                <div class="form-check mb-3 mt-2">
                    <input class="form-check-input" type="radio" name="deliveryOption" id="homeDelivery" value="homeDelivery"
                        <?php echo !$enable_pickup && $enable_home_delivery ? 'checked' : (!$enable_home_delivery ? 'disabled' : ''); ?>>
                    <label class="form-check-label <?php echo !$enable_home_delivery ? 'text-muted' : ''; ?>" for="homeDelivery">
                        Home Delivery <?php echo !$enable_home_delivery ? '(Unavailable)' : ''; ?>
                    </label>
                </div>

                <!-- Home Delivery Inputs -->
                <div id="homeDeliveryInputs" class="mt-2 d-none">
                    <div class="col mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <p class="small m-0">Full Address</p>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="openAddressModalBtn">
                                <i class="bi bi-geo-alt"></i> Select Address
                            </button>
                        </div>
                        <?php
                        // Build full address if user has saved address
                        $savedAddress = '';
                        $savedLat = '';
                        $savedLng = '';
                        if ($userAddress) {
                            $addressParts = array_filter([
                                $userAddress['street'] ?? '',
                                $userAddress['barangay'] ?? '',
                                $userAddress['city'] ?? '',
                                $userAddress['province'] ?? ''
                            ]);
                            $savedAddress = implode(', ', $addressParts);
                            $savedLat = $userAddress['latitude'] ?? '';
                            $savedLng = $userAddress['longitude'] ?? '';
                        }
                        ?>
                        <input type="text" class="form-control" id="deliveryAddress"
                            placeholder="Street, Barangay, City, etc."
                            value="<?php echo htmlspecialchars($savedAddress); ?>" readonly>
                        <input type="hidden" id="addressLatitude" value="<?php echo htmlspecialchars($savedLat); ?>">
                        <input type="hidden" id="addressLongitude" value="<?php echo htmlspecialchars($savedLng); ?>">
                    </div>
                    <div class="col">
                        <p class="small m-0">Notes to rider</p>
                        <input type="text" class="form-control" id="deliveryNotes" placeholder="Landmark, instructions, etc.">
                    </div>
                </div>

                <div id="editBtn-del" class="d-flex justify-content-end mt-3 d-none">
                    <!-- This edit button seems redundant if fields are always editable when delivery is selected? 
                         Or maybe user wants them locked too. Let's assume unlocked for now as address varies. -->
                </div>
            </div>

            <!-- Payment Method -->
            <div class="container p-3 rounded-4 text-start p-4 mt-4 bg-white">
                <h5>Payment Method</h5>
                <hr class="hr">

                <?php
                $enable_cod = isset($settings_header['enable_cod']) ? (int)$settings_header['enable_cod'] : 1;
                $enable_gcash = isset($settings_header['enable_gcash']) ? (int)$settings_header['enable_gcash'] : 1;
                ?>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="cod" value="cod"
                        <?php echo $enable_cod ? 'checked' : 'disabled'; ?>>
                    <label class="form-check-label <?php echo !$enable_cod ? 'text-muted' : ''; ?>" for="cod">
                        Cash on Delivery <?php echo !$enable_cod ? '(Unavailable)' : ''; ?>
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="gcash" value="gcash"
                        <?php echo !$enable_cod && $enable_gcash ? 'checked' : (!$enable_gcash ? 'disabled' : ''); ?>>
                    <label class="form-check-label <?php echo !$enable_gcash ? 'text-muted' : ''; ?>" for="gcash">
                        E-Wallet (Gcash) <?php echo !$enable_gcash ? '(Unavailable)' : ''; ?>
                    </label>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-12 col-md-8 col-lg-4">
            <div class="order-card p-4 rounded-4 w-100 mx-auto">
                <div class="d-flex justify-content-center align-items-center mb-2 position-relative">
                    <h5 class="fw-bold m-0 text-center">Order Summary</h5>
                    <span class="badge bg-secondary rounded-pill position-absolute end-0" id="order-summary-count" style="font-size: 0.8rem;">0 Items</span>
                </div>
                <hr>

                <div class="items" id="checkout-items-container">
                    <!-- JS will inject items here -->
                </div>

                <div class="bottom-part">
                    <hr class="mt-4">

                    <div class="d-flex justify-content-between mb-2">
                        <p class="mb-1">Subtotal</p>
                        <p class="mb-1" id="checkout-subtotal">₱0.00</p>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <p class="mb-1">Delivery Fee</p>
                        <p class="mb-1" id="checkout-delivery-fee">₱0.00</p>
                    </div>

                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <p>Total</p>
                        <p id="checkout-total">₱0.00</p>
                    </div>

                    <!-- <div class="d-flex justify-content-between fw-bold fs-5">
                        <p>Total</p>
                        <p>₱120.00</p>
                    </div> -->

                    <button class="btn btn-primary-custom w-100 mt-4" id="placeOrderBtn">Place Order</button>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../components/edit_address_modal.php'; ?>

<!-- Save Address Confirmation Modal -->
<div class="modal fade" id="saveAddressConfirmModal" tabindex="-1" aria-labelledby="saveAddressConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveAddressConfirmLabel">Save Address to Profile?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Would you like to save this address to your profile for future orders?</p>
            </div>
            <div class="modal-footer d-flex gap-2">
                <button type="button" class="btn btn-outline-dark flex-fill" id="skipSaveAddress">No, just for this order</button>
                <button type="button" class="btn btn-primary-custom flex-fill" id="confirmSaveAddress">Yes, save to profile</button>
            </div>
        </div>
    </div>
    <!-- Delivery Warning Modal -->
    <div class="modal fade" id="deliveryWarningModal" tabindex="-1" aria-hidden="true" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content text-center" style="border-radius: 20px; padding: 40px; padding-bottom: 30px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-body p-0">
                    <div class="d-flex justify-content-center mb-4">
                        <div style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid #fbbc05; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 45px; font-weight: bold; color: #fbbc05; line-height: 1; margin-top: -5px;">!</span>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-3" style="color: #333; font-size: 1.6rem;">Delivery Required</h3>
                    <p class="text-secondary mb-4" style="font-size: 1.1rem; line-height: 1.5; padding: 0 10px;">
                        No delivery method is selected or currently available. Please select one to place your order.
                    </p>
                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal" style="font-weight: 500; font-size: 1.1rem; width: 140px; background-color: #333; border: none; padding-top: 10px; padding-bottom: 10px;">Got it</button>
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
                    <h3 class="fw-bold mb-3" style="color: #333; font-size: 1.6rem;">Payment Required</h3>
                    <p class="text-secondary mb-4" style="font-size: 1.1rem; line-height: 1.5; padding: 0 10px;">
                        No payment method is selected or currently available. Please select one to place your order.
                    </p>
                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal" style="font-weight: 500; font-size: 1.1rem; width: 140px; background-color: #333; border: none; padding-top: 10px; padding-bottom: 10px;">Got it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo UrlHelper::getBaseUrl(); ?>/scripts/users/checkout_page.js?v=<?php echo time(); ?>"></script>