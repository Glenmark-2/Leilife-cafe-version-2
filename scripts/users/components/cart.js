const btn = document.querySelector('.btn.btn-primary-custom-cart');
const statusText = document.getElementById('dev-status');
const icon = document.getElementById('dev-icon');
const container = document.getElementById('cart-items-container');
const subtotalEl = document.getElementById('cart-subtotal');
const totalEl = document.getElementById('cart-total');
const deliveryFeeEl = document.getElementById('cart-delivery-fee');

let isPickup = true; // default
// Cart Data
let cart = JSON.parse(localStorage.getItem('leilife_cart')) || [];

// --- Toggle Pickup/Delivery ---
if (btn) {
    btn.addEventListener('click', () => {
        if (isPickup) {
            // switch to delivery
            statusText.textContent = "Delivery";
            icon.src = "/Leilife_2nd/public/assets/motorbike.png";
        } else {
            // switch back to pickup
            statusText.textContent = "Pick up";
            icon.src = "/Leilife_2nd/public/assets/walk.png";
        }

        isPickup = !isPickup;
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
    if (badge) {
        badge.textContent = cart.length;
        badge.style.display = cart.length > 0 ? 'inline-block' : 'none';
        // Also update mobile badge if it exists
    }

    const deliveryFee = (!isPickup && cart.length > 0) ? 50 : 0;
    const total = subtotal + deliveryFee;

    // Update Totals UI
    if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toFixed(2);
    if (deliveryFeeEl) deliveryFeeEl.textContent = '₱' + deliveryFee.toFixed(2);
    if (totalEl) totalEl.textContent = '₱' + total.toFixed(2);

    // --- Smart Item Rendering ---
    // Reuse existing DOM elements to avoid "vanishing" / focus loss
    const existingItems = Array.from(container.children);

    // 1. Remove excess items
    while (existingItems.length > cart.length) {
        container.removeChild(container.lastChild);
        existingItems.pop();
    }

    // 2. Update or Create items
    cart.forEach((item, index) => {
        let el = existingItems[index];

        // Prepare Logic
        let imageSrc = item.image;
        if (!imageSrc.startsWith('http') && !imageSrc.startsWith('/')) {
            imageSrc = '/Leilife_2nd/public/assets/products/' + imageSrc;
        }

        // Minus vs Trash Button
        const isTrash = item.qty === 1;
        const minusIcon = isTrash ? '<i class="bi bi-trash"></i>' : '-';
        const minusAction = isTrash ? `removeCartItem(${index})` : `updateCartQty(${index}, -1)`;
        const minusClass = isTrash ? 'btn-outline-danger' : 'btn-outline-secondary';

        if (!el) {
            // Create New
            el = document.createElement('div');
            el.className = 'cart-item';
            el.innerHTML = getCartItemHTML(item, index, minusIcon, minusAction, minusClass);
            container.appendChild(el);
        } else {
            // Update Existing (minimize reflows)
            const minusBtn = el.querySelector('.btn-minus');
            const plusBtn = el.querySelector('.btn-plus');
            const qtySpan = el.querySelector('.qty-span');
            const priceStrong = el.querySelector('.price-strong');
            const nameSpan = el.querySelector('.name-span');

            // If structure is solid, update attributes. Otherwise fallback to full replace.
            if (minusBtn && plusBtn && qtySpan && priceStrong) {
                // Update Minus Button
                if (minusBtn.getAttribute('onclick') !== minusAction) {
                    minusBtn.setAttribute('onclick', minusAction);
                    minusBtn.innerHTML = minusIcon;
                    minusBtn.className = `btn btn-sm ${minusClass} py-0 px-2 btn-minus`;
                } else {
                    // Ensure index is updated even if action didn't change (e.g. deletion shifted index but action is same type)
                    // But here action includes index, so it would have matched logic above.
                }

                // Update Plus Button Index
                plusBtn.setAttribute('onclick', `updateCartQty(${index}, 1)`);

                // Update Qty
                if (qtySpan.textContent != item.qty) qtySpan.textContent = item.qty;

                // Update Price
                priceStrong.textContent = '₱' + (item.price * item.qty).toFixed(2);

                // Optional: Update name if somehow position swapped with different product
                if (nameSpan && nameSpan.textContent !== item.name) {
                    nameSpan.textContent = item.name;
                    nameSpan.title = item.name;
                }
            } else {
                // Structure mismatch, replace content
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

// Global functions for inline onclicks
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
    // Check if exists
    const existing = cart.find(item => item.id == product.id);
    if (existing) {
        existing.qty += 1;
    } else {
        // Sanitize Image Path: Store only filename if possible, or full path.
        // The user said "make the product image to always include this... so i only need to save the image name"
        // So we try to extract filename
        let imageToSave = product.image;
        if (imageToSave.includes('/')) {
            imageToSave = imageToSave.split('/').pop();
        }

        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            qty: 1,
            image: imageToSave
        });
    }
    saveCart();
}

// --- Init ---
document.addEventListener('DOMContentLoaded', () => {
    renderCart();

    // Check for auto-open flag from redirect
    const shouldOpen = sessionStorage.getItem('trigger_cart_open');
    if (shouldOpen === 'true') {
        sessionStorage.removeItem('trigger_cart_open');
        // Trigger the toggle function in header.js if available or manually toggle
        const cartModal = document.getElementById("cart-container");
        if (cartModal && cartModal.classList.contains('d-none')) {
            cartModal.classList.remove("d-none");
            cartModal.classList.add("d-flex");
            document.body.classList.add("cart-open");
        }
    }
});