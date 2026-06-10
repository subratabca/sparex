<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-3 align-items-end">

            <!-- Period Buttons -->
            <div>
                <label class="form-label fw-semibold mb-2">
                    <i class="mdi mdi-calendar-range me-1"></i>Select Period:
                </label>
                <div class="d-flex flex-wrap gap-2" id="periodBtns">
                    <button class="btn btn-outline-primary rounded-pill px-3 active"
                            data-period="daily">Daily</button>
                    <button class="btn btn-outline-primary rounded-pill px-3"
                            data-period="weekly">Weekly</button>
                    <button class="btn btn-outline-primary rounded-pill px-3"
                            data-period="monthly">Monthly</button>
                    <button class="btn btn-outline-primary rounded-pill px-3"
                            data-period="yearly">Yearly</button>
                    <button class="btn btn-outline-primary rounded-pill px-3"
                            data-period="custom">Custom Range</button>
                </div>
            </div>

            <!-- Custom Date Range -->
            <div id="customDateRange" style="display:none;">
                <label class="form-label fw-semibold mb-2">Custom Range:</label>
                <div class="d-flex flex-wrap gap-2 align-items-end">
                    <div>
                        <label class="form-label small text-muted mb-1">Start Date</label>
                        <input type="date" id="startDate" class="form-control"
                               style="max-width:180px;">
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-1">End Date</label>
                        <input type="date" id="endDate" class="form-control"
                               style="max-width:180px;">
                    </div>
                    <button class="btn btn-primary rounded-pill px-4" id="applyCustomDate">
                        <i class="mdi mdi-magnify me-1"></i>Apply
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>