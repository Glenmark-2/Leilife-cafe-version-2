const favBtn = document.getElementById("favoriteBtn");
const qtyContainer = document.querySelector('.quantity');
const qtyValue = qtyContainer.querySelector('.qty-value');
const buttons = qtyContainer.querySelectorAll('.btn-slctr');

favBtn.addEventListener("click", () => {
    favBtn.classList.toggle("active");
});

buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        let current = parseInt(qtyValue.textContent);

        if (btn.textContent.trim() === '+') {
            current++;
        } else if (btn.textContent.trim() === '-' && current > 1) {
            current--;
        }

        qtyValue.textContent = current;
    });
});