document.addEventListener('DOMContentLoaded', () => {
    let currentPage = 1;
    let limit = 10;

    const statusFilter = document.getElementById('statusFilter');
    const paymentFilter = document.getElementById('paymentFilter');
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageInfo = document.getElementById('pageInfo');
    const tbody = document.querySelector('table tbody');

    // Load saved filters
    const savedFilters = JSON.parse(sessionStorage.getItem('sales_filters') || '{}');
    if (savedFilters.status) statusFilter.value = savedFilters.status;
    if (savedFilters.payment) paymentFilter.value = savedFilters.payment;
    if (savedFilters.from) fromDate.value = savedFilters.from;
    if (savedFilters.to) toDate.value = savedFilters.to;

    // Initial Load
    fetchSalesData();

    function saveFilters() {
        const filters = {
            status: statusFilter.value,
            payment: paymentFilter.value,
            from: fromDate.value,
            to: toDate.value
        };
        sessionStorage.setItem('sales_filters', JSON.stringify(filters));
    }

    // Export Excel Listener
    const exportExcelBtn = Array.from(document.querySelectorAll('.btn-primary-custom.btns')).find(btn => btn.textContent === 'Export Excel');
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', () => {
            const currentStatus = statusFilter.value;

            if (currentStatus === 'Cancelled') {
                alert('Action Denied: Cancelled orders are excluded from Sales Reports. Please select another status.');
                return;
            }

            const params = new URLSearchParams({
                status: currentStatus,
                payment: paymentFilter.value,
                fromDate: fromDate.value,
                toDate: toDate.value
            });

            // Trigger download
            window.location.href = `${window.BASE_URL}/backend/api/admin/export_sales_excel.php?` + params.toString();
        });
    }

    // Export CSV Listener
    const exportCsvBtn = Array.from(document.querySelectorAll('.btn-primary-custom.btns')).find(btn => btn.textContent === 'Export CSV');
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', () => {
            const currentStatus = statusFilter.value;

            if (currentStatus === 'Cancelled') {
                alert('Action Denied: Cancelled orders are excluded from Sales Reports. Please select another status.');
                return;
            }

            const params = new URLSearchParams({
                status: currentStatus,
                payment: paymentFilter.value,
                fromDate: fromDate.value,
                toDate: toDate.value
            });

            // Trigger download
            window.location.href = `${window.BASE_URL}/backend/api/admin/export_sales_csv.php?` + params.toString();
        });
    }

    // Event Listeners
    const exportPdfBtn = Array.from(document.querySelectorAll('.btn-primary-custom.btns')).find(btn => btn.textContent === 'Export PDF');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', () => {
            const currentStatus = statusFilter.value;

            if (currentStatus === 'Cancelled') {
                alert('Action Denied: Cancelled orders are excluded from Sales Reports. Please select another status.');
                return;
            }

            const from = fromDate.value;
            const to = toDate.value;

            const params = new URLSearchParams({
                status: currentStatus,
                payment: paymentFilter.value,
                fromDate: from,
                toDate: to
            });

            // Trigger export
            window.location.href = `${window.BASE_URL}/backend/api/admin/export_sales_pdf.php?` + params.toString();
        });
    }

    statusFilter.addEventListener('change', () => { currentPage = 1; saveFilters(); fetchSalesData(); });
    paymentFilter.addEventListener('change', () => { currentPage = 1; saveFilters(); fetchSalesData(); });
    fromDate.addEventListener('change', () => { currentPage = 1; saveFilters(); fetchSalesData(); });
    toDate.addEventListener('change', () => { currentPage = 1; saveFilters(); fetchSalesData(); });

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            fetchSalesData();
        }
    });

    nextBtn.addEventListener('click', () => {
        currentPage++;
        fetchSalesData();
    });

    async function fetchSalesData() {
        const params = new URLSearchParams({
            status: statusFilter.value,
            payment: paymentFilter.value,
            fromDate: fromDate.value,
            toDate: toDate.value,
            page: currentPage,
            limit: limit
        });

        try {
            const response = await fetch(`${window.BASE_URL}/backend/api/admin/get_sales_data.php?` + params.toString());
            const result = await response.json();

            if (result.status === 'success') {
                renderTable(result.data);
                updatePagination(result.pagination);
            } else {
                console.error('Error fetching data:', result.message);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error loading data: ${result.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Network error:', error);
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Network error: ${error.message}. Check console for details.</td></tr>`;
        }
    }

    function renderTable(data) {
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="text-align: center; padding: 20px;">No records found</td></tr>';
            return;
        }

        data.forEach(order => {
            const tr = document.createElement('tr');

            // Format ID
            const orderId = order.order_number || `#${order.id}`;

            // Format Name
            const name = (order.first_name || 'Guest') + ' ' + (order.last_name || '');

            // Format Amount
            const amount = '₱' + parseFloat(order.total_amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Format Status (Capitalize)
            let status = order.status.replace(/_/g, ' ');
            status = status.charAt(0).toUpperCase() + status.slice(1);

            // Format Payment
            let payment = '';
            if (order.payment_method === 'cod') {
                payment = order.delivery_method === 'delivery' ? 'Cash on Delivery' : 'Cash';
            } else {
                payment = 'E-Wallet (Gcash)';
            }

            // Format Date
            const date = new Date(order.created_at).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });

            tr.innerHTML = `
                <td class="oIDCol">${orderId}</td>
                <td class="nameCol">${name}</td>
                <td class="totalCol">${amount}</td>
                <td class="statusCol">${status}</td>
                <td class="paymentCol">${payment}</td>
                <td class="dateCol">${date}</td>
            `;

            tbody.appendChild(tr);
        });
    }

    function updatePagination(pagination) {
        currentPage = parseInt(pagination.current_page);
        const totalPages = parseInt(pagination.total_pages);

        pageInfo.textContent = `Page ${currentPage} of ${totalPages || 1}`;

        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= totalPages;
    }
});
