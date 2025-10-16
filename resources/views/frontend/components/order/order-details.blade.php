@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <h5 class="card-header pb-3 border-bottom mb-3">
                Order Details<span id="order-status-level">
                <span id="order-status-badge"></span>
            </h5>
            <div class="card-body">
                <div class="info-container">
                    <div id="checkout-cart" class="content">
                        <div class="row">
                            <!-- Cart left -->
                            <div class="col-xl-8 mb-3 mb-xl-0">
                                <ul class="list-group mb-3" id="order-items-list" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Order items will be dynamically inserted here -->
                                </ul>
                                <div class="text-center">
                                    <button id="load-more" class="btn btn-primary mt-3 d-none">Load More</button>
                                </div>
                            </div>

                            <!-- Cart right -->
                            <div class="col-xl-4">
                                <div class="border rounded p-3 mb-3" id="price-details-container">
                                     <!-- Price details will be dynamically inserted here -->
                                </div>
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('orders') }}" class="btn btn-primary me-3">Back to order list</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4 border-2 border-primary">
            <div class="card-body">
                <div class="card mb-4">
                    <h5 class="card-header">Order Schedule Details</h5>
                    <div class="table-responsive">
                      <table class="table">
                        <thead class="table-light">
                          <tr>
                            <th class="text-truncate">Client Name</th>
                            <th class="text-truncate">Product Name</th>
                            <th class="text-truncate">Order Status</th>
                            <th class="text-truncate">Order Date</th>
                            <th class="text-truncate">Order Time</th>
                            <th class="text-truncate">Approve Date</th>
                            <th class="text-truncate">Approve Time</th>
                            <th class="text-truncate">Delivery Date</th>
                            <th class="text-truncate">Delivery Time</th>
                            <th class="text-truncate">Cancel Date</th>
                            <th class="text-truncate">Cancel Time</th>
                          </tr>
                        </thead>
                        <tbody id="schedule-details">
                          <!-- Schedule details will be inserted here -->
                        </tbody>
                      </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {
    getOrderDetailsInfo();
    document.getElementById("load-more")?.addEventListener("click", loadMoreItems);
});

let displayedItems = 3;
let orderItems = [];
let orderData; 

function formatCurrency(amount) {
    return '$' + parseFloat(amount || 0).toFixed(2);
}

async function getOrderDetailsInfo() {
    try {
        const urlSegments = window.location.pathname.split('/');
        const orderId = urlSegments[urlSegments.length - 1];
        const res = await axios.get(`/user/get/order/details/${orderId}`);
        if (res.status === 200 && res.data.status === 'success') {
            orderData = res.data.data;
            orderItems = orderData.order_items;

            await updateOrderItems();
            await updatePriceDetails(orderData.order);
            await updateScheduleDetails(orderData.order);
        } else {
            errorToast(res.data.message || "Failed to fetch order details infoformation.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

async function updateOrderItems() {
    const orderItemsList = document.getElementById('order-items-list');
    const loadMoreButton = document.getElementById('load-more');

    orderItemsList.innerHTML = orderItems.slice(0, displayedItems).map(item => {
        // ✅ Get status directly from order_items model
        const itemStatus = item.status || "N/A";

        // Map status to badge class
        let statusBadgeClass = "bg-label-secondary"; // default
        switch (itemStatus) {
            case "pending":
                statusBadgeClass = "bg-label-danger";
                break;
            case "approved":
                statusBadgeClass = "bg-label-info";
                break;
            case "completed":
                statusBadgeClass = "bg-label-success";
                break;
            case "canceled":
                statusBadgeClass = "bg-label-warning";
                break;
            case "delivered":
                statusBadgeClass = "bg-label-primary";
                break;
        }

        // Capitalize status for display
        const displayStatus = itemStatus.charAt(0).toUpperCase() + itemStatus.slice(1);

        return `
        <li class="list-group-item p-4">
            <div class="d-flex gap-3">
                <div class="flex-shrink-0">
                    <img src="${item.product.image ? `/upload/product/small/${item.product.image}` : '/upload/no_image.jpg'}" 
                         alt="${item.product.name}" 
                         class="w-px-100" />
                </div>
                <div class="flex-grow-1">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="me-3">
                                <a href="javascript:void(0)" class="text-heading">${item.product.name}</a>
                            </h6>
                            ${item.product.category ? `<div class="mb-1"><small class="text-muted">Category: <strong>${item.product.category.name}</strong></small></div>` : ''}
                            ${item.product.brand ? `<div class="mb-1"><small class="text-muted">Brand: <strong>${item.product.brand.name}</strong></small></div>` : ''}
                            ${item.variant ? `
                            <div class="mb-2">
                                <span class="text-muted">Variant:</span>
                                ${item.variant.color ? `<span class="me-2">Color: ${item.variant.color}</span>` : ''}
                                ${item.variant.size ? `<span>Size: ${item.variant.size}</span>` : ''}
                            </div>` : ''}
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="me-1">Sold by:</span>
                                <a href="javascript:void(0)">${item.client.firstName} ${item.client.lastName}</a>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">Order Status:</small>
                                <span class="badge ${statusBadgeClass} ms-1">${displayStatus}</span>
                            </div>

                            ${(itemStatus === 'delivered' && !hasComplaintForOrderItem(item.id)) ? 
                                `<a href="/user/product/complaint/${item.id}" 
                                   class="btn btn-sm btn-outline-danger mt-1">
                                    Complaint
                                </a>` : ''}
                        </div>
                        <div class="col-md-4">
                            <div class="text-md-end">
                                <div class="my-2 mt-md-4 mb-md-5">
                                    <span class="text-primary">${formatCurrency(item.unit_price)}</span>
                                    <span class="text-body"> x ${item.quantity}</span>
                                </div>
                                <div class="h6">Total: ${formatCurrency(item.total_price)}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>`;
    }).join('');

    // Toggle 'Load More' button visibility
    if (orderItems.length > 3 && displayedItems < orderItems.length) {
        loadMoreButton.classList.remove('d-none');
    } else {
        loadMoreButton.classList.add('d-none');
    }

    // Scroll container management
    orderItemsList.style.maxHeight = displayedItems > 3 ? "550px" : "none";
    orderItemsList.style.overflowY = displayedItems > 3 ? "auto" : "visible";
}

async function updatePriceDetails(orderData) {
    const container = document.getElementById('price-details-container');

    // Calculate order total before delivery
    let orderTotal = (parseFloat(orderData.subtotal) || 0) 
                   - (parseFloat(orderData.coupon_discount) || 0) 
                   + (parseFloat(orderData.tax) || 0);
                   
    let deliveryCharges = parseFloat(orderData.delivery_fee) > 0
        ? formatCurrency(orderData.delivery_fee)
        : `<span class="badge bg-label-success rounded-pill">Free</span>`;

    let html = `
        <h5 class="mb-4">Price Details</h5><hr>
        <dl class="row mb-0">
            <dt class="col-6 fw-normal text-heading">Bag Total</dt>
            <dd class="col-6 text-end">${formatCurrency(orderData.subtotal)}</dd>
    `;

    if (parseFloat(orderData.coupon_discount) >= 1) {
        html += `
            <dt class="col-6 fw-normal text-heading">Coupon Discount</dt>
            <dd class="col-6 text-end text-danger">-${formatCurrency(orderData.coupon_discount)}</dd>
        `;
    }

    html += `
            <dt class="col-6 fw-normal text-heading">Tax</dt>
            <dd class="col-6 text-end">${formatCurrency(orderData.tax)}</dd>

            <dt class="col-6 fw-normal text-heading">Order Total</dt>
            <dd class="col-6 text-end">${formatCurrency(orderTotal)}</dd>

            <dt class="col-6 fw-normal text-heading">Delivery Charges</dt>
            <dd class="col-6 text-end">
                <span class="badge bg-label-success rounded-pill">${deliveryCharges}</span>
            </dd>
        </dl>
        <hr class="mx-n3 my-3" />
        <dl class="row mb-0 h6">
            <dt class="col-6 mb-0">Total</dt>
            <dd class="col-6 text-end mb-0">${formatCurrency(orderData.payable_amount)}</dd>
        </dl>
    `;

    container.innerHTML = html;
}

async function updateScheduleDetails(order) {
    const scheduleBody = document.getElementById('schedule-details');
    scheduleBody.innerHTML = "";

    if (!orderData.order_items || orderData.order_items.length === 0) {
        scheduleBody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-muted">No schedule details available</td>
            </tr>
        `;
        return;
    }

    scheduleBody.innerHTML = orderData.order_items.map(oi => {
        // Client name
        const clientName = oi.client
            ? `${oi.client.firstName ?? ""} ${oi.client.lastName ?? ""}`.trim()
            : "N/A";

        // Product name
        const productName = oi.product ? oi.product.name : "N/A";

        // Status badge
        const statusBadge = oi.status
            ? `<span class="badge bg-${getStatusColor(oi.status)} text-capitalize">${oi.status}</span>`
            : "---";

        // ✅ Extract order date & time from order_items.created_at
        const createdAt = oi.created_at ? new Date(oi.created_at) : null;
        const orderDate = createdAt ? formatDate(createdAt) : "---";
        const orderTime = createdAt ? createdAtTime(createdAt) : "---"

        return `
            <tr>
                <td>${clientName}</td>
                <td>${productName}</td>
                <td>${statusBadge}</td>
                <td>${orderDate}</td>
                <td>${orderTime}</td>
                <td>${oi.approve_date ? formatDate(oi.approve_date) : "---"}</td>
                <td>${oi.approve_time ? formatTime(oi.approve_time) : "---"}</td>
                <td>${oi.delivery_date ? formatDate(oi.delivery_date) : "---"}</td>
                <td>${oi.delivery_time ? formatTime(oi.delivery_time) : "---"}</td>
                <td>${oi.cancel_date ? formatDate(oi.cancel_date) : "---"}</td>
                <td>${oi.cancel_time ? formatTime(oi.cancel_time) : "---"}</td>
            </tr>
        `;
    }).join('');
}

function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case "pending": return "warning";
        case "approved": return "success";
        case "delivered": return "primary";
        case "canceled": return "danger";
        default: return "secondary";
    }
}

function loadMoreItems() {
    if (displayedItems < orderItems.length) {
        displayedItems += 3;
        updateOrderItems();
    }
}

function hasComplaintForOrderItem(orderItemId) {
    if (!orderData.order.complaints) return false;

    return orderData.order.complaints.some(complaint => {
        return complaint.order_item_id === orderItemId;
    });
}

function handleError(error) {
    let message = 'An unexpected error occurred';

    if (error.response) {
        const status = error.response.status;
        const serverMessage = error.response.data?.message;

        switch (status) {
            case 404:
                message = serverMessage || 'There is no order found';
                break;
            case 500:
                message = serverMessage || 'Server error. Please try again later.';
                break;
            default:
                message = serverMessage || message;
        }
    } else if (error.request) {
        message = 'No response received from the server.';
    } else {
        message = error.message || message;
    }

    errorToast(message);
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
}

function formatTime(timeString) {
    if (!timeString) return '---';
    const [hours, minutes, seconds] = timeString.split(':');
    const date = new Date();
    date.setHours(hours, minutes, seconds || 0);

    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit', // optional, remove if you don’t want seconds
        hour12: true 
    });
}

function createdAtTime(dateValue) {
    const date = new Date(dateValue);
    return date.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
}

</script>

<style type="text/css">
    #order-items-list {
        scroll-behavior: smooth;
    }
</style>


