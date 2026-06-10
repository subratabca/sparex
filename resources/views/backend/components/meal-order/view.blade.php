<div class="container">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="mdi mdi-silverware-fork-knife me-2"></i>Meal Order Details
            </h5>
            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body">

            <!-- Order Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="mb-1" id="mealPlanTitle">Order Details</h4>
                    <p class="mb-0 text-muted" id="orderNumberText"></p>
                </div>
            </div>

            <div class="row">
                <!-- Order Items -->
                <div class="col-lg-8 mb-4">
                    <div class="accordion" id="mealOrderAccordion"></div>
                </div>

                <!-- Summary & Address -->
                <div class="col-lg-4">
                    <div class="border rounded p-3 shadow-sm mb-4" id="meal-summary"></div>
                    <div class="border rounded p-3 shadow-sm mb-4" id="nutrition-summary"></div>
                    <div class="border rounded p-3 shadow-sm"      id="shipping-address"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    await loadMealOrderDetails();
});

// ===== Load Order Details =====
async function loadMealOrderDetails() {
    try {
        showLoader();
        const orderId  = window.location.pathname.split('/').pop();
        const response = await axios.get(`/admin/get/meal-order/details/${orderId}`);

        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;

            document.getElementById('mealPlanTitle').textContent  =
                `Order #${data.order.order_number}`;
            document.getElementById('orderNumberText').textContent =
                `${data.summary.total_items} Items`;

            renderMealOrderItems(data.meal_cart, data.items, data.delivery_statuses || {});
            renderMealSummary(data.summary);
            renderNutritionSummary(data.nutrition);
            renderShippingAddress(data.shipping_address);

        } else {
            document.getElementById('mealOrderAccordion').innerHTML =
                `<div class="alert alert-info">Order not found.</div>`;
        }
    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
    }
}

// ===== Render Meal Order Items =====
function renderMealOrderItems(mealCart, allItems, deliveryStatuses) {
    const container = document.getElementById('mealOrderAccordion');
    container.innerHTML = '';

    const dates = Object.keys(mealCart);
    if (dates.length === 0) {
        container.innerHTML = `<div class="alert alert-info">No items found in this order.</div>`;
        return;
    }

    dates.forEach((date, index) => {
        const dayItems   = mealCart[date];
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
            const items     = dayItems[type];
            const mealTime  = findMealTime(date, type, allItems);

            const firstDeliveryInfo = items[0]?.delivery_info || {
                delivery_status:       'pending',
                delivery_status_label: 'Pending'
            };

            const deliveryBadgeClass  = getDeliveryBadgeClass(firstDeliveryInfo.delivery_status);
            const deliveryStatusLabel = firstDeliveryInfo.delivery_status_label
                || deliveryStatuses[firstDeliveryInfo.delivery_status]
                || 'Pending';

            let itemsHtml = '';
            items.forEach(item => {
                const productName      = toTitleCase(item.product?.name || '');
                const img              = item.product?.image
                    ? `/upload/product/small/${item.product.image}`
                    : '/upload/no_image.jpg';
                const clientName       = item.client
                    ? toTitleCase(`${item.client.firstName} ${item.client.lastName}`)
                    : 'Unknown Provider';
                const deliveryInfo     = item.delivery_info || {};
                const deliveryPerson   = toTitleCase(deliveryInfo.delivery_person_name || 'Not Assigned');
                const trackingNumber   = deliveryInfo.order_tracking || null;

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
                                    <small class="text-info">Provider: ${clientName}</small><br>
                                    <small class="text-muted">
                                        <i class="mdi mdi-account-circle me-1"></i>
                                        Delivery: ${deliveryPerson}
                                    </small>
                                    ${trackingNumber ? `
                                        <br><small class="text-muted">
                                            <i class="mdi mdi-truck-delivery me-1"></i>
                                            Tracking: ${trackingNumber}
                                        </small>` : ''}
                                </div>
                            </div>
                            <div class="text-end">
                                <strong>${formatCurrency(item.total_price || 0)}</strong>
                            </div>
                        </div>
                    </li>`;
            });

            mealTypeHtml += `
                <div class="meal-type-section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-primary mb-0">
                            ${typeTitle} (${items.length} item${items.length !== 1 ? 's' : ''})
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            ${mealTime
                                ? `<span class="badge bg-light text-dark">
                                       <i class="mdi mdi-clock-outline me-1"></i>${mealTime}
                                   </span>`
                                : ''}
                            <span class="badge ${deliveryBadgeClass}">${deliveryStatusLabel}</span>
                        </div>
                    </div>
                    <ul class="list-group mb-3">${itemsHtml}</ul>
                </div>`;
        });

        container.insertAdjacentHTML('beforeend', `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#${collapseId}">
                        ${formattedDate}
                    </button>
                </h2>
                <div id="${collapseId}"
                     class="accordion-collapse collapse ${index === 0 ? 'show' : ''}"
                     data-bs-parent="#mealOrderAccordion">
                    <div class="accordion-body">${mealTypeHtml}</div>
                </div>
            </div>`
        );
    });
}

// ===== Render Meal Summary =====
function renderMealSummary(summary) {
    const container = document.getElementById('meal-summary');
    if (!summary) {
        container.innerHTML = `<div class="alert alert-warning">No summary data available.</div>`;
        return;
    }

    const subtotal           = parseFloat(summary.subtotal            || 0);
    const tax                = parseFloat(summary.tax                 || 0);
    const serviceFee         = parseFloat(summary.service_fee         || 0);
    const clientPlatformFee  = parseFloat(summary.client_platform_fee || 0);
    const riderPlatformFee   = parseFloat(summary.rider_platform_fee  || 0);
    const deliveryFee        = parseFloat(summary.delivery_fee        || 0);
    const total              = parseFloat(summary.total               || 0);
    const serviceFeeRate     = ((summary.service_fee_rate      || 0) * 100).toFixed(0);
    const taxRate            = ((summary.tax_rate              || 0) * 100).toFixed(0);
    const platformFeeRate    = ((summary.platform_fee_rate     || 0) * 100).toFixed(0);
    const riderFeeRate       = ((summary.rider_platform_fee_rate|| 0) * 100).toFixed(0);

    container.innerHTML = `
        <h5 class="mb-3">Price Summary</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
                <span>Subtotal</span>
                <span>${formatCurrency(subtotal)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Tax (${taxRate}%)</span>
                <span>${formatCurrency(tax)}</span>
            </li>
            ${serviceFee > 0 ? `
            <li class="list-group-item d-flex justify-content-between text-success">
                <span>
                    Service Fee (${serviceFeeRate}%)
                    <small class="d-block text-muted">(From Customer)</small>
                </span>
                <span>${formatCurrency(serviceFee)}</span>
            </li>` : ''}
            ${clientPlatformFee > 0 ? `
            <li class="list-group-item d-flex justify-content-between text-success">
                <span>
                    Client Platform Fee (${platformFeeRate}%)
                    <small class="d-block text-muted">(From Restaurants)</small>
                </span>
                <span>${formatCurrency(clientPlatformFee)}</span>
            </li>` : ''}
            ${riderPlatformFee > 0 ? `
            <li class="list-group-item d-flex justify-content-between text-success">
                <span>
                    Rider Platform Fee (${riderFeeRate}%)
                    <small class="d-block text-muted">(From Riders)</small>
                </span>
                <span>${formatCurrency(riderPlatformFee)}</span>
            </li>` : ''}
            ${deliveryFee > 0 ? `
            <li class="list-group-item d-flex justify-content-between">
                <span>Delivery Fee</span>
                <span>${formatCurrency(deliveryFee)}</span>
            </li>` : ''}
            <li class="list-group-item d-flex justify-content-between fw-bold border-top">
                <span>Total</span>
                <span class="text-primary">${formatCurrency(total)}</span>
            </li>
        </ul>`;
}

// ===== Render Nutrition Summary =====
function renderNutritionSummary(nutrition) {
    const container = document.getElementById('nutrition-summary');
    if (!nutrition) {
        container.innerHTML = `<div class="alert alert-warning">No nutrition data available.</div>`;
        return;
    }

    let caloriesByTypeHtml = '';
    if (nutrition.calories_by_meal_type) {
        Object.entries(nutrition.calories_by_meal_type).forEach(([mealType, calories]) => {
            caloriesByTypeHtml += `
                <li class="list-group-item d-flex justify-content-between">
                    <span>${toTitleCase(mealType)}</span>
                    <span>${parseInt(calories || 0)} cal</span>
                </li>`;
        });
    }

    container.innerHTML = `
        <h5 class="mb-3">Nutrition Summary</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between fw-bold">
                <span>Total Calories</span>
                <span>${parseInt(nutrition.total_calories || 0)} cal</span>
            </li>
            ${caloriesByTypeHtml}
        </ul>`;
}

// ===== Render Shipping Address =====
function renderShippingAddress(shippingAddress) {
    const container = document.getElementById('shipping-address');

    if (!shippingAddress) {
        container.innerHTML = `
            <h5 class="mb-3">Shipping Address</h5>
            <p class="text-muted">No shipping address provided.</p>`;
        return;
    }

    const city    = shippingAddress.city?.name    || '';
    const county  = shippingAddress.county?.name  || '';
    const country = shippingAddress.country?.name || '';

    const locationLine = [city, county, country].filter(Boolean).join(', ');

    const lines = [
        toTitleCase(shippingAddress.name),
        shippingAddress.email,
        shippingAddress.phone,
        shippingAddress.address1,
        shippingAddress.address2,
        locationLine,
        shippingAddress.zip_code,
    ].filter(Boolean);

    container.innerHTML = `
        <h5 class="mb-3">Shipping Address</h5>
        <address class="mb-0">
            ${lines.map(line => `<p class="mb-1">${line}</p>`).join('')}
        </address>`;
}

// ===== Find Meal Time =====
function findMealTime(date, mealType, allItems) {
    if (!allItems || !Array.isArray(allItems)) return null;

    const match = allItems.find(item =>
        item.meal_date === date &&
        (item.meal_type?.name || 'Other') === mealType &&
        item.meal_time
    );

    return match ? formatMealTime(match.meal_time) : null;
}

// ===== Format Meal Time =====
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
    const displayMin  = minute.toString().padStart(2, '0');

    return `${displayHour}:${displayMin} ${period}`;
}

// ===== Delivery Badge Class =====
function getDeliveryBadgeClass(status) {
    const map = {
        delivered:       'bg-success',
        arrived:         'bg-primary',
        on_the_way:      'bg-info',
        picked_up:       'bg-info',
        ready_for_pickup:'bg-warning',
        preparing:       'bg-warning',
        accept_order:    'bg-secondary',
        pending:         'bg-secondary',
        cancelled:       'bg-dark',
    };
    return map[status] || 'bg-secondary';
}

// ===== Helpers =====
function toTitleCase(str) {
    if (!str) return '';
    return str.trim().toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-GB', {
        style:    'currency',
        currency: 'GBP'
    }).format(parseFloat(amount) || 0);
}
</script>

<style>
.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color:            #0d6efd;
}
.meal-type-section {
    border-left:  3px solid #0d6efd;
    padding-left: 15px;
}
</style>