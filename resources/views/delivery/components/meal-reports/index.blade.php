<div class="container-fluid px-lg-4">

    {{-- Header + Period Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="mdi mdi-chart-box-outline me-2 text-primary"></i>My Delivery Reports</h5>
                    <p class="text-muted mb-0" id="period-badge">This Month</p>
                </div>
                <div class="d-flex flex-wrap gap-2" id="period-buttons">
                    <button class="btn btn-outline-primary btn-sm rounded-pill period-btn" data-period="daily">Daily</button>
                    <button class="btn btn-outline-primary btn-sm rounded-pill period-btn" data-period="weekly">Weekly</button>
                    <button class="btn btn-primary btn-sm rounded-pill period-btn active" data-period="monthly">Monthly</button>
                    <button class="btn btn-outline-primary btn-sm rounded-pill period-btn" data-period="yearly">Yearly</button>
                    <button class="btn btn-outline-primary btn-sm rounded-pill period-btn" data-period="custom">Custom</button>
                </div>
            </div>

            {{-- Custom date range --}}
            <div class="row g-2 mt-3" id="custom-range" style="display:none;">
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" id="start-date" class="form-control form-control-sm">
                </div>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" id="end-date" class="form-control form-control-sm">
                </div>
                <div class="col-sm-4 col-md-2 d-flex align-items-end">
                    <button id="apply-custom" class="btn btn-primary btn-sm w-100">Apply</button>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary"><i class="mdi mdi-truck-fast-outline"></i></div>
                    <div>
                        <p class="text-muted small mb-1">Total Deliveries</p>
                        <h4 class="fw-bold mb-0" id="kpi-total">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-success bg-opacity-10 text-success"><i class="mdi mdi-check-circle-outline"></i></div>
                    <div>
                        <p class="text-muted small mb-1">Delivered</p>
                        <h4 class="fw-bold mb-0" id="kpi-delivered">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-info bg-opacity-10 text-info"><i class="mdi mdi-cash-multiple"></i></div>
                    <div>
                        <p class="text-muted small mb-1">Total Delivery Charge</p>
                        <h4 class="fw-bold mb-0" id="kpi-delivery-charge">£0.00</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning"><i class="mdi mdi-percent-outline"></i></div>
                    <div>
                        <p class="text-muted small mb-1">Total Platform Fee</p>
                        <h4 class="fw-bold mb-0" id="kpi-platform-fee">£0.00</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-white bg-opacity-25 text-white"><i class="mdi mdi-cash-check"></i></div>
                    <div>
                        <p class="small mb-1 opacity-75">Total Earnings</p>
                        <h4 class="fw-bold mb-0" id="kpi-earnings">£0.00</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon bg-danger bg-opacity-10 text-danger"><i class="mdi mdi-clock-alert-outline"></i></div>
                    <div>
                        <p class="text-muted small mb-1">Due Payment</p>
                        <h4 class="fw-bold mb-0" id="kpi-due">£0.00</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Order Report Bar Chart --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0"><h6 class="fw-bold mb-0">Deliveries Overview</h6></div>
                <div class="card-body">
                    <div id="order-empty" class="text-center text-muted py-5" style="display:none;">No delivery data for this period.</div>
                    <canvas id="orderChart" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- Delivery Status Pie --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0"><h6 class="fw-bold mb-0">Delivery Status</h6></div>
                <div class="card-body">
                    <div id="status-empty" class="text-center text-muted py-5" style="display:none;">No data.</div>
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Earnings Line --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0"><h6 class="fw-bold mb-0">Earnings Trend</h6></div>
                <div class="card-body">
                    <div id="earnings-empty" class="text-center text-muted py-5" style="display:none;">No earnings data.</div>
                    <canvas id="earningsChart" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- Payment Status Doughnut --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0"><h6 class="fw-bold mb-0">Payment Status</h6></div>
                <div class="card-body">
                    <div id="payment-empty" class="text-center text-muted py-5" style="display:none;">No data.</div>
                    <canvas id="paymentChart" height="200"></canvas>
                </div>
            </div>
        </div>

        {{-- Tabular Report --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0"><h6 class="fw-bold mb-0">Detailed Report</h6></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Period</th>
                                <th class="text-center">Invoice No</th>
                                <th class="text-center">Total Deliveries</th>
                                <th class="text-center">Delivered</th>
                                <th class="text-center">Cancelled</th>
                                <th class="text-end">Delivery Charge</th>
                                <th class="text-end">Platform Fee</th>
                                <th class="text-end">Earnings</th>
                            </tr>
                        </thead>
                        <tbody id="report-table-body">
                            <tr><td colspan="8" class="text-center text-muted py-3">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.kpi-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.period-btn.active { color: #fff; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let currentPeriod = 'monthly';
let orderChart, statusChart, paymentChart, earningsChart;

const PERIOD_LABELS = {
    daily: 'Today', weekly: 'Last 7 Days', monthly: 'This Month',
    yearly: 'This Year', custom: 'Custom Range'
};

const PALETTE = ['#0d6efd','#198754','#ffc107','#dc3545','#6f42c1','#0dcaf0','#fd7e14','#20c997','#6c757d'];

document.addEventListener('DOMContentLoaded', () => {
    bindPeriodButtons();
    loadAllReports();
});

function bindPeriodButtons() {
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const period = this.dataset.period;
            document.querySelectorAll('.period-btn').forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-primary');
            });
            this.classList.add('active', 'btn-primary');
            this.classList.remove('btn-outline-primary');

            currentPeriod = period;
            document.getElementById('custom-range').style.display = (period === 'custom') ? 'flex' : 'none';
            document.getElementById('period-badge').textContent = PERIOD_LABELS[period];

            if (period !== 'custom') loadAllReports();
        });
    });

    document.getElementById('apply-custom').addEventListener('click', () => {
        const s = document.getElementById('start-date').value;
        const e = document.getElementById('end-date').value;
        if (!s || !e) return errorToast('Please select both dates.');
        if (s > e)    return errorToast('Start date must be before end date.');
        loadAllReports();
    });
}

function buildParams() {
    const p = { period: currentPeriod };
    if (currentPeriod === 'custom') {
        p.start_date = document.getElementById('start-date').value;
        p.end_date   = document.getElementById('end-date').value;
    }
    return p;
}

async function loadAllReports() {
    try {
        showLoader();
        const params = buildParams();
        await Promise.all([
            loadSummary(params),
            loadOrderReport(params),
            loadStatusReport(params),
            loadPaymentReport(params),
            loadEarningsReport(params),
        ]);
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function destroyChart(c) { if (c) c.destroy(); }
function fmt(v) { return new Intl.NumberFormat('en-GB', { style:'currency', currency:'GBP' }).format(v || 0); }

/* ===== Summary ===== */
async function loadSummary(params) {
    const res = await axios.get('/rider/get/meal-report/summary', { params });
    if (res.data.status === 'success') {
        const d = res.data.data;
        document.getElementById('kpi-total').textContent           = d.total_deliveries;
        document.getElementById('kpi-delivered').textContent       = d.delivered;
        document.getElementById('kpi-delivery-charge').textContent = fmt(d.total_delivery_charge);
        document.getElementById('kpi-platform-fee').textContent    = fmt(d.total_platform_fee);
        document.getElementById('kpi-earnings').textContent        = fmt(d.total_earnings);
        document.getElementById('kpi-due').textContent             = fmt(d.due_earnings);
    }
}

/* ===== Order Report (bar + table) ===== */
async function loadOrderReport(params) {
    const res = await axios.get('/rider/get/meal-report/orders', { params });
    const empty = document.getElementById('order-empty');
    const canvas = document.getElementById('orderChart');

    if (res.data.status === 'success' && res.data.data.labels.length > 0) {
        empty.style.display = 'none';
        canvas.style.display = 'block';
        const d = res.data.data;

        destroyChart(orderChart);
        orderChart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [
                    { label: 'Delivered', data: d.delivered, backgroundColor: '#198754', borderRadius: 4 },
                    { label: 'Cancelled', data: d.cancelled, backgroundColor: '#dc3545', borderRadius: 4 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { position: 'top' } }
            }
        });

        renderTable(d.table);
    } else {
        empty.style.display = 'block';
        canvas.style.display = 'none';
        destroyChart(orderChart);
        renderTable([]);
    }
}

function renderTable(rows) {
    const tbody = document.getElementById('report-table-body');
    if (!rows || rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3">No data for this period.</td></tr>`;
        return;
    }
    tbody.innerHTML = rows.map(r => `
        <tr>
            <td>${r.period}</td>
            <td class="text-center">${r.invoice_no}</td>
            <td class="text-center">${r.total}</td>
            <td class="text-center"><span class="badge bg-success">${r.delivered}</span></td>
            <td class="text-center"><span class="badge bg-danger">${r.cancelled}</span></td>
            <td class="text-end">${fmt(r.delivery_charge)}</td>
            <td class="text-end text-warning fw-semibold">${fmt(r.platform_fee)}</td>
            <td class="text-end fw-semibold">${fmt(r.earnings)}</td>
        </tr>`).join('');
}

/* ===== Delivery Status (pie) ===== */
async function loadStatusReport(params) {
    const res = await axios.get('/rider/get/meal-report/order-status', { params });
    const empty = document.getElementById('status-empty');
    const canvas = document.getElementById('statusChart');

    if (res.data.status === 'success' && res.data.data.length > 0) {
        empty.style.display = 'none';
        canvas.style.display = 'block';
        const d = res.data.data;

        destroyChart(statusChart);
        statusChart = new Chart(canvas.getContext('2d'), {
            type: 'pie',
            data: {
                labels: d.map(x => x.label),
                datasets: [{ data: d.map(x => x.total), backgroundColor: PALETTE }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    } else {
        empty.style.display = 'block';
        canvas.style.display = 'none';
        destroyChart(statusChart);
    }
}

/* ===== Payment Status (doughnut) ===== */
async function loadPaymentReport(params) {
    const res = await axios.get('/rider/get/meal-report/payment-status', { params });
    const empty = document.getElementById('payment-empty');
    const canvas = document.getElementById('paymentChart');

    if (res.data.status === 'success' && res.data.data.length > 0) {
        empty.style.display = 'none';
        canvas.style.display = 'block';
        const d = res.data.data;
        const colorMap = { paid: '#198754', due: '#dc3545', cancelled: '#6c757d' };

        destroyChart(paymentChart);
        paymentChart = new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: d.map(x => x.label),
                datasets: [{ data: d.map(x => x.total), backgroundColor: d.map(x => colorMap[x.status] || '#0dcaf0') }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => {
                        const item = d[ctx.dataIndex];
                        return `${item.label}: ${item.total} (${fmt(item.amount)})`;
                    }}}
                }
            }
        });
    } else {
        empty.style.display = 'block';
        canvas.style.display = 'none';
        destroyChart(paymentChart);
    }
}

/* ===== Earnings (line) ===== */
async function loadEarningsReport(params) {
    const res = await axios.get('/rider/get/meal-report/earnings', { params });
    const empty = document.getElementById('earnings-empty');
    const canvas = document.getElementById('earningsChart');

    if (res.data.status === 'success' && res.data.data.labels.length > 0) {
        empty.style.display = 'none';
        canvas.style.display = 'block';
        const d = res.data.data;

        destroyChart(earningsChart);
        earningsChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: [
                    { label: 'Net Payable', data: d.payable, borderColor: '#198754', backgroundColor: 'rgba(25,135,84,0.1)', fill: true, tension: 0.4 },
                    { label: 'Gross Charge', data: d.gross, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.08)', fill: true, tension: 0.4 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { callback: v => '£' + v } } },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${fmt(ctx.parsed.y)}` } }
                }
            }
        });
    } else {
        empty.style.display = 'block';
        canvas.style.display = 'none';
        destroyChart(earningsChart);
    }
}
</script>
