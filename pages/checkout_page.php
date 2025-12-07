<div class="container custom-width mt-4">
    <div class="row justify-content-center gy-4">
        <!-- Left Column -->
        <div class="col-12 col-md-8 col-lg-6">
            <!-- Contact Details -->
            <div class="container p-3 rounded-4 text-start p-4 bg-white">
                <h5>Contact Details</h5>
                <hr class="hr">
                <div class="mb-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <p class="small m-0">Fullname</p>
                            <input type="text" class="form-control" placeholder="Username">
                        </div>
                        <div class="col-12 col-md-6">
                            <p class="small m-0">Phone Number</p>
                            <input type="text" class="form-control" placeholder="Phone Number">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary-custom edit-btn-size">Edit</button>
                </div>
            </div>

            <!-- Delivery Options -->
            <div class="container bg-white p-3 rounded-4 text-start mt-4 p-4">
                <h5>Delivery Options</h5>
                <hr class="hr">

                <!-- Pickup -->
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="deliveryOption" id="pickup" value="pickup" checked>
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
                        <input type="text" class="form-control">
                    </div>
                    <div class="col">
                        <p class="small m-0">Notes to rider</p>
                        <input type="text" class="form-control">
                    </div>
                </div>

                <div id="editBtn-del" class="d-flex justify-content-end mt-3 d-none">
                    <button type="button" class="btn btn-primary-custom edit-btn-size">Edit</button>
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
                <h5 class="fw-bold text-center">Order Summary</h5>
                <hr>

                <div class="items">
                    <div class="order-item d-flex align-items-start gap-3 mt-3">
                        <img src="/Leilife_2nd/public/assets/cheesy_bacon_&_egg.jpeg" class="order-img">

                        <div class="flex-grow-1">
                            <p class="fw-bold mb-1">Cheesy Bacon & Egg</p>
                            <p class="text-muted mb-0">₱120.00 × 1</p>
                        </div>

                        <p class="fw-semibold mb-0 order-price">₱120.00</p>
                    </div>
                </div>

                <div class="bottom-part">
                    <hr class="mt-4">

                    <div class="d-flex justify-content-between mb-2">
                        <p class="mb-1">Subtotal</p>
                        <p class="mb-1">₱120.00</p>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <p class="mb-1">Delivery Fee</p>
                        <p class="mb-1">₱0.00</p>
                    </div>

                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <p>Total</p>
                        <p>₱120.00</p>
                    </div>

                    <button class="btn btn-primary-custom w-100 mt-4">Place Order</button>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="/Leilife_2nd/scripts/users/checkout_page.js"></script>