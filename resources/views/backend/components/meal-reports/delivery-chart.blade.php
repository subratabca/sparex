<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="mdi mdi-chart-donut me-2 text-warning"></i>Delivery Payment Status
        </h6>
        <span class="badge bg-warning rounded-pill" id="delivery-chart-period">Monthly</span>
    </div>
    <div class="card-body d-flex flex-column align-items-center justify-content-center">
        <div id="deliveryChartEmpty" class="text-center text-muted py-5" style="display:none;">
            <i class="mdi mdi-chart-donut mdi-48px opacity-25"></i>
            <p class="mt-2">No data for selected period</p>
        </div>
        <canvas id="deliveryPieChart"></canvas>
    </div>
</div>