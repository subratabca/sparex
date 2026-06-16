@extends('frontend.components.dashboard.dashboard-master')

@section('dashboard-content')

<div class="container">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="mdi mdi-silverware-fork-knife me-2"></i>Meal Order Date Details
            </h5>
            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body">

            <!-- Date Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h4 class="mb-1" id="datePlanTitle">Date Details</h4>
                    <p class="mb-0 text-muted" id="dateNumberText"></p>
                </div>
                <div>
                    <span class="badge bg-info fs-6" id="selectedDateText"></span>
                </div>
            </div>

            <!-- Calories Chart -->
            <div class="card mb-4">
                <div class="card-header header-elements">
                    <h5 class="card-title mb-0">Calories Statistics</h5>
                    <div class="card-action-element ms-auto py-0">
                        <div class="dropdown">
                            <button type="button"
                                    class="btn dropdown-toggle px-0"
                                    data-bs-toggle="dropdown"
                                    id="dateRangeDropdown">
                                <i class="mdi mdi-calendar-month-outline"></i> Current Date
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="javascript:void(0);" class="dropdown-item"
                                       onclick="changeDateRange('current_date', 'Current Date')">Current Date</a></li>
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
                    <div class="accordion" id="mealDateAccordion"></div>
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

@endsection

@push('styles')
<style>
.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color:            #0d6efd;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let barChartInstance   = null;
let mealTypeBreakdown  = {};
let currentRange       = 'current_date';
let currentOrderId     = null;
let currentDate        = null;

// ===== Init =====
document.addEventListener('DOMContentLoaded', async function () {
    const segments = window.location.pathname.split('/');
    currentOrderId = segments[segments.length - 3];
    currentDate    = segments[segments.length - 1];

    await loadMealOrderDetailsByDate();
    await loadDailyCalories('current_date');
});

// ===== Load Order Details By Date =====
async function loadMealOrderDetailsByDate() {
    try {
        showLoader();

        const res = await axios.get(`/get/meal-order/${currentOrderId}/date/${currentDate}`);

        if (res.status === 200 && res.data.status === 'success') {
            const d = res.data.data;

            const formattedDate = new Date(d.selected_date + 'T00:00:00')
                .toLocaleDateString('en-GB', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                });

            document.getElementById('datePlanTitle').textContent =
                `Order #${d.order.order_number} — ${formattedDate}`;
            document.getElementById('dateNumberText').textContent =
                `${d.summary.total_items} items`;
            document.getElementById('selectedDateText').textContent = formattedDate;

            renderMealDateItems(d.meal_cart);
            renderMealSummary(d.summary);
            renderNutritionSummary(d.nutrition);
            renderShippingAddress(d.shipping_address);

        } else {
            document.getElementById('mealDateAccordion').innerHTML =
                `<div class="alert alert-info">Date details not found.</div>`;
        }
    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
    }
}

// ===== Render Meal Date Items =====
function renderMealDateItems(mealCart) {
    const container = document.getElementById('mealDateAccordion');
    container.innerHTML = '';

    const mealTypes = Object.keys(mealCart);
    if (mealTypes.length === 0) {
        container.innerHTML = `<div class="alert alert-info">No items found for this date.</div>`;
        return;
    }

    mealTypes.forEach((mealType, index) => {
        const items      = mealCart[mealType];
        const collapseId = `mealType${index}`;

        let itemsHtml = '';
        items.forEach(item => {
            const productName = toTitleCase(item.product?.name || '');
            const img         = item.product?.image
                ? `/upload/product/small/${item.product.image}`
                : '/upload/no_image.jpg';
            const clientName  = item.client
                ? toTitleCase(`${item.client.firstName} ${item.client.lastName}`)
                : 'Unknown Provider';

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
                                <small class="text-info">Provider: ${clientName}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <strong>${formatCurrency(item.total_price || 0)}</strong>
                        </div>
                    </div>
                </li>`;
        });

        container.insertAdjacentHTML('beforeend', `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#${collapseId}">
                        ${toTitleCase(mealType)}
                        (${items.length} item${items.length !== 1 ? 's' : ''})
                    </button>
                </h2>
                <div id="${collapseId}"
                     class="accordion-collapse collapse ${index === 0 ? 'show' : ''}"
                     data-bs-parent="#mealDateAccordion">
                    <div class="accordion-body">
                        <ul class="list-group mb-3">${itemsHtml}</ul>
                    </div>
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

    const subtotal       = parseFloat(summary.subtotal         || 0);
    const tax            = parseFloat(summary.tax              || 0);
    const taxRate        = parseFloat(summary.tax_rate         || 0);
    const serviceFee     = parseFloat(summary.service_fee      || 0);
    const serviceFeeRate = parseFloat(summary.service_fee_rate || 0);
    const deliveryCharge = parseFloat(summary.delivery_charge  || 0);
    const total          = parseFloat(summary.total           || 0);

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
            <li class="list-group-item d-flex justify-content-between">
                <span>Service Fee (${serviceFeeRate}%)</span>
                <span>${formatCurrency(serviceFee)}</span>
            </li>` : ''}

            ${deliveryCharge > 0 ? `
            <li class="list-group-item d-flex justify-content-between">
                <span>Delivery Charge</span>
                <span>${formatCurrency(deliveryCharge)}</span>
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

// ===== Calories Chart =====
function changeDateRange(range, displayText) {
    currentRange = range;
    document.getElementById('dateRangeDropdown').innerHTML =
        `<i class="mdi mdi-calendar-month-outline"></i> ${displayText}`;
    loadDailyCalories(range);
}

async function loadDailyCalories(range) {
    try {
        showLoader();

        // Build params — pass order_id + date for current_date range
        const params = { range };
        if (range === 'current_date') {
            params.order_id = currentOrderId;
            params.date     = currentDate;
        }

        const res = await axios.get('/get/calories/history', { params });

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

function renderBarChart(labels, data, unit, totalCalories, range) {
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
                    grid:  { color: 'rgba(0,0,0,0.1)' }
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
                        title: (tooltipItems) => labels[tooltipItems[0].dataIndex] || 'Unknown Date',
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

    const options = {
        'current_date':  { weekday: 'short', month: 'short', day: 'numeric' },
        'today':         { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' },
        'yesterday':     { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' },
        '7days':         { month: 'short', day: 'numeric' },
        'current_month': null, // day number only
        'last_month':    null, // day number only
    };

    return labels.map(label => {
        const date = new Date(label);
        if (options[range] === null) return date.getDate().toString();
        return date.toLocaleDateString('en-GB', options[range] || { month: 'short', day: 'numeric' });
    });
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
@endpush