const btn = document.querySelector('.btn.btn-primary-custom-cart');
const statusText = document.getElementById('dev-status');
const icon = document.getElementById('dev-icon');
const container = document.getElementById('cart-items-container');
const subtotalEl = document.getElementById('cart-subtotal');
const totalEl = document.getElementById('cart-total');
const deliveryFeeEl = document.getElementById('cart-delivery-fee');

let isPickup = localStorage.getItem('leilife_delivery_choice') !== 'delivery'; // default to true unless explicitly delivery

// Initialize UI based on saved preference
if (statusText && icon) {
    if (isPickup) {
        statusText.textContent = "Pick up";
        icon.src = "/Leilife_2nd/public/assets/walk.png";
    } else {
        statusText.textContent = "Delivery";
        icon.src = "/Leilife_2nd/public/assets/motorbike.png";
    }
}

// Cart Data
let cart = JSON.parse(localStorage.getItem('leilife_cart')) || [];

// --- Toggle Pickup/Delivery ---
if (btn) {
    btn.addEventListener('click', () => {
        isPickup = !isPickup;

        if (isPickup) {
            statusText.textContent = "Pick up";
            icon.src = "/Leilife_2nd/public/assets/walk.png";
        } else {
            statusText.textContent = "Delivery";
            icon.src = "/Leilife_2nd/public/assets/motorbike.png";
        }

        localStorage.setItem('leilife_delivery_choice', isPickup ? 'pickup' : 'delivery');
        renderCart(); // Re-render to update totals if delivery fee changes
    });
}

// --- Cart Logic ---

function saveCart() {
    localStorage.setItem('leilife_cart', JSON.stringify(cart));
    renderCart();
}

function renderCart() {
    if (!container) return;

    // Calculate Totals
    let subtotal = 0;
    cart.forEach(item => subtotal += item.price * item.qty);

    // Update Badge (Count unique items, not total quantity)
    const badge = document.getElementById('cart-badge');
    const stickyCart = document.getElementById('mobile-sticky-cart');
    const stickyCount = document.getElementById('mobile-sticky-count');

    if (badge) {
        badge.textContent = cart.length;
        badge.style.display = cart.length > 0 ? 'inline-block' : 'none';
    }

    // Sticky Mobile Cart Visibility
    if (stickyCart && stickyCount) {
        stickyCount.textContent = cart.length;
        if (cart.length > 0) {
            stickyCart.classList.remove('d-none');
        } else {
            stickyCart.classList.add('d-none');
        }
    }

    const deliveryFee = (!isPickup && cart.length > 0) ? 50 : 0;
    const total = subtotal + deliveryFee;

    // Update Totals UI
    if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toFixed(2);
    if (deliveryFeeEl) deliveryFeeEl.textContent = '₱' + deliveryFee.toFixed(2);
    if (totalEl) totalEl.textContent = '₱' + total.toFixed(2);

    // Check out button
    const checkoutBtn = document.querySelector('.totals-box .btn-primary-custom.justify-content-center');
    if (checkoutBtn) {
        // Disable on init if empty
        if (cart.length === 0) {
            checkoutBtn.setAttribute('disabled', true);
            checkoutBtn.style.opacity = '0.6';
            checkoutBtn.style.cursor = 'not-allowed';
        } else {
            checkoutBtn.removeAttribute('disabled');
            checkoutBtn.style.opacity = '1';
            checkoutBtn.style.cursor = 'pointer';
        }

        // Use a named function or check to avoid multiple listeners
        checkoutBtn.onclick = () => {
            if (cart.length === 0) return;
            // Save state to localStorage for checkout page to pick up
            localStorage.setItem('leilife_delivery_choice', isPickup ? 'pickup' : 'delivery');

            // Redirect
            window.location.href = 'index.php?page=checkout';
        };
    }

    // --- Smart Item Rendering ---
    const existingItems = Array.from(container.children);

    while (existingItems.length > cart.length) {
        container.removeChild(container.lastChild);
        existingItems.pop();
    }

    cart.forEach((item, index) => {
        let el = existingItems[index];

        let imageSrc = item.image;
        if (!imageSrc.startsWith('http') && !imageSrc.startsWith('/')) {
            imageSrc = '/Leilife_2nd/public/assets/products/' + imageSrc;
        }

        const isTrash = item.qty === 1;
        const minusIcon = isTrash ? '<i class="bi bi-trash"></i>' : '-';
        const minusAction = isTrash ? `removeCartItem(${index})` : `updateCartQty(${index}, -1)`;
        const minusClass = isTrash ? 'btn-outline-danger' : 'btn-outline-secondary';

        if (!el) {
            el = document.createElement('div');
            el.className = 'cart-item';
            el.style.width = '100%';
            el.style.display = 'flex';
            el.style.justifyContent = 'space-between';
            el.style.alignItems = 'center';
            el.style.padding = '5px 0';
            el.innerHTML = getCartItemHTML(item, index, minusIcon, minusAction, minusClass);
            container.appendChild(el);
        } else {
            const minusBtn = el.querySelector('.btn-minus');
            const plusBtn = el.querySelector('.btn-plus');
            const qtySpan = el.querySelector('.qty-span');
            const priceStrong = el.querySelector('.price-strong');
            const nameSpan = el.querySelector('.name-span');

            if (minusBtn && plusBtn && qtySpan && priceStrong) {
                if (minusBtn.getAttribute('onclick') !== minusAction) {
                    minusBtn.setAttribute('onclick', minusAction);
                    minusBtn.innerHTML = minusIcon;
                    minusBtn.className = `btn btn-sm ${minusClass} py-0 px-2 btn-minus`;
                }
                plusBtn.setAttribute('onclick', `updateCartQty(${index}, 1)`);
                if (qtySpan.textContent != item.qty) qtySpan.textContent = item.qty;
                priceStrong.textContent = '₱' + (item.price * item.qty).toFixed(2);
                if (nameSpan && nameSpan.textContent !== item.name) {
                    nameSpan.textContent = item.name;
                    nameSpan.title = item.name;
                }
            } else {
                el.innerHTML = getCartItemHTML(item, index, minusIcon, minusAction, minusClass);
            }
        }
    });
}

function getCartItemHTML(item, index, minusIcon, minusAction, minusClass) {
    return `
        <div style="display:flex; align-items:center; gap:12px; flex-grow: 1;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <button class="btn btn-sm ${minusClass} py-0 px-2 btn-minus" onclick="${minusAction}">${minusIcon}</button>
                <span class="qty-span">${item.qty}</span>
                <button class="btn btn-sm btn-outline-secondary py-0 px-2 btn-plus" onclick="updateCartQty(${index}, 1)">+</button>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="text-truncate name-span" style="max-width: 120px;" title="${item.name}">${item.name}</span>
            </div>
        </div>
        <strong class="price-strong">₱${(item.price * item.qty).toFixed(2)}</strong>
    `;
}

window.removeCartItem = function (index) {
    cart.splice(index, 1);
    saveCart();
}

window.updateCartQty = function (index, delta) {
    if (cart[index].qty + delta <= 0) {
        removeCartItem(index);
    } else {
        cart[index].qty += delta;
        saveCart();
    }
}

window.addToCart = function (product) {
    const qtyToAdd = product.qty ? parseInt(product.qty) : 1;
    const existing = cart.find(item => item.id == product.id);
    if (existing) {
        existing.qty += qtyToAdd;
    } else {
        let imageToSave = product.image;
        if (imageToSave.includes('/')) {
            imageToSave = imageToSave.split('/').pop();
        }

        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            qty: qtyToAdd,
            image: imageToSave
        });
    }
    saveCart();
}

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    const shouldOpen = sessionStorage.getItem('trigger_cart_open');
    if (shouldOpen === 'true') {
        sessionStorage.removeItem('trigger_cart_open');
        const cartModal = document.getElementById("cart-container");
        if (cartModal && cartModal.classList.contains('d-none')) {
            cartModal.classList.remove("d-none");
            cartModal.classList.add("d-flex");
            document.body.classList.add("cart-open");
        }
    }
});