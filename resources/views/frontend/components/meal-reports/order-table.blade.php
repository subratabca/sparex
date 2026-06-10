<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">
            <i class="mdi mdi-table me-2 text-primary"></i>Order Details
        </h6>
        <span class="badge bg-primary rounded-pill" id="order-table-period">Monthly</span>
    </div>
    <div class="card-body">
        <div id="orderTableEmpty" class="text-center text-muted py-5" style="display:none;">
            <i class="mdi mdi-table mdi-48px opacity-25"></i>
            <p class="mt-2">No orders for selected period</p>
        </div>
        <div id="orderTableWrapper" style="display:none;">
            <div class="table-responsive">
                <table id="orderDataTable" class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Sl</th>
                            <th>Order No</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Subtotal</th>
                            <th>Tax</th>
                            <th>Service Fee</th>
                            <th>Delivery Fee</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody id="orderTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

