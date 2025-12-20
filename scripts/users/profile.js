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
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = "flex";
            // Trigger a resize event if showing address tab, though modal handles map size
        }
    });
});

// Optional: activate the first tab on page load
if (buttons.length > 0) buttons[0].click();

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

if (editPerInfoBtn && personalInfoForm) {
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

            if (phone === "") {
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
}

// address edit
const addressModal = document.getElementById("addressModal");
const editAddressBtn = document.getElementById("editAddressBtn");
const closeAddressModal = document.getElementById("closeAddressModal");
const addressForm = document.getElementById("addressForm");
let map, marker;

if (editAddressBtn) {
    editAddressBtn.addEventListener("click", () => {
        addressModal.style.display = "flex";

        // Initialize map after modal is shown to ensure correct rendering
        setTimeout(initMap, 100);

        // Populate dropdowns
        populateAddressFields();
    });
}

async function populateAddressFields() {
    const regionSelect = document.getElementById("regionSelect");
    const provinceSelect = document.getElementById("provinceSelect");
    const citySelect = document.getElementById("citySelect");
    const barangaySelect = document.getElementById("barangaySelect");

    // Avoid refetching if already populated (more than 1 option means loaded, 1 is placeholder)
    if (barangaySelect.options.length > 2) return;

    try {
        const apiBase = "https://psgc.gitlab.io/api";

        // 1. Region: NCR (Code 130000000)
        // We set it exclusively
        regionSelect.innerHTML = '<option value="NCR" selected>National Capital Region (NCR)</option>';

        // 2. Province: Metro Manila
        provinceSelect.innerHTML = '<option value="Metro Manila" selected>Metro Manila</option>';

        // 3. City: Caloocan City (Code 137501000)
        citySelect.innerHTML = '<option value="Caloocan City" selected>Caloocan City</option>';

        // 4. Barangays: Caloocan (South)
        // Fetch all barangays for Caloocan
        // Note: Using a direct fetch for Caloocan code 137501000
        const brgyResp = await fetch(`${apiBase}/cities-municipalities/137501000/barangays.json`);
        if (!brgyResp.ok) throw new Error("Failed to fetch barangays");

        const barangays = await brgyResp.json();

        // Filter South Caloocan: Barangays 1 to 164
        const southBarangays = barangays.filter(b => {
            const match = b.name.match(/(\d+)/);
            if (match) {
                const num = parseInt(match[1]);
                return num >= 1 && num <= 164;
            }
            return false;
        });

        // Sort numerically
        southBarangays.sort((a, b) => {
            const numA = parseInt(a.name.match(/(\d+)/)[1]);
            const numB = parseInt(b.name.match(/(\d+)/)[1]);
            return numA - numB;
        });

        // Populate
        barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
        southBarangays.forEach(b => {
            const opt = document.createElement("option");
            opt.value = b.name;
            opt.textContent = b.name;
            barangaySelect.appendChild(opt);
        });

    } catch (err) {
        console.error("Address API Error:", err);
        showToast("Failed to load address data. Please try again.", "error");
    }
}

if (closeAddressModal) {
    closeAddressModal.addEventListener("click", () => {
        addressModal.style.display = "none";
    });
}

// Close modal when clicking outside
window.addEventListener("click", (e) => {
    if (e.target === addressModal) {
        addressModal.style.display = "none";
    }
});

function initMap() {
    if (map) {
        map.invalidateSize();
        return;
    }

    // South Caloocan Coordinates (approx center)
    const southCaloocan = [14.65, 120.98];

    // Initialize Leaflet Map
    map = L.map('map').setView(southCaloocan, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Add boundaries logic (optional visual guide)
    // For now, simpler implementation: just let them pin.

    map.on('click', function (e) {
        const { lat, lng } = e.latlng;

        // Update Marker
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }

        // Update hidden inputs
        document.getElementById("latInput").value = lat;
        document.getElementById("lngInput").value = lng;
    });
}

if (addressForm) {
    addressForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const street = addressForm.querySelector('[name="street"]').value.trim();
        const barangay = addressForm.querySelector('[name="barangay"]').value.trim();
        const lat = document.getElementById("latInput").value;
        const lng = document.getElementById("lngInput").value;

        if (!street || !barangay) {
            showToast("Street and Barangay are required.", "error");
            return;
        }

        if (!lat || !lng) {
            showToast("Please pin your location on the map.", "error");
            return;
        }

        // Prepare data
        const fd = new FormData(addressForm);

        try {
            // Note: Update URL to your backend endpoint
            const resp = await fetch('../backend/api/update_user_address.php', {
                method: "POST",
                body: fd
            });

            // Check if response is JSON (it might return HTML if error page)
            const text = await resp.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error("Parsed error:", text);
                showToast("Server returned invalid response.", "error");
                return;
            }

            if (result.success) {
                showToast("Address updated successfully!", "success");
                addressModal.style.display = "none";

                // Optional: Update displayed values in the profile page immediately
                // This would require selecting the display elements and updating their textContent
                // e.g.
                // document.querySelector("#address .label:contains('Street') + .display-value").textContent = street;
                // But for now, a reload or generic success is enough as per instructions.
                // Reloading to reflect changes if not updating DOM dynamically:
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(result.message || "Failed to update address.", "error");
            }
        } catch (err) {
            showToast("Network error: " + err.message, "error");
        }
    });
}


// change password
const changePassBtn = document.getElementById("changePassBtn");
const changePasswordModal = document.getElementById("changePasswordModal");

if (changePassBtn) {
    changePassBtn.addEventListener("click", () => {
        changePasswordModal.style.display = "flex";
    });
}

window.addEventListener("click", (e) => {
    if (e.target === changePasswordModal) {
        changePasswordModal.style.display = "none";
    }
});