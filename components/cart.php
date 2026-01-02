<div class="cart-modal mobile-cart d-flex flex-column align-items-center">
    <div class="pickup-box">
        <img src="/Leilife_2nd/public/assets/walk.png" width="24" id="dev-icon" />
        <span style="flex-grow:1; font-size:16px;" id="dev-status">Pick up</span>
        <button class="btn btn-primary-custom-cart">Change</button>
    </div>

    <p class="mt-3 font-size fw-bold fs-6">My Cart</p>

    <div id="cart-items-container" style="width: 100%; display: flex; flex-direction: column; align-items: center; gap: 10px;">
        <!-- Items will be injected here via JS -->
    </div>

    <div class="totals-box">
        <div class="d-flex justify-content-between">
            <span>Subtotal</span>
            <span id="cart-subtotal">₱0.00</span>
        </div>

        <div class="d-flex justify-content-between mt-2">
            <span>Delivery fee</span>
            <span id="cart-delivery-fee">₱0.00</span>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <strong>Total</strong>
            <strong id="cart-total">₱0.00</strong>
        </div>

        <button class="btn btn-primary-custom justify-content-center">Check out</button>
    </div>

</div>


