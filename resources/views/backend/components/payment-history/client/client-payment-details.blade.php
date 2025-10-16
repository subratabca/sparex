<div class="card">
    <div class="card-header header-elements bg-light">
        <h5 class="fw-semibold mb-0">Order Details</h5>
    </div>
    <div class="card-body">
        <input type="hidden" id="clientId" value="{{ $client_id }}">
        <input type="hidden" id="orderId" value="{{ $order_id }}">

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
                                    <!-- Removed global buttons -->
                                    <div id="global-delivery-buttons" class="mt-3"></div>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        const clientId = document.getElementById('clientId').value;
        const orderId = document.getElementById('orderId').value;

        let res = await axios.get(`/admin/get/payment/details/info/client/${clientId}/order/${orderId}`);
        if (res.status === 200 && res.data.status === 'success') {
            orderData = res.data.data;
            orderItems = orderData.order_items;
            paymentStatus = orderData.client_orders[0].payment_status;
            //console.log('------------',paymentStatus);

            await updateOrderSummary(orderData.order);
            await updateOrderItems();
            await updatePriceDetails(orderData.client_orders[0]);
            await updateScheduleDetails(orderData.order);

            document.getElementById('load-more')?.addEventListener('click', loadMoreItems);
        } else {
            errorToast(res.data.message || "Failed to fetch order details information.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function formatCurrency(amount) {
    return '£' + parseFloat(amount || 0).toFixed(2);
}

async function updateOrderSummary(orderData) {
    const container = document.getElementById('order-details-container');

    // Capitalize first letter of payment status
    const formattedStatus = paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1);

    // Choose badge color based on payment status
    let badgeClass = '';
    if (paymentStatus.toLowerCase() === 'paid') {
        badgeClass = 'badge bg-success';
    } else if (paymentStatus.toLowerCase() === 'due') {
        badgeClass = 'badge bg-danger';
    } else {
        badgeClass = 'badge bg-secondary';
    }

    let html = `
    <div class="col-12">
        <div class="card border">
            <div class="card-body">
                <div class="row">
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
                            <dt class="col-sm-4">Payment Status:</dt>
                            <span class="col-sm-4 ${badgeClass}">${formattedStatus}</span>
                        </dl>
                    </div>

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
    const globalDeliveryContainer = document.getElementById('global-delivery-buttons');
    if (!orderItemsList) return;

    const itemsToShow = orderItems.slice(0, displayedItems);

    // Render order items list
    orderItemsList.innerHTML = itemsToShow.map(item => {
        const product = item.product;
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
                                <span class="badge bg-label-info ms-1">${item.status}</span>
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

    // Show/hide Load More button
    if (loadMoreButton) {
        const shouldShow = itemsToShow.length < orderItems.length;
        shouldShow ? loadMoreButton.classList.remove('d-none') : loadMoreButton.classList.add('d-none');
    }

    orderItemsList.style.maxHeight = orderItems.length > 3 ? "550px" : "none";
    orderItemsList.style.overflowY = orderItems.length > 3 ? "auto" : "visible";

    // 🔹 Show "Pay Now" button if payment status is due
    if (globalDeliveryContainer) {
        globalDeliveryContainer.innerHTML = '';

        if (paymentStatus && paymentStatus.toLowerCase() === 'due') {
            globalDeliveryContainer.innerHTML = `
                <button class="btn btn-success me-2 mb-2" onclick="markAsPaid(${orderData.order.id}, ${orderData.client_orders[0].client_id})">
                    Mark as Paid
                </button>
            `;
        }
    }
}

async function updatePriceDetails(clientOrder) {
    const priceDetailsContainer = document.getElementById('price-details');
    if (!priceDetailsContainer) return;

    // Calculate order total before delivery
    let orderTotal = (parseFloat(clientOrder.subtotal) || 0) 
                   - (parseFloat(clientOrder.coupon_discount) || 0) 
                   + (parseFloat(clientOrder.tax) || 0);

    // Delivery fee handling
    let deliveryCharges = parseFloat(clientOrder.delivery_fee) > 0
        ? formatCurrency(clientOrder.delivery_fee)
        : `<span class="badge bg-label-success rounded-pill">Free</span>`;

    priceDetailsContainer.innerHTML = `
        <h6 class="mb-4">Price Details</h6>
        <dl class="row mb-0">
            <dt class="col-6 fw-normal text-heading">Bag Total</dt>
            <dd class="col-6 text-end" id="subtotal">${formatCurrency(clientOrder.subtotal)}</dd>
        
            ${parseFloat(clientOrder.coupon_discount) >= 1 ? `
                <dt class="col-6 fw-normal text-heading">Coupon Discount</dt>
                <dd class="col-6 text-end text-danger" id="coupon_discount">-${formatCurrency(clientOrder.coupon_discount)}</dd>
            ` : ''}

            <dt class="col-6 fw-normal text-heading">Tax</dt>
            <dd class="col-6 text-end" id="tax">${formatCurrency(clientOrder.tax)}</dd>

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
            <dd class="col-6 text-end mb-0" id="total">${formatCurrency(clientOrder.payable_amount)}</dd>
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

    // Build rows from OrderItems
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

async function markAsPaid(orderId, clientId) {
    const result = await Swal.fire({
        title: 'Mark Payment as Paid?',
        text: "Are you sure you want to mark this payment as paid?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, mark as paid',
        cancelButtonText: 'Cancel',
        width: '500px', 
        padding: '1.2rem', 
        customClass: {
            popup: 'swal-sm-font',          
            title: 'swal-title-sm',         
            content: 'swal-content-sm',     
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-outline-secondary ms-2'
        },
        buttonsStyling: false
    });

    if (!result.isConfirmed) return;

    try {
        showLoader();

        const res = await axios.post(`/admin/payment/mark-as-paid/client/${clientId}/order/${orderId}`);

        if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message || "Payment marked as paid successfully.");
            await loadOrderDetails(); // Refresh payment info after update
        } else {
            errorToast(res.data.message || "Failed to mark payment as paid.");
        }
    } catch (error) {
        if (error.response) {
            errorToast(error.response.data.message || "An error occurred while updating payment status.");
        } else {
            errorToast("Network error. Please try again.");
        }
    } finally {
        hideLoader();
    }
}


function loadMoreItems() {
    const remainingItems = orderItems.length - displayedItems;
    if (remainingItems > 0) {
        displayedItems = Math.min(displayedItems + 3, orderItems.length);
        updateOrderItems();
    }
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

function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case "pending": return "warning";
        case "approved": return "success";
        case "delivered": return "primary";
        case "cancelled": return "danger";
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

</script>

<style type="text/css">
    .card-header {
        color: orange;
    }
    #order-items-list {
        scroll-behavior: smooth;
    }
</style>


