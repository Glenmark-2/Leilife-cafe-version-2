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
    const openAddressModalBtn = document.getElementById('openAddressModalBtn');
    const deliveryAddressInput = document.getElementById('deliveryAddress');

    // --- State ---
    // --- State ---
    let cart = [];
    let deliveryChoice = localStorage.getItem('leilife_delivery_choice') || 'pickup';
    let tempAddressData = null; // Store address before confirmation

    // Async Init
    const initPage = async () => {
        if (window.isLoggedIn) {
            try {
                const res = await fetch('/Leilife_2nd/backend/api/cart_actions.php?action=get_cart');
                const data = await res.json();
                if (data.success) {
                    cart = data.cart || [];
                }
            } catch (e) {
                console.error("Failed to fetch cart", e);
            }
        } else {
            cart = JSON.parse(localStorage.getItem('leilife_cart')) || [];
        }

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

                setTimeout(() => {
                    window.location.href = 'index.php?page=menu';
                }, 2000);
            }
            return;
        }

        // Render Initial View
        toggleDeliveryOptions(deliveryChoice);
    };

    // --- Delivery Logic ---
    function toggleDeliveryOptions(choice) {
        if (choice === 'pickup') {
            pickupRadio.checked = true;
            pickupAddress.classList.remove('d-none');
            homeInputs.classList.add('d-none');
            renderOrderSummary(0);
        } else {
            homeRadio.checked = true;
            pickupAddress.classList.add('d-none');
            homeInputs.classList.remove('d-none');
            renderOrderSummary(50);
        }
    }

    // Start Init
    initPage();


    // Listeners
    pickupRadio.addEventListener('change', () => toggleDeliveryOptions('pickup'));
    homeRadio.addEventListener('change', () => toggleDeliveryOptions('delivery'));


    // --- Address Modal Integration ---
    // Use event delegation since the button might be hidden initially
    document.addEventListener('click', (e) => {
        if (e.target && e.target.id === 'openAddressModalBtn') {
            e.preventDefault();
            const addressModal = document.getElementById('addressModal');
            console.log('Address modal element:', addressModal);

            if (addressModal) {
                addressModal.style.display = 'flex';

                // Initialize the map if not already done
                if (typeof initializeAddressModal === 'function') {
                    initializeAddressModal();
                } else {
                    console.error('initializeAddressModal function not found');
                }
            } else {
                console.error('Address modal element not found');
            }
        }
    });

    // Handle address modal save
    const addressForm = document.getElementById('addressForm');
    if (addressForm) {
        addressForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(addressForm);
            const street = formData.get('street');
            const barangay = formData.get('barangay');
            const city = formData.get('city');
            const province = formData.get('province');
            const region = formData.get('region');
            const latitude = formData.get('latitude');
            const longitude = formData.get('longitude');

            // Store address data temporarily
            tempAddressData = {
                street,
                barangay,
                city,
                province,
                region,
                latitude,
                longitude,
                fullAddress: `${street}, ${barangay}, ${city}, ${province}`
            };

            // Update the delivery address field
            deliveryAddressInput.value = tempAddressData.fullAddress;
            document.getElementById('addressLatitude').value = latitude;
            document.getElementById('addressLongitude').value = longitude;

            // Close address modal
            const addressModal = document.getElementById('addressModal');
            if (addressModal) {
                addressModal.style.display = 'none';
            }

            // Show confirmation modal
            const confirmModal = new bootstrap.Modal(document.getElementById('saveAddressConfirmModal'));
            confirmModal.show();
        });
    }

    // Handle save to profile confirmation
    const confirmSaveBtn = document.getElementById('confirmSaveAddress');
    const skipSaveBtn = document.getElementById('skipSaveAddress');

    if (confirmSaveBtn) {
        confirmSaveBtn.addEventListener('click', async () => {
            if (tempAddressData) {
                try {
                    const response = await fetch('/Leilife_2nd/backend/api/update_address.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(tempAddressData)
                    });

                    const result = await response.json();
                    if (result.success) {
                        console.log('Address saved to profile successfully');
                    } else {
                        console.error('Failed to save address:', result.message);
                    }
                } catch (error) {
                    console.error('Error saving address:', error);
                }
            }

            // Close confirmation modal
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('saveAddressConfirmModal'));
            if (confirmModal) {
                confirmModal.hide();
            }
        });
    }

    if (skipSaveBtn) {
        skipSaveBtn.addEventListener('click', () => {
            // Just close the modal without saving
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('saveAddressConfirmModal'));
            if (confirmModal) {
                confirmModal.hide();
            }
        });
    }

    // --- User Details Edit Logic ---
    if (editContactBtn) {
        editContactBtn.addEventListener('click', () => {
            // Check current state based on readOnly property
            if (contactPhone.readOnly) {
                // Switch to EDIT mode
                contactPhone.readOnly = false;
                contactPhone.focus();
                editContactBtn.textContent = 'Save';
                editContactBtn.classList.remove('btn-primary-custom');
                editContactBtn.classList.add('btn-success');
            } else {
                // Switch to READONLY mode (Save validation)
                const phone = contactPhone.value.trim();

                if (!phone) {
                    alert('Contact number is required.');
                    contactPhone.focus();
                    return;
                }

                if (phone.length !== 11 || !phone.startsWith('09')) {
                    alert('Invalid contact number format. It must be an 11-digit number starting with 09.');
                    contactPhone.focus();
                    return;
                }

                contactPhone.readOnly = true;
                editContactBtn.textContent = 'Edit';
                editContactBtn.classList.remove('btn-success');
                editContactBtn.classList.add('btn-primary-custom');
            }
        });
    }

    // Phone Numeric Only Validation
    if (contactPhone) {
        contactPhone.addEventListener('input', (e) => {
            // Remove non-numeric characters
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            // Limit to 11 digits (Standard PH Mobile)
            if (e.target.value.length > 11) {
                e.target.value = e.target.value.slice(0, 11);
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

        container.innerHTML = '';
        let subtotal = 0;

        cart.forEach(item => {
            subtotal += item.price * item.qty;

            let imageSrc = item.image || 'not_available.png';
            if (typeof imageSrc === 'string' && !imageSrc.startsWith('http') && !imageSrc.startsWith('/')) {
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

        if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toFixed(2);
        if (deliveryFeeEl) deliveryFeeEl.textContent = '₱' + deliveryFee.toFixed(2);
        if (totalEl) totalEl.textContent = '₱' + total.toFixed(2);

        const countBadge = document.getElementById('order-summary-count');
        if (countBadge) {
            countBadge.textContent = cart.length + (cart.length === 1 ? ' Item' : ' Items');
        }
    }

    // --- Place Order Logic ---
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', async () => {
            placeOrderBtn.disabled = true;
            placeOrderBtn.textContent = 'Processing...';

            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            let deliveryMethod = document.querySelector('input[name="deliveryOption"]:checked').value;

            if (deliveryMethod === 'homeDelivery') deliveryMethod = 'delivery';

            let deliveryAddress = '';
            let deliveryNotes = '';
            let deliveryFee = 0;

            if (deliveryMethod === 'pickup') {
                deliveryAddress = 'Lunduyan Langaray Village, Barangay 14 Caloocan City';
                deliveryFee = 0;
            } else {
                deliveryAddress = document.getElementById('deliveryAddress').value;
                deliveryNotes = document.getElementById('deliveryNotes').value;
                deliveryFee = 50;

                if (!deliveryAddress.trim()) {
                    alert('Please select a delivery address.');
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.textContent = 'Place Order';
                    return;
                }
            }

            const phone = document.getElementById('contactPhone').value.trim();
            if (!phone) {
                alert('Contact number is required.');
                placeOrderBtn.disabled = false;
                placeOrderBtn.textContent = 'Place Order';
                document.getElementById('contactPhone').focus();
                return;
            }

            if (phone.length !== 11 || !phone.startsWith('09')) {
                alert('Invalid contact number format. Please enter an 11-digit number starting with 09 (e.g., 09123456789).');
                placeOrderBtn.disabled = false;
                placeOrderBtn.textContent = 'Place Order';
                document.getElementById('contactPhone').focus();
                return;
            }

            const items = cart.map(item => ({
                product_id: item.id,
                quantity: item.qty
            }));

            const orderData = {
                items: items,
                phone: phone,
                payment_method: paymentMethod,
                delivery_method: deliveryMethod,
                delivery_address: deliveryAddress,
                delivery_notes: deliveryNotes,
                delivery_fee: deliveryFee
            };

            try {
                const response = await fetch('/Leilife_2nd/backend/api/place_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                });

                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const result = await response.json();
                    if (response.ok && result.success) {
                        localStorage.removeItem('leilife_cart');
                        window.location.href = 'index.php?page=order_tracking&order_id=' + result.order_id;
                    } else {
                        alert('Failed to place order: ' + (result.message || 'Unknown error'));
                        placeOrderBtn.disabled = false;
                        placeOrderBtn.textContent = 'Place Order';
                    }
                } else {
                    window.location.href = 'index.php?page=home&login=true';
                }

            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while placing the order. Please check console.');
                placeOrderBtn.disabled = false;
                placeOrderBtn.textContent = 'Place Order';
            }
        });
    }
});
