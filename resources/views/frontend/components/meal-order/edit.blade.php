@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Edit Meal Order</h5>
        <input type="date" id="orderDate" class="form-control w-auto" />
      </div>

      <div class="card-body">
        <form id="orderForm">
          <input type="hidden" id="updateID">
          <div id="mealTypesContainer" class="mt-3">
            <!-- Meal Types & Menus will load dynamically -->
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">Update Order</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const orderDate = document.getElementById('orderDate');
    const today = new Date();
    orderDate.min = today.toISOString().split('T')[0];
    orderDate.max = new Date(today.setDate(today.getDate() + 7)).toISOString().split('T')[0];

    // Get order ID from URL
    let url = window.location.pathname;
    let segments = url.split('/');
    let orderId = segments[segments.length - 1];
    document.getElementById('updateID').value = orderId;

    // Load meal types
    await loadMealTypes();

    // Load order details and pre-select menus
    await loadOrderDetails(orderId);

    // Handle form submission
    document.getElementById('orderForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await submitOrder(orderId);
    });
});

// Load all meal types
async function loadMealTypes() {
    try {
        const res = await axios.get('/user/get/meal-types');
        const container = document.getElementById('mealTypesContainer');
        container.innerHTML = '';

        if (res.data.status === 'success') {
            res.data.data.forEach(mealType => {
                const card = document.createElement('div');
                card.classList.add('card', 'mb-3');
                card.innerHTML = `
                    <div class="card-header bg-light">
                        <h6 class="mb-0">${mealType.name}</h6>
                    </div>
                    <div class="card-body">
                        <div id="meal-${mealType.id}-menus" class="row">
                            <div class="col-12 text-muted">Loading menus...</div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        } else {
            errorToast('Failed to load meal types');
        }
    } catch (err) {
        console.error(err);
        errorToast('Error loading meal types');
    }
}

// Load order details and pre-check menus
async function loadOrderDetails(orderId) {
    showLoader();
    try {
        const res = await axios.get(`/user/get/meal-order/details/${orderId}`);
        if (res.data.status !== 'success') return errorToast('Failed to load order details');

        const order = res.data.data;
        document.getElementById('orderDate').value = order.order_date;

        // Load menus for each meal type and pre-select
        const menuRes = await axios.get('/user/get/menus/group-by-meal-type');
        if (menuRes.data.status !== 'success') return errorToast('Failed to load menus');

        const groupedMenus = menuRes.data.data;

        Object.keys(groupedMenus).forEach(mealTypeId => {
            const container = document.getElementById(`meal-${mealTypeId}-menus`);
            if (!container) return;
            container.innerHTML = '';

            groupedMenus[mealTypeId].forEach(menu => {
                const menuDiv = document.createElement('div');
                menuDiv.classList.add('col-md-4', 'mb-2');

                // Pre-check if this menu is in the order
                const isChecked = order.items.some(item => item.menu_id === menu.id);

                menuDiv.innerHTML = `
                    <label class="ckbox d-flex align-items-center">
                        <input type="checkbox" 
                               value="${menu.id}" 
                               id="menu-${menu.id}" 
                               name="meal_type_${mealTypeId}[]"
                               ${isChecked ? 'checked' : ''}
                        >
                        <span class="ms-2 fw-semibold">${menu.name}</span>
                    </label>
                `;
                container.appendChild(menuDiv);
            });
        });
    } catch (err) {
        console.error(err);
        errorToast('Error loading order details');
    } finally {
        hideLoader();
    }
}

// Submit order update using POST
async function submitOrder(orderId) {
    const date = document.getElementById('orderDate').value;
    if (!date) return errorToast('Please select a date');

    const orderData = {
        id: orderId,
        date: date,
        items: []
    };

    const mealTypeContainers = document.querySelectorAll('[id^="meal-"][id$="-menus"]');
    mealTypeContainers.forEach(container => {
        const mealTypeId = parseInt(container.id.split('-')[1]);
        const checkboxes = container.querySelectorAll('input[type="checkbox"]:checked');

        checkboxes.forEach(cb => {
            orderData.items.push({
                meal_type_id: mealTypeId,
                menu_id: parseInt(cb.value),
                quantity: 1,
                unit_price: 0,
                total_price: 0
            });
        });
    });

    if (orderData.items.length === 0) {
        return errorToast('Please select at least one menu item.');
    }

    showLoader();
    try {
        const res = await axios.post('/user/update/meal-order', orderData, {
            headers: { 'Content-Type': 'application/json' }
        });

        if (res.data.status === 'success') {
            successToast(res.data.message || 'Order updated successfully');
        } else {
            errorToast(res.data.message || 'Failed to update order');
        }
    } catch (err) {
        console.error(err);
        errorToast('Something went wrong while updating the order');
    } finally {
        hideLoader();
    }
}
</script>
