document.addEventListener('DOMContentLoaded', () => {
    // --- Elements ---
    const pickupRadio = document.getElementById('pickup');
    const homeRadio = document.getElementById('homeDelivery');
    const pickupAddress = document.getElementById('pickupAddress');
    const homeInputs = document.getElementById('homeDeliveryInputs');
    const editBtnDel = document.getElementById('editBtn-del');
    const editContactBtn = document.getElementById('editContactBtn');
    const contactName = document.getElementById('contactName');
    const contactPhone = document.getElementById('contactPhone');

    // --- State ---
    let cart = JSON.parse(localStorage.getItem('leilife_cart')) || [];
    let deliveryChoice = localStorage.getItem('leilife_delivery_choice') || 'pickup';

    // --- Empty Cart Check ---
    if (cart.length === 0) {
        const container = document.getElementById('checkout-items-container');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <p class="mb-3">Your cart is empty. Redirecting to menu...</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = 'index.php?page=menu';
            }, 2000);
        }
        // Disable interaction with other elements if needed or just let the redirect happen
        return; // Stop further execution
    }

    // --- Delivery Logic ---
    function toggleDeliveryOptions(choice) {
        if (choice === 'pickup') {
            pickupRadio.checked = true;
            pickupAddress.classList.remove('d-none');
            homeInputs.classList.add('d-none');
            // Update Totals (Delivery Fee = 0)
            renderOrderSummary(0);
        } else {
            homeRadio.checked = true;
            pickupAddress.classList.add('d-none');
            homeInputs.classList.remove('d-none');
            // Update Totals (Delivery Fee = 50)
            renderOrderSummary(50);
        }
    }

    // Init
    toggleDeliveryOptions(deliveryChoice);

    // Listeners
    pickupRadio.addEventListener('change', () => toggleDeliveryOptions('pickup'));
    homeRadio.addEventListener('change', () => toggleDeliveryOptions('delivery'));


    // --- User Details Edit Logic ---
    if (editContactBtn) {
        editContactBtn.addEventListener('click', () => {
            if (contactName.hasAttribute('readonly')) {
                // Enable Editing
                contactName.removeAttribute('readonly');
                contactPhone.removeAttribute('readonly');
                contactName.focus();
                editContactBtn.textContent = 'Save';
                editContactBtn.classList.remove('btn-primary-custom');
                editContactBtn.classList.add('btn-success');
            } else {
                // Save (Disable Editing)
                // Here you might validation or API update call
                contactName.setAttribute('readonly', true);
                contactPhone.setAttribute('readonly', true);
                editContactBtn.textContent = 'Edit';
                editContactBtn.classList.remove('btn-success');
                editContactBtn.classList.add('btn-primary-custom');
            }
        });
    }

    // --- Render Order Summary ---
    function renderOrderSummary(deliveryFee) {
        const container = document.getElementById('checkout-items-container');
        const subtotalEl = document.getElementById('checkout-subtotal');
        const deliveryFeeEl = document.getElementById('checkout-delivery-fee');
        const totalEl = document.getElementById('checkout-total');

        if (!container) return;

        // Render Items
        container.innerHTML = '';
        let subtotal = 0;

        cart.forEach(item => {
            subtotal += item.price * item.qty;

            // Image Path Fix
            let imageSrc = item.image;
            if (!imageSrc.startsWith('http') && !imageSrc.startsWith('/')) {
                imageSrc = '/Leilife_2nd/public/assets/products/' + imageSrc;
            }

            const html = `
                <div class="order-item d-flex align-items-start gap-3 mt-3">
                    <img src="${imageSrc}" class="order-img" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                    <div class="flex-grow-1">
                        <p class="fw-bold mb-1">${item.name}</p>
                        <p class="text-muted mb-0">₱${parseFloat(item.price).toFixed(2)} × ${item.qty}</p>
                    </div>
                    <p class="fw-semibold mb-0 order-price">₱${(item.price * item.qty).toFixed(2)}</p>
                </div>
            `;
            container.innerHTML += html;
        });

        const total = subtotal + deliveryFee;

        // Update UI
        if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toFixed(2);
        if (deliveryFeeEl) deliveryFeeEl.textContent = '₱' + deliveryFee.toFixed(2);
        if (totalEl) totalEl.textContent = '₱' + total.toFixed(2);

        const countBadge = document.getElementById('order-summary-count');
        if (countBadge) {
            countBadge.textContent = cart.length + (cart.length === 1 ? ' Item' : ' Items');
            // If you want total units instead of unique items:
            // const totalUnits = cart.reduce((acc, item) => acc + item.qty, 0);
            // countBadge.textContent = totalUnits + (totalUnits === 1 ? ' Item' : ' Items');
        }
    }
});