document.addEventListener('DOMContentLoaded', function () {
    // --- Hover Effect for Products ---
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            cards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
        });
    });

    // --- Quantity & Add to Cart Logic ---
    cards.forEach(card => {
        // Quantity Buttons
        const minusBtn = card.querySelector('.btn-outline-secondary:first-child');
        const plusBtn = card.querySelector('.btn-outline-secondary:last-child');
        const quantitySpan = card.querySelector('span.mx-2');
        const addToCartBtn = card.querySelector('.btn-warning');

        let quantity = 1;

        if (minusBtn && plusBtn && quantitySpan) {
            minusBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (quantity > 1) {
                    quantity--;
                    quantitySpan.textContent = quantity;
                }
            });

            plusBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                quantity++;
                quantitySpan.textContent = quantity;
            });
        }

        // Add to Cart
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', (e) => {
                e.stopPropagation();

                const id = card.dataset.id;
                const name = card.dataset.name;
                const price = card.dataset.price;
                const image = card.dataset.image;

                if (!id) {
                    alert('Error: Product ID missing');
                    return;
                }

                const product = {
                    id: id,
                    name: name,
                    price: price,
                    qty: quantity,
                    image: image
                };

                // Call the global addToCart function from cart.js
                if (window.addToCart) {
                    window.addToCart(product);

                    // Show custom notification
                    const notification = document.getElementById('cart-notification');
                    const message = document.getElementById('cart-notification-message');
                    if (notification && message) {
                        message.innerHTML = `You've added <strong>${quantity}x ${name}</strong><br>to your cart.`;
                        notification.classList.add('show');

                        // Hide after 1.5 seconds
                        setTimeout(() => {
                            notification.classList.remove('show');
                        }, 1500);
                    } else {
                        // Fallback if modal missing
                        alert(`Added ${quantity}x ${name} to cart!`);
                    }

                    // Reset quantity
                    quantity = 1;
                    if (quantitySpan) quantitySpan.textContent = 1;
                } else {
                    console.error('addToCart function not found');
                    alert('Error: Cart functionality unavailable');
                }
            });
        }
    });

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        const sendBtn = contactForm.querySelector('button');
        if (sendBtn) {
            sendBtn.addEventListener('click', async function (e) {
                e.preventDefault();

                // Form validation handled by browser 'required' is not automatically triggerred by button type="button"
                // But we can check manually or change button type.
                if (!contactForm.checkValidity()) {
                    contactForm.reportValidity();
                    return;
                }

                const formData = new FormData(contactForm);
                const data = Object.fromEntries(formData.entries());

                try {
                    sendBtn.disabled = true;
                    sendBtn.innerText = 'Sending...';

                    const response = await fetch(`${window.BASE_URL}/backend/api/contact/send_message.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert('Thank you! Your message has been sent.');
                        contactForm.reset();
                    } else {
                        alert(result.message || 'Failed to send message.');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('An error occurred. Please try again later.');
                } finally {
                    sendBtn.disabled = false;
                    sendBtn.innerText = 'Send';
                }
            });
        }
    }
});
