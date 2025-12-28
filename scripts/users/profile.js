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
        activateTab(btn.textContent.trim());
    });
});

function activateTab(tabTitle) {
    if (!tabTitle) return;

    buttons.forEach(b => {
        b.classList.toggle("active", b.textContent.trim() === tabTitle);
    });

    // Hide all sections
    Object.values(sections).forEach(secId => {
        const el = document.getElementById(secId);
        if (el) el.style.display = "none";
    });

    // Show selected section
    const sectionId = sections[tabTitle];
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.display = "flex";
        if (sectionId === 'order_history') {
            fetchOrderHistory();
        }
    }
}

// Initial Load - always default to first tab (Personal Info)
document.addEventListener('DOMContentLoaded', () => {
    if (buttons.length > 0) {
        activateTab(buttons[0].textContent.trim());
    }
});

// --- Order History Logic ---
const orderHistoryContainer = document.getElementById('order-history-container');
let ordersHistoryData = [];

async function fetchOrderHistory() {
    if (!orderHistoryContainer) return;

    try {
        const resp = await fetch('../backend/api/get_my_orders.php');
        const data = await resp.json();

        if (data.success) {
            ordersHistoryData = data.orders;
            renderOrderHistory(data.orders);
        } else {
            orderHistoryContainer.innerHTML = `<p class="text-center p-4">${data.message || 'No orders found.'}</p>`;
        }
    } catch (err) {
        console.error("Order history fetch error:", err);
        orderHistoryContainer.innerHTML = `<p class="text-center p-4 text-danger">Failed to load order history.</p>`;
    }
}

function renderOrderHistory(orders) {
    if (!orders || orders.length === 0) {
        orderHistoryContainer.innerHTML = '<p class="text-center p-4">You haven\'t placed any orders yet.</p>';
        return;
    }

    let html = '';
    orders.forEach(order => {
        const date = new Date(order.created_at).toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        const statusLabel = order.status.replace(/_/g, ' ').toUpperCase();
        const statusClass = 'status-' + (order.status || 'pending').toLowerCase();

        const itemsList = order.items.map(item => {
            const isCancelled = item.status === 'cancelled';
            const style = isCancelled ? 'style="text-decoration: line-through; color: #dc3545; opacity: 0.7;"' : '';
            const statusText = isCancelled ? ' <small class="fw-bold" style="color: #dc3545;">(Cancelled)</small>' : '';
            return `<li ${style}>${item.product_name} x ${item.quantity}${statusText}</li>`;
        }).join('');

        // Feedback section
        let feedbackHtml = '';
        if (order.feedback) {
            feedbackHtml = `
                <div class="mt-3 p-3 rounded" style="background: #f8f9fa; border-left: 4px solid #d0b28c; border: 1px solid #eee;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span style="font-weight: bold; font-size: 0.9rem;">Your Review:</span>
                        <div style="color: #ffc107; font-size: 1rem;">
                            ${Array(5).fill(0).map((_, i) => `<i class="bi bi-star${i < order.feedback.rating ? '-fill' : ''}"></i>`).join('')}
                        </div>
                    </div>
                    <p class="m-0 small text-muted" style="font-style: italic;">"${order.feedback.comment || 'No comment provided.'}"</p>
                </div>
            `;
        } else if (['delivered', 'picked_up', 'completed'].includes(order.status)) {
            feedbackHtml = `
                <button type="button" class="btn btn-sm btn-outline-primary-custom mt-3 px-3" onclick="openFeedbackModal(${order.id})">
                    Submit Feedback
                </button>
            `;
        }

        html += `
            <div class="info" style="width: 100%; padding: 25px; background: #fff; border: 1px solid #eee; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); margin-bottom: 20px;">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="label m-0" style="font-size: 1.2rem; display: block;">Order ID: <span style="font-weight: 800; color: #333;">${order.order_number || ('#' + order.id)}</span></p>
                        <p class="text-muted small m-0">${date}</p>
                    </div>
                    <span class="order-status ${statusClass}" style="margin: 0; padding: 6px 14px; border-radius: 50px; font-size: 0.75rem;">${statusLabel}</span>
                </div>
                
                <div class="py-3 border-top border-bottom">
                    <p class="label mb-2" style="font-size: 0.95rem; font-weight: 700;">Items Ordered:</p>
                    <ul style="margin: 0 0 0 15px; padding: 0; color: #555; list-style-type: square;">
                        ${itemsList}
                    </ul>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <p class="m-0" style="font-weight: 800; font-size: 1.2rem; color: #d0b28c;">₱${parseFloat(order.total_amount).toFixed(2)}</p>
                    <div class="d-flex gap-2">
                        ${['delivered', 'picked_up', 'completed'].includes(order.status.toLowerCase()) ? `
                            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 px-3" onclick="downloadReceipt(${order.id})">
                                <i class="bi bi-download"></i> Receipt
                            </button>
                        ` : ''}
                        ${['delivered', 'picked_up', 'completed', 'cancelled'].includes(order.status.toLowerCase()) ? `
                            <button type="button" class="btn btn-sm btn-primary-custom d-flex align-items-center gap-1 px-3" onclick='reorderItems(${order.id})'>
                                <i class="bi bi-arrow-repeat"></i> Reorder
                            </button>
                        ` : `
                            <button type="button" class="btn btn-sm btn-primary-custom d-flex align-items-center gap-1 px-3" onclick="window.location.href='index.php?page=order_tracking&order_id=${order.id}'">
                                <i class="bi bi-geo-alt"></i> Track
                            </button>
                        `}
                    </div>
                </div>
                ${feedbackHtml}
            </div>
        `;
    });

    orderHistoryContainer.innerHTML = html;
}

window.reorderItems = function (orderId) {
    const order = ordersHistoryData.find(o => o.id == orderId);
    if (!order) return;

    if (confirm("Reordering will clear your current cart and replace it with these items. Continue?")) {
        const newCart = order.items.map(item => {
            console.log("Reordering item:", item);
            return {
                id: item.product_id,
                name: item.product_name,
                price: parseFloat(item.price),
                qty: parseInt(item.quantity),
                image: item.product_image || item.image_path || item.image || 'not_available.png'
            };
        });

        localStorage.setItem('leilife_cart', JSON.stringify(newCart));
        window.location.href = 'index.php?page=checkout';
    }
}

window.downloadReceipt = function (orderId) {
    window.open(`index.php?page=user-receipt&order_id=${orderId}`, '_blank');
}

// --- Feedback Logic ---
const feedbackModal = document.getElementById('feedbackModal');
const feedbackForm = document.getElementById('feedbackForm');
const ratingInput = document.getElementById('ratingInput');
const stars = document.querySelectorAll('.rating-stars .star');

window.openFeedbackModal = function (orderId) {
    document.getElementById('feedbackOrderId').value = orderId;
    feedbackModal.style.display = 'flex';
    resetStars();
}

window.closeFeedbackModal = function () {
    feedbackModal.style.display = 'none';
    feedbackForm.reset();
}

function resetStars() {
    stars.forEach(s => s.style.color = '#ccc');
    ratingInput.value = '';
}

stars.forEach(star => {
    star.addEventListener('click', () => {
        const val = parseInt(star.getAttribute('data-value'));
        ratingInput.value = val;
        stars.forEach((s, idx) => {
            s.style.color = (idx < val) ? '#ffc107' : '#ccc';
        });
    });

    star.addEventListener('mouseover', () => {
        const val = parseInt(star.getAttribute('data-value'));
        stars.forEach((s, idx) => {
            if (idx < val) s.style.color = '#ffc107';
        });
    });

    star.addEventListener('mouseout', () => {
        const currentVal = parseInt(ratingInput.value) || 0;
        stars.forEach((s, idx) => {
            s.style.color = (idx < currentVal) ? '#ffc107' : '#ccc';
        });
    });
});

if (feedbackForm) {
    feedbackForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const rating = ratingInput.value;
        if (!rating) {
            showToast("Please provide a rating.", "warning");
            return;
        }

        const fd = {
            order_id: document.getElementById('feedbackOrderId').value,
            rating: rating,
            comment: feedbackForm.querySelector('textarea[name="comment"]').value
        };

        try {
            const resp = await fetch('../backend/api/submit_feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(fd)
            });
            const result = await resp.json();

            if (result.success) {
                showToast("Feedback submitted! Thank you.", "success");
                closeFeedbackModal();
                fetchOrderHistory(); // Refresh list
            } else {
                showToast(result.message || "Failed to submit feedback", "error");
            }
        } catch (err) {
            showToast("Network error", "error");
        }
    });
}

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

// settings - change password 
const changePassBtn = document.getElementById("changePassBtn");
const setPassBtn = document.getElementById("setPassBtn");
const changePasswordModal = document.getElementById("changePasswordModal");
const closeChangePasswordModal = document.getElementById("closeChangePasswordModal");

if (changePassBtn) {
    changePassBtn.addEventListener("click", () => {
        changePasswordModal.style.display = "flex";
    });
}

if (setPassBtn) {
    setPassBtn.addEventListener("click", () => {
        changePasswordModal.style.display = "flex";
    });
}

if (closeChangePasswordModal) {
    closeChangePasswordModal.addEventListener("click", () => {
        changePasswordModal.style.display = "none";
    });
}

const changePasswordForm = document.getElementById("changePasswordForm");
if (changePasswordForm) {
    const newPassInput = changePasswordForm.querySelector('input[name="new_password"]');
    const strengthText = document.getElementById("password-strength");

    if (newPassInput && strengthText) {
        newPassInput.addEventListener("input", () => {
            const val = newPassInput.value;
            if (val.length === 0) {
                strengthText.textContent = "";
                return;
            }
            if (val.length < 8) {
                strengthText.textContent = "Too short (min 8 chars)";
                strengthText.style.color = "red";
            } else {
                const hasLetter = /[a-zA-Z]/.test(val);
                const hasNumber = /\d/.test(val);
                const hasSpecial = /[^a-zA-Z0-9]/.test(val);

                if (hasLetter && hasNumber && hasSpecial) {
                    strengthText.textContent = "Strong";
                    strengthText.style.color = "green";
                } else if (hasLetter && (hasNumber || hasSpecial)) {
                    strengthText.textContent = "Medium";
                    strengthText.style.color = "orange";
                } else {
                    strengthText.textContent = "Weak";
                    strengthText.style.color = "#d9534f";
                }
            }
        });
    }

    changePasswordForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const fd = new FormData(changePasswordForm);
        const newPass = fd.get("new_password");
        const confirmPass = fd.get("confirm_password");

        if (newPass.length < 8) {
            showToast("Password must be at least 8 characters", "error");
            return;
        }

        if (newPass !== confirmPass) {
            showToast("New passwords do not match", "error");
            return;
        }

        try {
            const resp = await fetch('../backend/api/update_user_password.php', {
                method: 'POST',
                body: fd
            });
            const result = await resp.json();

            if (result.success) {
                showToast(result.message || "Password updated successfully", "success");
                changePasswordModal.style.display = "none";
                changePasswordForm.reset();
                if (strengthText) strengthText.textContent = "";
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(result.message || "Failed to update password", "error");
            }
        } catch (err) {
            showToast("Network error: " + err.message, "error");
        }
    });
}