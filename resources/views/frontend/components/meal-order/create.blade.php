@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Order Your Meals</h5>
        <input type="date" id="orderDate" class="form-control w-auto" />
      </div>

      <div class="card-body">
        <form id="orderForm">
          <div id="mealTypesContainer" class="mt-3">
            <!-- Meal Types & Menus will load dynamically -->
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">Submit Order</button>
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
    orderDate.valueAsDate = today;
    orderDate.min = today.toISOString().split('T')[0];
    orderDate.max = new Date(today.setDate(today.getDate() + 7)).toISOString().split('T')[0];

    await loadMealTypes();
    await loadMenusForDate(orderDate.value);

    orderDate.addEventListener('change', () => loadMenusForDate(orderDate.value));

    // Handle form submission
    document.getElementById('orderForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await submitOrder();
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


// Load menus for the selected date
async function loadMenusForDate(date) {
    showLoader();
    try {
        const res = await axios.get('/user/get/menus/group-by-meal-type');

        if (res.data.status === 'success') {
            const groupedMenus = res.data.data; // Already grouped by meal type
            console.log('Menus data:', groupedMenus);

            // Iterate through each meal type
            Object.keys(groupedMenus).forEach(mealTypeId => {
                const container = document.getElementById(`meal-${mealTypeId}-menus`);
                if (!container) return; // Skip if no container for this meal type

                container.innerHTML = ''; // Clear old menus

                groupedMenus[mealTypeId].forEach(menu => {
                    const menuDiv = document.createElement('div');
                    menuDiv.classList.add('col-md-4', 'mb-2');

                    // Clean description text
                    const plainDescription = menu.description
                        ? menu.description.replace(/<[^>]*>/g, '')
                        : '';

                    menuDiv.innerHTML = `
                        <label class="ckbox d-flex align-items-center">
                            <input type="checkbox" 
                                   value="${menu.id}" 
                                   id="menu-${menu.id}" 
                                   name="meal_type_${mealTypeId}[]"
                            >
                            <span class="ms-2 fw-semibold">${menu.name}</span>
                        </label>
                    `;

                    container.appendChild(menuDiv);
                });
            });
        } else {
            errorToast('Failed to load menus');
        }
    } catch (err) {
        console.error('Error response:', err.response?.data || err);
        errorToast('Error loading menus');
    } finally {
        hideLoader();
    }
}


// Submit meal order
async function submitOrder() {
    const date = document.getElementById('orderDate').value;
    if (!date) return errorToast('Please select a date');

    // Prepare the order payload
    const orderData = {
        date: date,
        items: []
    };

    // Collect checked menus per meal type
    const mealTypeContainers = document.querySelectorAll('[id^="meal-"][id$="-menus"]');
    mealTypeContainers.forEach(container => {
        const mealTypeId = parseInt(container.id.split('-')[1]); // "meal-1-menus" => 1
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
        const res = await axios.post('/user/store/meal-order', orderData, {
            headers: { 'Content-Type': 'application/json' } 
        });

        if (res.data.status === 'success') {
            successToast(res.data.message || 'Order placed successfully');
            await loadMenusForDate(date); 
        } else {
            errorToast(res.data.message || 'Failed to place order');
        }
    } catch (err) {
        console.error('Submit Order Error:', err);

        if (err.response?.data?.errors) {
            const errors = err.response.data.errors;
            Object.keys(errors).forEach(key => {
                errorToast(errors[key][0]);
            });
        } else if (err.response?.data?.message) {
            errorToast(err.response.data.message);
        } else {
            errorToast('Something went wrong while placing the order');
        }
    } finally {
        hideLoader();
    }
}

async function submitOrder22222() {
    const date = document.getElementById('orderDate').value;
    if (!date) return errorToast('Please select a date');

    const orderData = {
        date: date,
        items: []
    };

    // Iterate through each meal type container
    const mealTypeContainers = document.querySelectorAll('[id^="meal-"][id$="-menus"]');
    mealTypeContainers.forEach(container => {
        const mealTypeId = container.id.split('-')[1]; // e.g., "meal-1-menus" => "1"
        const checkboxes = container.querySelectorAll('input[type="checkbox"]:checked');

        checkboxes.forEach(cb => {
            orderData.items.push({
                meal_type_id: mealTypeId,
                menu_id: cb.value,
                quantity: 1, // Default quantity. You can add a quantity input if needed
                unit_price: 0, // Default price. Replace if you have dynamic pricing
                total_price: 0 // Default total. Can calculate as quantity * unit_price
            });
        });
    });

    if (orderData.items.length === 0) {
        return errorToast('Please select at least one menu item.');
    }

    showLoader();
    try {
        const res = await axios.post('/user/store/meal-order', orderData);

        if (res.data.status === 'success') {
            successToast(res.data.message || 'Order placed successfully');
            await loadMenusForDate(date); // Reload menus to reflect selected/disabled state
        } else {
            errorToast(res.data.message || 'Failed to place order');
        }
    } catch (err) {
        console.error(err);
        if (err.response?.data?.errors) {
            const errors = err.response.data.errors;
            Object.keys(errors).forEach(key => {
                errorToast(errors[key][0]);
            });
        } else {
            errorToast('Something went wrong');
        }
    } finally {
        hideLoader();
    }
}


// Submit meal order
async function submitOrder11111() {
    const date = document.getElementById('orderDate').value;
    if (!date) return errorToast('Please select a date');

    const formData = new FormData(document.getElementById('orderForm'));
    formData.append('date', date);

    showLoader();
    try {
        const res = await axios.post('/user/store/meal-order', formData);
        if (res.data.status === 'success') {
            successToast(res.data.message || 'Order placed successfully');
            await loadMenusForDate(date);
        } else {
            errorToast(res.data.message || 'Failed to place order');
        }
    } catch (err) {
        console.error(err);
        if (err.response?.data?.errors) {
            const errors = err.response.data.errors;
            Object.keys(errors).forEach(key => {
                errorToast(errors[key][0]);
            });
        } else {
            errorToast('Something went wrong');
        }
    } finally {
        hideLoader();
    }
}
</script>

