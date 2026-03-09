const btn = document.querySelector('.btn.btn-primary-custom-cart');
const statusText = document.getElementById('dev-status');
const icon = document.getElementById('dev-icon');
const container = document.getElementById('cart-items-container');
const subtotalEl = document.getElementById('cart-subtotal');
const totalEl = document.getElementById('cart-total');
const deliveryFeeEl = document.getElementById('cart-delivery-fee');

let isPickup = localStorage.getItem('leilife_delivery_choice') !== 'delivery'; // default to true unless explicitly delivery
let cart = [];

// Initialize UI based on saved preference
if (statusText && icon) {
    if (isPickup) {
        statusText.textContent = "Pick up";
        icon.src = (window.BASE_URL) + "/public/assets/walk.png";
    } else {
        statusText.textContent = "Delivery";
        icon.src = (window.BASE_URL) + "/public/assets/motorbike.png";
    }
}

// --- Toggle Pickup/Delivery ---
if (btn) {
    btn.addEventListener('click', () => {
        isPickup = !isPickup;

        if (isPickup) {
            statusText.textContent = "Pick up";
            icon.src = (window.BASE_URL) + "/public/assets/walk.png";
        } else {
            statusText.textContent = "Delivery";
            icon.src = (window.BASE_URL) + "/public/assets/motorbike.png";
        }

        localStorage.setItem('leilife_delivery_choice', isPickup ? 'pickup' : 'delivery');
        renderCart(); // Re-render to update totals if delivery fee changes
    });
}

// --- API Helpers ---
const API_URL = (window.BASE_URL) + '/backend/api/cart_actions.php';

async function fetchCartAPI() {
    try {
        const response = await fetch(API_URL + '?action=get_cart');
        const data = await response.json();
        if (data.success) {
            cart = data.cart || [];
            renderCart();
        }
    } catch (error) {
        console.error('Error fetching cart:', error);
    }
}

async function addToCartAPI(productId, qty) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add_item', product_id: productId, qty: qty })
        });
        const data = await response.json();
        if (data.success) {
            cart = data.cart || [];
            renderCart();
        }
    } catch (error) {
        console.error('Error adding item:', error);
    }
}

async function updateQtyAPI(productId, qty) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_qty', product_id: productId, qty: qty })
        });
        const data = await response.json();
        if (data.success) {
            cart = data.cart || [];
            renderCart();
        }
    } catch (error) {
        console.error('Error updating qty:', error);
    }
}

async function removeItemAPI(productId) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove_item', product_id: productId })
        });
        const data = await response.json();
        if (data.success) {
            cart = data.cart || [];
            renderCart();
        }
    } catch (error) {
        console.error('Error removing item:', error);
    }
}

async function mergeCartAPI(localItems) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'merge_cart', items: localItems })
        });
        const data = await response.json();
        if (data.success) {
            cart = data.cart || [];
            localStorage.removeItem('leilife_cart'); // Clear after merge
            renderCart();
        }
    } catch (error) {
        console.error('Error merging cart:', error);
    }
}

// --- Cart Logic ---

function saveCartLocal() {
    localStorage.setItem('leilife_cart', JSON.stringify(cart));
    renderCart();
}

function renderCart() {
    if (!container) return;

    // Calculate Totals
    let subtotal = 0;
    cart.forEach(item => {
        if (item.is_available !== false) {
            subtotal += item.price * item.qty;
        }
    });

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
        // Disable on init if empty OR only unavailable items
        const hasAvailableItems = cart.some(item => item.is_available !== false);
        if (cart.length === 0 || !hasAvailableItems) {
            checkoutBtn.setAttribute('disabled', true);
            checkoutBtn.style.opacity = '0.6';
            checkoutBtn.style.cursor = 'not-allowed';
        } else {
            checkoutBtn.removeAttribute('disabled');
            checkoutBtn.style.opacity = '1';
            checkoutBtn.style.cursor = 'pointer';
        }

        checkoutBtn.onclick = () => {
            if (cart.length === 0 || !hasAvailableItems) return;

            if (window.isStoreOpen === false) {
                alert("Sorry, the store is currently closed. We are not accepting orders at the moment.");
                return;
            }

            // If logged in, cart is already in DB. If guest, cart is in localStorage.
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
        if (imageSrc && !imageSrc.startsWith('http') && !imageSrc.startsWith('/')) {
            imageSrc = (window.BASE_URL) + '/public/assets/products/' + imageSrc;
        }

        const isTrash = item.qty === 1;
        const minusIcon = isTrash ? '<i class="bi bi-trash"></i>' : '-';
        const minusAction = isTrash ? `removeCartItem(${index})` : `updateCartQty(${index}, -1)`;
        const minusClass = isTrash ? 'btn-outline-danger' : 'btn-outline-secondary';

        const capitalizedName = capitalizeFirstLetter(item.name);

        if (!el) {
            el = document.createElement('div');
            el.className = 'cart-item';
            if (item.is_available === false) el.classList.add('unavailable-item');
            el.innerHTML = getCartItemHTML(item, index, minusIcon, minusAction, minusClass, imageSrc);
            container.appendChild(el);
        } else {
            if (item.is_available === false) {
                el.classList.add('unavailable-item');
            } else {
                el.classList.remove('unavailable-item');
            }
            
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
                
                if (item.is_available === false) {
                    priceStrong.innerHTML = '<span class="badge bg-danger">Unavailable</span>';
                } else {
                    priceStrong.textContent = '₱' + (item.price * item.qty).toFixed(2);
                }

                if (nameSpan && nameSpan.textContent !== capitalizedName) {
                    nameSpan.textContent = capitalizedName;
                    nameSpan.title = capitalizedName;
                    nameSpan.style.textDecoration = 'none'; // Ensure no line-through
                }
            } else {
                el.innerHTML = getCartItemHTML(item, index, minusIcon, minusAction, minusClass, imageSrc);
            }
        }
    });
}

function getCartItemHTML(item, index, minusIcon, minusAction, minusClass, imageSrc) {
    const isUnavailable = item.is_available === false;
    const priceDisplay = isUnavailable 
        ? '<span class="badge bg-danger" style="font-size: 0.7rem;">Unavailable</span>' 
        : `₱${(item.price * item.qty).toFixed(2)}`;
    
    const capitalizedName = capitalizeFirstLetter(item.name);
    
    return `
        <div style="display:flex; align-items:center; gap:12px; flex-grow: 1; ${isUnavailable ? 'opacity: 0.7;' : ''}">
            <div style="display: flex; align-items: center; gap: 8px;">
                <button class="btn btn-sm ${minusClass} py-0 px-2 btn-minus" onclick="${minusAction}">${minusIcon}</button>
                <span class="qty-span">${item.qty}</span>
                <button class="btn btn-sm btn-outline-secondary py-0 px-2 btn-plus" onclick="updateCartQty(${index}, 1)" ${isUnavailable ? 'disabled' : ''}>+</button>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="text-truncate name-span" style="max-width: 140px;" title="${capitalizedName}">${capitalizedName}</span>
            </div>
        </div>
        <strong class="price-strong">${priceDisplay}</strong>
    `;
}

function capitalizeFirstLetter(string) {
    if (!string) return '';
    return string.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

window.removeCartItem = function (index) {
    if (window.isLoggedIn) {
        removeItemAPI(cart[index].id);
    } else {
        cart.splice(index, 1);
        saveCartLocal();
    }
}

window.updateCartQty = function (index, delta) {
    const newQty = cart[index].qty + delta;
    if (window.isLoggedIn) {
        updateQtyAPI(cart[index].id, newQty);
    } else {
        if (newQty <= 0) {
            removeCartItem(index);
        } else {
            cart[index].qty += delta;
            saveCartLocal();
        }
    }
}

window.addToCart = function (product) {
    const qtyToAdd = product.qty ? parseInt(product.qty) : 1;

    if (window.isLoggedIn) {
        addToCartAPI(product.id, qtyToAdd);
    } else {
        const existing = cart.find(item => item.id == product.id);
        if (existing) {
            existing.qty += qtyToAdd;
        } else {
            let imageToSave = product.image;
            if (imageToSave && imageToSave.includes('/')) {
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
        saveCartLocal();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Initial Load
    if (window.isLoggedIn) {
        // Check for local cart merge
        const localCart = JSON.parse(localStorage.getItem('leilife_cart')) || [];
        if (localCart.length > 0) {
            mergeCartAPI(localCart);
        } else {
            fetchCartAPI();
        }
    } else {
        cart = JSON.parse(localStorage.getItem('leilife_cart')) || [];
        if (cart.length > 0) {
            checkGuestCartStatus(cart);
        } else {
            renderCart();
        }
    }

    async function checkGuestCartStatus(items) {
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'check_guest_availability', items: items })
            });
            const data = await response.json();
            if (data.success) {
                cart = data.cart || [];
                renderCart();
            }
        } catch (error) {
            console.error('Error checking guest availability:', error);
            renderCart();
        }
    }

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