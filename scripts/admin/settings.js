document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('settingsForm');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const hoursContainer = document.getElementById('openingHoursContainer');

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

    // --- Opening Hours Logic ---
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    function initHours(savedHours = {}) {
        hoursContainer.innerHTML = '';
        days.forEach(day => {
            const row = document.createElement('div');
            row.className = 'hours-row';

            const timeRange = savedHours[day] || "08:00-20:00";
            const [start, end] = timeRange.split('-');

            row.innerHTML = `
                <label>${day}</label>
                <input type="time" class="start-time" data-day="${day}" value="${start || '08:00'}">
                <input type="time" class="end-time" data-day="${day}" value="${end || '20:00'}">
            `;
            hoursContainer.appendChild(row);
        });
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
            }
        } catch (error) {
            console.error('Error fetching settings:', error);
        }
    }

    // --- Save Settings ---
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const saveBtn = document.getElementById('saveSettingsBtn');
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';

        const formData = new FormData(form);

        // Collect opening hours
        const hours = {};
        document.querySelectorAll('.hours-row').forEach(row => {
            const day = row.querySelector('label').innerText;
            const start = row.querySelector('.start-time').value;
            const end = row.querySelector('.end-time').value;
            hours[day] = `${start}-${end}`;
        });
        formData.append('opening_hours', JSON.stringify(hours));

        // Fix checkbox values (true/false)
        formData.set('is_store_open', document.getElementById('is_store_open').checked);
        formData.set('enable_cod', document.getElementById('enable_cod').checked);
        formData.set('enable_gcash', document.getElementById('enable_gcash').checked);

        try {
            const response = await fetch('/Leilife_2nd/backend/api/admin/update_settings.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                alert('Settings updated successfully!');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            alert('An unexpected error occurred.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Save All Changes';
        }
    });

    fetchSettings();
});
