const btn = document.querySelector('.btn.btn-primary-custom-cart');
const statusText = document.getElementById('dev-status');
const icon = document.getElementById('dev-icon');

let isPickup = true; // default

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
});