<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Delivery Order Details</h5>
                    <a href="{{ route('delivery.meal.orders') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Delivery Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bx bx-truck me-2"></i>Delivery Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Tracking Number</small>
                        <h6 class="mb-0" id="tracking-number">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Delivery Date</small>
                        <h6 class="mb-0" id="delivery-date">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Delivery Status</small>
                        <div>
                            <span class="badge" id="delivery-status-badge">-</span>
                            <span id="delivery-status-label" class="ms-1">-</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Delivery Charge</small>
                        <h6 class="mb-0 text-success" id="delivery-charge">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Distance</small>
                        <h6 class="mb-0" id="distance">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Payment Status</small>
                        <h6 class="mb-0">
                            <span class="badge" id="payment-status">-</span>
                        </h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Charge Key</small>
                        <h6 class="mb-0 text-muted small" id="charge-key">-</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Person Details -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="bx bx-store me-2"></i>Delivery Person Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img id="delivery-person-image" src="" class="rounded-circle me-2" style="width: 50px; height: 50px; object-fit: cover;">
                        <div>
                            <small class="text-muted">Name</small>
                            <h6 class="mb-0" id="delivery-person-name">-</h6>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Email</small>
                        <h6 class="mb-0" id="delivery-person-email">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Phone</small>
                        <h6 class="mb-0" id="delivery-person-phone">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Address</small>
                        <h6 class="mb-0 small" id="delivery-person-address">-</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="bx bx-user me-2"></i>Customer Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img id="customer-image" src="" class="rounded-circle me-2" style="width: 50px; height: 50px; object-fit: cover;">
                        <div>
                            <small class="text-muted">Customer Name</small>
                            <h6 class="mb-0" id="customer-name">-</h6>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Email</small>
                        <h6 class="mb-0" id="customer-email">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Phone</small>
                        <h6 class="mb-0" id="customer-phone">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Shipping Address</small>
                        <h6 class="mb-0 small" id="shipping-address">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Shipping Name</small>
                        <h6 class="mb-0" id="shipping-name">-</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Items -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="bx bx-food-menu me-2"></i>Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Meal Time</th>
                                </tr>
                            </thead>
                            <tbody id="order-items-table">
                                <!-- Items will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0"><i class="bx bx-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Order Number</small>
                        <h6 class="mb-0" id="order-number">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Invoice No</small>
                        <h6 class="mb-0" id="invoice-no">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Meal Type</small>
                        <h6 class="mb-0" id="meal-type">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Delivery Type</small>
                        <h6 class="mb-0" id="delivery-type">-</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Order Date</small>
                        <h6 class="mb-0" id="order-date">-</h6>
                    </div>
                    <hr>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span id="subtotal">-</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Tax:</span>
                        <span id="tax">-</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Delivery Fee:</span>
                        <span id="delivery-fee">-</span>
                    </div>
                    <hr>
                    <div class="mb-0 d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span id="total">-</span>
                    </div>
                </div>
            </div>

            <!-- Status History -->
            <div class="card mt-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0"><i class="bx bx-history me-2"></i>Status History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline" id="status-history">
                        <!-- Status history will be populated here -->
                        <div class="text-center text-muted py-3">No status history available</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    loadDeliveryOrderDetails();
});

function toTitleCase(str) {
    if (!str) return "";
    return str.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

function getImageUrl(type, filename) {
    if (!filename) return '/upload/no_image.jpg';
    const basePaths = {
        delivery: '/upload/delivery-profile/medium/',
        customer: '/upload/customer-profile/medium/',
        product: '/upload/product/small/'
    };
    return basePaths[type] + filename;
}

async function loadDeliveryOrderDetails() {
    try {
        showLoader();
        
        const pathSegments = window.location.pathname.split('/');
        const ledgerId = pathSegments[pathSegments.length - 1];
        
        const response = await axios.get(`/client/get/meal-order/payment/details/${ledgerId}`);
        
        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;

            // Delivery Information
            document.getElementById('tracking-number').textContent = data.delivery_info.tracking_number || '-';
            document.getElementById('delivery-date').textContent = data.delivery_info.delivery_date || '-';
            
            const deliveryStatusBadge = document.getElementById('delivery-status-badge');
            deliveryStatusBadge.textContent = data.delivery_info.delivery_status_label || '-';
            deliveryStatusBadge.className = 'badge ' + getDeliveryBadgeClass(data.delivery_info.delivery_status);
            
            document.getElementById('delivery-status-label').textContent = data.delivery_info.delivery_status_label || '-';
            document.getElementById('delivery-charge').textContent = '$' + (data.delivery_info.delivery_charge || '0.00');
            
            const distance = data.delivery_info.distance_km ? 
                `${data.delivery_info.distance_km} km (${data.delivery_info.distance_category})` : '-';
            document.getElementById('distance').textContent = distance;
            
            const paymentStatusBadge = document.getElementById('payment-status');
            paymentStatusBadge.textContent = data.delivery_info.payment_status || '-';
            paymentStatusBadge.className = 'badge ' + (data.delivery_info.payment_status === 'paid' ? 'bg-success' : 'bg-warning');
            
            document.getElementById('charge-key').textContent = data.delivery_info.charge_key || '-';
            
            // Delivery Person Details
            if (data.delivery_person) {
                document.getElementById('delivery-person-image').src = getImageUrl('delivery', data.delivery_person.image);
                document.getElementById('delivery-person-name').textContent = data.delivery_person.name ? toTitleCase(data.delivery_person.name) : '-';
                document.getElementById('delivery-person-email').textContent = data.delivery_person.email || '-';
                document.getElementById('delivery-person-phone').textContent = data.delivery_person.mobile || '-';
                
                const personAddress = [
                    data.delivery_person.address1,
                    data.delivery_person.address2,
                    data.delivery_person.zip_code
                ].filter(Boolean).join(', ') || '-';
                document.getElementById('delivery-person-address').textContent = personAddress;
            } else {
                document.getElementById('delivery-person-image').src = '/upload/no_image.jpg';
            }
            
            // Customer Details
            if (data.customer_details) {
                document.getElementById('customer-image').src = getImageUrl('customer', data.customer_details.image);
                document.getElementById('customer-name').textContent = data.customer_details.name ? toTitleCase(data.customer_details.name) : '-';
                document.getElementById('customer-email').textContent = data.customer_details.email || '-';
                document.getElementById('customer-phone').textContent = data.customer_details.mobile || '-';
                document.getElementById('shipping-name').textContent = data.customer_details.shipping_name ? toTitleCase(data.customer_details.shipping_name) : '-';
                
                const shippingAddress = [
                    data.customer_details.address1,
                    data.customer_details.address2,
                    data.customer_details.zip_code
                ].filter(Boolean).join(', ') || '-';
                document.getElementById('shipping-address').textContent = shippingAddress;
            } else {
                document.getElementById('customer-image').src = '/upload/no_image.jpg';
            }
            
            // Order Summary
            document.getElementById('order-number').textContent = data.order_summary.order_number || '-';
            document.getElementById('invoice-no').textContent = data.order_summary.invoice_no || '-';
            document.getElementById('meal-type').textContent = data.order_summary.meal_type ? toTitleCase(data.order_summary.meal_type) : '-';
            document.getElementById('delivery-type').textContent = data.order_summary.delivery_type ? toTitleCase(data.order_summary.delivery_type) : '-';
            document.getElementById('order-date').textContent = data.order_summary.order_date || '-';
            document.getElementById('subtotal').textContent = '$' + data.order_summary.subtotal;
            document.getElementById('tax').textContent = '$' + data.order_summary.tax;
            document.getElementById('delivery-fee').textContent = '$' + data.order_summary.delivery_fee;
            document.getElementById('total').textContent = '$' + data.order_summary.total;
            
            // Order Items
            const orderItemsTable = document.getElementById('order-items-table');
            orderItemsTable.innerHTML = '';
            
            if (data.order_items && data.order_items.length > 0) {
                data.order_items.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${getImageUrl('product', item.product_image)}" 
                                     class="rounded me-3" 
                                     style="width: 50px; height: 50px; object-fit: cover;"
                                     alt="${item.product_name}">
                                <div>
                                    <strong>${item.product_name ? toTitleCase(item.product_name) : '-'}</strong>
                                </div>
                            </div>
                        </td>
                        <td>${item.quantity}</td>
                        <td>$${item.unit_price}</td>
                        <td>$${item.total_price}</td>
                        <td>${item.meal_time || '-'}</td>
                    `;
                    orderItemsTable.appendChild(row);
                });
            } else {
                orderItemsTable.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No items found</td>
                    </tr>
                `;
            }
            
            // Status History
            const statusHistory = document.getElementById('status-history');
            statusHistory.innerHTML = '';
            
            if (data.status_history && data.status_history.length > 0) {
                data.status_history.forEach(history => {
                    const historyItem = document.createElement('div');
                    historyItem.className = 'timeline-item mb-3';
                    historyItem.innerHTML = `
                        <div class="d-flex">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content ms-3">
                                <small class="text-muted d-block">${history.created_at}</small>
                                <div class="d-flex align-items-center">
                                    <span class="badge ${getDeliveryBadgeClass(history.delivery_status)} me-2">
                                        ${history.status_label}
                                    </span>
                                    <small class="text-muted">Updated by: ${history.updated_by_label}</small>
                                </div>
                                ${history.notes ? `<small class="d-block text-muted mt-1">${history.notes}</small>` : ''}
                                ${history.pick_up_at ? `<small class="d-block text-muted mt-1">Pickup scheduled: ${history.pick_up_at}</small>` : ''}
                            </div>
                        </div>
                    `;
                    statusHistory.appendChild(historyItem);
                });
            } else {
                statusHistory.innerHTML = '<div class="text-center text-muted py-3">No status history available</div>';
            }
            
        } else {
            errorToast('Failed to load order details');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
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
}

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
</script>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -24px;
    top: 6px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #0d6efd;
    border: 2px solid white;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>


