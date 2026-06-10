<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">Delivery Order Details</h4>
            <p class="text-muted mb-0" id="header-tracking">-</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success d-none" id="headerPaymentBtn">
                <i class="mdi mdi-credit-card-outline me-1"></i> Make Payment
            </button>
            <a href="{{ route('admin.delivery.paypents') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Rider Fee KPI Strip -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                         style="width:48px;height:48px;min-width:48px;">
                        <i class="mdi mdi-truck-delivery-outline mdi-24px text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-1">Delivery Charge</p>
                        <h5 class="fw-bold mb-0 text-primary" id="kpi-delivery-charge">£0.00</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10"
                         style="width:48px;height:48px;min-width:48px;">
                        <i class="mdi mdi-percent-outline mdi-24px text-warning"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-1">Rider Platform <span id="kpi-rider-fee-rate"></span></p>
                        <h5 class="fw-bold mb-0 text-warning" id="kpi-rider-fee">£0.00</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25"
                         style="width:48px;height:48px;min-width:48px;">
                        <i class="mdi mdi-cash-check mdi-24px text-white"></i>
                    </div>
                    <div>
                        <p class="mb-1 small opacity-75">Payable to Rider</p>
                        <h5 class="fw-bold mb-0" id="kpi-payable">£0.00</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10"
                         style="width:48px;height:48px;min-width:48px;">
                        <i class="mdi mdi-cash-multiple mdi-24px text-info"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-1">Payment Status</p>
                        <h5 class="fw-bold mb-0"><span class="badge bg-secondary" id="kpi-payment-status">-</span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row g-4 mb-4">

        <!-- Delivery Person -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="mdi mdi-bike-fast me-2"></i>Delivery Person</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img id="delivery-person-image" src="/upload/no_image.jpg"
                             class="rounded-circle me-3"
                             style="width:56px;height:56px;object-fit:cover;">
                        <div>
                            <h6 class="mb-0" id="delivery-person-name">-</h6>
                            <small class="text-muted" id="delivery-person-phone">-</small>
                        </div>
                    </div>
                    <p class="mb-1 small"><i class="mdi mdi-email-outline me-1 text-muted"></i><span id="delivery-person-email">-</span></p>
                    <p class="mb-0 small"><i class="mdi mdi-map-marker-outline me-1 text-muted"></i><span id="delivery-person-address">-</span></p>
                </div>
            </div>
        </div>

        <!-- Customer -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="mdi mdi-account-outline me-2"></i>Customer</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img id="customer-image" src="/upload/no_image.jpg"
                             class="rounded-circle me-3"
                             style="width:56px;height:56px;object-fit:cover;">
                        <div>
                            <h6 class="mb-0" id="customer-name">-</h6>
                            <small class="text-muted" id="customer-phone">-</small>
                        </div>
                    </div>
                    <p class="mb-1 small"><i class="mdi mdi-email-outline me-1 text-muted"></i><span id="customer-email">-</span></p>
                    <p class="mb-1 small"><i class="mdi mdi-truck-outline me-1 text-muted"></i>Ship to: <span id="shipping-name">-</span></p>
                    <p class="mb-0 small"><i class="mdi mdi-map-marker-outline me-1 text-muted"></i><span id="shipping-address">-</span></p>
                </div>
            </div>
        </div>

        <!-- Restaurant -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="mdi mdi-store-outline me-2"></i>Restaurant</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img id="restaurant-image" src="/upload/no_image.jpg"
                             class="rounded-circle me-3"
                             style="width:56px;height:56px;object-fit:cover;">
                        <div>
                            <h6 class="mb-0" id="restaurant-name">-</h6>
                            <small class="text-muted" id="restaurant-phone">-</small>
                        </div>
                    </div>
                    <p class="mb-1 small"><i class="mdi mdi-email-outline me-1 text-muted"></i><span id="restaurant-email">-</span></p>
                    <p class="mb-0 small"><i class="mdi mdi-map-marker-outline me-1 text-muted"></i><span id="restaurant-address">-</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- Left: Delivery Info + Order Items -->
        <div class="col-lg-8">

            <!-- Delivery Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="mdi mdi-information-outline me-2 text-primary"></i>Delivery Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Tracking Number</small>
                            <span class="fw-semibold" id="tracking-number">-</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Delivery Date</small>
                            <span class="fw-semibold" id="delivery-date">-</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Delivered Time</small>
                            <span class="fw-semibold text-success" id="delivered-time">-</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Delivery Status</small>
                            <span class="badge" id="delivery-status-badge">-</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Distance</small>
                            <span class="fw-semibold" id="distance">-</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Meal Type</small>
                            <span class="fw-semibold" id="meal-type">-</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Delivery Type</small>
                            <span class="fw-semibold" id="delivery-type">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="mdi mdi-food-outline me-2"></i>Order Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Meal Time</th>
                                </tr>
                            </thead>
                            <tbody id="order-items-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Order Summary + Status History -->
        <div class="col-lg-4">

            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="mdi mdi-receipt-text-outline me-2"></i>Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Order Number</small>
                        <span class="fw-semibold" id="order-number">-</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Invoice No</small>
                        <span class="fw-semibold" id="invoice-no">-</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Order Date</small>
                        <span class="fw-semibold" id="order-date">-</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span><span id="subtotal">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax <span id="tax-rate-label"></span></span><span id="tax">-</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee</span><span id="delivery-fee">-</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span><span class="text-primary" id="total">-</span>
                    </div>
                </div>
            </div>

            <!-- Status History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="mdi mdi-history me-2"></i>Status History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline" id="status-history">
                        <div class="text-center text-muted py-3">No status history available</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backend.components.meal-payment-history.delivery.credit-modal')

<script>
let currentLedgerData = null;

document.addEventListener('DOMContentLoaded', function () {
    loadDeliveryOrderDetails();

    // Header payment button → open modal
    document.getElementById('headerPaymentBtn').addEventListener('click', function () {
        if (!currentLedgerData) return;
        $('#ledgerID').val(currentLedgerData.id);
        $('#deiveryChargeID').val(currentLedgerData.payable_amount_raw);
        $('#paymentModal').modal('show');
    });
});

async function loadDeliveryOrderDetails() {
    try {
        showLoader();

        const segments = window.location.pathname.split('/');
        const ledgerId = segments[segments.length - 1];

        const res = await axios.get(`/admin/get/meal-delivery/payment/details/${ledgerId}`);

        if (res.status === 200 && res.data.status === 'success') {
            const d = res.data.data;
            currentLedgerData = d.delivery_info;

            renderDeliveryInfo(d.delivery_info);
            renderDeliveryPerson(d.delivery_person);
            renderCustomer(d.customer_details);
            renderRestaurant(d.restaurant_details);
            renderOrderSummary(d.order_summary);
            renderOrderItems(d.order_items);
            renderStatusHistory(d.status_history);

        } else {
            errorToast(res.data.message || 'Failed to load order details.');
        }
    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
    }
}

// ===== Delivery Info + KPIs =====
function renderDeliveryInfo(info) {
    document.getElementById('header-tracking').textContent =
        info.tracking_number ? `Tracking: ${info.tracking_number}` : '-';
    document.getElementById('tracking-number').textContent = info.tracking_number || '-';
    document.getElementById('delivery-date').textContent   = info.delivery_date || '-';
    document.getElementById('delivered-time').textContent = info.delivered_at || 'Not delivered yet';

    const badge = document.getElementById('delivery-status-badge');
    badge.textContent = info.delivery_status_label || '-';
    badge.className   = 'badge ' + getDeliveryBadgeClass(info.delivery_status);

    document.getElementById('distance').textContent =
        info.distance_km ? `${info.distance_km} km` : '-';

    // KPIs
    document.getElementById('kpi-delivery-charge').textContent = '£' + info.delivery_charge;
    document.getElementById('kpi-rider-fee').textContent       = '£' + info.rider_platform_fee;
    document.getElementById('kpi-rider-fee-rate').textContent  = `(${info.rider_platform_fee_rate}%)`;
    document.getElementById('kpi-payable').textContent         = '£' + info.payable_amount;

    const statusBadge = document.getElementById('kpi-payment-status');
    statusBadge.textContent = toTitleCase(info.payment_status || 'Unknown');
    statusBadge.className    = 'badge ' + (info.payment_status === 'paid' ? 'bg-success' : 'bg-danger');

    // Payment button — show only when delivered AND due
    const payBtn = document.getElementById('headerPaymentBtn');
    if (info.is_delivered && info.payment_status === 'due') {
        payBtn.classList.remove('d-none');
    } else {
        payBtn.classList.add('d-none');
    }
}

// ===== Delivery Person =====
function renderDeliveryPerson(person) {
    if (!person) return;
    document.getElementById('delivery-person-image').src   = getImageUrl('delivery', person.image);
    document.getElementById('delivery-person-name').textContent  = person.name   || '-';
    document.getElementById('delivery-person-phone').textContent = person.mobile || '-';
    document.getElementById('delivery-person-email').textContent = person.email  || '-';
    document.getElementById('delivery-person-address').textContent =
        [person.address1, person.address2, person.zip_code].filter(Boolean).join(', ') || '-';
}

// ===== Customer =====
function renderCustomer(customer) {
    if (!customer) return;
    document.getElementById('customer-image').src   = getImageUrl('customer', customer.image);
    document.getElementById('customer-name').textContent  = customer.name   || '-';
    document.getElementById('customer-phone').textContent = customer.mobile || '-';
    document.getElementById('customer-email').textContent = customer.email  || '-';
    document.getElementById('shipping-name').textContent  =
        customer.shipping_name ? toTitleCase(customer.shipping_name) : '-';
    document.getElementById('shipping-address').textContent =
        [customer.address1, customer.address2, customer.zip_code].filter(Boolean).join(', ') || '-';
}

// ===== Restaurant =====
function renderRestaurant(client) {
    if (!client) return;
    document.getElementById('restaurant-image').src   = getImageUrl('client', client.image);
    document.getElementById('restaurant-name').textContent  = client.name   || '-';
    document.getElementById('restaurant-phone').textContent = client.mobile || '-';
    document.getElementById('restaurant-email').textContent = client.email  || '-';
    document.getElementById('restaurant-address').textContent =
        [client.address1, client.address2, client.zip_code].filter(Boolean).join(', ') || '-';
}

// ===== Order Summary =====
function renderOrderSummary(summary) {
    document.getElementById('order-number').textContent  = summary.order_number || '-';
    document.getElementById('invoice-no').textContent    = summary.invoice_no   || '-';
    document.getElementById('order-date').textContent    = summary.order_date   || '-';
    document.getElementById('meal-type').textContent     =
        summary.meal_type ? toTitleCase(summary.meal_type) : '-';
    document.getElementById('delivery-type').textContent =
        summary.delivery_type ? toTitleCase(summary.delivery_type) : '-';

    document.getElementById('subtotal').textContent     = '£' + summary.subtotal;
    document.getElementById('tax').textContent          = '£' + summary.tax;
    document.getElementById('tax-rate-label').textContent = `(${summary.tax_rate}%)`;
    document.getElementById('delivery-fee').textContent = '£' + summary.delivery_fee;
    document.getElementById('total').textContent        = '£' + summary.total;
}

// ===== Order Items =====
function renderOrderItems(items) {
    const tbody = document.getElementById('order-items-table');
    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">No items found</td></tr>`;
        return;
    }

    items.forEach(item => {
        const img = getImageUrl('product', item.product_image);
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="${img}" class="rounded"
                             style="width:44px;height:44px;object-fit:cover;"
                             alt="${item.product_name}">
                        <strong>${toTitleCase(item.product_name || '-')}</strong>
                    </div>
                </td>
                <td>${item.quantity}</td>
                <td>£${item.unit_price}</td>
                <td>£${item.total_price}</td>
                <td>${item.meal_time ? formatMealTime(item.meal_time) : '-'}</td>
            </tr>`);
    });
}

// ===== Status History =====
function renderStatusHistory(history) {
    const container = document.getElementById('status-history');
    container.innerHTML = '';

    if (!history || history.length === 0) {
        container.innerHTML = `<div class="text-center text-muted py-3">No status history available</div>`;
        return;
    }

    history.forEach(h => {
        container.insertAdjacentHTML('beforeend', `
            <div class="timeline-item mb-3">
                <div class="d-flex">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content ms-3">
                        <small class="text-muted d-block">${h.created_at}</small>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge ${getDeliveryBadgeClass(h.delivery_status)}">
                                ${h.status_label}
                            </span>
                            <small class="text-muted">by ${h.updated_by_label || '-'}</small>
                        </div>
                        ${h.notes ? `<small class="d-block text-muted mt-1">${h.notes}</small>` : ''}
                        ${h.pick_up_at ? `<small class="d-block text-muted mt-1">Pickup: ${h.pick_up_at}</small>` : ''}
                    </div>
                </div>
            </div>`);
    });
}

// ===== Helpers =====
function getImageUrl(type, filename) {
    if (!filename) return '/upload/no_image.jpg';
    const basePaths = {
        delivery: '/upload/delivery-profile/medium/',
        customer: '/upload/customer-profile/medium/',
        client:   '/upload/client-profile/medium/',
        product:  '/upload/product/small/',
    };
    return (basePaths[type] || '/upload/') + filename;
}

function formatMealTime(timeString) {
    if (!timeString) return '-';
    if (/am|pm/i.test(timeString)) return timeString;
    const parts = timeString.split(':');
    if (parts.length < 2) return timeString;
    const hour   = parseInt(parts[0], 10);
    const minute = parseInt(parts[1], 10);
    if (isNaN(hour) || isNaN(minute)) return timeString;
    const period      = hour >= 12 ? 'pm' : 'am';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minute.toString().padStart(2, '0')} ${period}`;
}

function getDeliveryBadgeClass(status) {
    const map = {
        delivered:        'bg-success',
        arrived:          'bg-primary',
        on_the_way:       'bg-info',
        picked_up:        'bg-info',
        ready_for_pickup: 'bg-warning',
        preparing:        'bg-warning',
        accept_order:     'bg-secondary',
        pending:          'bg-secondary',
        cancelled:        'bg-dark',
    };
    return map[status] || 'bg-secondary';
}

function toTitleCase(str) {
    if (!str) return '';
    return str.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
}
</script>

<style>
.timeline {
    position:     relative;
    padding-left: 20px;
}
.timeline::before {
    content:          '';
    position:         absolute;
    left:             0;
    top:              0;
    bottom:           0;
    width:            2px;
    background-color: #dee2e6;
}
.timeline-item { position: relative; }
.timeline-marker {
    position:         absolute;
    left:             -24px;
    top:              6px;
    width:            10px;
    height:           10px;
    border-radius:    50%;
    background-color: #0d6efd;
    border:           2px solid #fff;
    box-shadow:       0 0 0 3px rgba(13,110,253,0.1);
}
</style>