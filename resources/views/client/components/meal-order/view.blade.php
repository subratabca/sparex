<div class="container">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="mdi mdi-credit-card-outline me-2"></i>Client Payment Details
            </h5>
            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body">
            <div id="mainContent">

                <!-- Order Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="mb-1" id="mealPlanTitle">Client Payment Details11</h4>
                        <p class="mb-0 text-muted" id="orderNumberText"></p>
                    </div>
                </div>

                <!-- Customer Info & Payment Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm hover-card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="mdi mdi-account-circle me-2"></i>Customer Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img id="customerImage"
                                             src="/upload/no_image.jpg"
                                             alt="Customer"
                                             class="rounded-circle"
                                             style="width:70px;height:70px;object-fit:cover;">
                                    </div>
                                    <div>
                                        <h5 class="mb-1" id="customerName">-</h5>
                                        <p class="mb-1 text-muted" id="customerEmail">Email: -</p>
                                        <p class="mb-0 text-muted" id="customerPhone">Phone: -</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm hover-card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="mdi mdi-cash-multiple me-2"></i>Payment Status
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <span class="badge payment-status-badge fs-6 mb-3 bg-secondary"
                                      id="paymentStatusBadge">-</span>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="mb-1 text-muted">Payable</p>
                                        <h4 class="text-primary" id="payableAmount">£0.00</h4>
                                    </div>
                                    <div class="col-4">
                                        <p class="mb-1 text-muted">Paid</p>
                                        <h4 class="text-success" id="paidAmount">£0.00</h4>
                                    </div>
                                    <div class="col-4">
                                        <p class="mb-1 text-muted">Balance</p>
                                        <h4 class="text-danger" id="balanceAmount">£0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Order Items -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="mdi mdi-silverware-fork-knife me-2"></i>Order Items by Date
                                </h6>
                                <small class="text-muted">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    Click on delivery status badge to update
                                </small>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="mealOrderAccordion"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div class="col-lg-4">
                        <div class="border rounded p-3 shadow-sm mb-4" id="price-summary">
                            <h5 class="mb-3">
                                <i class="mdi mdi-calculator me-2"></i>Price Breakdown
                            </h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Subtotal</span>
                                    <span id="summarySubtotal">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Tax <span id="taxRateLabel">(0%)</span></span>
                                    <span id="summaryTax">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                                    <span>
                                        <span class="text-warning fw-semibold">
                                            Platform Fee <span id="platformFeeRateLabel">(0%)</span>
                                        </span>
                                        <small class="d-block text-muted">(Admin deduction)</small>
                                    </span>
                                    <span id="summaryPlatformFee"
                                          class="text-warning fw-semibold">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 fw-bold border-top">
                                    <span>Total</span>
                                    <span id="summaryTotal" class="text-primary">£0.00</span>
                                </li>
                            </ul>
                            <div class="alert alert-warning mt-3 mb-0 py-2 px-3 small">
                                <i class="mdi mdi-information-outline me-1"></i>
                                Platform fee is deducted by admin from gross payable amount.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="alert alert-danger mt-3" style="display:none;">
                <i class="mdi mdi-alert-circle me-2"></i>
                <span id="errorText">An error occurred while loading payment details.</span>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Status Modal -->
<div class="modal fade" id="deliveryStatusModal" tabindex="-1"
     aria-labelledby="deliveryStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryStatusModalLabel">Update Delivery Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="updateMealOrderItemId">
                <input type="hidden" id="updateOrderId">
                <input type="hidden" id="currentDeliveryStatus">

                <div class="mb-3">
                    <label class="form-label">Order Information</label>
                    <div class="form-control bg-light p-3" id="currentItemInfo" style="min-height:60px;">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-information-outline me-2 text-primary"></i>
                            <span>Loading item details...</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deliveryStatusSelect" class="form-label fw-semibold">
                        <i class="mdi mdi-truck-delivery-outline me-1"></i>Delivery Status
                    </label>
                    <select class="form-select" id="deliveryStatusSelect" required>
                        <option value="">Select Status</option>
                    </select>
                </div>

                <div class="mb-3" id="pickupTimeContainer" style="display:none;">
                    <label for="pickup_time" class="form-label fw-semibold">
                        <i class="mdi mdi-clock-outline me-1"></i>Pickup Time
                    </label>
                    <input type="datetime-local" class="form-control" id="pickup_time">
                    <small class="text-muted">Set the time when items will be ready for pickup</small>
                </div>

                <div class="mb-3">
                    <label for="deliveryNotes" class="form-label fw-semibold">
                        <i class="mdi mdi-note-text-outline me-1"></i>Notes (Optional)
                    </label>
                    <textarea class="form-control" id="deliveryNotes" rows="3"
                              placeholder="Add any notes about the delivery status update..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveDeliveryStatusBtn">
                    <i class="mdi mdi-check-circle-outline me-1"></i>Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
    await loadClientPaymentDetails();
    document.getElementById('saveDeliveryStatusBtn').addEventListener('click', updateDeliveryStatus);

    document.getElementById('deliveryStatusModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('deliveryStatusForm')?.reset();
        document.getElementById('currentItemInfo').innerHTML = `
            <div class="d-flex align-items-center">
                <i class="mdi mdi-information-outline me-2 text-primary"></i>
                <span>Loading item details...</span>
            </div>`;
        document.getElementById('pickupTimeContainer').style.display = 'none';
        document.getElementById('pickup_time').required = false;
    });
});

// ===== Load Payment Details =====
async function loadClientPaymentDetails() {
    try {
        showLoader();
        const orderId  = window.location.pathname.split('/').pop();
        const response = await axios.get(`/restaurant/get/meal-order/details/${orderId}`);

        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;

            window.deliveryStatuses  = data.delivery_statuses || {};
            window.currentOrderData  = data;

            document.getElementById('mealPlanTitle').textContent =
                `Client Payment — Order #${data.order.order_number}`;
            document.getElementById('orderNumberText').textContent =
                `Invoice: ${data.order.invoice_no} • ${data.summary.total_items} items`;

            updateCustomerInfo(data.customer);
            updatePaymentInfo(data.client_meal_order);
            updateSummaryStats(data.summary);
            renderMealOrderItems(data.items, data.dates, data.items_with_time);

        } else {
            showPageError(response.data.message || 'Failed to load payment details.');
        }
    } catch (error) {
        handleError(error); // ← global config.js
        showPageError('An error occurred while loading payment details.');
    } finally {
        hideLoader();
    }
}

// ===== Update Customer Info =====
function updateCustomerInfo(customer) {
    if (!customer) {
        document.getElementById('customerName').textContent  = 'Unknown Customer';
        document.getElementById('customerEmail').textContent = 'Email: N/A';
        document.getElementById('customerPhone').textContent = 'Phone: N/A';
        return;
    }
    document.getElementById('customerName').textContent  = customer.name  || '-';
    document.getElementById('customerEmail').textContent = 'Email: ' + (customer.email  || 'N/A');
    document.getElementById('customerPhone').textContent = 'Phone: ' + (customer.mobile || 'N/A');

    if (customer.image) {
        document.getElementById('customerImage').src =
            `/upload/customer-profile/small/${customer.image}`;
    }
}

// ===== Update Payment Info =====
function updatePaymentInfo(clientMealOrder) {
    const status = clientMealOrder?.payment_status || 'unknown';
    const badge  = document.getElementById('paymentStatusBadge');

    let badgeClass = 'bg-secondary';
    switch (status.toLowerCase()) {
        case 'paid':       badgeClass = 'bg-success'; break;
        case 'due':        badgeClass = 'bg-danger';  break;
        case 'pending':    badgeClass = 'bg-warning'; break;
        case 'refunded':   badgeClass = 'bg-info';    break;
        case 'failed':     badgeClass = 'bg-dark';    break;
        case 'processing': badgeClass = 'bg-primary'; break;
    }

    badge.className   = `badge payment-status-badge ${badgeClass}`;
    badge.textContent = toTitleCase(status);

    const payable  = parseFloat(clientMealOrder?.payable_amount || 0);
    const paid     = parseFloat(clientMealOrder?.paid_amount    || 0);
    const balance  = payable - paid;

    document.getElementById('payableAmount').textContent  = formatCurrency(payable);
    document.getElementById('paidAmount').textContent     = formatCurrency(paid);
    document.getElementById('balanceAmount').textContent  = formatCurrency(balance < 0 ? 0 : balance);
}

// ===== Update Summary Stats =====
function updateSummaryStats(summary) {
    if (!summary) return;

    document.getElementById('summarySubtotal').textContent = formatCurrency(summary.subtotal     || 0);
    document.getElementById('summaryTax').textContent      = formatCurrency(summary.tax          || 0);
    document.getElementById('summaryTotal').textContent    = formatCurrency(summary.total_amount || 0);

    // Tax rate label
    document.getElementById('taxRateLabel').textContent =
        `(${summary.tax_rate || 0}%)`;

    // Platform fee with rate
    const platformFee  = parseFloat(summary.platform_fee || 0);
    document.getElementById('platformFeeRateLabel').textContent =
        `(${summary.platform_fee_rate || 0}%)`;
    document.getElementById('summaryPlatformFee').textContent =
        '- ' + formatCurrency(platformFee);
}

// ===== Render Meal Order Items =====
function renderMealOrderItems(items, dates, itemsWithTime = []) {
    const container = document.getElementById('mealOrderAccordion');

    if (!items || Object.keys(items).length === 0) {
        container.innerHTML = '<div class="alert alert-info">No items found for this payment.</div>';
        return;
    }

    let html = '';

    // Sort dates ascending
    [...dates].sort((a, b) => new Date(a) - new Date(b)).forEach((date, index) => {
        const dayItems   = items[date];
        const collapseId = `mealDay${index}`;

        const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('en-GB', {
            weekday: 'long',
            month:   'short',
            day:     'numeric',
            year:    'numeric'
        });

        const mealTypes  = Object.keys(dayItems);
        let mealTypeHtml = '';

        mealTypes.forEach(type => {
            const typeTitle    = toTitleCase(type);
            const group        = dayItems[type];
            const itemsList    = group.items || [];
            const typeTotal    = group.total_price || 0;
            const mealTime     = group.meal_time || getMealTimeForType(date, type, itemsWithTime);
            const deliveryStatus      = group.delivery_status      || 'pending';
            const deliveryStatusLabel = group.delivery_status_label || toTitleCase(deliveryStatus);
            const deliveryBadgeClass  = getDeliveryBadgeClass(deliveryStatus);
            const deliveryPerson      = group.delivery_person_name || 'Not Assigned';
            const itemIds             = itemsList.map(i => i.id).join(',');

            let itemsHtml = '';
            itemsList.forEach(item => {
                const productName = toTitleCase(item.product_name || '');
                const img = item.product_image
                    ? `/upload/product/small/${item.product_image}`
                    : '/upload/no_image.jpg';

                itemsHtml += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${img}" alt="${productName}"
                                     class="rounded"
                                     style="width:60px;height:60px;object-fit:cover;">
                                <div>
                                    <strong>${productName}</strong><br>
                                    <small class="text-muted">
                                        ${formatCurrency(item.unit_price || 0)} × ${item.quantity || 0}
                                    </small><br>
                                    <small class="text-info">
                                        Provider: ${item.client_name || 'Unknown'}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <strong>${formatCurrency(item.total_price || 0)}</strong>
                            </div>
                        </div>
                    </li>`;
            });

            mealTypeHtml += `
                <div class="card mb-3 border-0">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 d-flex align-items-center flex-wrap gap-2">
                                    <span>
                                        ${typeTitle}
                                        (${itemsList.length} item${itemsList.length !== 1 ? 's' : ''})
                                    </span>
                                    ${mealTime ? `
                                        <span class="text-muted d-flex align-items-center gap-1">
                                            <span class="mx-1">|</span>
                                            <i class="mdi mdi-clock-outline"></i>
                                            <span>Delivery time: ${formatMealTime(mealTime)}</span>
                                        </span>` : ''}
                                </h6>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-primary fw-bold">${formatCurrency(typeTotal)}</span>
                                <button type="button"
                                        class="btn btn-sm p-0 border-0 bg-transparent update-delivery-btn-group"
                                        data-item-ids="${itemIds}"
                                        data-order-id="${window.location.pathname.split('/').pop()}"
                                        data-group-name="${typeTitle}"
                                        data-meal-date="${date}"
                                        data-meal-time="${mealTime || ''}"
                                        data-meal-type-id="${group.meal_type_id}"
                                        data-current-status="${deliveryStatus}"
                                        data-current-status-label="${deliveryStatusLabel}">
                                    <span class="badge ${deliveryBadgeClass} px-3 py-2"
                                          style="cursor:pointer;">
                                        ${deliveryStatusLabel}
                                    </span>
                                </button>
                            </div>
                        </div>
                        ${deliveryPerson !== 'Not Assigned' ? `
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="mdi mdi-account-circle me-1"></i>
                                    Delivery Person: ${deliveryPerson}
                                </small>
                            </div>` : ''}
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">${itemsHtml}</ul>
                    </div>
                </div>`;
        });

        const dateTotal = mealTypes.reduce((t, type) => t + (dayItems[type].total_price || 0), 0);

        html += `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#${collapseId}">
                        <div class="d-flex justify-content-between align-items-center w-100 me-2">
                            <span>${formattedDate}</span>
                            <span class="badge bg-primary rounded-pill">
                                ${formatCurrency(dateTotal)}
                            </span>
                        </div>
                    </button>
                </h2>
                <div id="${collapseId}"
                     class="accordion-collapse collapse ${index === 0 ? 'show' : ''}"
                     data-bs-parent="#mealOrderAccordion">
                    <div class="accordion-body p-3">${mealTypeHtml}</div>
                </div>
            </div>`;
    });

    container.innerHTML = html;
    attachDeliveryStatusGroupEventListeners();
}

// ===== Delivery Status Listeners =====
function attachDeliveryStatusGroupEventListeners() {
    document.querySelectorAll('.update-delivery-btn-group').forEach(btn => {
        btn.addEventListener('click', function () {
            checkAndOpenDeliveryModal(
                this.dataset.itemIds,
                this.dataset.orderId,
                this.dataset.groupName,
                this.dataset.mealDate,
                this.dataset.mealTypeId,
                this.dataset.currentStatus,
                this.dataset.currentStatusLabel,
                this.dataset.mealTime
            );
        });
    });
}

// ===== Status Options =====
function populateStatusOptions(currentStatus) {
    const select = document.getElementById('deliveryStatusSelect');

    const transitions = {
        pending:         [{ value: 'accept_order', label: 'Accept Order' }, { value: 'cancelled', label: 'Cancelled' }],
        accept_order:    [{ value: 'cancelled',    label: 'Cancelled'    }],
        accept_delivery: [{ value: 'preparing',    label: 'Preparing'    }],
        preparing:       [{ value: 'ready_for_pickup', label: 'Ready for Pickup' }],
        ready_for_pickup: [],
    };

    const options = transitions[currentStatus] || [];

    if (options.length === 0) {
        select.disabled  = true;
        select.innerHTML = '<option value="">No further status changes allowed</option>';
        return;
    }

    select.disabled  = false;
    select.innerHTML = '<option value="">Select Status</option>';
    options.forEach(o => {
        const opt    = document.createElement('option');
        opt.value    = o.value;
        opt.textContent = o.label;
        select.appendChild(opt);
    });

    // Default-select the primary (forward) next status — it's always listed first
    // (pending → Accept Order, accept_delivery → Preparing, preparing → Ready for Pickup).
    // The client can still change it (e.g. to Cancelled). Don't auto-select when the only
    // option is Cancelled (e.g. accept_order, where the client is waiting for a rider).
    if (options.length > 0 && options[0].value !== 'cancelled') {
        select.value = options[0].value;
    }
}

// ===== Check & Open Modal =====
async function checkAndOpenDeliveryModal(itemIds, orderId, groupName, mealDate, mealTypeId, currentStatus, currentStatusLabel, mealTime) {
    try {
        const response = await axios.post(`/restaurant/check/delivery-acceptance/${orderId}`, {
            meal_date:    mealDate,
            meal_type_id: mealTypeId,
        });

        if (response.data.status === 'success') {
            const data = response.data.data;

            const nextMap  = { pending: 'accept_order', accept_delivery: 'preparing', preparing: 'ready_for_pickup' };
            const nextStat = nextMap[currentStatus];

            if (data.blocked_statuses?.includes(nextStat)) {
                errorToast(data.blocked_reason || 'Cannot update status. No delivery person has accepted yet.');
                if (!data.delivery_person_assigned) {
                    Swal.fire({
                        title:             'Delivery Person Needed',
                        text:              'A delivery person must accept this order before you can proceed.',
                        icon:              'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor:'#3085d6',
                    });
                }
                return;
            }

            openDeliveryStatusModalForGroup(
                itemIds, orderId, groupName, mealDate, mealTypeId, currentStatus, currentStatusLabel
            );
        }
    } catch (error) {
        // Still open modal — backend will validate
        openDeliveryStatusModalForGroup(
            itemIds, orderId, groupName, mealDate, mealTypeId, currentStatus, currentStatusLabel, mealTime
        );
    }
}

// ===== Open Modal =====
// Latest delivery cutoff (meal_date + meal_time) so submit can validate the pickup time
let modalDeliveryDeadline = null;

// Date -> "YYYY-MM-DDTHH:MM" in local time (for <input type="datetime-local">)
function toLocalInput(d) {
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function openDeliveryStatusModalForGroup(itemIds, orderId, groupName, mealDate, mealTypeId, currentStatus, currentStatusLabel, mealTime) {
    document.getElementById('updateMealOrderItemId').value  = itemIds;
    document.getElementById('updateOrderId').value          = orderId;
    document.getElementById('currentDeliveryStatus').value  = currentStatus;

    const formattedDate = new Date(mealDate + 'T00:00:00').toLocaleDateString('en-GB', {
        weekday: 'long', month: 'short', day: 'numeric', year: 'numeric'
    });

    document.getElementById('currentItemInfo').innerHTML = `
        <div class="d-flex align-items-center">
            <i class="mdi mdi-package-variant-closed me-2 text-primary"></i>
            <div>
                <strong>${groupName}</strong><br>
                <small class="text-muted">
                    ${formattedDate} • ${itemIds.split(',').length} items
                </small>
            </div>
        </div>`;

    const pickupContainer = document.getElementById('pickupTimeContainer');
    const pickupInput     = document.getElementById('pickup_time');
    modalDeliveryDeadline = null;

    if (currentStatus === 'preparing') {
        pickupContainer.style.display = 'block';
        pickupInput.required          = true;

        // Delivery time = meal_date + meal_time (from the order item). Default & latest-allowed
        // pickup is 30 min before that — the client may pick earlier but not later.
        // Resolve meal_time: value passed from the button, else look it up by meal_type_id in the order data.
        let resolvedMealTime = mealTime || '';
        if (!resolvedMealTime && window.currentOrderData && Array.isArray(window.currentOrderData.items_with_time)) {
            const wt = window.currentOrderData.items_with_time;
            let m = wt.find(it => it.meal_date === mealDate && String(it.meal_type_id) === String(mealTypeId));
            if (!m) m = wt.find(it => String(it.meal_type_id) === String(mealTypeId));
            resolvedMealTime = (m && m.meal_time) ? m.meal_time : '';
        }
        const deliveryDT = (mealDate && resolvedMealTime) ? new Date(`${mealDate}T${resolvedMealTime}`) : null;

        if (deliveryDT && !isNaN(deliveryDT.getTime())) {
            const cutoffDT = new Date(deliveryDT.getTime() - 30 * 60 * 1000); // 30 min before delivery
            modalDeliveryDeadline = cutoffDT;

            pickupInput.value = toLocalInput(cutoffDT);
            pickupInput.min   = `${mealDate}T00:00`;      // keep it on the delivery date
            pickupInput.max   = toLocalInput(cutoffDT);   // cannot exceed 30 min before delivery
        } else {
            const now = new Date();
            now.setHours(now.getHours() + 1);
            pickupInput.value = toLocalInput(now);
            pickupInput.removeAttribute('min');
            pickupInput.removeAttribute('max');
        }
    } else {
        pickupContainer.style.display = 'none';
        pickupInput.required          = false;
        pickupInput.removeAttribute('min');
        pickupInput.removeAttribute('max');
    }

    populateStatusOptions(currentStatus);
    new bootstrap.Modal(document.getElementById('deliveryStatusModal')).show();
}

// ===== Update Delivery Status =====
async function updateDeliveryStatus() {
    const itemIds       = document.getElementById('updateMealOrderItemId').value;
    const orderId       = document.getElementById('updateOrderId').value;
    const status        = document.getElementById('deliveryStatusSelect').value;
    const notes         = document.getElementById('deliveryNotes').value;
    const currentStatus = document.getElementById('currentDeliveryStatus').value;
    const pickupTime    = document.getElementById('pickup_time').value;

    if (!status) { errorToast('Please select a delivery status.'); return; }
    if (currentStatus === 'preparing' && status === 'ready_for_pickup') {
        if (!pickupTime) {
            errorToast('Please provide a pickup time.');
            return;
        }
        // Pickup must be at least 30 minutes before the delivery time
        if (modalDeliveryDeadline && new Date(pickupTime) > modalDeliveryDeadline) {
            errorToast('Pickup time must be at least 30 minutes before the delivery time.');
            return;
        }
    }

    try {
        showLoader();
        const btn = document.getElementById('saveDeliveryStatusBtn');
        const el  = document.querySelector(`.update-delivery-btn-group[data-item-ids="${itemIds}"]`);

        const requestData = {
            meal_order_item_id: itemIds,
            delivery_status:    status,
            notes:              notes,
            meal_date:          el?.dataset.mealDate,
            meal_type_id:       el?.dataset.mealTypeId,
        };
        if (pickupTime) requestData.pickup_time = pickupTime;

        const response = await axios.post(`/restaurant/update/delivery-status/${orderId}`, requestData);

        if (response.data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('deliveryStatusModal')).hide();
            successToast(response.data.message || 'Delivery status updated successfully.');
            setTimeout(() => loadClientPaymentDetails(), 1000);
        } else {
            errorToast(response.data.message || 'Failed to update delivery status.');
        }
    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
    }
}

// ===== Helpers =====
function getMealTimeForType(date, mealType, itemsWithTime) {
    if (!itemsWithTime || !Array.isArray(itemsWithTime)) return null;
    return itemsWithTime.find(
        item => item.meal_date === date &&
                (item.meal_type_name || 'Other') === mealType &&
                item.meal_time
    )?.meal_time || null;
}

function formatMealTime(timeString) {
    if (!timeString) return '';
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
        accept_delivery:  'bg-primary',
        accept_order:     'bg-secondary',
        pending:          'bg-secondary',
        cancelled:        'bg-dark',
    };
    return map[status] || 'bg-secondary';
}

function toTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency', currency: 'GBP'
    }).format(parseFloat(amount) || 0);
}

function showPageError(message) {
    document.getElementById('errorText').textContent       = message;
    document.getElementById('errorMessage').style.display = 'block';
}
</script>

<style>
.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color:            #0d6efd;
}
.hover-card:hover {
    transform:  translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.payment-status-badge {
    font-size:     0.9rem;
    padding:       0.5rem 1rem;
    border-radius: 20px;
}
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color:       white;
}
.modal-header .btn-close {
    filter: invert(1);
}
</style>