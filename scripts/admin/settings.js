document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('settingsForm');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const hoursContainer = document.getElementById('openingHoursContainer');
    const saveBtn = document.getElementById('saveSettingsBtn');
    let initialState = null;

    // Initial button state
    saveBtn.disabled = true;

    // --- Tab Switching ---
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;

            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            document.getElementById(target).classList.add('active');
        });
    });

    // --- Time Format Helpers ---
    function format24to12(time24) {
        if (!time24) return "";
        let [hours, minutes] = time24.split(':');
        hours = parseInt(hours, 10);
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return `${hours.toString().padStart(2, '0')}:${minutes} ${ampm}`;
    }

    function format12to24(time12) {
        if (!time12) return "";
        const match = time12.trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
        if (!match) return time12.trim();
        let [ , hours, minutes, ampm ] = match;
        hours = parseInt(hours, 10);
        if (ampm.toUpperCase() === 'PM' && hours < 12) hours += 12;
        if (ampm.toUpperCase() === 'AM' && hours === 12) hours = 0;
        return `${hours.toString().padStart(2, '0')}:${minutes}`;
    }

    // --- Opening Hours Logic ---
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    function initHours(savedHours = {}) {
        hoursContainer.innerHTML = '';
        days.forEach(day => {
            const row = document.createElement('div');
            row.className = 'hours-row';

            let startVal = '08:00';
            let endVal = '20:00';

            const dayData = savedHours[day];
            if (dayData) {
                if (typeof dayData === 'object') {
                    startVal = format12to24(dayData.open) || '08:00';
                    endVal = format12to24(dayData.close) || '20:00';
                } else if (typeof dayData === 'string') {
                    const parts = dayData.split('-');
                    if (parts.length === 2) {
                        startVal = format12to24(parts[0]) || '08:00';
                        endVal = format12to24(parts[1]) || '20:00';
                    }
                }
            }

            row.innerHTML = `
                <label>${day}</label>
                <input type="time" class="start-time hours-input" data-day="${day}" value="${startVal}">
                <input type="time" class="end-time hours-input" data-day="${day}" value="${endVal}">
            `;
            hoursContainer.appendChild(row);
        });
    }

    function getFormState() {
        const formData = new FormData(form);
        const state = {};
        
        // Simple inputs
        formData.forEach((value, key) => {
            if (key !== 'opening_hours') {
                state[key] = value;
            }
        });

        // Checkboxes specifically (FormData only includes checked ones, we need all)
        state['is_store_open'] = document.getElementById('is_store_open').checked;
        state['enable_cod'] = document.getElementById('enable_cod').checked;
        state['enable_gcash'] = document.getElementById('enable_gcash').checked;

        // Opening hours
        const hours = {};
        document.querySelectorAll('.hours-row').forEach(row => {
            const day = row.querySelector('label').innerText;
            const start = row.querySelector('.start-time').value;
            const end = row.querySelector('.end-time').value;
            hours[day] = {
                open: format24to12(start),
                close: format24to12(end)
            };
        });
        state['opening_hours'] = JSON.stringify(hours);

        return JSON.stringify(state);
    }

    function checkChanges() {
        if (!initialState) return;
        const currentState = getFormState();
        const hasChanged = currentState !== initialState;
        saveBtn.disabled = !hasChanged;
    }

    form.addEventListener('input', checkChanges);
    form.addEventListener('change', checkChanges);

    // --- Custom Modal For Unsaved Changes ---
    const unsavedModalEl = document.getElementById('unsavedChangesModal');
    const discardBtn = document.getElementById('discardChangesBtn'); // This is the Leave Page button
    let unsavedModal = null;
    let pendingLocation = null;

    if (unsavedModalEl) {
        unsavedModal = new bootstrap.Modal(unsavedModalEl);

        // Sidebar Links
        document.querySelectorAll('.buttonDiv').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!saveBtn.disabled && this.href && !this.href.includes('#')) {
                    e.preventDefault();
                    pendingLocation = this.href;
                    unsavedModal.show();
                }
            });
        });

        if (discardBtn) {
            discardBtn.addEventListener('click', () => {
                window.onbeforeunload = null;
                window.location.href = pendingLocation;
            });
        }
    }



    // --- Fetch Settings ---
    async function fetchSettings() {
        try {
            const response = await fetch(`${window.BASE_URL}/backend/api/admin/get_settings.php`);
            const result = await response.json();

            if (result.success) {
                const data = result.data;

                // Populate simple inputs
                for (let key in data) {
                    const input = document.getElementById(key);
                    if (input) {
                        if (input.type === 'checkbox') {
                            input.checked = data[key] == 1;
                        } else {
                            input.value = data[key] || '';
                        }
                    }
                }

                // Populate hours
                initHours(data.opening_hours || {});

                // Capture initial state
                initialState = getFormState();
                checkChanges();
            }
        } catch (error) {
            console.error('Error fetching settings:', error);
        }
    }

    // --- Save Settings Wrapper ---
    async function saveSettingsAction() {
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';

        const formData = new FormData(form);

        // Collect new opening hours format
        const hours = {};
        document.querySelectorAll('.hours-row').forEach(row => {
            const day = row.querySelector('label').innerText;
            const start = row.querySelector('.start-time').value;
            const end = row.querySelector('.end-time').value;
            hours[day] = {
                open: format24to12(start),
                close: format24to12(end)
            };
        });
        formData.append('opening_hours', JSON.stringify(hours));

        const isCodEnabled = document.getElementById('enable_cod').checked;
        const isGcashEnabled = document.getElementById('enable_gcash').checked;

        if (!isCodEnabled && !isGcashEnabled) {
            const warningModalEl = document.getElementById('paymentWarningModal');
            if (warningModalEl) {
                const warningModal = bootstrap.Modal.getOrCreateInstance(warningModalEl);
                warningModal.show();
            } else {
                alert('At least one payment method (COD or E-Wallet) must be enabled to save the settings.');
            }
            saveBtn.disabled = false;
            saveBtn.innerText = 'Save All Changes';
            return false;
        }

        formData.set('is_store_open', document.getElementById('is_store_open').checked);
        formData.set('enable_cod', isCodEnabled);
        formData.set('enable_gcash', isGcashEnabled);

        try {
            const response = await fetch('/Leilife_2nd/backend/api/admin/update_settings.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                alert('Settings updated successfully!');
                initialState = getFormState();
                checkChanges();
                return true;
            } else {
                alert('Error: ' + result.message);
                return false;
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            alert('An unexpected error occurred.');
            return false;
        } finally {
            checkChanges();
            saveBtn.innerText = 'Save All Changes';
        }
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        await saveSettingsAction();
    });

    fetchSettings();
});
