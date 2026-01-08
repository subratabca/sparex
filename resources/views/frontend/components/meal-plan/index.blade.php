@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="container">               
    <div class="app-academy"> 
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center" style="gap: 1rem; flex-wrap: nowrap;">

                <!-- Card Title: 20% -->
                <div class="card-title mb-0" style="flex: 0 0 20%;">
                    <h5 class="mb-1">Meal Item List</h5>
                    <p class="mb-0" id='item-count'>Total 0 meal items found</p>
                </div>

                <!-- Search Field: 50% -->
                <div style="flex: 0 0 50%;">
                    <input type="search" id="search-field" name="search-field"
                           placeholder="Find items name or meal type"
                           class="form-control w-100" />
                    <div id="search-error" class="text-danger"></div>
                </div>

                <!-- Meal Type Dropdown: 20% -->
                <div style="flex: 0 0 20%;">
                    <select id="meal-type-filter" class="form-select w-100">
                        <option value="">Search By Meal Types</option>
                    </select>
                </div>

            </div>

            <div class="card-body">
                <div class="row gy-4 mb-4" id="product-list"></div>
                <nav aria-label="Page navigation" class="d-flex align-items-center justify-content-center">
                    <ul class="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>


<script>
let debounceTimeout;
let userLatitude, userLongitude;
let selectedMealType = '';
let selectedProductId = null;
let selectedClientName = null;

// ✅ Debounce function
function debounce(func, delay) {
    return function(...args) {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// ✅ Get user location and initialize
async function getUserLocation() {
    if (navigator.geolocation) {
        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject);
            });
            userLatitude = position.coords.latitude;
            userLongitude = position.coords.longitude;
        } catch (error) {
            console.warn("Location denied or unavailable", error);
        }
    }

    await loadMealTypes(); // Load dropdown first
    await loadProducts();  // Then load products
}

// ✅ Load meal types into dropdown
async function loadMealTypes() {
    try {
        const res = await axios.get('/get/meal-types');
        const dropdown = document.getElementById('meal-type-filter');

        if (res.status === 200 && res.data.status === 'success') {
            dropdown.innerHTML = `<option value="" disabled selected>Search By Meal Types</option>`;
            res.data.data.forEach(mt => {
                const option = document.createElement('option');
                option.value = mt.id;
                option.textContent = mt.name;
                dropdown.appendChild(option);
            });
        } else {
            dropdown.innerHTML = `<option value="">No meal types found</option>`;
        }

        // ✅ Reattach change event after options are loaded
        dropdown.removeEventListener('change', handleMealTypeChange);
        dropdown.addEventListener('change', handleMealTypeChange);

    } catch (error) {
        console.error(error);
    }
}

// ✅ Handle meal type change
function handleMealTypeChange(e) {
    selectedMealType = e.target.value.trim();
    loadProducts(1);
}

// ✅ Load products with filters
async function loadProducts(page = 1) {
    try {
        showLoader();
        const searchQuery = document.querySelector('#search-field').value.trim();
        let url = `/user/search-meal?page=${page}`;

        if (searchQuery) url += `&query=${encodeURIComponent(searchQuery)}`;
        if (selectedMealType) url += `&meal_type_id=${selectedMealType}`;
        if (userLatitude && userLongitude) url += `&latitude=${userLatitude}&longitude=${userLongitude}`;

        const res = await axios.get(url);
        const paginationContainer = document.querySelector('.pagination');

        if (res.status === 200 && res.data.status === 'success') {
            const products = res.data.products.data || [];
            const total = res.data.total;
            updateProductList(products);
            updateTotalCount(total);
            updatePagination(res.data.products);
            if (total === 0) paginationContainer.innerHTML = '';
        } else {
            paginationContainer.innerHTML = '';
        }
    } catch (error) {
        console.error(error);
    } finally {
        hideLoader();
    }
}

// ✅ Update product list
function updateProductList(productList) {
    const gridViewContainer = document.getElementById('product-list');
    gridViewContainer.innerHTML = productList.map(product => {
        const isProcessing = product.status === "processing";
        const disabledStyle = isProcessing ? 'style="pointer-events: none; opacity: 0.5;"' : '';
        const productName = product.name;
        const clientFirstName = product.client_info?.first_name || '';
        const clientLastName = product.client_info?.last_name || '';
        const fullName = clientLastName ? `${clientFirstName} ${clientLastName}` : clientFirstName;
        const requestBadge = isProcessing ? `<span class="btn btn-danger">under request</span>` : '';
        const collectionAddress = !isProcessing ? `<span style='color:green'><strong>Collection Address:</strong></span>` : '';
        const productAddress = !isProcessing ? `<span><i class="mdi mdi-map-marker me-2"></i>${product.address1}</span>` : requestBadge;

        let availability = '';
        if (product.meal_types && product.meal_types.length > 0) {
            const mealTypeBadges = product.meal_types
                .map(mt => `<span class="badge rounded-pill bg-success me-1">${mt.name}</span>`)
                .join(' ');
            availability = `
                <p class="mt-2 mb-1">
                    <span class="text-dark fw-bold">Available For:</span>
                    <span>${mealTypeBadges}</span>
                </p>`;
        }

        return `
        <div class="col-sm-6 col-lg-4" ${disabledStyle}>
            <div class="card p-2 h-100 shadow-none border">
                <div class="rounded-2 text-center mb-3">
                    <a href="/product/details/${product.id}" ${disabledStyle}>
                        <img class="img-fluid" src="/upload/product/medium/${product.image}" alt="${productName}">
                    </a>
                </div>
                <div class="card-body p-3 pt-2">
                    <a href="/product/details/${product.id}" class="h5" ${disabledStyle}>${productName}</a>
                    
                    <div class="price-info mt-2">
                        <span class="me-1 text-dark">Price: </span>
                        ${product.is_free === 1 ? 
                            '<span class="text-success fw-bold ms-1">Free</span>' :
                            product.has_discount_price === 1 ?
                                `<del class="text-muted me-2">$${product.price}</del>
                                 <span class="text-success fw-bold">$${product.discount_price}</span>` :
                                `<span class="fw-bold ms-1">$${product.price}</span>`
                        }
                    </div>

                    ${availability}

                    <p class="d-flex align-items-center mt-2">
                        <span class="me-1 text-dark">Donator:</span> ${fullName}
                    </p>
                    <p class="d-flex align-items-center">${productAddress}</p>
                    <div class="progress rounded-pill mb-4" style="height: 8px">
                        <div class="progress-bar" style="width: ${product.progress}%" role="progressbar" aria-valuenow="${product.progress}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-3 text-nowrap">
                        <a class="w-80 p-1 btn btn-outline-success d-flex align-items-center justify-content-center add-to-meal-plan-btn"
                           href="javascript:void(0)" 
                           data-id="${product.id}" 
                           data-name="${productName}" 
                           data-client="${fullName}">
                           <i class="mdi mdi-plus-circle-outline me-1"></i> Add to Meal Plan
                        </a>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
}

function updateTotalCount(totalProducts) {
    const totalItemsElement = document.querySelector('.card-title p.mb-0');
    if (totalProducts > 0) {
        totalItemsElement.innerHTML = `Total ${totalProducts} items found`;
        totalItemsElement.classList.remove('text-danger'); 
        totalItemsElement.classList.add('text-success'); 
    } else {
        totalItemsElement.innerHTML = `No items found`;
        totalItemsElement.classList.remove('text-success'); 
        totalItemsElement.classList.add('text-danger'); 
    }
}

function updatePagination(paginationData) {
    const paginationContainer = document.querySelector('.pagination');
    paginationContainer.innerHTML = ''; 
    paginationData.links.forEach(link => {
        if (link.active) {
            paginationContainer.innerHTML += `
            <li class="page-item active">
                <a class="page-link" href="#">${link.label}</a>
            </li>`;
        } else if (link.url) {
            const page = new URL(link.url).searchParams.get('page');
            paginationContainer.innerHTML += `
            <li class="page-item">
                <a class="page-link" href="javascript:void(0);" onclick="loadProducts(${page})">${link.label}</a>
            </li>`;
        } else {
            paginationContainer.innerHTML += `
            <li class="page-item disabled">
                <span class="page-link">${link.label}</span>
            </li>`;
        }
    });
}

async function loadMealTypesForProduct(productId) {
    const select = document.getElementById('mealTypeSelect');
    select.innerHTML = '<option value="" disabled selected>Loading...</option>';

    try {
        const res = await axios.get(`/user/get/product-meal-types/${productId}`);
        if (res.data.status === 'success') {
            const mealTypes = res.data.meal_types;

            if (mealTypes.length > 0) {
                // ✅ Keep the default "Select Meal Type" option
                select.innerHTML = `
                    <option value="" disabled selected>Select Meal Type</option>
                    ${mealTypes.map(mt => `<option value="${mt.id}">${mt.name}</option>`).join('')}
                `;
            } else {
                select.innerHTML = `
                    <option value="" disabled selected>Select Meal Type</option>
                    <option value="">No meal types available</option>
                `;
            }
        } else {
            select.innerHTML = `
                <option value="" disabled selected>Select Meal Type</option>
                <option value="">No meal types available</option>
            `;
        }
    } catch (error) {
        select.innerHTML = `
            <option value="" disabled selected>Select Meal Type</option>
            <option value="">Failed to load</option>
        `;
    }
}

document.addEventListener('click', async function(e) {
    if (e.target.closest('.add-to-meal-plan-btn')) {
        const btn = e.target.closest('.add-to-meal-plan-btn');
        selectedProductId = btn.dataset.id;
        selectedClientName = btn.dataset.client;
        const productName = btn.dataset.name;
        document.getElementById('selectedFoodName').textContent = `${productName} by ${selectedClientName}`;
        await loadMealTypesForProduct(selectedProductId);
        new bootstrap.Modal(document.getElementById('mealPlanModal')).show();
    }
});

document.getElementById('confirmAddMeal').addEventListener('click', async function() {
  let isValid = true;
  document.querySelectorAll(".error-message").forEach(span => span.innerText = '');

    const mealType = document.getElementById('mealTypeSelect').value;
    const mealDate = document.getElementById('mealDate').value;
    const mealQuantity = document.getElementById('mealQuantity').value;



    try {
        showLoader();
        const res = await axios.post('/user/store/meal-order', {
            product_id: selectedProductId,
            meal_type_id: mealType,
            meal_date: mealDate,
            quantity: mealQuantity,
        });

        if (res.data.status === 'success') {
            successToast('Meal added successfully!');
            bootstrap.Modal.getInstance(document.getElementById('mealPlanModal')).hide();
        } else {
            errorToast(res.data.message || 'Unable to add meal.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

function handleError(error) {
  if (error.response) {
    const { status, data } = error.response;
    switch (status) {
      case 401:
        return errorToast(data?.message || "Unauthorized. Need login.");

      case 403:
        return errorToast(data?.message || "Forbidden.");

      case 404:
        return errorToast(data?.message || "Data not found.");

      case 409:
        return errorToast(data?.message || "Conflict occurred.");

      case 422:
        // ✅ Display Laravel validation errors under fields
        if (data.errors) {
          Object.entries(data.errors).forEach(([field, messages]) => {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
              errorElement.innerText = messages[0];
            }
          });
        } else {
          errorToast(data?.message || "Validation error. Please check your input.");
        }
        return; // ✅ stop execution here so no extra toast shows

      case 500:
        return errorToast(data?.error || "Internal server error. Please try again later.");

      default:
        return errorToast(data?.message || "Something went wrong.");
    }
  } else if (error.request) {
    return errorToast("No response from the server. Please check your internet connection.");
  } else {
    return errorToast(error.message || "An unexpected error occurred.");
  }
}

// ✅ Debounced search
const debouncedSearch = debounce(() => loadProducts(1), 500);
document.getElementById('search-field').addEventListener('input', debouncedSearch);

// ✅ Pagination event
document.addEventListener('click', function(e) {
    if (e.target.matches('.pagination li a')) {
        e.preventDefault();
        const page = e.target.dataset.page;
        if (page) loadProducts(page);
    }
});

// ✅ Initial load
window.onload = getUserLocation;
</script>

@endsection

@php
    use Carbon\Carbon;
    $tomorrow = Carbon::tomorrow();
    $maxDate = Carbon::now()->addDays(7);
@endphp
<!-- Meal Plan Modal -->
<div class="modal fade" id="mealPlanModal" tabindex="-1" aria-labelledby="mealPlanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="mealPlanModalLabel">Add to Meal Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="selectedFoodName" class="fw-bold text-primary"></p>

        <div class="form-group mb-3">
          <label for="mealDate" class="form-label">Select Meal Date: <span class="text-danger">*</span></label>
          <input 
              type="date" 
              id="mealDate" 
              name="meal_date"
              class="form-control" 
              min="{{ $tomorrow->format('Y-m-d') }}" 
              max="{{ $maxDate->format('Y-m-d') }}" 
              value="{{ $tomorrow->format('Y-m-d') }}"
          >
          <span class="error-message text-danger" id="meal_date-error"></span>
        </div>

        <div class="form-group mb-3">
          <label for="mealTypeSelect" class="form-label">Select Meal Type: <span class="text-danger">*</span></label>
          <select id="mealTypeSelect" name="meal_type_id" class="form-select">
            <option value="" disabled selected>Select Meal Type</option>
            <option value="1">Breakfast</option>
            <option value="2">Lunch</option>
            <option value="3">Evening Snacks</option>
            <option value="4">Dinner</option>
          </select>
          <span class="error-message text-danger" id="meal_type_id-error"></span>
        </div>

        <!-- ✅ New Quantity Field -->
        <div class="form-group mb-3">
          <label for="mealQuantity" class="form-label">Quantity: <span class="text-danger">*</span></label></label>
          <input 
              type="number" 
              id="mealQuantity" 
              name="quantity"
              class="form-control" 
              value="1" 
              min="1"
              placeholder="Enter quantity"
          >
          <span class="error-message text-danger" id="quantity-error"></span>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmAddMeal">Add</button>
      </div>
    </div>
  </div>
</div>

