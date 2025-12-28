// Shared address modal functionality
let addressMap, addressMarker;

function initializeAddressModal() {
    populateAddressDropdowns();

    // Initialize map after a short delay to ensure modal is visible
    setTimeout(() => {
        if (!addressMap) {
            initAddressMap();
        } else {
            addressMap.invalidateSize();
        }
    }, 200);
}

async function populateAddressDropdowns() {
    const regionSelect = document.getElementById("regionSelect");
    const provinceSelect = document.getElementById("provinceSelect");
    const citySelect = document.getElementById("citySelect");
    const barangaySelect = document.getElementById("barangaySelect");

    // Avoid refetching if already populated
    if (barangaySelect && barangaySelect.options.length > 2) return;

    try {
        const apiBase = "https://psgc.gitlab.io/api";

        // 1. Region: NCR (Code 130000000)
        if (regionSelect) {
            regionSelect.innerHTML = '<option value="NCR" selected>National Capital Region (NCR)</option>';
        }

        // 2. Province: Metro Manila
        if (provinceSelect) {
            provinceSelect.innerHTML = '<option value="Metro Manila" selected>Metro Manila</option>';
        }

        // 3. City: Caloocan City (Code 137501000)
        if (citySelect) {
            citySelect.innerHTML = '<option value="Caloocan City" selected>Caloocan City</option>';
        }

        // 4. Barangays: Caloocan (South)
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
        if (barangaySelect) {
            barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';
            southBarangays.forEach(b => {
                const opt = document.createElement("option");
                opt.value = b.name;
                opt.textContent = b.name;
                barangaySelect.appendChild(opt);
            });
        }

    } catch (err) {
        console.error("Address API Error:", err);
        alert("Failed to load address data. Please try again.");
    }
}

function initAddressMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) return;

    // South Caloocan Coordinates (approx center)
    const southCaloocan = [14.65, 120.98];

    // Initialize Leaflet Map
    addressMap = L.map('map').setView(southCaloocan, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(addressMap);

    addressMap.on('click', function (e) {
        const { lat, lng } = e.latlng;

        // Update Marker
        if (addressMarker) {
            addressMarker.setLatLng(e.latlng);
        } else {
            addressMarker = L.marker(e.latlng).addTo(addressMap);
        }

        // Update hidden inputs
        const latInput = document.getElementById("latInput");
        const lngInput = document.getElementById("lngInput");
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
    });
}

// Close modal handlers
const closeAddressModalBtn = document.getElementById('closeAddressModal');
if (closeAddressModalBtn) {
    closeAddressModalBtn.addEventListener('click', () => {
        const addressModal = document.getElementById('addressModal');
        if (addressModal) {
            addressModal.style.display = 'none';
        }
    });
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    const addressModal = document.getElementById('addressModal');
    if (e.target === addressModal) {
        addressModal.style.display = 'none';
    }
});
