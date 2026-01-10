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
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th class="oIDCol">Order ID</th>
                    <th class="nameCol">Customer</th>
                    <th class="totalCol">Total</th>
                    <th class="statusCol">Status</th>
                    <th class="paymentCol">Payment</th>
                    <th class="dateCol">Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="oIDCol">#ORD-20251110-25C68B</td>
                    <td class="nameCol">Ellie</td>
                    <td class="totalCol">₱119.00</td>
                    <td class="statusCol">Delivered</td>
                    <td class="paymentCol">Cash</td>
                    <td class="dateCol">Dec 12, 2025</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<div class="pagination-container" style="display: flex; justify-content: flex-end; align-items: center; margin-top: 15px; gap: 10px;">
    <button id="prevBtn" class="btn-primary-custom" disabled>Previous</button>
    <span id="pageInfo">Page 1 of 1</span>
    <button id="nextBtn" class="btn-primary-custom" disabled>Next</button>
</div>

