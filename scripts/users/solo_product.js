document.addEventListener('DOMContentLoaded', () => {
    // Favorite Button Toggle (Visual only for now)
    const favBtn = document.getElementById('favoriteBtn');
    if (favBtn) {
        favBtn.addEventListener('click', function () {
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (this.classList.contains('active')) {
                icon.classList.remove('bi-heart-fill'); // or keep fill but change color via CSS
                icon.style.color = 'white';
            } else {
                icon.style.color = ''; // reset
            }
        });
    }

    // Quantity Logic
    const qtyValueDisplay = document.querySelector('.qty-value');
    const btnMinus = document.querySelector('.btn-slctr:first-child'); // Assuming structure: - span +
    const btnPlus = document.querySelector('.btn-slctr:last-child');
    let currentQty = 1;

    function updateQtyUI() {
        if (qtyValueDisplay) qtyValueDisplay.textContent = currentQty;
        if (btnMinus) {
            if (currentQty <= 1) {
                btnMinus.setAttribute('disabled', true);
                // Optional: add visual disabled style if the framework doesn't handle it
                btnMinus.style.cursor = 'not-allowed';
                btnMinus.style.opacity = '0.5';
            } else {
                btnMinus.removeAttribute('disabled');
                btnMinus.style.cursor = 'pointer';
                btnMinus.style.opacity = '1';
            }
        }
    }

    // Initialize
    updateQtyUI();

    if (btnMinus) {
        btnMinus.addEventListener('click', () => {
            if (currentQty > 1) {
                currentQty--;
                updateQtyUI();
            }
        });
    }

    if (btnPlus) {
        btnPlus.addEventListener('click', () => {
            currentQty++;
            updateQtyUI();
        });
    }

    // Add to Cart Logic
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function () {
            const btn = this;

            // Extract image source from the visible image on the page
            const imgEl = document.querySelector('.product-img');
            const imgSrc = imgEl ? imgEl.src : '';

            const product = {
                id: btn.getAttribute('data-id'),
                name: btn.getAttribute('data-name'),
                price: btn.getAttribute('data-price'),
                image: imgSrc,
                qty: currentQty // Pass the selected quantity
            };

            // Add to global cart
            if (window.addToCart) {
                window.addToCart(product);
            } else {
                console.error("addToCart function not found. Ensure cart.js is loaded.");
                // Fallback: manually push if cart.js failed (redundancy)
                let cart = JSON.parse(localStorage.getItem('leilife_cart')) || [];

                // Sanitize image here too just in case
                let imageToSave = product.image;
                if (imageToSave.includes('/')) {
                    imageToSave = imageToSave.split('/').pop();
                }

                const existing = cart.find(item => item.id == product.id);
                if (existing) {
                    existing.qty += currentQty;
                } else {
                    cart.push({
                        id: product.id,
                        name: product.name,
                        price: parseFloat(product.price),
                        qty: currentQty,
                        image: imageToSave
                    });
                }
                localStorage.setItem('leilife_cart', JSON.stringify(cart));
            }

            // Set flag to open cart on next page load
            sessionStorage.setItem('trigger_cart_open', 'true');

            // Redirect to menu
            window.location.href = 'index.php?page=menu';
        });
    }
});