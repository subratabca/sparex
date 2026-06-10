<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="mdi mdi-silverware-fork-knife me-2 text-info"></i>Orders by Meal Type
        </h6>
        <span class="badge bg-info rounded-pill" id="mealtype-chart-period">Monthly</span>
    </div>
    <div class="card-body">
        <div id="mealTypeBarChartEmpty" class="text-center text-muted py-5" style="display:none;">
            <i class="mdi mdi-silverware-fork-knife mdi-48px opacity-25"></i>
            <p class="mt-2">No data for selected period</p>
        </div>
        <canvas id="mealTypeBarChart"></canvas>
    </div>
</div>

