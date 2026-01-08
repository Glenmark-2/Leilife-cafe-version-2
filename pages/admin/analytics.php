<?php
require_once __DIR__ . '/../../backend/helpers/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../../.env');
$pusherKey = getenv('PUSHER_KEY');
$pusherCluster = getenv('PUSHER_CLUSTER') ?: 'ap1';
?>
<!-- Include Pusher, Phosphor Icons & Chart.js -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    window.pusherConfig = {
        key: '<?php echo $pusherKey; ?>',
        cluster: '<?php echo $pusherCluster; ?>'
    };
</script>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span><span></span><span></span>
        </button>
        <p class="title">Business Analytics</p>
    </div>
    <div class="action-btns">
        <button type="button" class="btn-secondary-custom btns" onclick="window.print()"><i class="ph ph-printer me-2"></i>Print</button>
        <button type="button" class="btn-primary-custom btns"><i class="ph ph-export me-2"></i>Export Data</button>
    </div>
</div>

<style>
    :root {
        --primary-gold: #d0b28c;
        --dark-coffee: #2c2b2c;
        --soft-bg: #f8f9fa;
        --border-color: #eee;
    }

    .filters { background: #fff; padding: 20px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 25px; display: flex; align-items: center; gap: 20px; border: 1px solid var(--border-color); }
    .filter-group { display: flex; align-items: center; gap: 10px; }
    #fromDate, #toDate { border: 1px solid #ddd; padding: 10px 14px; border-radius: 10px; font-weight: 500; font-family: inherit; transition: all 0.2s; }
    #fromDate:focus, #toDate:focus { border-color: var(--primary-gold); outline: none; box-shadow: 0 0 0 3px rgba(208, 178, 140, 0.2); }

    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
    
    .stat-card {
        background: #fff; border-radius: 16px; padding: 24px; display: flex; align-items: flex-start; gap: 18px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border-color); transition: all 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    
    .stat-icon {
        width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; flex-shrink: 0;
    }
    .icon-sales { background: #fff7ed; color: #ea580c; }
    .icon-orders { background: #eff6ff; color: #2563eb; }
    .icon-avg { background: #f0fdf4; color: #16a34a; }
    .icon-top { background: #faf5ff; color: #9333ea; }

    .stat-info h3 { margin: 0; font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .stat-value { margin: 5px 0; font-size: 1.6rem; font-weight: 800; color: var(--dark-coffee); line-height: 1; }
    .stat-trend { font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 4px; }
    .trend-up { color: #16a34a; }

    .main-analytics-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; align-items: start; }
    
    .chart-panel { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid var(--border-color); }
    
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .chart-tabs { display: flex; background: #f1f1f1; padding: 5px; border-radius: 12px; }
    .tab-btn {
        border: none; background: transparent; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem;
        cursor: pointer; transition: all 0.2s; color: #666;
    }
    .tab-btn.active { background: #fff; color: var(--dark-coffee); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

    .data-table-panel { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid var(--border-color); }
    .panel-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--dark-coffee); display: flex; align-items: center; gap: 10px; }
    
    .analytic-list { list-style: none; padding: 0; margin: 0; }
    .analytic-item {
        display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid #f5f5f5;
    }
    .analytic-item:last-child { border-bottom: none; }
    .item-label { display: flex; flex-direction: column; }
    .item-name { font-weight: 700; color: var(--dark-coffee); font-size: 0.95rem; }
    .item-sub { font-size: 0.75rem; color: #999; }
    .item-value { font-weight: 800; color: var(--primary-gold); }

    .chart-container { height: 350px; position: relative; width: 100%; }
</style>

<div class="filters">
    <div class="filter-group">
        <i class="ph ph-calendar-blank fs-5 text-muted"></i>
        <input type="date" id="fromDate">
        <span class="text-muted">to</span>
        <input type="date" id="toDate">
    </div>
    <button id="applyFilter" class="btn-primary-custom" style="padding: 10px 24px; border-radius: 10px;">
        Apply Analysis
    </button>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-icon icon-sales"><i class="ph ph-chart-line-up"></i></div>
        <div class="stat-info">
            <h3>Total Revenue</h3>
            <p id="stat-total-sales" class="stat-value">₱0.00</p>
            <span class="stat-trend trend-up"><i class="ph ph-trend-up"></i> Live Tracking</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-orders"><i class="ph ph-shopping-bag"></i></div>
        <div class="stat-info">
            <h3>Volume</h3>
            <p id="stat-total-orders" class="stat-value">0</p>
            <span class="text-muted small">Completed Orders</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-avg"><i class="ph ph-money"></i></div>
        <div class="stat-info">
            <h3>Ticket Size</h3>
            <p id="stat-avg-value" class="stat-value">₱0</p>
            <span class="text-muted small">Avg. per Order</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-top"><i class="ph ph-crown"></i></div>
        <div class="stat-info">
            <h3>Best Seller</h3>
            <p id="stat-top-product" class="stat-value" style="font-size: 1.1rem; margin-top: 10px;">N/A</p>
        </div>
    </div>
</div>

<div class="main-analytics-layout">
    <div class="chart-panel">
        <div class="chart-header">
            <h4 class="panel-title" id="main-chart-title"><i class="ph ph-presentation-chart"></i> Revenue Performance</h4>
            <div class="chart-tabs">
                <button class="tab-btn active" data-type="sales">Trend</button>
                <button class="tab-btn" data-type="category">Category</button>
                <button class="tab-btn" data-type="sentiment">Sentiment</button>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="analyticsChart"></canvas>
        </div>
    </div>

    <div class="data-table-panel">
        <h4 class="panel-title"><i class="ph ph-list-numbers"></i> Performance Breakdown</h4>
        <div id="breakdown-container">
            <ul class="analytic-list" id="category-breakdown-list">
                <!-- Data populated by JS -->
                <li class="text-center py-5 text-muted">Select date range to see breakdown</li>
            </ul>
        </div>
        
        <div class="mt-4 pt-4 border-top">
            <h4 class="panel-title" style="font-size: 0.9rem;"><i class="ph ph-chat-circle-dots"></i> Customer Sentiment</h4>
            <div id="sentiment-summary-panel" class="p-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ph-fill ph-smiley text-success"></i>
                        <span class="small fw-bold">Positive</span>
                    </div>
                    <span id="sentiment-pos-count" class="badge rounded-pill bg-success-subtle text-success">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ph-fill ph-meh text-warning"></i>
                        <span class="small fw-bold">Neutral</span>
                    </div>
                    <span id="sentiment-neu-count" class="badge rounded-pill bg-warning-subtle text-warning">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ph-fill ph-smiley-sad text-danger"></i>
                        <span class="small fw-bold">Negative</span>
                    </div>
                    <span id="sentiment-neg-count" class="badge rounded-pill bg-danger-subtle text-danger">0</span>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-top">
            <h4 class="panel-title" style="font-size: 0.9rem;"><i class="ph ph-users"></i> Top Customer</h4>
            <div class="d-flex align-items-center gap-3">
                <div class="bg-light rounded-circle p-2"><i class="ph ph-user fs-4"></i></div>
                <div>
                   <p id="stat-top-customer" class="mb-0 fw-bold">N/A</p>
                   <p class="small text-muted mb-0">Most Frequent Buyer</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Leilife_2nd/scripts/admin/analytics.js"></script>
