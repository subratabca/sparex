<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="mdi mdi-chart-bar me-2 text-info"></i>Client Payment — Payable vs Paid vs Due
        </h6>
        <span class="badge bg-info rounded-pill" id="client-chart-period">Monthly</span>
    </div>
    <div class="card-body">
        <div id="clientChartEmpty" class="text-center text-muted py-5" style="display:none;">
            <i class="mdi mdi-chart-bar mdi-48px opacity-25"></i>
            <p class="mt-2">No data for selected period</p>
        </div>
        <canvas id="clientBarChart"></canvas>
    </div>
</div>