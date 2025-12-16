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
                image: imgSrc // Pass full src, cart.js will strip it if needed
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
                    existing.qty += 1;
                } else {
                    cart.push({
                        id: product.id,
                        name: product.name,
                        price: parseFloat(product.price),
                        qty: 1,
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

    // Quantity Selectors (Visual only for now on solo page, Logic in Cart)
    // If you want these buttons to actually change the quantity *before* adding to cart,
    // you need to capturing that value. For now, assuming default 1.
});