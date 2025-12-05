@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="container">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-silverware-fork-knife me-2"></i>Meal Order Date Details</h5>
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
<!-- Calories Statistics Card -->
<div class="container-xxl flex-grow-1 container-p-y mb-4">
    <div class="row">
        <div class="col-xl-12 col-12 mb-4">
            <div class="card">
                <div class="card-header header-elements">
                    <h5 class="card-title mb-0">Calories Statistics</h5>
                    <div class="card-action-element ms-auto py-0">
                        <div class="dropdown">
                            <button type="button" class="btn dropdown-toggle px-0" data-bs-toggle="dropdown" aria-expanded="false" id="dateRangeDropdown">
                                <i class="mdi mdi-calendar-month-outline"></i> Last 7 Days
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" onclick="changeDateRange('today', 'Today')">Today</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" onclick="changeDateRange('yesterday', 'Yesterday')">Yesterday</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" onclick="changeDateRange('7days', 'Last 7 Days')">Last 7 Days</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" onclick="changeDateRange('current_month', 'Current Month')">Current Month</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center" onclick="changeDateRange('last_month', 'Last Month')">Last Month</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="barChart" style="width:100%; height:200px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
            <div class="row">
                <!-- Date Items (Left) -->
                <div class="col-lg-8 mb-4">
                    <div class="accordion" id="mealDateAccordion"></div>
                </div>

                <!-- Summary & Address (Right) -->
                <div class="col-lg-4">
                    <div class="border rounded p-3 shadow-sm mb-4" id="meal-summary"></div>
                    <div class="border rounded p-3 shadow-sm mb-4" id="nutrition-summary"></div>
                    <div class="border rounded p-3 shadow-sm" id="shipping-address"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let barChartInstance = null;
let mealTypeBreakdown = {};
let currentRange = '7days';

document.addEventListener('DOMContentLoaded', async function() {
    await loadMealOrderDetailsByDate();
    await loadDailyCalories("7days");
});

async function loadMealOrderDetailsByDate() {
    try {
        showLoader();
        const pathSegments = window.location.pathname.split('/');
        const orderId = pathSegments[pathSegments.length - 3];
        const date = pathSegments[pathSegments.length - 1];
        
        const response = await axios.get(`/user/get/meal-order/${orderId}/date/${date}`);

        if (response.status === 200 && response.data.status === 'success') {
            const mealCart = response.data.data.meal_cart;
            const summary = response.data.data.summary;
            const nutrition = response.data.data.nutrition;
            const shippingAddress = response.data.data.shipping_address;
            const order = response.data.data.order;
            const selectedDate = response.data.data.selected_date;

            // Format date for display
            const formattedDate = new Date(selectedDate).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Update header information
            document.getElementById('datePlanTitle').textContent = `Order #${order.order_number} - ${formattedDate}`;
            document.getElementById('dateNumberText').textContent = `${summary.total_items} items`;
            document.getElementById('selectedDateText').textContent = formattedDate;

            renderMealDateItems(mealCart);
            renderMealSummary(summary);
            renderNutritionSummary(nutrition);
            renderShippingAddress(shippingAddress);
        } else {
            document.getElementById('mealDateAccordion').innerHTML = `<div class="alert alert-info">Date details not found.</div>`;
        }
    } catch (error) {
        console.error(error);
        errorToast('Failed to load date details');
    } finally {
        hideLoader();
    }
}

function renderMealDateItems(mealCart) {
    const container = document.getElementById('mealDateAccordion');
    container.innerHTML = '';

    const mealTypes = Object.keys(mealCart);
    if (mealTypes.length === 0) {
        container.innerHTML = `<div class="alert alert-info">No items found for this date.</div>`;
        return;
    }

    mealTypes.forEach((mealType, index) => {
        const items = mealCart[mealType];
        const collapseId = `mealType${index}`;

        const mealTypeHtml = `
            <h6 class="text-primary mb-3">${toTitleCase(mealType)} (${items.length} items)</h6>
            <ul class="list-group mb-3">
                ${items.map(item => {
                    const productName = toTitleCase(item.product?.name || '');
                    const img = item.product?.image ? `/upload/product/small/${item.product.image}` : '/upload/no_image.jpg';
                    const clientName = item.client ? `${item.client.firstName} ${item.client.lastName}` : 'Unknown Provider';
                    
                    return `
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="${img}" alt="${productName}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                    <div>
                                        <strong>${productName}</strong><br>
                                        <small class="text-muted">$${parseFloat(item.unit_price || 0).toFixed(2)} each × ${item.quantity || 0}</small><br>
                                        <small class="text-info">Provider: ${clientName}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong>$${parseFloat(item.total_price || 0).toFixed(2)}</strong>
                                </div>
                            </div>
                        </li>
                    `;
                }).join('')}
            </ul>
        `;

        const block = `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                        ${toTitleCase(mealType)}
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#mealDateAccordion">
                    <div class="accordion-body">${mealTypeHtml}</div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', block);
    });
}

// Reuse the same render functions from your main order details page
function renderMealSummary(summary) {
    const container = document.getElementById('meal-summary');
    if (!summary) {
        container.innerHTML = `<div class="alert alert-warning">No summary data available.</div>`;
        return;
    }
    
    const subtotal = parseFloat(summary.subtotal || 0);
    const tax = parseFloat(summary.tax || 0);
    const taxRate = parseFloat(summary.tax_rate || 20); // Default to 20% if not provided
    const deliveryCharge = parseFloat(summary.delivery_charge || 0);
    const total = parseFloat(summary.total || 0);
    
    container.innerHTML = `
        <h5 class="mb-3">Price Summary</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
                Subtotal: <span>$${subtotal.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                Delivery Charge: <span>$${deliveryCharge.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                Tax (${taxRate}%): <span>$${tax.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between fw-bold">
                Total: <span>$${total.toFixed(2)}</span>
            </li>
        </ul>
    `;
}

function renderNutritionSummary(nutrition) {
    const container = document.getElementById('nutrition-summary');
    if (!nutrition) {
        container.innerHTML = `<div class="alert alert-warning">No nutrition data available.</div>`;
        return;
    }
    
    let caloriesByTypeHtml = '';
    
    if (nutrition.calories_by_meal_type) {
        Object.entries(nutrition.calories_by_meal_type).forEach(([mealType, calories]) => {
            const caloriesValue = parseInt(calories || 0);
            caloriesByTypeHtml += `
                <li class="list-group-item d-flex justify-content-between">
                    ${toTitleCase(mealType)}: <span>${caloriesValue} cal</span>
                </li>
            `;
        });
    }

    const totalCalories = parseInt(nutrition.total_calories || 0);
    
    container.innerHTML = `
        <h5 class="mb-3">Nutrition Summary</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between fw-bold">
                Total Calories: <span>${totalCalories} cal</span>
            </li>
            ${caloriesByTypeHtml}
        </ul>
    `;
}

function renderShippingAddress(shippingAddress) {
    const container = document.getElementById('shipping-address');
    
    if (!shippingAddress) {
        container.innerHTML = `
            <h5 class="mb-3">Shipping Address</h5>
            <p class="text-muted">No shipping address provided.</p>
        `;
        return;
    }

    const addressLines = [
        shippingAddress.name,
        shippingAddress.email,
        shippingAddress.phone,
        shippingAddress.address1,
        shippingAddress.address2,
        `${shippingAddress.city?.name || ''}${shippingAddress.county?.name ? ', ' + shippingAddress.county.name : ''}${shippingAddress.country?.name ? ', ' + shippingAddress.country.name : ''}`,
        shippingAddress.zip_code
    ].filter(Boolean);

    container.innerHTML = `
        <h5 class="mb-3">Shipping Address</h5>
        <address class="mb-0">
            ${addressLines.map(line => `<p class="mb-1">${line}</p>`).join('')}
        </address>
    `;
}

function changeDateRange(range, displayText) {
    currentRange = range;
    document.getElementById('dateRangeDropdown').innerHTML = `<i class="mdi mdi-calendar-month-outline"></i> ${displayText}`;
    loadDailyCalories(range);
}

async function loadDailyCalories(range) {
    try {
        showLoader();
        const res = await axios.get(`/user/get/calories/history?range=${range}`);

        if (res.data.status === "success") {
            mealTypeBreakdown = { ...res.data.meal_type_breakdown };
            renderBarChart(res.data.dates, res.data.calories, res.data.calories_unit, res.data.total_calories_sum, range);
        } else {
            errorToast(res.data.message);
        }
    } catch (err) {
        console.error(err);
        errorToast('Failed to load calories data');
    } finally {
        hideLoader();
    }
}

function renderBarChart(labels, data, unit, totalCalories, range) {
    mealTypeBreakdown = mealTypeBreakdown || {};
    const ctx = document.getElementById("barChart").getContext("2d");

    // Destroy previous chart
    if (barChartInstance) {
        barChartInstance.destroy();
        barChartInstance = null;
    }

    // Format labels based on range
    const formattedLabels = formatChartLabels(labels, range);

    barChartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels: formattedLabels,
            datasets: [{
                label: `Total Calories: ${totalCalories} ${unit}`,
                data: data,
                backgroundColor: "rgba(54, 162, 235, 0.2)",
                borderColor: "rgba(54, 162, 235, 1)",
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: "rgba(54, 162, 235, 1)",
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: `Calories (${unit})`
                    },
                    grid: {
                        color: "rgba(0, 0, 0, 0.1)"
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    },
                    grid: {
                        color: "rgba(0, 0, 0, 0.1)"
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: "rgba(0, 0, 0, 0.8)",
                    titleColor: "#fff",
                    bodyColor: "#fff",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        title: function(tooltipItems) {
                            // Show original date in tooltip title
                            const index = tooltipItems[0].dataIndex;
                            return labels[index] || 'Unknown Date';
                        },
                        label: function(context) {
                            const date = labels[context.dataIndex];
                            const mealTypes = mealTypeBreakdown[date] || {};
                            const lines = [];

                            lines.push(`Total: ${context.parsed.y} ${unit}`);

                            // Add meal type breakdown
                            Object.entries(mealTypes).forEach(([type, cal]) => {
                                lines.push(`${type}: ${cal} ${unit}`);
                            });

                            // If no meal type data, show message
                            if (Object.keys(mealTypes).length === 0) {
                                lines.push('No meal type data available');
                            }

                            return lines;
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animations: {
                tension: {
                    duration: 1000,
                    easing: 'linear'
                }
            }
        }
    });
}

function formatChartLabels(labels, range) {
    if (!labels || labels.length === 0) return labels;

    switch (range) {
        case 'today':
        case 'yesterday':
            // For single day, show time if available, otherwise just the date
            return labels.map(label => {
                const date = new Date(label);
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            });

        case '7days':
            // For 7 days, show short date format
            return labels.map(label => {
                const date = new Date(label);
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            });

        case 'current_month':
        case 'last_month':
            // For months, show day numbers
            return labels.map(label => {
                const date = new Date(label);
                return date.getDate().toString();
            });

        default:
            return labels.map(label => {
                const date = new Date(label);
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            });
    }
}

function toTitleCase(str) {
    if (!str) return "";
    return str.trim().toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
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
.date-link {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 500;
}
.date-link:hover {
    color: #0a58ca;
    text-decoration: underline;
}
</style>
@endsection