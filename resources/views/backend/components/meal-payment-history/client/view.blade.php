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
                        <h4 class="mb-1" id="mealPlanTitle">Client Payment Details</h4>
                        <p class="mb-0 text-muted" id="orderNumberText"></p>
                    </div>
                    <div>
                        <span class="badge bg-success fs-6" id="orderStatusText"></span>
                    </div>
                </div>

                <!-- Client Info & Payment Status -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm hover-card h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="mdi mdi-account-circle me-2"></i>Client Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img id="clientImage"
                                             src="/upload/no_image.jpg"
                                             alt="Client"
                                             class="rounded-circle"
                                             style="width:70px;height:70px;object-fit:cover;">
                                    </div>
                                    <div>
                                        <h5 class="mb-1" id="clientName">-</h5>
                                        <p class="mb-1 text-muted" id="clientEmail">Email: -</p>
                                        <p class="mb-0 text-muted" id="clientPhone">Phone: -</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card border-0 shadow-sm hover-card h-100">
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
                                        <p class="mb-1 text-muted small">Payable</p>
                                        <h5 class="text-primary" id="payableAmount">£0.00</h5>
                                    </div>
                                    <div class="col-4">
                                        <p class="mb-1 text-muted small">Paid</p>
                                        <h5 class="text-success" id="paidAmount">£0.00</h5>
                                    </div>
                                    <div class="col-4">
                                        <p class="mb-1 text-muted small">Balance</p>
                                        <h5 class="text-danger" id="balanceAmount">£0.00</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items & Summary -->
                <div class="row">
                    <!-- Order Items -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="mdi mdi-silverware-fork-knife me-2"></i>
                                    Order Items by Date
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="mealOrderAccordion"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Summary -->
                    <div class="col-lg-4">
                        <div class="border rounded p-3 shadow-sm mb-4">
                            <h5 class="mb-3">
                                <i class="mdi mdi-calculator me-2"></i>Price Breakdown
                            </h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Subtotal</span>
                                    <span id="summarySubtotal">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Tax</span>
                                    <span id="summaryTax">£0.00</span>
                                </li>

                                {{-- Platform Fee — always shown --}}
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0"
                                    id="platformFeeRow">
                                    <span>
                                        <span class="text-warning fw-semibold">
                                            Platform Fee <span id="platformFeePercent">(0%)</span>
                                        </span>
                                        <small class="d-block text-muted">(Admin deduction)</small>
                                    </span>
                                    <span id="summaryPlatformFee" class="text-warning fw-semibold">
                                        £0.00
                                    </span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between px-0 fw-bold border-top">
                                    <span>Payable Amount</span>
                                    <span id="summaryTotal" class="text-primary">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 text-success">
                                    <span>Paid</span>
                                    <span id="summaryPaid">£0.00</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 text-danger fw-bold">
                                    <span>Balance Due</span>
                                    <span id="summaryBalance">£0.00</span>
                                </li>
                            </ul>

                            {{-- Platform Fee Info Box --}}
                            <div class="alert alert-warning mt-3 mb-0 py-2 px-3 small" id="platformFeeInfo"
                                 style="display:none;">
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

<script>
document.addEventListener('DOMContentLoaded', async function () {
    await loadClientPaymentDetails();
});

// ===== Load Payment Details =====
async function loadClientPaymentDetails() {
    try {
        showLoader();

        const path    = window.location.pathname;
        const pattern = /\/admin\/meal\/payment\/details\/client\/(\d+)\/order\/(\d+)/;
        const match   = path.match(pattern);

        if (!match) {
            throw new Error('Invalid URL pattern. Cannot extract client and order IDs.');
        }

        const clientId = match[1];
        const orderId  = match[2];

        const response = await axios.get(
            `/admin/get/meal/payment/details/by/client/${clientId}/order/${orderId}`
        );

        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;

            // Header
            document.getElementById('mealPlanTitle').textContent =
                `Client Payment — Order #${data.order.order_number}`;
            document.getElementById('orderNumberText').textContent =
                `Invoice: ${data.order.invoice_no} • ${data.summary.total_items} items`;
            document.getElementById('orderStatusText').textContent =
                toTitleCase(data.order.status || 'Unknown');

            updateClientInfo(data.client);
            updatePaymentInfo(data.client_meal_order);
            updateSummaryStats(data.summary);
            renderMealOrderItems(data.items, data.dates);

        } else {
            showPageError(response.data.message || 'Failed to load payment details.');
        }

    } catch (error) {
        handleError(error);
        showPageError('An error occurred while loading payment details.');
    } finally {
        hideLoader();
    }
}

// ===== Update Client Info =====
function updateClientInfo(client) {
    document.getElementById('clientName').textContent  = client.name   || '-';
    document.getElementById('clientEmail').textContent = 'Email: '  + (client.email  || '-');
    document.getElementById('clientPhone').textContent = 'Phone: '  + (client.mobile || 'N/A');

    if (client.image) {
        document.getElementById('clientImage').src =
            `/upload/client-profile/small/${client.image}`;
    }
}

// ===== Update Payment Info =====
function updatePaymentInfo(clientMealOrder) {
    const status = clientMealOrder.payment_status || 'unknown';
    const badge  = document.getElementById('paymentStatusBadge');

    let badgeClass = 'bg-secondary';
    switch (status.toLowerCase()) {
        case 'paid':     badgeClass = 'bg-success'; break;
        case 'due':      badgeClass = 'bg-danger';  break;
        case 'pending':  badgeClass = 'bg-warning'; break;
        case 'refunded': badgeClass = 'bg-info';    break;
        case 'failed':   badgeClass = 'bg-dark';    break;
    }

    badge.className   = `badge payment-status-badge ${badgeClass}`;
    badge.textContent = toTitleCase(status);

    const payable = parseFloat(clientMealOrder.payable_amount || 0);
    const paid    = parseFloat(clientMealOrder.paid_amount    || 0);
    const balance = payable - paid;

    document.getElementById('payableAmount').textContent = formatCurrency(payable);
    document.getElementById('paidAmount').textContent    = formatCurrency(paid);
    document.getElementById('balanceAmount').textContent = formatCurrency(balance < 0 ? 0 : balance);
}

// ===== Update Summary Stats =====
function updateSummaryStats111(summary) {
    document.getElementById('summarySubtotal').textContent = formatCurrency(summary.subtotal      || 0);
    document.getElementById('summaryTax').textContent      = formatCurrency(summary.tax           || 0);
    document.getElementById('summaryTotal').textContent    = formatCurrency(summary.total_amount  || 0);
    document.getElementById('summaryPaid').textContent     = formatCurrency(summary.paid_amount   || 0);
    document.getElementById('summaryBalance').textContent  = formatCurrency(summary.balance_amount || 0);

    // Platform fee — always show
    const platformFee     = parseFloat(summary.platform_fee || 0);
    const platformFeeEl   = document.getElementById('summaryPlatformFee');
    const platformInfoEl  = document.getElementById('platformFeeInfo');

    platformFeeEl.textContent       = '- ' + formatCurrency(platformFee);
    platformInfoEl.style.display    = platformFee > 0 ? 'block' : 'none';
}

function updateSummaryStats(summary) {
    document.getElementById('summarySubtotal').textContent = formatCurrency(summary.subtotal      || 0);
    document.getElementById('summaryTax').textContent      = formatCurrency(summary.tax           || 0);
    document.getElementById('summaryTotal').textContent    = formatCurrency(summary.total_amount  || 0);
    document.getElementById('summaryPaid').textContent     = formatCurrency(summary.paid_amount   || 0);
    document.getElementById('summaryBalance').textContent  = formatCurrency(summary.balance_amount || 0);

    // Platform fee with percentage
    const platformFee      = parseFloat(summary.platform_fee  || 0);
    const subtotal         = parseFloat(summary.subtotal       || 0);
    const platformInfoEl   = document.getElementById('platformFeeInfo');
    const platformFeeEl    = document.getElementById('summaryPlatformFee');
    const platformFeePercent = document.getElementById('platformFeePercent');

    // Calculate percentage from subtotal
    const percentage = subtotal > 0
        ? ((platformFee / subtotal) * 100).toFixed(0)
        : 0;

    platformFeeEl.textContent      = '- ' + formatCurrency(platformFee);
    platformFeePercent.textContent = `(${percentage}%)`;
    platformInfoEl.style.display   = platformFee > 0 ? 'block' : 'none';
}

// ===== Render Meal Order Items =====
function renderMealOrderItems(items, dates) {
    const container = document.getElementById('mealOrderAccordion');

    if (!items || Object.keys(items).length === 0) {
        container.innerHTML = '<div class="alert alert-info">No items found.</div>';
        return;
    }

    // Sort dates descending
    const sortedDates = [...dates].sort((a, b) => new Date(b) - new Date(a));

    let html = '';

    sortedDates.forEach((date, index) => {
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
            const typeTitle = toTitleCase(type);
            const itemsList = dayItems[type];
            const typeTotal = itemsList.reduce(
                (sum, item) => sum + parseFloat(item.total_price || 0), 0
            );

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
                                        Provider: ${item.client_name || '-'}
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
                        <h6 class="mb-0 d-flex justify-content-between align-items-center">
                            <span>
                                ${typeTitle}
                                <small class="text-muted">(${itemsList.length} item${itemsList.length !== 1 ? 's' : ''})</small>
                            </span>
                            <span class="text-primary fw-bold">${formatCurrency(typeTotal)}</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">${itemsHtml}</ul>
                    </div>
                </div>`;
        });

        const dateTotal = mealTypes.reduce((total, type) => {
            return total + dayItems[type].reduce(
                (sum, item) => sum + parseFloat(item.total_price || 0), 0
            );
        }, 0);

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
}

// ===== Helpers =====
function toTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-GB', {
        style:    'currency',
        currency: 'GBP'
    }).format(parseFloat(amount) || 0);
}

function showPageError(message) {
    document.getElementById('errorText').textContent       = message;
    document.getElementById('errorMessage').style.display = 'block';
}
</script>

<style>
.hover-card {
    transition: all 0.3s ease;
}
.hover-card:hover {
    transform:  translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}
.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color:            #0d6efd;
}
.payment-status-badge {
    font-size:     0.9rem;
    padding:       0.5rem 1rem;
    border-radius: 20px;
}
</style>