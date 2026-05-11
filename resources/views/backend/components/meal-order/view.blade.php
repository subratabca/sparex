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
            </div>

            <div class="row">
                <!-- Order Items (Left) -->
                <div class="col-lg-8 mb-4">
                    <div class="accordion" id="mealOrderAccordion"></div>
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


<script>
document.addEventListener('DOMContentLoaded', async function() {
    await loadMealOrderDetails();
});

async function loadMealOrderDetails() {
    try {
        showLoader();
        const orderId = window.location.pathname.split('/').pop();
        const response = await axios.get(`/admin/get/meal-order/details/${orderId}`);

        if (response.status === 200 && response.data.status === 'success') {
            const data = response.data.data;
            const mealCart = data.meal_cart;
            const summary = data.summary;
            const nutrition = data.nutrition;
            const shippingAddress = data.shipping_address;
            const order = data.order;
            const items = data.items;
            const deliveryStatuses = data.delivery_statuses || {};

            document.getElementById('mealPlanTitle').textContent = `Order #${order.order_number}`;
            document.getElementById('orderNumberText').textContent = `${summary.total_items} Items`;

            renderMealOrderItems(mealCart, items, deliveryStatuses);
            renderMealSummary(summary);
            renderNutritionSummary(nutrition);
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

function renderMealOrderItems(mealCart, allItems, deliveryStatuses) {
    const container = document.getElementById('mealOrderAccordion');
    container.innerHTML = '';

    const dates = Object.keys(mealCart);
    if (dates.length === 0) {
        container.innerHTML = `<div class="alert alert-info">No items found in this order.</div>`;
        return;
    }

    dates.forEach((date, index) => {
        const dayItems = mealCart[date];
        const collapseId = `mealDay${index}`;

        const formattedDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'short',
            day: 'numeric'
        });

        const mealTypes = Object.keys(dayItems);
        let mealTypeHtml = '';

        mealTypes.forEach(type => {
            const typeTitle = toTitleCase(type);
            const items = dayItems[type];
            
            const mealTime = findMealTime(date, type, allItems);

            // Delivery status (group badge only)
            const firstDeliveryInfo = items[0]?.delivery_info || {
                delivery_status: 'pending',
                delivery_status_label: 'Pending'
            };

            const deliveryBadgeClass = getDeliveryBadgeClass(firstDeliveryInfo.delivery_status);
            const deliveryStatusLabel = firstDeliveryInfo.delivery_status_label || deliveryStatuses[firstDeliveryInfo.delivery_status] || 'Pending';

            mealTypeHtml += `
                <div class="meal-type-section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-primary mb-0">${typeTitle} (${items.length} items)</h6>
                        <div class="d-flex align-items-center gap-2">
                            ${mealTime ? `<span class="badge bg-light text-dark"><i class="mdi mdi-clock-outline me-1"></i>${mealTime}</span>` : ''}
                            <span class="badge ${deliveryBadgeClass}">${deliveryStatusLabel}</span>
                        </div>
                    </div>
                    <ul class="list-group mb-3">
            `;

            items.forEach(item => {
                const productName = toTitleCase(item.product?.name || '');
                const img = item.product?.image ? `/upload/product/small/${item.product.image}` : '/upload/no_image.jpg';
                const clientName = item.client ? toTitleCase(`${item.client.firstName} ${item.client.lastName}`) : 'Unknown Provider';

                // ✅ CLIENT-SPECIFIC DELIVERY INFO
                const deliveryInfo = item.delivery_info || {};
                const deliveryPersonName = toTitleCase(deliveryInfo.delivery_person_name) || 'Not Assigned';
                const trackingNumber = deliveryInfo.order_tracking || null;

                mealTypeHtml += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${img}" alt="${productName}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                <div>
                                    <strong>${productName}</strong><br>
                                    <small class="text-muted">${formatCurrency(item.unit_price || 0)} each × ${item.quantity || 0}</small><br>
                                    <small class="text-info">Provider: ${clientName}</small><br>

                                    <!-- ✅ Delivery Person (Per Client) -->
                                    <small class="text-muted">
                                        <i class="mdi mdi-account-circle me-1"></i>
                                        Delivery Person: ${deliveryPersonName}
                                    </small><br>

                                    <!-- ✅ Tracking Number (Per Client) -->
                                    ${trackingNumber ? `
                                        <small class="text-muted">
                                            <i class="mdi mdi-truck-delivery me-1"></i>
                                            Tracking: ${trackingNumber}
                                        </small>
                                    ` : ''}
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
                    <div class="accordion-body">${mealTypeHtml}</div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', block);
    });
}

function findMealTime(date, mealType, allItems) {
    if (!allItems || !Array.isArray(allItems)) {
        return null;
    }
    
    // Find ALL items that match date and meal type
    const matchingItems = allItems.filter(item => {
        const itemDate = item.meal_date;
        const itemMealTypeName = item.meal_type?.name || 'Other';
        
        return itemDate === date && itemMealTypeName === mealType;
    });
    
    if (matchingItems.length > 0) {
        // Get the first matching item with a meal_time
        const itemWithTime = matchingItems.find(item => item.meal_time);
        
        if (itemWithTime) {
            return formatMealTime(itemWithTime.meal_time);
        }
    }
    
    return null;
}

function formatMealTime(timeString) {
    if (!timeString) {
        return '';
    }
    
    try {
        // If it's already in 12-hour format with AM/PM, return as-is
        if (timeString.toLowerCase().includes('am') || timeString.toLowerCase().includes('pm')) {
            return timeString;
        }
        
        // Handle time formats like "08:00:00" (with seconds) or "08:00" (without seconds)
        const timeParts = timeString.split(':');
        
        if (timeParts.length >= 2) {
            let hour = parseInt(timeParts[0], 10);
            let minute = parseInt(timeParts[1], 10);
            
            // Validate hour and minute
            if (isNaN(hour) || isNaN(minute)) {
                return timeString;
            }
            
            // Convert to 12-hour format
            const period = hour >= 12 ? 'pm' : 'am';
            const displayHour = hour % 12 || 12; // Convert 0 to 12 for midnight
            const displayMinute = minute.toString().padStart(2, '0');
            
            return `${displayHour}:${displayMinute} ${period}`;
        }
        
        return timeString; // Return as-is if format is unknown
    } catch (e) {
        console.error('Error formatting time:', e, 'Input:', timeString);
        return timeString; // Return as-is if parsing fails
    }
}

function renderMealSummary(summary) {
    const container = document.getElementById('meal-summary');
    if (!summary) {
        container.innerHTML = `<div class="alert alert-warning">No summary data available.</div>`;
        return;
    }
    
    // Convert to numbers to ensure toFixed works
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
        toTitleCase(shippingAddress.name),
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