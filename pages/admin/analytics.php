<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Reports & Analytics</p>
    </div>
    <button type="button" class="btn-primary-custom btns">Generate Report</button>
</div>

<style>
    .btn-primary-custom {
        background-color: #d0b28c;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-primary-custom:hover {
        background-color: #c4a076;
    }

    .filters {
        background: #fff;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .charts-container{
        display: flex;
        flex-direction: row;
        gap: 20px;
    }
    .charts {
        background: #fff;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        width: 60%;
    } 
    .buttons-container{
        display: flex;
        flex-direction: row;
        gap: 20px;
        width: 100%;
    }

    #fromDate,
    #toDate {
        background-color: #f8f9fa;
        color: #333;
        border: 1px solid #ddd;
        padding: 10px 15px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    /* Dashboard Grid Layout */
    .dashboard-grid {
        display: grid;
        /* Create a responsive grid that fills the row with auto-sized columns */
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        /* Center content */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-align: center;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .center-text {
        width: 100%;
    }

    .stat-info h3 {
        margin: 0;
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        margin: 10px 0 0 0;
        font-size: 2rem;
        font-weight: bold;
        color: #333;
    }

    .text-pending {
        color: #fd7e14;
    }

    /* Orange */
    .text-preparing {
        color: #ffc107;
    }

    /* Yellow */
    .text-ready {
        color: #17a2b8;
    }

    /* Teal/Info */
    .text-delivered {
        color: #198754;
    }

    /* Green */
    .text-cancelled {
        color: #dc3545;
    }

    /* Red */
    .text-primary {
        color: #0d6efd;
    }

    .cat-btns{
        font-size: 0.8rem;
        padding: 5px;
    }
</style>

<div class="filters">
    <div class="filter-group" id="date-range">
        <label>Date Range:</label>
        <input type="date" id="fromDate" value=""><span id="dash">-</span>
        <input type="date" id="toDate" value="">
    </div>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Total Sales</h3>
            <p class="stat-value text-pending">8</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Total Orders</h3>
            <p class="stat-value text-preparing">3</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Average Order Value</h3>
            <p class="stat-value text-ready">5</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Revenue Growth</h3>
            <p class="stat-value text-delivered">120</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Top Product</h3>
            <p class="stat-value text-cancelled">2</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info center-text">
            <h3>Top Customer</h3>
            <p class="stat-value text-primary">3</p>
        </div>
    </div>
</div>

<div class="charts-container">
    <div class="charts">
        <div class="buttons-container">
            <button type="button" class="btn-primary-custom btns cat-btns">Sales Trend</button>
            <button type="button" class="btn-primary-custom btns cat-btns">Revenue by Category</button>
            <button type="button" class="btn-primary-custom btns cat-btns">Customer Growth</button>
        </div>

        <div class="charts" style="width: 100%;">
            <p>No revenue data found for the selected range.</p>
        </div>
    </div>

    <div class="charts" style="flex: 1;">
        <p>Customer Sentiment Summary</p>
    </div>
</div>