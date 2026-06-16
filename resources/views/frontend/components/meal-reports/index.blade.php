@extends('frontend.components.dashboard.dashboard-master')

@section('dashboard-content')

    @include('frontend.components.meal-reports.filters')
    @include('frontend.components.meal-reports.kpi-cards')

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            @include('frontend.components.meal-reports.order-chart')
        </div>
        <div class="col-lg-4">
            @include('frontend.components.meal-reports.payment-chart')
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            @include('frontend.components.meal-reports.order-table')
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            @include('frontend.components.meal-reports.meal-type-chart')
        </div>
        <div class="col-lg-4">
            @include('frontend.components.meal-reports.order-status-chart')
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            @include('frontend.components.meal-reports.meal-type-table')
        </div>
    </div>

    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            @include('frontend.components.meal-reports.spending-chart')
        </div>
    </div>

@endsection

{{-- ===== Styles ===== --}}
@push('styles')
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
@endpush

{{-- ===== Scripts ===== --}}
@push('scripts')
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

// ===== Load All =====
async function loadAllReports() {
    try {
        showLoader();
        updatePeriodBadges();
        await Promise.all([
            loadSummary(),
            loadOrderReport(),
            loadPaymentStatusReport(),
            loadOrderStatusReport(),
            loadMealTypeReport(),
            loadSpendingReport(),
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
        const res = await axios.get('/get/meal-report/summary', { params: buildParams() });
        if (res.data.status !== 'success') return;
        const d = res.data.data;
        document.getElementById('kpi-total-orders').textContent  = d.total_orders;
        document.getElementById('kpi-total-spent').textContent   = '£' + d.total_spent;
        document.getElementById('kpi-total-items').textContent   = d.total_items;
        document.getElementById('kpi-paid-count').textContent    = d.paid_count;
        document.getElementById('kpi-due-count').textContent     = d.due_count;
        document.getElementById('kpi-completed').textContent     = d.completed_count;
        document.getElementById('kpi-service-fee').textContent   = '£' + d.total_service_fee;
        document.getElementById('kpi-delivery-fee').textContent  = '£' + d.total_delivery_fee;
    } catch (error) {
        handleError(error);
    }
}

// ===== Order Bar Chart + Table =====
async function loadOrderReport() {
    try {
        const res = await axios.get('/get/meal-report/orders', { params: buildParams() });
        if (res.data.status !== 'success') return;

        const d       = res.data.data;
        const isEmpty = !d.chart.labels || d.chart.labels.length === 0;
        showEmpty('orderBarChart', isEmpty);

        if (!isEmpty) {
            destroyChart('orderBarChart');
            charts['orderBarChart'] = new Chart(
                document.getElementById('orderBarChart'),
                {
                    type: 'bar',
                    data: {
                        labels:   d.chart.labels,
                        datasets: [
                            {
                                label:           'Total Orders',
                                data:            d.chart.total_orders,
                                backgroundColor: 'rgba(78,115,223,0.8)',
                                borderColor:     '#4e73df',
                                borderWidth:     1,
                                borderRadius:    4,
                            },
                            {
                                label:           'Paid',
                                data:            d.chart.paid_count,
                                backgroundColor: 'rgba(28,200,138,0.8)',
                                borderColor:     '#1cc88a',
                                borderWidth:     1,
                                borderRadius:    4,
                            },
                            {
                                label:           'Due',
                                data:            d.chart.due_count,
                                backgroundColor: 'rgba(231,74,59,0.8)',
                                borderColor:     '#e74a3b',
                                borderWidth:     1,
                                borderRadius:    4,
                            },
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'top' } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                            x: { grid: { display: false } }
                        }
                    }
                }
            );
        }

        renderOrderTable(d.table);

    } catch (error) {
        handleError(error);
    }
}

// ===== Order Table =====
function renderOrderTable(rows) {
    const tbody = document.getElementById('orderTableBody');
    const empty = document.getElementById('orderTableEmpty');
    const table = document.getElementById('orderTableWrapper');

    if (!rows || rows.length === 0) {
        empty.style.display = 'block';
        table.style.display = 'none';
        return;
    }

    empty.style.display = 'none';
    table.style.display = 'block';
    tbody.innerHTML     = '';

    rows.forEach((row, i) => {
        const statusBadge = {
            pending:   'bg-warning',
            completed: 'bg-success',
            cancelled: 'bg-danger',
        }[row.status] || 'bg-secondary';

        const paymentBadge = {
            paid: 'bg-success',
            due:  'bg-danger',
        }[row.payment_status] || 'bg-secondary';

        tbody.innerHTML += `
            <tr>
                <td>${i + 1}</td>
                <td>
                    <a href="/meal-order/details/${row.id}"
                       class="text-primary text-decoration-underline"
                       target="_blank">${row.order_number}</a>
                </td>
                <td>${row.invoice_no}</td>
                <td>${row.order_date}</td>
                <td>£${row.subtotal}</td>
                <td>£${row.tax}</td>
                <td>£${row.service_fee}</td>
                <td>£${row.delivery_fee}</td>
                <td><strong>£${row.payable_amount}</strong></td>
                <td>
                    <span class="badge ${statusBadge}">
                        ${row.status.charAt(0).toUpperCase() + row.status.slice(1)}
                    </span>
                </td>
                <td>
                    <span class="badge ${paymentBadge}">
                        ${row.payment_status.charAt(0).toUpperCase() + row.payment_status.slice(1)}
                    </span>
                </td>
                <td>
                    <span class="badge bg-info">
                        ${row.delivery_type.replace('_', ' ')}
                    </span>
                </td>
            </tr>`;
    });

    if ($.fn.DataTable.isDataTable('#orderDataTable')) {
        $('#orderDataTable').DataTable().destroy();
    }
    $('#orderDataTable').DataTable({
        paging:     true,
        searching:  true,
        ordering:   true,
        pageLength: 10,
        order:      [[3, 'desc']],
        columnDefs: [{ targets: [0], orderable: false }]
    });
}

// ===== Payment Status Pie =====
async function loadPaymentStatusReport() {
    try {
        const res = await axios.get('/get/meal-report/payment-status', { params: buildParams() });
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

// ===== Order Status Pie =====
async function loadOrderStatusReport() {
    try {
        const res = await axios.get('/get/meal-report/order-status', { params: buildParams() });
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
                    responsive: true,
                    cutout:     '60%',
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

// ===== Meal Type Chart + Table =====
async function loadMealTypeReport() {
    try {
        const res = await axios.get('/get/meal-report/meal-type', { params: buildParams() });
        if (res.data.status !== 'success') return;

        const d       = res.data.data;
        const isEmpty = !d.chart.labels || d.chart.labels.length === 0;
        showEmpty('mealTypeBarChart', isEmpty);

        if (!isEmpty) {
            destroyChart('mealTypeBarChart');
            charts['mealTypeBarChart'] = new Chart(
                document.getElementById('mealTypeBarChart'),
                {
                    type: 'bar',
                    data: {
                        labels:   d.chart.labels,
                        datasets: [
                            {
                                label:           'Items Ordered',
                                data:            d.chart.total_items,
                                backgroundColor: 'rgba(54,185,204,0.8)',
                                borderColor:     '#36b9cc',
                                borderWidth:     1,
                                borderRadius:    4,
                                yAxisID:         'y',
                            },
                            {
                                label:           'Amount Spent (£)',
                                data:            d.chart.total_spent,
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
                        plugins: { legend: { position: 'top' } },
                        scales: {
                            y:  { beginAtZero: true, position: 'left',  ticks: { precision: 0 }, title: { display: true, text: 'Items' } },
                            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: v => '£' + v }, title: { display: true, text: 'Spent (£)' } },
                            x:  { grid: { display: false } }
                        }
                    }
                }
            );
        }

        renderMealTypeTable(d.table);

    } catch (error) {
        handleError(error);
    }
}

// ===== Meal Type Table =====
function renderMealTypeTable(rows) {
    const tbody = document.getElementById('mealTypeTableBody');
    const empty = document.getElementById('mealTypeTableEmpty');
    const table = document.getElementById('mealTypeTableWrapper');

    if (!rows || rows.length === 0) {
        empty.style.display = 'block';
        table.style.display = 'none';
        return;
    }

    empty.style.display = 'none';
    table.style.display = 'block';
    tbody.innerHTML     = '';

    rows.forEach((row, i) => {
        tbody.innerHTML += `
            <tr>
                <td>${i + 1}</td>
                <td>${row.meal_type}</td>
                <td>${row.total_items}</td>
                <td>${row.total_orders}</td>
                <td>£${row.total_spent}</td>
            </tr>`;
    });
}

// ===== Spending Line Chart =====
async function loadSpendingReport() {
    try {
        const res = await axios.get('/get/meal-report/daily-spending', { params: buildParams() });
        if (res.data.status !== 'success') return;

        const d       = res.data.data;
        const isEmpty = !d.labels || d.labels.length === 0;
        showEmpty('spendingLineChart', isEmpty);
        if (isEmpty) return;

        destroyChart('spendingLineChart');
        charts['spendingLineChart'] = new Chart(
            document.getElementById('spendingLineChart'),
            {
                type: 'line',
                data: {
                    labels:   d.labels,
                    datasets: [
                        {
                            label:                'Total Spent (£)',
                            data:                 d.total_spent,
                            borderColor:          '#4e73df',
                            backgroundColor:      'rgba(78,115,223,0.1)',
                            borderWidth:          2,
                            tension:              0.4,
                            fill:                 true,
                            pointRadius:          4,
                            pointBackgroundColor: '#4e73df',
                            yAxisID:              'y',
                        },
                        {
                            label:                'Subtotal (£)',
                            data:                 d.subtotal,
                            borderColor:          '#1cc88a',
                            backgroundColor:      'rgba(28,200,138,0.05)',
                            borderWidth:          2,
                            tension:              0.4,
                            fill:                 false,
                            pointRadius:          3,
                            pointBackgroundColor: '#1cc88a',
                            yAxisID:              'y',
                        },
                        {
                            label:                'Orders',
                            data:                 d.total_orders,
                            borderColor:          '#f6c23e',
                            backgroundColor:      'rgba(246,194,62,0.05)',
                            borderWidth:          2,
                            tension:              0.4,
                            fill:                 false,
                            pointRadius:          3,
                            pointBackgroundColor: '#f6c23e',
                            yAxisID:              'y1',
                        },
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            position:    'left',
                            ticks:       { callback: v => '£' + v },
                            grid:        { color: 'rgba(0,0,0,0.05)' },
                            title:       { display: true, text: 'Amount (£)' }
                        },
                        y1: {
                            beginAtZero: true,
                            position:    'right',
                            grid:        { drawOnChartArea: false },
                            ticks:       { precision: 0 },
                            title:       { display: true, text: 'Orders' }
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
@endpush