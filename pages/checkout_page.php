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
                if (SessionManager::isLoggedIn()) {
                    $userId = SessionManager::get('user_id');
                    $db = (new Database())->getConnection();
                    $userRepo = new UserRepository($db);
                    // We need a findById, or re-use findByEmail if needed, but findById is cleaner.
                    // Assuming findById isn't there, we'll implement a quick fetch or just populate from session if enough data exists.
                    // Actually, SessionManager has user_name and user_email. Phone might be missing.
                    // Let's rely on Session or fetch if possible.
                    // For now, let's just use what's in Session or placeholders.
                    // Better: fetch fresh data.
                    $query = "SELECT * FROM users WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute([':id' => $userId]);
                    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
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
                            <input type="text" class="form-control" id="contactPhone" placeholder="Phone Number" 
                                value="<?php echo $userData ? htmlspecialchars($userData['phone_number'] ?? '') : ''; ?>" readonly>
                        </div>
                    </div>
                </div>
                <!-- Hidden inputs for validation / submission -->
                <input type="hidden" id="contactEmail" value="<?php echo $userData ? htmlspecialchars($userData['email']) : ''; ?>">

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary-custom edit-btn-size" id="editContactBtn">Edit</button>
                </div>
            </div>

            <!-- Delivery Options -->
            <div class="container bg-white p-3 rounded-4 text-start mt-4 p-4">
                <h5>Delivery Options</h5>
                <hr class="hr">

                <!-- Pickup -->
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="deliveryOption" id="pickup" value="pickup">
                    <label class="form-check-label" for="pickup">Pick up</label>
                </div>

                <!-- Pickup Sub Option -->
                <div class="form-check ms-4" id="pickupAddress">
                    <input class="form-check-input" type="radio" name="pickupLocation" id="pickupLocation1" value="lunduyan" checked>
                    <label class="form-check-label" for="pickupLocation1">
                        Lunduyan Langaray Village, Barangay 14 Caloocan City
                    </label>
                </div>

                <!-- Home Delivery -->
                <div class="form-check mb-3 mt-2">
                    <input class="form-check-input" type="radio" name="deliveryOption" id="homeDelivery" value="homeDelivery">
                    <label class="form-check-label" for="homeDelivery">Home Delivery</label>
                </div>

                <!-- Home Delivery Inputs -->
                <div id="homeDeliveryInputs" class="mt-2 d-none">
                    <div class="col mb-2">
                        <p class="small m-0">Full Address</p>
                        <input type="text" class="form-control" id="deliveryAddress" placeholder="Street, Barangay, City, etc.">
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

                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="cod" value="cod" checked>
                    <label class="form-check-label" for="cod">Cash on Delivery</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="gcash" value="gcash">
                    <label class="form-check-label" for="gcash">E-Wallet (Gcash)</label>
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

                    <button class="btn btn-primary-custom w-100 mt-4">Place Order</button>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="/Leilife_2nd/scripts/users/checkout_page.js"></script>