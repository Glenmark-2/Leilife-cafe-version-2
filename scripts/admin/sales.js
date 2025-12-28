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

    // Initial Load
    fetchSalesData();

    // Event Listeners
    statusFilter.addEventListener('change', () => { currentPage = 1; fetchSalesData(); });
    paymentFilter.addEventListener('change', () => { currentPage = 1; fetchSalesData(); });
    fromDate.addEventListener('change', () => { currentPage = 1; fetchSalesData(); });
    toDate.addEventListener('change', () => { currentPage = 1; fetchSalesData(); });

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
            const response = await fetch('/Leilife_2nd/backend/api/admin/get_sales_data.php?' + params.toString());
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
            let payment = order.payment_method === 'cod' ? 'Cash' : 'E-Wallet (Gcash)'; // Adjust mapping as per DB values
            if (order.payment_method === 'gcash') payment = 'E-Wallet (Gcash)';

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
