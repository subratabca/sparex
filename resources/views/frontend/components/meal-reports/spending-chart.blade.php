<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="mdi mdi-trending-up me-2 text-danger"></i>Spending Trend
        </h6>
        <span class="badge bg-danger rounded-pill" id="spending-chart-period">Monthly</span>
    </div>
    <div class="card-body">
        <div id="spendingLineChartEmpty" class="text-center text-muted py-5" style="display:none;">
            <i class="mdi mdi-trending-up mdi-48px opacity-25"></i>
            <p class="mt-2">No data for selected period</p>
        </div>
        <canvas id="spendingLineChart"></canvas>
    </div>
</div>
