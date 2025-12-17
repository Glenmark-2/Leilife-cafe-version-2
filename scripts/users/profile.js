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

function showToast(message, type = "success", duration = 2500) {
    let toast = document.getElementById("toast-notif");
    if (!toast) {
        toast = document.createElement("div");
        toast.id = "toast-notif";
        toast.style.cssText = `
                position: fixed; bottom: 20px; right: 20px;
                padding: 12px 20px; border-radius: 8px;
                color: white; font-size: 14px; opacity: 0;
                transition: opacity 0.3s ease; z-index: 10000;
            `;
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    if (type === "success") toast.style.background = "#4caf50";
    else if (type === "error") toast.style.background = "#f44336";
    else if (type === "warning") toast.style.background = "#ff9800";
    toast.style.opacity = 1;
    setTimeout(() => toast.style.opacity = 0, duration);
}

// personal info edit
const editPerInfoBtn = document.getElementById("editPersonalInfoBtn");
const personalInfoForm = document.getElementById("personal-info-form");

editPerInfoBtn.addEventListener("click", async (e) => {
    e.preventDefault();
    const state = editPerInfoBtn.getAttribute("data-state") || "edit";
    const infos = personalInfoForm.querySelectorAll(".info");

    if (state === "edit") {
        editPerInfoBtn.textContent = "Save";
        editPerInfoBtn.style.backgroundColor = "#28a745";
        editPerInfoBtn.setAttribute("data-state", "save");

        infos.forEach(info => {
            const disp = info.querySelector(".display-value");
            const input = info.querySelector(".edit-input");
            if (disp && input) {
                disp.style.display = "none";
                input.style.display = "block";
            }
        });
        
    } else if (state === "save") {
        
        const firstName = personalInfoForm.querySelector('[name="first_name"]').value.trim();
        const lastName = personalInfoForm.querySelector('[name="last_name"]').value.trim();
        const phone = personalInfoForm.querySelector('[name="phone_number"]').value.trim();
        const phonePattern = /^09\d{9}$/;

        if (firstName === "" || lastName === "") {
            showToast("First name and last name cannot be empty.", "error");
            return; 
        }

        if( phone === "") {
            showToast("Phone number cannot be empty.", "error");
            return;
        }
        if (!phonePattern.test(phone)) {
            showToast("Invalid phone number.", "error");
            return;
        }

        const fd = new FormData(personalInfoForm);

        try {
            const resp = await fetch(personalInfoForm.action, {
                method: "POST",
                body: fd
            });
            const result = await resp.json();

            if (result.success) {
                showToast(result.message || "Profile updated!", "success");

                infos.forEach(info => {
                    const disp = info.querySelector(".display-value");
                    const input = info.querySelector(".edit-input");
                    if (disp && input) {
                        disp.textContent = input.value;
                        input.style.display = "none";
                        disp.style.display = "block";
                    }
                });

                editPerInfoBtn.textContent = "Edit";
                editPerInfoBtn.style.backgroundColor = "";
                editPerInfoBtn.setAttribute("data-state", "edit");
            } else {
                showToast(result.error || "Save failed", "error");
            }
        } catch (err) {
            showToast("Request error: " + err.message, "error");
        }
    }
});