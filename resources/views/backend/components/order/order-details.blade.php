<div class="card">
    <div class="card-header header-elements bg-light">
        <h5 class="fw-semibold mb-0">Order Details</h5>
    </div>
    <div class="card-body">
        <input type="hidden" id="orderId">
        <div id="order-details-container" class="row g-4">
            <!-- Order Summary Section will be loaded here -->
        </div>

        <!-- Order Details Static Layout -->
        <div class="col-xl-12 mt-4">
            <div class="card mb-4">
                <h5 class="card-header pb-3 border-bottom mb-3">Order Details</h5>
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
                                    <div class="border rounded p-3 mb-3">
                                        <div id="price-details">
                                            <!-- Price Details heading and list will be inserted dynamically -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="col-xl-12 mt-4">
    <div class="card mb-4">
        <h5 class="card-header pb-3 border-bottom mb-3">Order Schedule Details</h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="schedule-table">
                    <thead class="table-light" id="schedule-head">
                        <!-- Dynamic header -->
                    </thead>
                    <tbody id="schedule-details">
                        <!-- Schedule details will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    loadOrderDetails();
});

let displayedItems = 3;
let orderItems = [];
let orderData;

async function loadOrderDetails() {
    showLoader();
    try {
        displayedItems = 3; 
        let segments = window.location.pathname.split('/');
        let orderId = segments[segments.length - 1];
        document.getElementById('orderId').value = orderId;

        let res = await axios.get(`/admin/get/order/details/${orderId}`);
        if (res.status === 200 && res.data.status === 'success') {
            orderData = res.data.data;
            orderItems = orderData.order_items;
            orderStatus = orderData.client_orders.status;
            //console.log('------------',orderData);

            await updateOrderSummary(orderData.order);
            await updateOrderItems();
            await updatePriceDetails(orderData.order);
            await updateScheduleDetails(orderData.order);

            // Add event listener after elements are rendered
            document.getElementById('load-more')?.addEventListener('click', loadMoreItems);
        } else {
            errorToast(res.data.message || "Failed to fetch order details infoformation.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

async function updateOrderSummary(orderData) {
    const container = document.getElementById('order-details-container');
    let html = `
    <div class="col-12">
        <div class="card border">
            <div class="card-body">
                <div class="row">
                    <!-- Order Summary Column -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <h5 class="mb-3 text-primary"><i class="mdi mdi-information-outline me-2"></i>Order Summary</h5>
                        <dl class="row">
                            <dt class="col-sm-4">Order ID:</dt>
                            <dd class="col-sm-8 fw-semibold">#${orderData.id}</dd>
                            <dt class="col-sm-4">Order No:</dt>
                            <dd class="col-sm-8 fw-semibold">${orderData.order_number}</dd>
                            <dt class="col-sm-4">Invoice No:</dt>
                            <dd class="col-sm-8 fw-semibold">${orderData.invoice_no}</dd>
                            <dt class="col-sm-4">Payment Method:</dt>
                            <dd class="col-sm-8 fw-semibold">${orderData.payment_method}</dd>
                            <dt class="col-sm-4">Customer:</dt>
                            <dd class="col-sm-8">${orderData.customer.firstName} ${orderData.customer.lastName}</dd>
                            <dt class="col-sm-4">Contact:</dt>
                            <dd class="col-sm-8">${orderData.customer.mobile}</dd>
                            <dt class="col-sm-4">Order Date:</dt>
                            <dd class="col-sm-8">${new Date(orderData.created_at).toLocaleDateString()}</dd>
                        </dl>
                    </div>

                    <!-- Shipping Address Column -->
                    <div class="col-md-6">
                        <h5 class="mb-3 text-primary"><i class="mdi mdi-truck-delivery-outline me-2"></i>Shipping Address</h5>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-1 fw-semibold">${orderData.shipping_address.address1 || ''} ${orderData.shipping_address.address2 || ''}</p>
                            <p class="mb-1">${orderData.shipping_address.city.name}, ${orderData.shipping_address.county.name}</p>
                            <p class="mb-1">${orderData.shipping_address.country.name}</p>
                            <p class="mb-0">Postcode: ${orderData.shipping_address.zip_code}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
    container.innerHTML = html;
}

async function updateOrderItems() {
    const orderItemsList = document.getElementById('order-items-list');
    const loadMoreButton = document.getElementById('load-more');
    if (!orderItemsList) return;

    const itemsToShow = orderItems.slice(0, displayedItems);

    orderItemsList.innerHTML = itemsToShow.map(item => {
        const product = item.product;
        // ✅ Use status directly from order_items
        const itemStatus = item.status || 'N/A';

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
                    <img src="${product.image?.length ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg'}" 
                         alt="${product.name}" 
                         class="w-px-100" />
                </div>
                <div class="flex-grow-1">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="me-3">
                                <a href="javascript:void(0)" class="text-heading">${product.name}</a>
                            </h6>
                            ${product.category ? `<div class="mb-1"><small class="text-muted">Category: <strong>${product.category.name}</strong></small></div>` : ''}
                            ${product.brand ? `<div class="mb-1"><small class="text-muted">Brand: <strong>${product.brand.name}</strong></small></div>` : ''}
                            ${item.variant ? `
                            <div class="mb-2">
                                <span class="text-muted">Variant:</span>
                                ${item.variant.color ? `<span class="me-2">Color: ${item.variant.color}</span>` : ''}
                                ${item.variant.size ? `<span>Size: ${item.variant.size}</span>` : ''}
                            </div>` : ''}
                            <div class="d-flex flex-wrap">
                                <span class="me-1">Sold by:</span>
                                <a href="javascript:void(0)">${item.client?.firstName} ${item.client?.lastName}</a>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">Order Status:</small>
                                <span class="badge ${statusBadgeClass} ms-1">${displayStatus}</span>
                            </div>
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

    if (loadMoreButton) {
        const shouldShow = itemsToShow.length < orderItems.length;
        shouldShow ? loadMoreButton.classList.remove('d-none') : loadMoreButton.classList.add('d-none');
    }

    orderItemsList.style.maxHeight = orderItems.length > 3 ? "550px" : "none";
    orderItemsList.style.overflowY = orderItems.length > 3 ? "auto" : "visible";
}

async function updatePriceDetails(order) {
    const priceDetailsContainer = document.getElementById('price-details');
    if (!priceDetailsContainer) return;

    // Calculate order total before delivery
    let orderTotal = (parseFloat(order.subtotal) || 0) 
                   - (parseFloat(order.coupon_discount) || 0) 
                   + (parseFloat(order.tax) || 0);
                   
    let deliveryCharges = parseFloat(order.delivery_fee) > 0
        ? formatCurrency(order.delivery_fee)
        : `<span class="badge bg-label-success rounded-pill">Free</span>`;

    priceDetailsContainer.innerHTML = `
        <h6 class="mb-4">Price Details</h6>
        <dl class="row mb-0">
            <dt class="col-6 fw-normal text-heading">Bag Total</dt>
            <dd class="col-6 text-end" id="subtotal">${formatCurrency(order.subtotal)}</dd>
        
            ${parseFloat(order.coupon_discount) >= 1 ? `
                <dt class="col-6 fw-normal text-heading">Coupon Discount</dt>
                <dd class="col-6 text-end text-danger" id="coupon_discount">-${formatCurrency(order.coupon_discount)}</dd>
            ` : ''}

            <dt class="col-6 fw-normal text-heading">Tax</dt>
            <dd class="col-6 text-end" id="tax">${formatCurrency(order.tax)}</dd>

            <dt class="col-6 fw-normal text-heading">Order Total</dt>
            <dd class="col-6 text-end" id="payable_amount">${formatCurrency(orderTotal)}</dd>

            <dt class="col-6 fw-normal text-heading">Delivery Charges</dt>
            <dd class="col-6 text-end">
                <span class="badge bg-label-success rounded-pill">${deliveryCharges}</span>
            </dd>
        </dl>
        <hr class="mx-n3 my-3" />
        <dl class="row mb-0 h6">
            <dt class="col-6 mb-0">Total</dt>
            <dd class="col-6 text-end mb-0" id="total">${formatCurrency(order.payable_amount)}</dd>
        </dl>
    `;
}

async function updateScheduleDetails(order) {
    const scheduleHead = document.getElementById('schedule-head');
    const scheduleBody = document.getElementById('schedule-details');
    scheduleHead.innerHTML = "";
    scheduleBody.innerHTML = "";

    if (!orderData.order_items || orderData.order_items.length === 0) {
        scheduleHead.innerHTML = `
            <tr>
                <th>Product</th>
                <th>Client</th>
                <th>Status</th>
            </tr>
        `;
        scheduleBody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-muted">No schedule details available</td>
            </tr>
        `;
        return;
    }

    // Table headers
    let headerCols = `
        <tr>
            <th>Product</th>
            <th>Client</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Order Time</th>
            <th>Approve Date</th>
            <th>Approve Time</th>
            <th>Delivery Date</th>
            <th>Delivery Time</th>
            <th>Cancel Date</th>
            <th>Cancel Time</th>
        </tr>
    `;
    scheduleHead.innerHTML = headerCols;

    // Build rows dynamically from OrderItems
    scheduleBody.innerHTML = orderData.order_items.map(oi => {
        const clientName = oi.client 
            ? `${oi.client.firstName ?? ""} ${oi.client.lastName ?? ""}`.trim() 
            : "N/A";

        const statusBadge = oi.status 
            ? `<span class="badge bg-${getStatusColor(oi.status)} text-capitalize">${oi.status}</span>` 
            : "---";

        // ✅ Extract order date & time from order_items.created_at
        const createdAt = oi.created_at ? new Date(oi.created_at) : null;
        const orderDate = createdAt ? formatDate(createdAt) : "---";
        const orderTime = createdAt ? createdAtTime(createdAt) : "---";

        return `
            <tr>
                <td>${oi.product ? oi.product.name : "N/A"}</td>
                <td>${clientName}</td>
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

function loadMoreItems() {
    const remainingItems = orderItems.length - displayedItems;
    if (remainingItems > 0) {
        displayedItems = Math.min(displayedItems + 3, orderItems.length);
        updateOrderItems();
    }
}

function formatCurrency(amount) {
    return '£' + parseFloat(amount || 0).toFixed(2);
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
        second: '2-digit', 
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
</script>

<style type="text/css">
    .card-header {
        color: orange;
    }
    #order-items-list {
        scroll-behavior: smooth;
    }
</style>

