@extends('client.layout.master')

@section('title', 'Meal Reports')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Meal Reports
@endsection

@section('content')
    @include('client.components.meal-reports.filters')
    @include('client.components.meal-reports.kpi-cards')

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            @include('client.components.meal-reports.order-chart')
        </div>
        <div class="col-lg-4">
            @include('client.components.meal-reports.payment-chart')
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            @include('client.components.meal-reports.meal-type-chart')
        </div>
        <div class="col-lg-4">
            @include('client.components.meal-reports.order-status-chart')
        </div>
    </div>

    <div class="row g-4 mt-1 mb-4">
        <div class="col-lg-12">
            @include('client.components.meal-reports.revenue-chart')
        </div>
    </div>

@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ===== State =====
let currentPeriod   = 'monthly';
let customStartDate = '';
let customEndDate   = '';
let charts          = {};

const PIE_COLORS = [
    '#4e73df','#1cc88a','#36b9cc','#f6c23e',
    '#e74a3b','#858796','#5a5c69','#fd7e14'
];

const PERIOD_LABELS = {
    daily:   'Daily',
    weekly:  'Weekly',
    monthly: 'Monthly',
    yearly:  'Yearly',
    custom:  'Custom',
};

// ===== Init =====
document.addEventListener('DOMContentLoaded', function () {
    initPeriodButtons();
    initCustomDatePicker();

    const today    = new Date().toISOString().split('T')[0];
    const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
                        .toISOString().split('T')[0];
    document.getElementById('startDate').value = monthAgo;
    document.getElementById('endDate').value   = today;

    loadAllReports();
});

// ===== Period Buttons =====
function initPeriodButtons() {
    document.querySelectorAll('#periodBtns button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#periodBtns button')
                    .forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.dataset.period;

            const customRange = document.getElementById('customDateRange');
            if (currentPeriod === 'custom') {
                customRange.style.display = 'block';
            } else {
                customRange.style.display = 'none';
                loadAllReports();
            }
        });
    });
}

// ===== Custom Date =====
function initCustomDatePicker() {
    document.getElementById('applyCustomDate').addEventListener('click', function () {
        customStartDate = document.getElementById('startDate').value;
        customEndDate   = document.getElementById('endDate').value;

        if (!customStartDate || !customEndDate) {
            errorToast('Please select both start and end dates.');
            return;
        }
        if (customStartDate > customEndDate) {
            errorToast('Start date cannot be after end date.');
            return;
        }
        loadAllReports();
    });
}

// ===== Build Params =====
function buildParams() {
    const params = { period: currentPeriod };
    if (currentPeriod === 'custom') {
        params.start_date = customStartDate;
        params.end_date   = customEndDate;
    }
    return params;
}

// ===== Update Period Badges =====
function updatePeriodBadges() {
    const label = PERIOD_LABELS[currentPeriod] || 'Monthly';
    document.querySelectorAll('[id$="-chart-period"]')
            .forEach(el => el.textContent = label);
}

// ===== Destroy Chart =====
function destroyChart(id) {
    if (charts[id]) {
        charts[id].destroy();
        delete charts[id];
    }
}

// ===== Show/Hide Empty State =====
function showEmpty(id, isEmpty) {
    const emptyEl  = document.getElementById(id + 'Empty');
    const canvasEl = document.getElementById(id);
    if (emptyEl)  emptyEl.style.display  = isEmpty ? 'block' : 'none';
    if (canvasEl) canvasEl.style.display = isEmpty ? 'none'  : 'block';
}

// ===== Load All Reports =====
async function loadAllReports() {
    try {
        showLoader();
        updatePeriodBadges();
        await Promise.all([
            loadSummary(),
            loadOrderReport(),
            loadPaymentStatusReport(),
            loadMealTypeReport(),
            loadOrderStatusReport(),
            loadRevenueReport(),
        ]);
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// ===== KPI Summary =====
async function loadSummary() {
    try {
        const res = await axios.get('/client/get/meal-report/summary', { params: buildParams() });
        if (res.data.status === 'success') {
            const d = res.data.data;
            document.getElementById('kpi-total-orders').textContent  = d.total_orders;
            document.getElementById('kpi-total-payable').textContent = '£' + d.total_payable;
            document.getElementById('kpi-paid-amount').textContent   = '£' + d.paid_amount;
            document.getElementById('kpi-due-amount').textContent    = '£' + d.due_amount;
            document.getElementById('kpi-items-sold').textContent    = d.total_items_sold;
            document.getElementById('kpi-item-revenue').textContent  = '£' + d.total_item_revenue;
            document.getElementById('kpi-paid-count').textContent    = d.paid_count;
            document.getElementById('kpi-due-count').textContent     = d.due_count;
        }
    } catch (error) {
        handleError(error);
    }
}

// ===== Order Bar Chart =====
async function loadOrderReport() {
    try {
        const res = await axios.get('/client/get/meal-report/orders', { params: buildParams() });
        if (res.data.status !== 'success') return;

        const d       = res.data.data;
        const isEmpty = !d.labels || d.labels.length === 0;
        showEmpty('orderBarChart', isEmpty);
        if (isEmpty) return;

        destroyChart('orderBarChart');
        charts['orderBarChart'] = new Chart(
            document.getElementById('orderBarChart'),
            {
                type: 'bar',
                data: {
                    labels:   d.labels,
                    datasets: [
                        {
                            label:           'Total Orders',
                            data:            d.total_orders,
                            backgroundColor: 'rgba(78,115,223,0.8)',
                            borderColor:     '#4e73df',
                            borderWidth:     1,
                            borderRadius:    4,
                        },
                        {
                            label:           'Paid (£)',
                            data:            d.paid_amount,
                            backgroundColor: 'rgba(28,200,138,0.8)',
                            borderColor:     '#1cc88a',
                            borderWidth:     1,
                            borderRadius:    4,
                        },
                        {
                            label:           'Due (£)',
                            data:            d.due_amount,
                            backgroundColor: 'rgba(231,74,59,0.8)',
                            borderColor:     '#e74a3b',
                            borderWidth:     1,
                            borderRadius:    4,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.datasetIndex === 0
                                    ? ` Orders: ${ctx.parsed.y}`
                                    : ` ${ctx.dataset.label}: £${parseFloat(ctx.parsed.y).toFixed(2)}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                        },
                        x: { grid: { display: false } }
                    }
                }
            }
        );
    } catch (error) {
        handleError(error);
    }
}

// ===== Payment Status Pie Chart =====
async function loadPaymentStatusReport() {
    try {
        const res = await axios.get('/client/get/meal-report/payment-status', { params: buildParams() });
        if (res.data.status !== 'success') return;

        const d       = res.data.data;
        const isEmpty = !d.labels || d.labels.length === 0;
        showEmpty('paymentPieChart', isEmpty);
        if (isEmpty) return;

        destroyChart('paymentPieChart');
        charts['paymentPieChart'] = new Chart(
            document.getElementById('paymentPieChart'),
            {
                type: 'pie',
                data: {
                    labels:   d.labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                    datasets: [{
                        data:            d.counts,
                        backgroundColor: PIE_COLORS.slice(0, d.labels.length),
                        borderWidth:     2,
                        borderColor:     '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed} orders`
                            }
                        }
                    }
                }
            }
        );
    } catch (error) {
        handleError(error);
    }
}

// ===== Meal Type Bar Chart =====
async function loadMealTypeReport() {
    try {
        const res = await axios.get('/client/get/meal-report/meal-type', { params: buildParams() });
        if (res.data.status !== 'success') return;

        const d       = res.data.data;
        const isEmpty = !d.labels || d.labels.length === 0;
        showEmpty('mealTypeBarChart', isEmpty);
        if (isEmpty) return;

        destroyChart('mealTypeBarChart');
        charts['mealTypeBarChart'] = new Chart(
            document.getElementById('mealTypeBarChart'),
            {
                type: 'bar',
                data: {
                    labels:   d.labels,
                    datasets: [
                        {
                            label:           'Items Sold',
                            data:            d.total_items,
                            backgroundColor: 'rgba(54,185,204,0.8)',
                            borderColor:     '#36b9cc',
                            borderWidth:     1,
                            borderRadius:    4,
                            yAxisID:         'y',
                        },
                        {
                            label:           'Revenue (£)',
                            data:            d.total_revenue,
                            backgroundColor: 'rgba(246,194,62,0.8)',
                            borderColor:     '#f6c23e',
                            borderWidth:     1,
                            borderRadius:    4,
                            yAxisID:         'y1',
                            type:            'line',
                            tension:         0.4,
                            fill:            false,
                            pointRadius:     4,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.datasetIndex === 0
                                    ? ` Items: ${ctx.parsed.y}`
                                    : ` Revenue: £${parseFloat(ctx.parsed.y).toFixed(2)}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            position:    'left',
                            ticks:       { precision: 0 },
                            grid:        { color: 'rgba(0,0,0,0.05)' },
                            title:       { display: true, text: 'Items Sold' }
                        },
                        y1: {
                            beginAtZero: true,
                            position:    'right',
                            grid:        { drawOnChartArea: false },
                            ticks:       { callback: val => '£' + val },
                            title:       { display: true, text: 'Revenue (£)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            }
        );
    } catch (error) {
        handleError(error);
    }
}

// ===== Order Status Doughnut Chart =====
async function loadOrderStatusReport() {
    try {
        const res = await axios.get('/client/get/meal-report/order-status', { params: buildParams() });
        if (res.data.status !== 'success') return;

        const d       = res.data.data;
        const isEmpty = !d.labels || d.labels.length === 0;
        showEmpty('orderStatusPieChart', isEmpty);
        if (isEmpty) return;

        destroyChart('orderStatusPieChart');
        charts['orderStatusPieChart'] = new Chart(
            document.getElementById('orderStatusPieChart'),
            {
                type: 'doughnut',
                data: {
                    labels:   d.labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                    datasets: [{
                        data:            d.counts,
                        backgroundColor: PIE_COLORS.slice(0, d.labels.length),
                        borderWidth:     2,
                        borderColor:     '#fff',
                    }]
                },
                options: {
                    responsive:  true,
                    cutout:      '60%',
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed} orders`
                            }
                        }
                    }
                }
            }
        );
    } catch (error) {
        handleError(error);
    }
}

// ===== Revenue Line Chart =====
async function loadRevenueReport() {
    try {
        const res = await axios.get('/client/get/meal-report/daily-revenue', { params: buildParams() });
        if (res.data.status !== 'success') return;

        const d       = res.data.data;
        const isEmpty = !d.labels || d.labels.length === 0;
        showEmpty('revenueLineChart', isEmpty);
        if (isEmpty) return;

        destroyChart('revenueLineChart');
        charts['revenueLineChart'] = new Chart(
            document.getElementById('revenueLineChart'),
            {
                type: 'line',
                data: {
                    labels:   d.labels,
                    datasets: [
                        {
                            label:           'Revenue (£)',
                            data:            d.revenue,
                            borderColor:     '#4e73df',
                            backgroundColor: 'rgba(78,115,223,0.1)',
                            borderWidth:     2,
                            tension:         0.4,
                            fill:            true,
                            pointRadius:     4,
                            pointBackgroundColor: '#4e73df',
                            yAxisID:         'y',
                        },
                        {
                            label:           'Items Sold',
                            data:            d.items_sold,
                            borderColor:     '#1cc88a',
                            backgroundColor: 'rgba(28,200,138,0.1)',
                            borderWidth:     2,
                            tension:         0.4,
                            fill:            true,
                            pointRadius:     4,
                            pointBackgroundColor: '#1cc88a',
                            yAxisID:         'y1',
                        },
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.datasetIndex === 0
                                    ? ` Revenue: £${parseFloat(ctx.parsed.y).toFixed(2)}`
                                    : ` Items Sold: ${ctx.parsed.y}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            position:    'left',
                            ticks:       { callback: val => '£' + val },
                            grid:        { color: 'rgba(0,0,0,0.05)' },
                            title:       { display: true, text: 'Revenue (£)' }
                        },
                        y1: {
                            beginAtZero: true,
                            position:    'right',
                            grid:        { drawOnChartArea: false },
                            ticks:       { precision: 0 },
                            title:       { display: true, text: 'Items Sold' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            }
        );
    } catch (error) {
        handleError(error);
    }
}
</script>

<style>
#kpiCards .card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
#kpiCards .card:hover {
    transform:  translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
}
#periodBtns .btn.active {
    background-color: #0d6efd;
    color:            white;
    border-color:     #0d6efd;
}
[id$="Empty"] {
    color: #adb5bd;
}
.card-header h6 {
    font-size: 0.95rem;
}
</style>