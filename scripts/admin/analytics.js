function initAnalytics() {
    const fromDateInput = document.getElementById('fromDate');
    const toDateInput = document.getElementById('toDate');
    const applyFilterBtn = document.getElementById('applyFilter');
    const chartCanvas = document.getElementById('analyticsChart');
    const tabBtns = document.querySelectorAll('.tab-btn');
    const breakdownList = document.getElementById('category-breakdown-list');
    const mainChartTitle = document.getElementById('main-chart-title');

    // Sentiment sidebar elements
    const posCountEl = document.getElementById('sentiment-pos-count');
    const neuCountEl = document.getElementById('sentiment-neu-count');
    const negCountEl = document.getElementById('sentiment-neg-count');

    // Register DataLabels plugin globally
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    // REAL-TIME: Initialize Pusher to listen for new feedback
    function initRealtime() {
        console.log("Initializing Real-time Sentiment Listeners...");
        if (!window.pusherConfig || !window.pusherConfig.key) {
            console.warn("Pusher configuration missing!");
            return;
        }

        const pusher = new Pusher(window.pusherConfig.key, {
            cluster: window.pusherConfig.cluster
        });

        const channel = pusher.subscribe('admin-analytics');

        // When feedback is submitted, refresh the data
        channel.bind('feedback-submitted', function (data) {
            console.log("Real-time: New feedback received!", data);
            fetchAnalytics(); // Refresh the chart and sidebar instantly
        });
    }

    // Always attempt to start realtime listeners
    initRealtime();

    if (!fromDateInput || !toDateInput || !applyFilterBtn || !chartCanvas) {
        setTimeout(initAnalytics, 100);
        return;
    }

    const totalSalesEl = document.getElementById('stat-total-sales');
    const totalOrdersEl = document.getElementById('stat-total-orders');
    const avgValueEl = document.getElementById('stat-avg-value');
    const topProductEl = document.getElementById('stat-top-product');
    const topCustomerEl = document.getElementById('stat-top-customer');

    let myChart = null;
    let analyticsData = null;
    let sentimentData = null; // Store sentiment data separately
    let currentChartType = 'sales';

    const today = new Date().toISOString().split('T')[0];
    fromDateInput.value = today;
    toDateInput.value = today;

    async function fetchAnalytics() {
        const fromDate = fromDateInput.value;
        const toDate = toDateInput.value;

        try {
            // Fetch main stats & categories
            const statsPromise = fetch(`../backend/api/admin/get_analytics.php?fromDate=${fromDate}&toDate=${toDate}`).then(res => res.json());
            // Fetch sentiment analysis (Machine Learning)
            const sentimentPromise = fetch(`../backend/api/admin/get_sentiment.php?fromDate=${fromDate}&toDate=${toDate}`).then(res => res.json());

            const [statsResult, sentimentResult] = await Promise.all([statsPromise, sentimentPromise]);

            if (statsResult.status === 'success') {
                analyticsData = statsResult.data;
                updateStats(analyticsData.summary);
                populateBreakdown(analyticsData.categories || []);
            }

            if (sentimentResult.status === 'success') {
                sentimentData = sentimentResult.data;
                updateSentimentSidebar(sentimentData);
            }

            renderCurrentChart();

        } catch (error) {
            console.error("Error fetching analytics:", error);
        }
    }

    function updateStats(summary) {
        totalSalesEl.textContent = `₱${parseFloat(summary.totalSales || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        totalOrdersEl.textContent = summary.totalOrders || 0;
        avgValueEl.textContent = `₱${parseFloat(summary.avgOrderValue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        topProductEl.textContent = summary.topProduct || 'N/A';
        topCustomerEl.textContent = summary.topCustomer || 'N/A';
    }

    function updateSentimentSidebar(data) {
        posCountEl.textContent = data.Positive || 0;
        neuCountEl.textContent = data.Neutral || 0;
        negCountEl.textContent = data.Negative || 0;
    }

    function populateBreakdown(categories) {
        if (!categories || categories.length === 0) {
            breakdownList.innerHTML = '<li class="text-center py-4 text-muted small">No data for this period</li>';
            return;
        }

        const totalRevenue = categories.reduce((sum, cat) => sum + parseFloat(cat.revenue || 0), 0);

        breakdownList.innerHTML = categories.map((cat, index) => {
            const revNum = parseFloat(cat.revenue || 0);
            const percentage = totalRevenue > 0 ? ((revNum / totalRevenue) * 100).toFixed(1) : '0.0';
            const color = ['#d0b28c', '#5e4f3e', '#2c2b2c', '#8c7a66', '#a8927b'][index % 5];
            return `
                <li class="analytic-item">
                    <div class="item-label">
                        <span class="item-name" style="display: flex; align-items: center; gap: 8px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: ${color};"></span>
                            ${cat.category_name}
                        </span>
                        <span class="item-sub">${percentage}% of total sales</span>
                    </div>
                    <span class="item-value">₱${revNum.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                </li>
            `;
        }).join('');
    }

    function renderCurrentChart() {
        if (currentChartType === 'sales') {
            mainChartTitle.innerHTML = '<i class="ph ph-presentation-chart"></i> Revenue Trend Analysis';
            if (analyticsData) {
                const isSingleDay = (fromDateInput.value === toDateInput.value);
                const processed = prepareTrendData(analyticsData.trend || [], isSingleDay, fromDateInput.value, toDateInput.value);
                updateLineChart(processed.labels, processed.values);
            }
        } else if (currentChartType === 'category') {
            mainChartTitle.innerHTML = '<i class="ph ph-chart-pie-slice"></i> Sales Distribution';
            if (analyticsData) updateCategoryChart(analyticsData.categories || []);
        } else if (currentChartType === 'sentiment') {
            mainChartTitle.innerHTML = '<i class="ph ph-chat-circle-dots"></i> Customer Feelings (ML)';
            if (sentimentData) updateSentimentChart(sentimentData);
        }
    }

    function prepareTrendData(rawData, isSingleDay, start, end) {
        let labels = [];
        let values = [];
        if (isSingleDay) {
            for (let i = 0; i < 24; i++) {
                const hourLabel = i === 0 ? '12 AM' : (i < 12 ? i + ' AM' : (i === 12 ? '12 PM' : (i - 12) + ' PM'));
                labels.push(hourLabel);
                const match = rawData.find(d => parseInt(d.time_unit) === i);
                values.push(match ? parseFloat(match.total_sales) : 0);
            }
        } else {
            let current = new Date(start);
            const last = new Date(end);
            while (current <= last) {
                const dateStr = current.toISOString().split('T')[0];
                labels.push(new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                const match = rawData.find(d => d.time_unit === dateStr);
                values.push(match ? parseFloat(match.total_sales) : 0);
                current.setDate(current.getDate() + 1);
            }
        }
        return { labels, values };
    }

    function updateLineChart(labels, values) {
        if (myChart) myChart.destroy();
        const ctx = chartCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 50, 0, 350);
        gradient.addColorStop(0, 'rgba(208, 178, 140, 0.35)');
        gradient.addColorStop(1, 'rgba(208, 178, 140, 0)');

        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    borderColor: '#a8927b',
                    borderWidth: 3.5,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.45,
                    pointRadius: 0,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#a8927b',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: '#999' } },
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f0f0f0', drawBorder: false },
                        ticks: { font: { size: 11, weight: '600' }, color: '#999', callback: v => '₱' + v.toLocaleString() }
                    }
                },
                plugins: {
                    legend: { display: false },
                    datalabels: { display: false },
                    tooltip: {
                        backgroundColor: '#2c2b2c',
                        padding: 15,
                        cornerRadius: 12,
                        callbacks: {
                            label: context => ` Revenue: ₱${parseFloat(context.raw).toLocaleString(undefined, { minimumFractionDigits: 2 })}`
                        }
                    }
                }
            }
        });
    }

    function updateCategoryChart(catData) {
        if (myChart) myChart.destroy();
        const ctx = chartCanvas.getContext('2d');
        const labels = catData.map(d => d.category_name);
        const values = catData.map(d => parseFloat(d.revenue || 0));
        const total = values.reduce((a, b) => a + b, 0);

        myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#d0b28c', '#5e4f3e', '#2c2b2c', '#8c7a66', '#a8927b'],
                    borderWidth: 4,
                    borderColor: '#fff',
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                layout: { padding: 30 },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 25,
                            font: { size: 13, weight: '700' },
                            generateLabels: (chart) => {
                                return chart.data.labels.map((label, i) => ({
                                    text: `${label}: ${((chart.data.datasets[0].data[i] / total) * 100).toFixed(1)}%`,
                                    fillStyle: chart.data.datasets[0].backgroundColor[i],
                                    strokeStyle: '#fff', linewidth: 0, index: i
                                }));
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff', font: { weight: '800', size: 12 },
                        formatter: v => ((v / total) * 100).toFixed(0) + '%',
                        display: v => (v / total) > 0.05
                    },
                    tooltip: {
                        backgroundColor: '#2c2b2c', padding: 12, cornerRadius: 10,
                        callbacks: { label: context => ` ₱${parseFloat(context.raw).toLocaleString()} (${((context.raw / total) * 100).toFixed(1)}%)` }
                    }
                }
            }
        });
    }

    function updateSentimentChart(data) {
        if (myChart) myChart.destroy();
        const ctx = chartCanvas.getContext('2d');

        const labels = ['Positive', 'Neutral', 'Negative'];
        const values = [data.Positive || 0, data.Neutral || 0, data.Negative || 0];
        const total = values.reduce((a, b) => a + b, 0);

        myChart = new Chart(ctx, {
            type: 'bar', // Using bar for sentiment comparison
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.8)', // Success Green
                        'rgba(255, 193, 7, 0.8)', // Warning Yellow
                        'rgba(220, 53, 69, 0.8)'  // Danger Red
                    ],
                    borderRadius: 12,
                    borderWidth: 0,
                    barThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false }, ticks: { font: { weight: '700' } } },
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end', align: 'top', color: '#666', font: { weight: '800' },
                        formatter: v => v > 0 ? v : ''
                    },
                    tooltip: {
                        backgroundColor: '#2c2b2c',
                        callbacks: {
                            label: context => ` Total Feedback: ${context.raw} (${((context.raw / total) * 100).toFixed(1)}%)`
                        }
                    }
                }
            }
        });
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentChartType = btn.dataset.type;
            renderCurrentChart();
        });
    });

    applyFilterBtn.addEventListener('click', fetchAnalytics);
    fetchAnalytics();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAnalytics);
} else {
    initAnalytics();
}
