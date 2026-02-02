@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="container">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="mdi mdi-silverware-fork-knife me-2"></i>Meal Order Details</h5>
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
                <div>
                    <span class="badge bg-success fs-6" id="orderStatusText"></span>
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
                <!-- Order Items (Left) -->
                <div class="col-lg-8 mb-4">
                    <div class="accordion" id="mealOrderAccordion"></div>
                </div>

                <!-- Summary & Address (Right) -->
                <div class="col-lg-4">
                    <div class="border rounded p-3 shadow-sm mb-4" id="meal-summary"></div>
                    <div class="border rounded p-3 shadow-sm mb-4" id="delivery-summary"></div>
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
let deliveryStatuses = {};

document.addEventListener('DOMContentLoaded', async function() {
    await loadMealOrderDetails();
    await loadDailyCalories("7days");
});

async function loadMealOrderDetails() {
    try {
        showLoader();
        const orderId = window.location.pathname.split('/').pop();
        const response = await axios.get(`/user/get/meal-order/details/${orderId}`);

        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;
            const items = data.items;
            const summary = data.summary;
            const shippingAddress = data.shipping_address;
            const order = data.order;
            const dates = data.dates;
            deliveryStatuses = data.delivery_statuses || {};

            // Update header information
            document.getElementById('mealPlanTitle').textContent = `Order #${order.order_number}`;
            document.getElementById('orderNumberText').textContent = `${summary.total_items} items • ${order.status}`;
            document.getElementById('orderStatusText').textContent = order.status;

            renderMealOrderItems(items, dates);
            renderMealSummary(summary);
            renderDeliverySummary(items);
            renderShippingAddress(shippingAddress);
        } else {
            document.getElementById('mealOrderAccordion').innerHTML = `<div class="alert alert-info">Order not found.</div>`;
        }
    } catch (error) {
        console.error('Error loading order details:', error);
        errorToast('Failed to load order details');
    } finally {
        hideLoader();
    }
}

function renderMealOrderItems(items, dates) {
    const container = document.getElementById('mealOrderAccordion');
    container.innerHTML = '';

    if (!items || Object.keys(items).length === 0) {
        container.innerHTML = `<div class="alert alert-info">No items found in this order.</div>`;
        return;
    }

    dates.forEach((date, index) => {
        const dayItems = items[date];
        const collapseId = `mealDay${index}`;

        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'short',
            day: 'numeric'
        });

        const ledgerIds = Object.keys(dayItems);
        let dayHtml = '';

        ledgerIds.forEach(ledgerId => {
            const group = dayItems[ledgerId];
            const itemsList = group.items || [];
            const clientName = group.client_name || 'Unknown Provider';
            const mealTypeName = group.meal_type_name || 'Other';
            
            // Get meal time
            const mealTime = group.meal_time;
            const formattedMealTime = mealTime ? formatMealTime(mealTime) : '';
            
            // Get delivery info
            const deliveryStatus = group.delivery_status || 'pending';
            const deliveryStatusLabel = group.delivery_status_label || deliveryStatuses[deliveryStatus] || toTitleCase(deliveryStatus);
            const deliveryBadgeClass = getDeliveryBadgeClass(deliveryStatus);
            const deliveryPerson = group.delivery_person_name || 'Not Assigned';
            const orderTracking = group.order_tracking;

            dayHtml += `
                <div class="meal-type-section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-primary mb-0">${clientName} (${itemsList.length} items)</h6>
                        <div class="d-flex align-items-center gap-2">
                            ${formattedMealTime ? `<span class="badge bg-light text-dark"><i class="mdi mdi-clock-outline me-1"></i>${formattedMealTime}</span>` : ''}
                            <span class="badge ${deliveryBadgeClass}">${deliveryStatusLabel}</span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="mdi mdi-food me-1"></i>
                            Meal Type: ${mealTypeName}
                        </small>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="mdi mdi-account-circle me-1"></i>
                            Delivery Person: ${deliveryPerson}
                        </small>
                    </div>
                    ${orderTracking ? `
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="mdi mdi-truck-delivery me-1"></i>
                                Tracking: ${orderTracking}
                            </small>
                        </div>
                    ` : ''}
                    <ul class="list-group mb-3">
            `;

            itemsList.forEach(item => {
                const productName = toTitleCase(item.product_name || 'Unknown Product');
                const img = item.product_image ? `/upload/product/small/${item.product_image}` : '/upload/no_image.jpg';

                dayHtml += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${img}" alt="${productName}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                <div>
                                    <strong>${productName}</strong><br>
                                    <small class="text-muted">${formatCurrency(item.unit_price || 0)} each × ${item.quantity || 0}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <strong>${formatCurrency(item.total_price || 0)}</strong>
                            </div>
                        </div>
                    </li>
                `;
            });

            dayHtml += `
                    </ul>
                </div>
            `;
        });

        const block = `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                        ${formattedDate}
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#mealOrderAccordion">
                    <div class="accordion-body">${dayHtml}</div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', block);
    });
}

function renderDeliverySummary(items) {
    const container = document.getElementById('delivery-summary');
    
    if (!items || Object.keys(items).length === 0) {
        container.innerHTML = `
            <h5 class="mb-3">Delivery Status</h5>
            <p class="text-muted">No delivery information available.</p>
        `;
        return;
    }

    // Count delivery statuses across all groups
    const statusCount = {};
    let totalGroups = 0;
    let deliveryAssigned = 0;

    Object.values(items).forEach(dateItems => {
        Object.values(dateItems).forEach(group => {
            const status = group.delivery_status || 'pending';
            statusCount[status] = (statusCount[status] || 0) + 1;
            totalGroups++;
            
            if (group.delivery_person_name !== 'Not Assigned') {
                deliveryAssigned++;
            }
        });
    });

    let statusHtml = '';
    const statusOrder = ['delivered', 'arrived', 'on_the_way', 'picked_up', 'ready_for_pickup', 'preparing', 'accept_order', 'pending', 'cancelled'];
    
    statusOrder.forEach(status => {
        if (statusCount[status]) {
            const label = deliveryStatuses[status] || toTitleCase(status);
            const count = statusCount[status];
            const percentage = totalGroups > 0 ? ((count / totalGroups) * 100).toFixed(1) : '0.0';
            const badgeClass = getDeliveryBadgeClass(status);
            
            statusHtml += `
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2">
                        <div class="d-flex align-items-center">
                            <span class="badge ${badgeClass} me-2">${count}</span>
                            <span>${label}</span>
                        </div>
                        <small class="text-muted">${percentage}%</small>
                    </div>
                </div>
            `;
        }
    });

    container.innerHTML = `
        <h5 class="mb-3">Delivery Summary</h5>
        <div class="row g-2">
            ${statusHtml}
        </div>
        <div class="mt-3">
            <small class="text-muted">
                <i class="mdi mdi-information-outline me-1"></i>
                ${totalGroups} provider(s) • ${deliveryAssigned} with assigned delivery person
            </small>
        </div>
    `;
}

function renderMealSummary(summary) {
    const container = document.getElementById('meal-summary');
    if (!summary) {
        container.innerHTML = `<div class="alert alert-warning">No summary data available.</div>`;
        return;
    }
    
    // Convert to numbers
    const subtotal = parseFloat(summary.subtotal || 0);
    const tax = parseFloat(summary.tax || 0);
    const deliveryFee = parseFloat(summary.delivery_fee || 0);
    const total = parseFloat(summary.total || 0);
    
    container.innerHTML = `
        <h5 class="mb-3">Price Summary</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
                Subtotal: <span>${formatCurrency(subtotal)}</span>
            </li>
            ${deliveryFee > 0 ? `
            <li class="list-group-item d-flex justify-content-between">
                Delivery Fee: <span>${formatCurrency(deliveryFee)}</span>
            </li>
            ` : ''}
            <li class="list-group-item d-flex justify-content-between">
                Tax: <span>${formatCurrency(tax)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between fw-bold">
                Total: <span>${formatCurrency(total)}</span>
            </li>
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
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        callback: function(value, index, values) {
                            const totalLabels = this.getLabels().length;
                            if (totalLabels > 15 && (index % 2 === 0 || index === 0 || index === totalLabels - 1)) {
                                return formattedLabels[index];
                            } else if (totalLabels <= 15) {
                                return formattedLabels[index];
                            }
                            return '';
                        }
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
                            const index = tooltipItems[0].dataIndex;
                            const date = new Date(labels[index]);
                            if (!isNaN(date.getTime())) {
                                return date.toLocaleDateString('en-US', {
                                    weekday: 'long',
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                });
                            }
                            return 'Unknown Date';
                        },
                        label: function(context) {
                            const date = labels[context.dataIndex];
                            const mealTypes = mealTypeBreakdown[date] || {};
                            const lines = [];

                            lines.push(`Total: ${context.parsed.y} ${unit}`);

                            Object.entries(mealTypes).forEach(([type, cal]) => {
                                lines.push(`${type}: ${cal} ${unit}`);
                            });

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

    const sortedLabels = [...labels].sort((a, b) => new Date(a) - new Date(b));

    return sortedLabels.map(label => {
        const date = new Date(label);
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
    });
}

function formatMealTime(timeString) {
    if (!timeString) return '';
    
    try {
        if (timeString.toLowerCase().includes('am') || timeString.toLowerCase().includes('pm')) {
            return timeString;
        }
        
        const timeParts = timeString.split(':');
        
        if (timeParts.length >= 2) {
            let hour = parseInt(timeParts[0], 10);
            let minute = parseInt(timeParts[1], 10);
            
            if (isNaN(hour) || isNaN(minute)) {
                return timeString;
            }
            
            const period = hour >= 12 ? 'pm' : 'am';
            const displayHour = hour % 12 || 12;
            const displayMinute = minute.toString().padStart(2, '0');
            
            return `${displayHour}:${displayMinute} ${period}`;
        }
        
        return timeString;
    } catch (e) {
        console.error('Error formatting time:', e, 'Input:', timeString);
        return timeString;
    }
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

function toTitleCase(str) {
    if (!str) return "";
    return str.trim().toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

function formatCurrency(amount) {
    const numAmount = parseFloat(amount) || 0;
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
    }).format(numAmount);
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
.meal-type-section {
    border-left: 3px solid #0d6efd;
    padding-left: 15px;
}
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
</style>
@endsection