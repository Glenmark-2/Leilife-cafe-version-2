<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Sales Report</p>
    </div>
    <div>
        <button type="button" class="btn-primary-custom btns">Export CSV</button>
        <button type="button" class="btn-primary-custom btns">Export Excel</button>
        <button type="button" class="btn-primary-custom btns">Export PDF</button>
    </div>
</div>
<div class="filters">
    <div class="filter-group">
        <label>Status:</label>
        <select id="statusFilter">
            <option value="All">All</option>
            <option value="Picked_up">Picked up</option>
            <option value="Delivered">Delivered</option>
            <option value="Cancelled">Cancelled</option>
        </select>

    </div>

    <div class="filter-group" id="date-range">
        <label>Date Range:</label>
        <input type="date" id="fromDate" value=""><span id="dash">-</span>
        <input type="date" id="toDate" value="">
    </div>

    <div class="filter-group">
        <label>Payment:</label>
        <select id="paymentFilter">
            <option value="All">All</option>
            <option value="Gcash">E-Wallet (Gcash)</option>
            <option value="Cash">Cash</option>
        </select>
    </div>
</div>


<div class="table-container">
    <div class="table-wrapper" style="max-height: 500px; overflow-y: auto;">
        <table>
            <thead style="position: sticky; top: 0; z-index: 1; background-color: #e1d1bbff;">
                <tr>
                    <th class="oIDCol">Order ID</th>
                    <th class="nameCol">Customer</th>
                    <th class="totalCol">Total</th>
                    <th class="statusCol">Status</th>
                    <th class="paymentCol">Payment</th>
                    <th class="dateCol">Date</th>
                </tr>
            </thead>
            <tbody id="sales-body">
                <!-- Data will be populated by JS -->
            </tbody>
        </table>
    </div>
</div>

<div id="pagination-container" class="pagination-container mt-3 d-flex justify-content-between align-items-center">
    <div id="page-info" class="text-muted fw-bold">Showing 0 to 0 of 0 entries</div>
    <nav aria-label="Sales pagination">
        <ul class="pagination pagination-sm mb-0" id="pagination-controls">
            <!-- Pagination items will be populated by JS -->
        </ul>
    </nav>
</div>