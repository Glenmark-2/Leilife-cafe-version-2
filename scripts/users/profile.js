    const buttons = document.querySelectorAll(".sideTabBtns");
    const sections = {
        "Personal Info": "personal_info",
        "Address": "address",
        "Favorites": "favorites",
        "Order History": "order_history",
        "Settings": "settings"
    };

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            // Hide all sections
            Object.values(sections).forEach(secId => {
                document.getElementById(secId).style.display = "none";
            });

            // Show the section corresponding to clicked button
            const sectionId = sections[btn.textContent.trim()];
            document.getElementById(sectionId).style.display = "flex";
        });
    });

    // Optional: activate the first tab on page load
    buttons[0].click();