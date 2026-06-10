@extends('frontend.components.dashboard.dashboard-master')

@section('dashboard-content')
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

            <!-- Calories Statistics -->
            <div class="card mb-4">
                <div class="card-header header-elements">
                    <h5 class="card-title mb-0">Calories Statistics</h5>
                    <div class="card-action-element ms-auto py-0">
                        <div class="dropdown">
                            <button type="button" class="btn dropdown-toggle px-0"
                                    data-bs-toggle="dropdown" id="dateRangeDropdown">
                                <i class="mdi mdi-calendar-month-outline"></i> Current Order
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="javascript:void(0);" class="dropdown-item"
                                       onclick="changeDateRange('current_order', 'Current Order')">Current Order</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a href="javascript:void(0);" class="dropdown-item"
                                       onclick="changeDateRange('today', 'Today')">Today</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item"
                                       onclick="changeDateRange('yesterday', 'Yesterday')">Yesterday</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item"
                                       onclick="changeDateRange('7days', 'Last 7 Days')">Last 7 Days</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a href="javascript:void(0);" class="dropdown-item"
                                       onclick="changeDateRange('current_month', 'Current Month')">Current Month</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item"
                                       onclick="changeDateRange('last_month', 'Last Month')">Last Month</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="barChart" style="width:100%;height:200px;"></canvas>
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

<!-- Delivery Tracking Modal -->
<div class="modal fade" id="deliveryStatusModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-gradient bg-primary text-white">
                <h5 class="modal-title">
                    <i class="mdi mdi-truck-delivery me-2"></i>Delivery Tracking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <strong>Delivery Progress</strong>
                        <span id="progressPercent" class="fw-bold text-primary">0%</span>
                    </div>
                    <div class="progress" style="height:10px;">
                        <div id="progressBar"
                             class="progress-bar progress-bar-striped progress-bar-animated"
                             style="width:0%"></div>
                    </div>
                </div>
                <div id="delivery-status-history" class="modern-timeline"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color:            #0d6efd;
}
.meal-type-section {
    border-left:  3px solid #0d6efd;
    padding-left: 15px;
}
.modern-timeline {
    position:     relative;
    padding-left: 30px;
}
.timeline-item {
    position:      relative;
    margin-bottom: 25px;
}
.timeline-marker {
    position:      absolute;
    left:          -18px;
    top:           5px;
    width:         14px;
    height:        14px;
    background:    #dee2e6;
    border-radius: 50%;
    border:        3px solid white;
    box-shadow:    0 0 0 2px #dee2e6;
}
.timeline-item::before {
    content:    "";
    position:   absolute;
    left:       -12px;
    top:        20px;
    width:      2px;
    height:     calc(100% + 5px);
    background: #dee2e6;
}
.timeline-item:last-child::before { display: none; }
.active-marker {
    background: #0d6efd;
    box-shadow: 0 0 0 2px #0d6efd;
    animation:  pulse 1.5s infinite;
}
.latest-status .timeline-content {
    background:    #f8f9fa;
    padding:       12px;
    border-radius: 8px;
    border-left:   4px solid #0d6efd;
}
.timeline-content { margin-left: 10px; }
@keyframes pulse {
    0%   { box-shadow: 0 0 0 0 rgba(13,110,253,0.7); }
    70%  { box-shadow: 0 0 0 8px rgba(13,110,253,0); }
    100% { box-shadow: 0 0 0 0 rgba(13,110,253,0); }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let barChartInstance  = null;
let mealTypeBreakdown = {};
let currentRange      = 'current_order';
let currentOrderId    = null;

document.addEventListener('DOMContentLoaded', async function () {
    currentOrderId = window.location.pathname.split('/').pop();
    await loadMealOrderDetails();
    await loadDailyCalories('current_order');
});

// ===== Load Order Details =====
async function loadMealOrderDetails() {
    try {
        showLoader();
        const response = await axios.get(`/user/get/meal-order/details/${currentOrderId}`);

        if (response.status === 200 && response.data.status === 'success') {
            const data            = response.data.data;
            const deliveryStatuses = data.delivery_statuses || {};

            document.getElementById('mealPlanTitle').textContent  = `Order #${data.order.order_number}`;
            document.getElementById('orderNumberText').textContent = `${data.summary.total_items} Items`;

            renderMealOrderItems(data.meal_cart, data.items, deliveryStatuses);
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
            weekday: 'long', month: 'short', day: 'numeric', year: 'numeric'
        });

        const mealTypes  = Object.keys(dayItems);
        let mealTypeHtml = '';

        mealTypes.forEach(type => {
            const typeTitle = toTitleCase(type);
            const items     = dayItems[type];
            const mealTime  = findMealTime(date, type, allItems);

            const firstDeliveryInfo = items[0]?.delivery_info || {
                delivery_status: 'pending', delivery_status_label: 'Pending'
            };
            const deliveryBadgeClass  = getDeliveryBadgeClass(firstDeliveryInfo.delivery_status);
            const deliveryStatusLabel = firstDeliveryInfo.delivery_status_label
                || deliveryStatuses[firstDeliveryInfo.delivery_status] || 'Pending';

            let itemsHtml = '';
            items.forEach(item => {
                const productName = toTitleCase(item.product?.name || '');
                const img         = item.product?.image
                    ? `/upload/product/small/${item.product.image}` : '/upload/no_image.jpg';
                const clientName  = item.client
                    ? toTitleCase(`${item.client.firstName} ${item.client.lastName}`) : 'Unknown Provider';
                const deliveryInfo     = item.delivery_info || {};
                const deliveryPerson   = toTitleCase(deliveryInfo.delivery_person_name || 'Not Assigned');
                const trackingNumber   = deliveryInfo.order_tracking || null;

                itemsHtml += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${img}" alt="${productName}"
                                     class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                <div>
                                    <strong>${productName}</strong><br>
                                    <small class="text-muted">
                                        ${formatCurrency(item.unit_price || 0)} × ${item.quantity || 0}
                                    </small><br>
                                    <small class="text-info">Provider: ${clientName}</small><br>
                                    <small class="text-muted">
                                        <i class="mdi mdi-account-circle me-1"></i>
                                        Delivery Person: ${deliveryPerson}
                                    </small>
                                    ${trackingNumber ? `
                                        <br><small class="text-primary"
                                            style="cursor:pointer;"
                                            onclick='openTrackingModal(${JSON.stringify(deliveryInfo.status_history || [])})'>
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
                            ${mealTime ? `<span class="badge bg-light text-dark">
                                <i class="mdi mdi-clock-outline me-1"></i>${mealTime}</span>` : ''}
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
                            type="button" data-bs-toggle="collapse"
                            data-bs-target="#${collapseId}">
                        ${formattedDate}
                    </button>
                </h2>
                <div id="${collapseId}"
                     class="accordion-collapse collapse ${index === 0 ? 'show' : ''}"
                     data-bs-parent="#mealOrderAccordion">
                    <div class="accordion-body">${mealTypeHtml}</div>
                </div>
            </div>`);
    });
}

// ===== Render Meal Summary =====
function renderMealSummary(summary) {
    const container = document.getElementById('meal-summary');
    if (!summary) {
        container.innerHTML = `<div class="alert alert-warning">No summary data available.</div>`;
        return;
    }

    const subtotal       = parseFloat(summary.subtotal         || 0);
    const tax            = parseFloat(summary.tax              || 0);
    const serviceFee     = parseFloat(summary.service_fee      || 0);
    const deliveryFee    = parseFloat(summary.delivery_fee     || 0);
    const total          = parseFloat(summary.total           || 0);
    const taxRate        = parseFloat(summary.tax_rate         || 0);
    const serviceFeeRate = parseFloat(summary.service_fee_rate || 0);

    container.innerHTML = `
        <h5 class="mb-3">Price Summary</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
                <span>Subtotal</span><span>${formatCurrency(subtotal)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Tax (${taxRate}%)</span><span>${formatCurrency(tax)}</span>
            </li>

            ${deliveryFee > 0 ? `
            <li class="list-group-item d-flex justify-content-between">
                <span>Delivery Fee</span><span>${formatCurrency(deliveryFee)}</span>
            </li>` : ''}

            ${serviceFee > 0 ? `
            <li class="list-group-item d-flex justify-content-between">
                <span>Service Fee (${serviceFeeRate}%)</span><span>${formatCurrency(serviceFee)}</span>
            </li>` : ''}
            
            <li class="list-group-item d-flex justify-content-between fw-bold border-top">
                <span>Total</span><span class="text-primary">${formatCurrency(total)}</span>
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

// ===== Calories Range =====
function changeDateRange(range, displayText) {
    currentRange = range;
    document.getElementById('dateRangeDropdown').innerHTML =
        `<i class="mdi mdi-calendar-month-outline"></i> ${displayText}`;
    loadDailyCalories(range);
}

async function loadDailyCalories(range) {
    try {
        showLoader();

        // Build params — pass order_id for current_order range
        const params = { range };
        if (range === 'current_order') {
            params.order_id = currentOrderId;
        }

        const res = await axios.get('/user/get/calories/history', { params });

        if (res.data.status === 'success') {
            mealTypeBreakdown = { ...res.data.meal_type_breakdown };
            renderBarChart(
                res.data.dates,
                res.data.calories,
                res.data.calories_unit,
                res.data.total_calories_sum,
                range
            );
        } else {
            errorToast(res.data.message || 'Failed to load calories data.');
        }
    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
    }
}

// ===== Bar Chart =====
function renderBarChart(labels, data, unit, totalCalories, range) {
    mealTypeBreakdown = mealTypeBreakdown || {};
    const ctx = document.getElementById('barChart').getContext('2d');

    if (barChartInstance) {
        barChartInstance.destroy();
        barChartInstance = null;
    }

    const formattedLabels = formatChartLabels(labels, range);

    barChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels:   formattedLabels,
            datasets: [{
                label:                `Total Calories: ${totalCalories} ${unit}`,
                data:                 data,
                backgroundColor:      'rgba(54,162,235,0.2)',
                borderColor:          'rgba(54,162,235,1)',
                borderWidth:          3,
                tension:              0.4,
                fill:                 true,
                pointBackgroundColor: 'rgba(54,162,235,1)',
                pointBorderColor:     '#fff',
                pointBorderWidth:     2,
                pointRadius:          5,
                pointHoverRadius:     7,
            }]
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: `Calories (${unit})` },
                    grid:  { color: 'rgba(0,0,0,0.1)' }
                },
                x: {
                    title: { display: true, text: 'Date' },
                    grid:  { color: 'rgba(0,0,0,0.1)' },
                    ticks: { maxRotation: 45, minRotation: 45 }
                }
            },
            plugins: {
                legend: {
                    display:  true,
                    position: 'top',
                    labels:   { usePointStyle: true, padding: 20 }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor:      '#fff',
                    bodyColor:       '#fff',
                    borderColor:     'rgba(54,162,235,1)',
                    borderWidth:     1,
                    padding:         12,
                    displayColors:   false,
                    callbacks: {
                        title: (items) => {
                            const date = new Date(labels[items[0].dataIndex]);
                            return !isNaN(date.getTime())
                                ? date.toLocaleDateString('en-GB', {
                                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                                  })
                                : 'Unknown Date';
                        },
                        label: (ctx) => {
                            const date      = labels[ctx.dataIndex];
                            const mealTypes = mealTypeBreakdown[date] || {};
                            const lines     = [`Total: ${ctx.parsed.y} ${unit}`];
                            Object.entries(mealTypes).forEach(([type, cal]) => {
                                lines.push(`${type}: ${cal} ${unit}`);
                            });
                            if (!Object.keys(mealTypes).length) {
                                lines.push('No meal type data available');
                            }
                            return lines;
                        }
                    }
                }
            },
            interaction: { intersect: false, mode: 'index' },
        }
    });
}

function formatChartLabels(labels, range) {
    if (!labels || labels.length === 0) return labels;
    return [...labels].sort((a, b) => new Date(a) - new Date(b)).map(label => {
        const date = new Date(label);
        return date.toLocaleDateString('en-GB', {
            weekday: 'short', month: 'short', day: 'numeric'
        });
    });
}

// ===== Helpers =====
function findMealTime(date, mealType, allItems) {
    if (!allItems || !Array.isArray(allItems)) return null;
    const match = allItems.find(item =>
        item.meal_date === date &&
        (item.meal_type?.name || 'Other') === mealType &&
        item.meal_time
    );
    return match ? formatMealTime(match.meal_time) : null;
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
        accept_order:     'bg-secondary',
        pending:          'bg-secondary',
        cancelled:        'bg-dark',
    };
    return map[status] || 'bg-secondary';
}

function toTitleCase(str) {
    if (!str) return '';
    return str.trim().toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency', currency: 'GBP'
    }).format(parseFloat(amount) || 0);
}

// ===== Tracking Modal =====
function openTrackingModal(statusHistory) {
    const container       = document.getElementById('delivery-status-history');
    const progressBar     = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');

    container.innerHTML       = '';
    progressBar.style.width   = '0%';
    progressPercent.innerText = '0%';

    if (!Array.isArray(statusHistory) || statusHistory.length === 0) {
        container.innerHTML =
            '<div class="text-center text-muted py-3">No status history available</div>';
        new bootstrap.Modal(document.getElementById('deliveryStatusModal')).show();
        return;
    }

    // statusHistory is ASC (oldest first) from controller
    const totalSteps  = statusHistory.length;
    const percentage  = Math.round((totalSteps / totalSteps) * 100);

    setTimeout(() => {
        progressBar.style.width   = percentage + '%';
        progressPercent.innerText = percentage + '%';
    }, 200);

    statusHistory.forEach((history, index) => {
        const isLatest = index === statusHistory.length - 1;

        container.insertAdjacentHTML('beforeend', `
            <div class="timeline-item ${isLatest ? 'latest-status' : ''}">
                <div class="timeline-marker ${isLatest ? 'active-marker' : ''}"></div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge ${getDeliveryBadgeClass(history.delivery_status)}">
                            ${history.status_label}
                        </span>
                        <small class="text-muted">${history.created_at}</small>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Updated by: ${history.updated_by_label}
                    </small>
                    ${history.notes ? `<div class="mt-1 text-secondary">${history.notes}</div>` : ''}
                    ${history.pick_up_at ? `<div class="mt-1 text-info">Pickup: ${history.pick_up_at}</div>` : ''}
                </div>
            </div>`);
    });

    new bootstrap.Modal(document.getElementById('deliveryStatusModal')).show();
}
</script>
@endpush