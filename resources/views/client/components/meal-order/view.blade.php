<div class="container">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-credit-card-outline me-2"></i>Client Payment Details</h5>
            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading payment details...</p>
            </div>

            <!-- Main Content -->
            <div id="mainContent">
                <!-- Order Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h4 class="mb-1" id="mealPlanTitle">Client Payment Details</h4>
                        <p class="mb-0 text-muted" id="orderNumberText"></p>
                    </div>
                    <div>
                        <span class="badge bg-success fs-6" id="orderStatusText"></span>
                    </div>
                </div>

                <!-- Client Info Card -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm hover-card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="mdi mdi-account-circle me-2"></i>Customer Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img id="customerImage" src="/upload/no_image.jpg" alt="Client" class="rounded-circle" style="width: 70px; height: 70px; object-fit: cover;">
                                    </div>
                                    <div>
                                        <h5 class="mb-1" id="customerName">Loading...</h5>
                                        <p class="mb-1 text-muted" id="customerEmail">Email: Loading...</p>
                                        <p class="mb-0 text-muted" id="customerPhone">Phone: Loading...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm hover-card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="mdi mdi-cash-multiple me-2"></i>Payment Status</h6>
                            </div>
                            <div class="card-body text-center">
                                <span class="badge payment-status-badge fs-6 mb-3" id="paymentStatusBadge">Loading...</span>
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
                    <!-- Order Items (Left) -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="mdi mdi-silverware-fork-knife me-2"></i>Order Items by Date</h6>
                                <small class="text-muted" id="deliveryStatusHelp">
                                    <i class="mdi mdi-information-outline me-1"></i>Click on delivery status badge to update
                                </small>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="mealOrderAccordion">
                                    <!-- Items will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary & Stats (Right) -->
                    <div class="col-lg-4">
                        <div class="border rounded p-3 shadow-sm mb-4" id="price-summary">
                            <h5 class="mb-3"><i class="mdi mdi-calculator me-2"></i>Price Breakdown</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    Subtotal: <span id="summarySubtotal">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0" id="deliveryFeeItem" style="display: none;">
                                    Delivery Fee: <span id="summaryDeliveryFee">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    Tax: <span id="summaryTax">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 fw-bold">
                                    Total: <span id="summaryTotal">£0.00</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Delivery Summary Card -->
                        <div class="border rounded p-3 shadow-sm mb-4" id="deliverySummary">
                            <h5 class="mb-3"><i class="mdi mdi-truck-delivery me-2"></i>Delivery Status Summary</h5>
                            <div id="deliveryStats" class="text-center py-3">
                                <p class="text-muted">Loading delivery information...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="alert alert-danger mt-3" style="display: none;">
                <i class="mdi mdi-alert-circle me-2"></i>
                <span id="errorText">An error occurred while loading payment details.</span>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Status Update Modal -->
<!-- Delivery Status Update Modal -->
<div class="modal fade" id="deliveryStatusModal" tabindex="-1" aria-labelledby="deliveryStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryStatusModalLabel">Update Delivery Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deliveryStatusForm">
                    <input type="hidden" id="updateMealOrderItemId">
                    <input type="hidden" id="updateOrderId">
                    <input type="hidden" id="currentDeliveryStatus">
                    
                    <div class="mb-3">
                        <label for="currentItemInfo" class="form-label">Order Information</label>
                        <div class="form-control bg-light p-3" id="currentItemInfo" style="min-height: 60px;">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-information-outline me-2 text-primary"></i>
                                <span>Loading item details...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery Status Selection -->
                    <div class="mb-3">
                        <label for="deliveryStatusSelect" class="form-label fw-semibold">
                            <i class="mdi mdi-truck-delivery-outline me-1"></i>Delivery Status
                        </label>
                        <select class="form-select" id="deliveryStatusSelect" required>
                            <option value="">Select Status</option>
                            <!-- Options will be populated dynamically based on current status -->
                        </select>
                    </div>
                    
                    <!-- Pickup Time Field (shown only when current status is "preparing") -->
                    <div class="mb-3" id="pickupTimeContainer" style="display: none;">
                        <label for="pickup_time" class="form-label fw-semibold">
                            <i class="mdi mdi-clock-outline me-1"></i>Pickup Time
                        </label>
                        <input type="datetime-local" class="form-control" id="pickup_time" 
                               placeholder="Select pickup time">
                        <small class="text-muted">Set the time when items will be ready for pickup</small>
                    </div>
                    
                    <!-- Notes Section -->
                    <div class="mb-3">
                        <label for="deliveryNotes" class="form-label fw-semibold">
                            <i class="mdi mdi-note-text-outline me-1"></i>Notes (Optional)
                        </label>
                        <textarea class="form-control" id="deliveryNotes" rows="3" placeholder="Add any notes about the delivery status update..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveDeliveryStatusBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="updateSpinner"></span>
                    <i class="mdi mdi-check-circle-outline me-1"></i>Update Status
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Same HTML structure above -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script>
// Toast notification functions
function successToast(message) {
    Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: "#28a745",
    }).showToast();
}

function errorToast(message) {
    Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: "#dc3545",
    }).showToast();
}

function showLoader() {
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('mainContent').style.display = 'none';
    document.getElementById('errorMessage').style.display = 'none';
}

function hideLoader() {
    document.getElementById('loadingSpinner').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
}

function handleError(error) {
    let message = "An unexpected error occurred.";
    if (error.response) {
        const { status, data } = error.response;
        switch (status) {
            case 500:
                message = data?.message || "Internal server error. Please try again later.";
                break;
            case 404:
                message = data?.message || "Order not found.";
                break;
            default:
                message = data?.message || "Something went wrong.";
        }
    } else if (error.request) {
        message = "No response from server. Please check your connection.";
    } else {
        message = error.message;
    }
    errorToast(message);
    
    // Show error message in UI
    const errorDiv = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    if (errorDiv && errorText) {
        errorText.textContent = message;
        errorDiv.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', async function() {
    await loadClientPaymentDetails();
});

async function loadClientPaymentDetails() {
    showLoader();
    try {
        const orderId = window.location.pathname.split('/').pop();
        const response = await axios.get(`/client/get/meal-order/details/${orderId}`);

        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;
            console.log('Data received:', data);
            
            // Store delivery statuses globally
            window.deliveryStatuses = data.delivery_statuses || {};
            window.currentOrderData = data;
            
            // Update header information
            document.getElementById('mealPlanTitle').textContent = `Client Payment - Order #${data.order.order_number}`;
            document.getElementById('orderNumberText').textContent = `Invoice: ${data.order.invoice_no} • ${data.summary.total_items} items`;
            document.getElementById('orderStatusText').textContent = data.order.status ? toTitleCase(data.order.status) : 'Unknown';

            updateCustomerInfo(data.customer);
            
            // Update payment information
            updatePaymentInfo(data.client_meal_order);
            
            // Update summary statistics
            updateSummaryStats(data.summary);
            
            // Update delivery summary
            updateDeliverySummary(data);
            
            // Render order items with delivery status
            renderMealOrderItems(data.items, data.dates, data.items_with_time);
            
            successToast('Payment details loaded successfully');
        } else {
            errorToast(response.data.message || 'Failed to load payment details');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function updateCustomerInfo(customer) {
    if (!customer) {
        document.getElementById('customerName').textContent = 'Unknown Customer';
        document.getElementById('customerEmail').textContent = `Email: N/A`;
        document.getElementById('customerPhone').textContent = `Phone: N/A`;
        return;
    }
    
    document.getElementById('customerName').textContent = customer.name || `${customer.firstName || ''} ${customer.lastName || ''}`.trim() || 'Unknown Customer';
    document.getElementById('customerEmail').textContent = `Email: ${customer.email || 'N/A'}`;
    document.getElementById('customerPhone').textContent = `Phone: ${customer.mobile || 'N/A'}`;
    
    if (customer.image) {
        document.getElementById('customerImage').src = `/upload/customer-profile/small/${customer.image}`;
    }
}

function updatePaymentInfo(clientMealOrder) {
    const paymentStatus = clientMealOrder?.payment_status || 'unknown';
    const badge = document.getElementById('paymentStatusBadge');
    
    // Set badge color based on payment status
    let badgeClass = 'bg-secondary';
    switch (paymentStatus.toLowerCase()) {
        case 'paid': badgeClass = 'bg-success'; break;
        case 'due': badgeClass = 'bg-danger'; break;
        case 'pending': badgeClass = 'bg-warning'; break;
        case 'refunded': badgeClass = 'bg-info'; break;
        case 'failed': badgeClass = 'bg-dark'; break;
        case 'processing': badgeClass = 'bg-primary'; break;
    }
    
    badge.className = `badge payment-status-badge ${badgeClass}`;
    badge.textContent = paymentStatus ? toTitleCase(paymentStatus) : 'Unknown';
    
    // Update amounts
    document.getElementById('payableAmount').textContent = formatCurrency(clientMealOrder?.payable_amount || 0);
    document.getElementById('paidAmount').textContent = formatCurrency(clientMealOrder?.paid_amount || 0);
    document.getElementById('balanceAmount').textContent = formatCurrency(
        (clientMealOrder?.payable_amount || 0) - (clientMealOrder?.paid_amount || 0)
    );
}

function updateSummaryStats(summary) {
    if (!summary) return;
    
    // Update price breakdown
    document.getElementById('summarySubtotal').textContent = formatCurrency(summary.subtotal || 0);
    document.getElementById('summaryTax').textContent = formatCurrency(summary.tax || 0);
    document.getElementById('summaryTotal').textContent = formatCurrency(summary.total_amount || 0);
    
    // Show/hide delivery fee
    const deliveryFeeItem = document.getElementById('deliveryFeeItem');
    const deliveryFee = parseFloat(summary.delivery_fee || 0);
    if (deliveryFee > 0) {
        deliveryFeeItem.style.display = 'flex';
        document.getElementById('summaryDeliveryFee').textContent = formatCurrency(deliveryFee);
    } else {
        deliveryFeeItem.style.display = 'none';
    }
}

function updateDeliverySummary(data) {
    const container = document.getElementById('deliveryStats');
    
    if (!data.items || Object.keys(data.items).length === 0) {
        container.innerHTML = '<p class="text-muted">No delivery information available.</p>';
        return;
    }
    
    // Count delivery statuses across all groups
    const statusCount = {};
    let totalItems = data.summary?.total_items || 0;
    
    // Count statuses from grouped items
    Object.values(data.items).forEach(dateItems => {
        Object.values(dateItems).forEach(group => {
            const status = group.delivery_status || 'pending';
            statusCount[status] = (statusCount[status] || 0) + 1;
        });
    });
    
    // Create HTML for status summary
    let html = `<div class="text-start mb-3">
                    <p class="mb-2"><strong>Total Groups:</strong> ${data.summary?.total_groups || 0}</p>
                    <p class="mb-2"><strong>Total Items:</strong> ${totalItems}</p>
                </div>`;
    
    // Add status badges
    html += '<div class="row g-2">';
    
    const statusOrder = ['delivered', 'arrived', 'on_the_way', 
                        'picked_up', 'ready_for_pickup', 'preparing', 
                        'accept_order', 'pending', 'cancelled'];
    
    statusOrder.forEach(status => {
        if (statusCount[status]) {
            const label = data.delivery_statuses?.[status] || toTitleCase(status);
            const count = statusCount[status];
            const percentage = totalItems > 0 ? ((count / totalItems) * 100).toFixed(1) : '0.0';
            const badgeClass = getDeliveryBadgeClass(status);
            
            html += `
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2">
                        <div class="d-flex align-items-center">
                            <span class="badge ${badgeClass} me-2">${count}</span>
                            <span>${label}</span>
                        </div>
                        <small class="text-muted">${percentage}%</small>
                    </div>
                </div>
            `;
        }
    });
    
    html += '</div>';
    
    container.innerHTML = html;
}

function renderMealOrderItems(items, dates, itemsWithTime = []) {
    const container = document.getElementById('mealOrderAccordion');
    
    if (!items || Object.keys(items).length === 0) {
        container.innerHTML = '<div class="alert alert-info">No items found for this payment.</div>';
        return;
    }
    
    let html = '';
    
    // Sort dates in ascending order
    dates.sort((a, b) => new Date(a) - new Date(b));
    
    dates.forEach((date, index) => {
        const dayItems = items[date];
        const collapseId = `mealDay${index}`;

        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        const mealTypes = Object.keys(dayItems);
        let mealTypeHtml = '';

        mealTypes.forEach(type => {
            const typeTitle = toTitleCase(type);
            const group = dayItems[type];
            const itemsList = group.items || [];
            
            const mealTime = group.meal_time || getMealTimeForType(date, type, itemsWithTime);
            const formattedMealTime = mealTime ? formatMealTime(mealTime) : '';
            
            const typeTotal = group.total_price || itemsList.reduce((sum, item) => sum + parseFloat(item.total_price || 0), 0);
            
            const deliveryStatus = group.delivery_status || 'pending';
            const deliveryStatusLabel = group.delivery_status_label || window.deliveryStatuses?.[deliveryStatus] || toTitleCase(deliveryStatus);
            const deliveryBadgeClass = getDeliveryBadgeClass(deliveryStatus);
            const deliveryPerson = group.delivery_person_name || 'Not Assigned';
            
            const itemIds = itemsList.map(item => item.id).join(',');

            mealTypeHtml += `
                <div class="card mb-3 border-0">
                    <div class="card-header bg-light-subtle">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 d-flex align-items-center flex-wrap gap-2">
                                    <span>${typeTitle} (${itemsList.length} item${itemsList.length !== 1 ? 's' : ''})</span>
                                    ${formattedMealTime ? `
                                        <span class="text-muted d-flex align-items-center gap-1">
                                            <span class="mx-2">||</span>
                                            <i class="mdi mdi-clock-outline"></i>
                                            <span>Delivery time: ${formattedMealTime}</span>
                                        </span>
                                    ` : ''}
                                </h6>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-primary fw-bold">${formatCurrency(typeTotal)}</span>
                                <button type="button" class="btn btn-sm p-0 border-0 bg-transparent update-delivery-btn-group" 
                                        data-item-ids="${itemIds}"
                                        data-order-id="${window.location.pathname.split('/').pop()}"
                                        data-group-name="${typeTitle}"
                                        data-meal-date="${date}"
                                        data-meal-type-id="${group.meal_type_id}"
                                        data-current-status="${deliveryStatus}"
                                        data-current-status-label="${deliveryStatusLabel}"
                                        title="Update delivery status for all ${itemsList.length} ${typeTitle} items">
                                    <span class="badge ${deliveryBadgeClass} fs-6 px-3 py-2" style="cursor: pointer;">
                                        ${deliveryStatusLabel}
                                    </span>
                                </button>
                            </div>
                        </div>
                        ${deliveryPerson !== 'Not Assigned' ? `
                            <div class="mt-2 d-flex align-items-center">
                                <small class="text-muted">
                                    <i class="mdi mdi-account-circle me-1"></i>
                                    Delivery Person: ${deliveryPerson}
                                </small>
                            </div>
                        ` : ''}
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
            `;

            itemsList.forEach(item => {
                const productName = toTitleCase(item.product_name || 'Unknown Product');
                const img = item.product_image ? `/upload/product/small/${item.product_image}` : '/upload/no_image.jpg';

                mealTypeHtml += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${img}" alt="${productName}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                <div>
                                    <strong>${productName}</strong><br>
                                    <small class="text-muted">${formatCurrency(item.unit_price || 0)} each × ${item.quantity || 0}</small><br>
                                    <small class="text-info">Provider: ${item.client_name || 'Unknown'}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <strong>${formatCurrency(item.total_price || 0)}</strong>
                            </div>
                        </div>
                    </li>
                `;
            });

            mealTypeHtml += `
                        </ul>
                    </div>
                </div>
            `;
        });

        const dateTotal = mealTypes.reduce((total, type) => {
            const group = dayItems[type];
            return total + (group.total_price || 0);
        }, 0);

        const block = `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <span>${formattedDate}</span>
                            <span class="badge bg-primary rounded-pill">${formatCurrency(dateTotal)}</span>
                        </div>
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#mealOrderAccordion">
                    <div class="accordion-body p-3">${mealTypeHtml}</div>
                </div>
            </div>
        `;

        html += block;
    });
    
    container.innerHTML = html;
    
    attachDeliveryStatusGroupEventListeners();
}

function attachDeliveryStatusGroupEventListeners() {
    document.querySelectorAll('.update-delivery-btn-group').forEach(button => {
        button.addEventListener('click', function() {
            openDeliveryStatusModalForGroup(
                this.dataset.itemIds,
                this.dataset.orderId,
                this.dataset.groupName,
                this.dataset.mealDate,
                this.dataset.mealTypeId,
                this.dataset.currentStatus,
                this.dataset.currentStatusLabel
            );
        });
    });
}

function populateStatusOptions(currentStatus) {
    const select = document.getElementById('deliveryStatusSelect');
    select.innerHTML = '<option value="">Select Status</option>';
    
    let availableOptions = [];
    
    // Define available status transitions based on user role (client side)
    switch(currentStatus) {
        case 'pending':
            availableOptions = [
                { value: 'accept_order', label: 'Accept Order' },
                { value: 'cancelled', label: 'Cancelled' }
            ];
            break;
        case 'accept_order':
            availableOptions = [
                { value: 'preparing', label: 'Preparing' }
            ];
            break;
        case 'preparing':
            availableOptions = [
                { value: 'ready_for_pickup', label: 'Ready for Pickup' }
            ];
            break;
        case 'ready_for_pickup':
            availableOptions = [];
            // Delivery man will handle from here
            break;
        default:
            availableOptions = [];
    }
    
    // Add options to select
    availableOptions.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.value;
        optionElement.textContent = option.label;
        select.appendChild(optionElement);
    });
    
    // Disable select if no options available
    if (availableOptions.length === 0) {
        select.disabled = true;
        select.innerHTML = '<option value="">No further status changes allowed</option>';
    } else {
        select.disabled = false;
    }
}

function openDeliveryStatusModalForGroup(itemIds, orderId, groupName, mealDate, mealTypeId, currentStatus, currentStatusLabel) {
    // Set form values
    document.getElementById('updateMealOrderItemId').value = itemIds;
    document.getElementById('updateOrderId').value = orderId;
    document.getElementById('currentDeliveryStatus').value = currentStatus;
    
    const formattedDate = new Date(mealDate).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
    
    document.getElementById('currentItemInfo').innerHTML = `
        <div class="d-flex align-items-center">
            <i class="mdi mdi-package-variant-closed me-2 text-primary"></i>
            <div>
                <strong>${groupName}</strong><br>
                <small class="text-muted">${formattedDate} • ${itemIds.split(',').length} items</small>
            </div>
        </div>
    `;
    
    // Show/hide pickup time field based on current status
    const pickupTimeContainer = document.getElementById('pickupTimeContainer');
    if (currentStatus === 'preparing') {
        pickupTimeContainer.style.display = 'block';
        // Set default pickup time to 1 hour from now
        const now = new Date();
        now.setHours(now.getHours() + 1);
        const formattedTime = now.toISOString().slice(0, 16);
        document.getElementById('pickup_time').value = formattedTime;
        document.getElementById('pickup_time').required = true;
    } else {
        pickupTimeContainer.style.display = 'none';
        document.getElementById('pickup_time').required = false;
    }
    
    // Populate status options based on current status
    populateStatusOptions(currentStatus);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deliveryStatusModal'));
    modal.show();
}

async function updateDeliveryStatus() {
    const itemIds = document.getElementById('updateMealOrderItemId').value;
    const orderId = document.getElementById('updateOrderId').value;
    const status = document.getElementById('deliveryStatusSelect').value;
    const notes = document.getElementById('deliveryNotes').value;
    const currentStatus = document.getElementById('currentDeliveryStatus').value;
    const pickupTime = document.getElementById('pickup_time').value;
    
    if (!status) {
        errorToast('Please select a delivery status');
        return;
    }
    
    // Validate pickup time if status is "ready_for_pickup"
    if (currentStatus === 'preparing' && status === 'ready_for_pickup' && !pickupTime) {
        errorToast('Please provide pickup time');
        return;
    }
    
    showLoader();
    try {
        // Get meal date and meal type from the current data
        const mealDate = document.querySelector('.update-delivery-btn-group[data-item-ids="' + itemIds + '"]')?.dataset.mealDate;
        const mealTypeId = document.querySelector('.update-delivery-btn-group[data-item-ids="' + itemIds + '"]')?.dataset.mealTypeId;
        
        // Prepare request data
        const requestData = {
            meal_order_item_id: itemIds,
            delivery_status: status,
            notes: notes,
            meal_date: mealDate,
            meal_type_id: mealTypeId
        };
        
        // Add pickup time if provided
        if (pickupTime) {
            requestData.pickup_time = pickupTime;
        }
        
        // Make API call
        const response = await axios.post(`/client/update/delivery-status/${orderId}`, requestData);
        
        if (response.data.status === 'success') {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deliveryStatusModal'));
            modal.hide();
            
            // Show success message
            successToast(response.data.message || 'Delivery status updated successfully');
            
            // Reload data
            setTimeout(() => {
                loadClientPaymentDetails();
            }, 1000);
        } else {
            errorToast(response.data.message || 'Failed to update delivery status');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Handle save button click
    document.getElementById('saveDeliveryStatusBtn').addEventListener('click', updateDeliveryStatus);
    
    // Reset modal when closed
    const modal = document.getElementById('deliveryStatusModal');
    modal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('deliveryStatusForm').reset();
        document.getElementById('currentItemInfo').innerHTML = `
            <div class="d-flex align-items-center">
                <i class="mdi mdi-information-outline me-2 text-primary"></i>
                <span>Loading item details...</span>
            </div>
        `;
        document.getElementById('pickupTimeContainer').style.display = 'none';
        document.getElementById('pickup_time').required = false;
    });
});

function getDeliveryBadgeClass(status) {
    switch(status) {
        case 'delivered': return 'bg-success';
        case 'arrived': return 'bg-primary';
        case 'on_the_way': return 'bg-info';
        case 'picked_up': return 'bg-info';
        case 'ready_for_pickup': return 'bg-warning';
        case 'preparing': return 'bg-warning';
        case 'accept_order': return 'bg-secondary';
        case 'pending': return 'bg-secondary';
        case 'cancelled': return 'bg-dark';
        default: return 'bg-secondary';
    }
}

function getMealTimeForType(date, mealType, itemsWithTime) {
    if (!itemsWithTime || !Array.isArray(itemsWithTime)) return null;
    
    const matchingItem = itemsWithTime.find(item => {
        const itemDate = item.meal_date;
        const itemMealTypeName = item.meal_type_name || 'Other';
        
        return itemDate === date && itemMealTypeName === mealType && item.meal_time;
    });
    
    if (matchingItem && matchingItem.meal_time) {
        return matchingItem.meal_time;
    }
    
    return null;
}

function formatMealTime(timeString) {
    if (!timeString) return '';
    
    try {
        if (timeString.toLowerCase().includes('am') || timeString.toLowerCase().includes('pm')) {
            return timeString;
        }
        
        const timeParts = timeString.split(':');
        
        if (timeParts.length >= 2) {
            let hour = parseInt(timeParts[0], 10);
            let minute = parseInt(timeParts[1], 10);
            
            if (isNaN(hour) || isNaN(minute)) {
                return timeString;
            }
            
            const period = hour >= 12 ? 'pm' : 'am';
            const displayHour = hour % 12 || 12;
            const displayMinute = minute.toString().padStart(2, '0');
            
            return `${displayHour}:${displayMinute} ${period}`;
        }
        
        return timeString;
    } catch (e) {
        console.error('Error formatting time:', e, 'Input:', timeString);
        return timeString;
    }
}

function toTitleCase(str) {
    if (!str) return "";
    return str.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

function formatCurrency(amount) {
    const numAmount = parseFloat(amount) || 0;
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
    }).format(numAmount);
}
</script>

<style>
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modal-header .btn-close {
    filter: invert(1);
}

#currentItemInfo {
    background: linear-gradient(to right, #f8f9fa, #e9ecef);
    border: 1px solid #dee2e6;
}

#deliveryNotes {
    resize: vertical;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

#deliveryStatusSelect:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.hover-card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>