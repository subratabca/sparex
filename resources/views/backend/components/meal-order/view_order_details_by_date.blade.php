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

<script>
document.addEventListener('DOMContentLoaded', async function() {
    await loadMealOrderDetailsByDate();
});

async function loadMealOrderDetailsByDate() {
    try {
        showLoader();
        const pathSegments = window.location.pathname.split('/');
        const orderId = pathSegments[pathSegments.length - 3];
        const date = pathSegments[pathSegments.length - 1];
        
        const response = await axios.get(`/admin/get/meal-order/${orderId}/date/${date}`);

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
