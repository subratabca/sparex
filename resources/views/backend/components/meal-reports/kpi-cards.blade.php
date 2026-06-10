<div class="row g-3 mb-4" id="kpiCards">

    {{-- Row 1 — Order stats --}}
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-cart-outline mdi-24px text-primary"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Total Orders</p>
                    <h4 class="fw-bold mb-0 text-primary" id="kpi-total-orders">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-cash-multiple mdi-24px text-success"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Total Revenue</p>
                    <h4 class="fw-bold mb-0 text-success" id="kpi-total-revenue">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-check-circle-outline mdi-24px text-info"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Completed</p>
                    <h4 class="fw-bold mb-0 text-info" id="kpi-completed-orders">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-close-circle-outline mdi-24px text-danger"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Cancelled</p>
                    <h4 class="fw-bold mb-0 text-danger" id="kpi-cancelled-orders">-</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2 — Payment stats --}}
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-credit-card-check-outline mdi-24px text-success"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Paid Orders</p>
                    <h4 class="fw-bold mb-0 text-success" id="kpi-paid-orders">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-clock-alert-outline mdi-24px text-warning"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Due Orders</p>
                    <h4 class="fw-bold mb-0 text-warning" id="kpi-due-orders">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-account-cash-outline mdi-24px text-danger"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Client Due</p>
                    <h4 class="fw-bold mb-0 text-danger" id="kpi-client-due">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-truck-delivery-outline mdi-24px text-danger"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Delivery Due</p>
                    <h4 class="fw-bold mb-0 text-danger" id="kpi-delivery-due">-</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3 — Platform fee & Net Profit --}}
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-account-star-outline mdi-24px text-primary"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Service Fee
                        <small class="text-muted d-block">(From Customers)</small>
                    </p>
                    <h4 class="fw-bold mb-0 text-primary" id="kpi-service-fee">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-store-outline mdi-24px text-info"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Client Platform Fee
                        <small class="text-muted d-block">(From Restaurants)</small>
                    </p>
                    <h4 class="fw-bold mb-0 text-info" id="kpi-client-platform-fee">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-bike-fast mdi-24px text-warning"></i>
                </div>
                <div>
                    <p class="text-muted small mb-1">Rider Platform Fee
                        <small class="text-muted d-block">(From Riders)</small>
                    </p>
                    <h4 class="fw-bold mb-0 text-warning" id="kpi-rider-platform-fee">-</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Net Profit — highlighted card --}}
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-success text-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25"
                     style="width:50px;height:50px;min-width:50px;">
                    <i class="mdi mdi-trending-up mdi-24px text-white"></i>
                </div>
                <div>
                    <p class="mb-1 small opacity-75">Net Profit
                        <small class="d-block opacity-75">(Service + Client + Rider)</small>
                    </p>
                    <h4 class="fw-bold mb-0" id="kpi-net-profit">-</h4>
                </div>
            </div>
        </div>
    </div>

</div>