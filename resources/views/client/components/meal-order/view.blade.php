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
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="mdi mdi-silverware-fork-knife me-2"></i>Order Items by Date</h6>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
    await loadClientPaymentDetails();
});

async function loadClientPaymentDetails() {
    try {
        showLoader();
        
        const orderId = window.location.pathname.split('/').pop();
        const response = await axios.get(`/client/get/meal-order/details/${orderId}`);

        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;
            console.log('Data received:', data);
            
            // Update header information
            document.getElementById('mealPlanTitle').textContent = `Client Payment - Order #${data.order.order_number}`;
            document.getElementById('orderNumberText').textContent = `Invoice: ${data.order.invoice_no} • ${data.summary.total_items} items`;
            document.getElementById('orderStatusText').textContent = data.order.status ? toTitleCase(data.order.status) : 'Unknown';

            updateCustomerInfo(data.customer);
            
            // Update payment information
            updatePaymentInfo(data.client_meal_order);
            
            // Update summary statistics
            updateSummaryStats(data.summary);
            
            // Render order items
            renderMealOrderItems(data.items, data.dates);
            
        } else {
            showError(response.data.message || 'Failed to load payment details');
        }
    } catch (error) {
        console.error('Error loading payment details:', error);
        let errorMessage = 'An error occurred while loading payment details';
        
        if (error.response) {
            console.error('Error response:', error.response.data);
            errorMessage = error.response.data.message || errorMessage;
            
            // If there's a server error message, show it
            if (error.response.data.error && env('APP_DEBUG')) {
                errorMessage += `: ${error.response.data.error}`;
            }
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        showError(errorMessage);
    } finally {
        hideLoader();
    }
}

function updateCustomerInfo(customer) {
    document.getElementById('customerName').textContent = customer.name;
    document.getElementById('customerEmail').textContent = `Email: ${customer.email}`;
    document.getElementById('customerPhone').textContent = `Phone: ${customer.mobile || 'N/A'}`;
    
    if (customer.image) {
        document.getElementById('customerImage').src = `/upload/customer-profile/small/${customer.image}`;
    }
}

function updatePaymentInfo(clientMealOrder) {
    const paymentStatus = clientMealOrder.payment_status;
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
    document.getElementById('payableAmount').textContent = formatCurrency(clientMealOrder.payable_amount || 0);
    document.getElementById('paidAmount').textContent = formatCurrency(clientMealOrder.paid_amount || 0);
    document.getElementById('balanceAmount').textContent = formatCurrency(
        (clientMealOrder.payable_amount || 0) - (clientMealOrder.paid_amount || 0)
    );
}

function updateSummaryStats(summary) {
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

function renderMealOrderItems(items, dates) {
    const container = document.getElementById('mealOrderAccordion');
    
    if (!items || Object.keys(items).length === 0) {
        container.innerHTML = '<div class="alert alert-info">No items found for this payment.</div>';
        return;
    }
    
    let html = '';
    
    // Sort dates in descending order (newest first)
    dates.sort((a, b) => new Date(b) - new Date(a));
    
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
            const itemsList = dayItems[type];
            
            // Calculate total for this meal type on this date
            const typeTotal = itemsList.reduce((sum, item) => sum + parseFloat(item.total_price || 0), 0);

            mealTypeHtml += `
                <div class="card mb-3 border-0">
                    <div class="card-header bg-light-subtle">
                        <h6 class="mb-0 d-flex justify-content-between align-items-center">
                            <span>${typeTitle} (${itemsList.length} item${itemsList.length !== 1 ? 's' : ''})</span>
                            <span class="text-primary fw-bold">${formatCurrency(typeTotal)}</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
            `;

            itemsList.forEach(item => {
                const productName = toTitleCase(item.product_name || '');
                const img = item.product_image ? `/upload/product/small/${item.product_image}` : '/upload/no_image.jpg';

                mealTypeHtml += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${img}" alt="${productName}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                <div>
                                    <strong>${productName}</strong><br>
                                    <small class="text-muted">${formatCurrency(item.unit_price || 0)} each × ${item.quantity || 0}</small><br>
                                    <small class="text-info">Provider: ${item.client_name}</small>
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

        // Calculate total for this date
        const dateTotal = mealTypes.reduce((total, type) => {
            return total + dayItems[type].reduce((sum, item) => sum + parseFloat(item.total_price || 0), 0);
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

function showLoader() {
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('mainContent').style.opacity = '0.5';
    document.getElementById('errorMessage').style.display = 'none';
}

function hideLoader() {
    document.getElementById('loadingSpinner').style.display = 'none';
    document.getElementById('mainContent').style.opacity = '1';
}

function showError(message) {
    hideLoader();
    document.getElementById('errorText').textContent = message;
    document.getElementById('errorMessage').style.display = 'block';
}

// Utility function for toast notifications (if needed)
function errorToast(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert('Error: ' + message);
    }
}
</script>

<style>
.hover-card {
    transition: all 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}
.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color: #0d6efd;
}
.payment-status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}
</style>