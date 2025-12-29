@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="container">
    <h3 class="mb-4" id="mealPlanTitle">My Weekly Meal Plan</h3>

    <div class="row">
        <!-- Accordion (Left) -->
        <div class="col-lg-8 mb-4">
            <div class="accordion" id="mealCartAccordion"></div>
        </div>

        <!-- Summary (Right) -->
        <div class="col-lg-4">
            <div class="border rounded p-3 shadow-sm" id="meal-summary"></div>

            <div class="d-grid mt-4">
                <a href="{{ route('meal.checkout') }}">
                    <button class="btn btn-info btn-lg">Proceed to Checkout</button>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', async () => {
    await loadMealCart();
});

function toTitleCase(str) {
    if (!str) return "";
    return str.trim().toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

function formatTime(timeString) {
    if (!timeString) return "Not specified";
    try {
        const [hours, minutes] = timeString.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        const formattedMinutes = minutes.padStart(2, '0');
        return `${hour12}:${formattedMinutes} ${ampm}`;
    } catch (e) {
        return timeString;
    }
}

async function loadMealCart() {
    try {
        showLoader();

        const response = await axios.get('/user/get/meal-cart');

        if (response.status === 200 && response.data.status === 'success') {
            const mealCart = response.data.data.meal_cart;
            const summary = response.data.data.summary;

            // Update title with total items
            const title = document.getElementById('mealPlanTitle');
            title.textContent = `My Weekly Meal Plan (${summary.total_items} items)`;

            renderMealCart(mealCart);
            renderMealSummary(summary);
        } else {
            document.getElementById('mealCartAccordion').innerHTML = `<div class="alert alert-info">No meals found.</div>`;
            document.getElementById('meal-summary').innerHTML = '';
            document.getElementById('mealPlanTitle').textContent = 'My Weekly Meal Plan (0 items)';
        }
    } catch (error) {
        console.error(error);
        errorToast('Failed to load meal cart');
    } finally {
        hideLoader();
    }
}

function renderMealCart(mealCart) {
    const container = document.getElementById('mealCartAccordion');
    container.innerHTML = '';

    const dates = Object.keys(mealCart);
    if (dates.length === 0) {
        container.innerHTML = `<div class="alert alert-info">Your meal cart is empty.</div>`;
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

        // Group items by meal type
        const mealTypeGroups = {};
        dayItems.forEach(item => {
            const mealTypeName = item.meal_type?.name || 'Unknown';
            if (!mealTypeGroups[mealTypeName]) {
                mealTypeGroups[mealTypeName] = [];
            }
            mealTypeGroups[mealTypeName].push(item);
        });

        let mealTypeHtml = '';
        Object.keys(mealTypeGroups).forEach(type => {
            const typeTitle = toTitleCase(type);
            const items = mealTypeGroups[type];
            
            // Get the common meal time for this meal type (all items should have same time)
            const mealTime = items[0]?.meal_time;
            const formattedTime = formatTime(mealTime);
            
            // Show meal type with count and delivery time
            mealTypeHtml += `
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold">${typeTitle} (${items.length} items)</h6>
                        <div class="text-primary">
                            <i class="fas fa-clock me-1"></i>
                            <strong>Delivery Time:</strong> ${formattedTime}
                        </div>
                    </div>
                    <div class="border rounded p-3 bg-light">
                        <ul class="list-group">
            `;

            items.forEach(item => {
                const productName = toTitleCase(item.product?.name || '');
                const img = item.product?.image ? `/upload/product/small/${item.product.image}` : '/upload/no_image.jpg';

                mealTypeHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${img}" alt="${productName}" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                            <div>
                                <strong>${productName}</strong><br>
                                <small class="text-muted">$${parseFloat(item.unit_price).toFixed(2)} each</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="me-3">
                                <small>Qty:</small>
                                <input type="number" class="form-control form-control-sm d-inline-block" style="width: 70px;" value="${item.quantity}" min="1" onchange="updateMealItem(${item.id}, this.value)">
                            </div>

                            <button class="btn btn-sm btn-outline-danger" onclick="removeMealItem(${item.id})">&times;</button>
                        </div>
                    </li>
                `;
            });

            mealTypeHtml += `
                        </ul>
                        <div class="mt-2 text-end">
                            <small class="text-muted">Subtotal for ${typeTitle}: $${items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0).toFixed(2)}</small>
                        </div>
                    </div>
                </div>
            `;
        });

        const block = `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                        <div class="d-flex justify-content-between w-100">
                            <span>${formattedDate}</span>
                            <span class="badge bg-primary">${dayItems.length} items</span>
                        </div>
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#mealCartAccordion">
                    <div class="accordion-body">${mealTypeHtml}</div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', block);
    });
}

function renderMealSummary(summary) {
    const container = document.getElementById('meal-summary');
    container.innerHTML = `
        <h5 class="mb-3">Price Summary</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
                Subtotal: <span>$${summary.subtotal.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                Tax: <span>$${summary.tax.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between fw-bold">
                Total: <span>$${summary.total.toFixed(2)}</span>
            </li>
        </ul>
        <div class="mt-3 text-center">
            <small class="text-muted">${summary.total_items} items in cart</small>
        </div>
    `;
}

async function updateMealItem(id, quantity) {
    if (quantity < 1) {
        errorToast('Quantity must be at least 1');
        await loadMealCart(); // Reload to reset the input value
        return;
    }
    try {
        const response = await axios.post('/user/meal-cart/update', { meal_item_id: id, quantity });
        if (response.data.status === 'success') {
            await loadMealCart();
            successToast('Quantity updated successfully');
        } else {
            errorToast('Failed to update item');
        }
    } catch (error) {
        errorToast('Failed to update item');
    }
}

async function removeMealItem(id) {
    //if (!confirm('Are you sure you want to remove this item from your meal plan?')) return;
    
    try {
        const response = await axios.post('/user/meal-cart/remove', { meal_item_id: id });
        if (response.data.status === 'success') {
            await loadMealCart();
            successToast('Item removed successfully');
        } else {
            errorToast('Failed to remove item');
        }
    } catch (error) {
        errorToast('Failed to remove item');
    }
}
</script>

<style>
.accordion-button {
    font-weight: 600;
    background-color: #f8f9fa;
}

.accordion-button:not(.collapsed) {
    background-color: #e3f2fd;
    color: #0d6efd;
    box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
}

.accordion-button .badge {
    font-size: 0.8em;
    padding: 0.35em 0.65em;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.list-group-item {
    border-left: none;
    border-right: none;
    padding: 1rem 0.75rem;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.text-primary i {
    color: #0d6efd;
}

.form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

.btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
}

h6.fw-bold {
    color: #495057;
    font-size: 1.1rem;
}
</style>