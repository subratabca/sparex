@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="container">               
    <div class="app-academy"> 
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between bg-white" style="gap: 1rem;">

                <!-- Go to Meal Plan Button -->
                <a href="{{ route('meal.order') }}" type="button" class="btn btn-primary waves-effect waves-light">
                    <span class="tf-icon mdi mdi-plus me-1"></span>Meal Order List
                </a>

                <!-- Add Credit Limit Button -->
                <button type="button" class="btn btn-success waves-effect waves-light"
                        data-bs-toggle="modal" data-bs-target="#creditLimitModal">
                    <span class="tf-icon mdi mdi-cash-plus me-1"></span>Add Credit Limit
                </button>

                <!-- 📅 Date Input -->
                <div>
                    <label for="meal-date" class="form-label fw-bold mb-1">Select Date:</label>
                    <input type="date" id="meal-date" class="form-control" style="max-width: 220px;">
                </div>

                <!-- ⏰ Time Input -->
                <div>
                    <label for="meal-time" class="form-label fw-bold mb-1">Select Time:</label>
                    <input type="time" id="meal-time" class="form-control" style="max-width: 150px;" value="12:00">
                </div>

                <!-- 🍽️ Meal Type Buttons -->
                <div id="meal-type-buttons" class="d-flex flex-wrap gap-2 mt-3 mt-md-0"></div>
            </div>

            <div class="card-body">
                <!-- ✅ Keyword Checkboxes -->
                <div id="keyword-section" class="mb-3" style="display: none;">
                    <h6 class="fw-bold mb-2">Filter by Keywords:</h6>
                    <div id="keyword-checkboxes" class="d-flex flex-wrap gap-3 mb-3"></div>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="text" id="keyword-input" class="form-control" style="max-width: 350px;" readonly placeholder="Selected keywords will appear here...">
                        <button id="search-btn" class="btn btn-primary fw-semibold px-4">Search</button>
                    </div>
                    <small id="keyword-error" class="text-danger mt-1 d-block" style="display:none;"></small>
                </div>

                <!-- ✅ Product List -->
                <div class="row gy-4 mb-4" id="product-list"></div>

                <nav aria-label="Page navigation" class="d-flex align-items-center justify-content-center">
                    <ul class="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add to Meal Plan Modal -->
<div class="modal fade" id="addMealPlanModal" tabindex="-1" aria-labelledby="addMealPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="addMealPlanModalLabel">Add to Meal Plan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="modal-product-image" 
                         src="{{ asset('upload/product/small/no_image.jpg') }}" 
                         alt="Product Image" 
                         class="rounded-3 shadow-sm" 
                         style="width: 160px; height: 160px; object-fit: cover;">
                </div>

                <h6 id="modal-product-name" class="fw-bold text-center mb-2"></h6>
                <div id="modal-provided-by" class="text-center mb-3"></div>

                <!-- Meal Type -->
                <div class="mb-3">
                    <label for="modal-meal-type" class="form-label fw-semibold">Meal Type:</label>
                    <select id="modal-meal-type" class="form-select">
                        <option value="">Select a meal type</option>
                    </select>
                </div>

                <!-- Meal Date -->
                <div class="mb-3">
                    <label for="modal-meal-date" class="form-label fw-semibold">Date:</label>
                    <input type="date" id="modal-meal-date" class="form-control">
                </div>

                <!-- Meal Time -->
                <div class="mb-3">
                    <label for="modal-meal-time" class="form-label fw-semibold">Time:</label>
                    <input type="time" id="modal-meal-time" class="form-control">
                </div>

                <!-- Quantity -->
                <div class="mb-3">
                    <label for="meal-quantity" class="form-label fw-semibold">Quantity:</label>
                    <input type="number" id="meal-quantity" class="form-control" value="1" min="1">
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-add-meal" class="btn btn-primary">Add to Plan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Nutrient Modal -->
<div class="modal fade" id="viewNutrientModal" tabindex="-1" aria-labelledby="viewNutrientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-semibold" id="viewNutrientModalLabel">Product Nutrients</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="nutrient-list" class="row g-2"></div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let userLatitude       = null;
let userLongitude      = null;
let selectedMealTypeId = null;
let selectedKeywords   = [];
let selectedProduct    = null;
let allProducts        = [];
let selectedMealDate   = null;
let selectedMealTime   = null;
let customerMealTimes  = {};

document.addEventListener("DOMContentLoaded", async () => {
    try {
        showLoader();
        setDateRange();
        await loadMealTypes();
        await getUserLocation();

        const urlParams       = new URLSearchParams(window.location.search);
        const selectedTypeName = urlParams.get('meal_type');
        const selectedDate     = urlParams.get('meal_date');

        if (selectedTypeName) {
            const btn = Array.from(document.querySelectorAll('#meal-type-buttons button'))
                             .find(b => b.textContent.trim().toLowerCase() === selectedTypeName.toLowerCase());
            if (btn) btn.click();
        }

        if (selectedDate) {
            document.getElementById('meal-date').value = selectedDate;
        }

        document.getElementById('meal-time').addEventListener('change', function() {
            selectedMealTime = this.value;
        });

        document.getElementById('meal-date').addEventListener('change', function() {
            updateTimeForSelectedMealType();
        });

        selectedMealTime = document.getElementById('meal-time').value;

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

async function updateTimeForSelectedMealType() {
    const date       = document.getElementById('meal-date').value;
    const mealTypeId = selectedMealTypeId;

    if (!date || !mealTypeId) return;

    const key          = `${date}_${mealTypeId}`;
    const existingTime = customerMealTimes[key];

    if (existingTime) {
        document.getElementById('meal-time').value = existingTime;
        selectedMealTime = existingTime;
    } else {
        setDefaultTimeForMealType(mealTypeId);
    }
}

function setDefaultTimeForMealType(mealTypeId) {
    const mealTypeButton = document.querySelector('#meal-type-buttons button.active');
    if (!mealTypeButton) return;

    const mealTypeName = mealTypeButton.textContent.trim().toLowerCase();
    const defaultTimes = {
        'breakfast': '08:00',
        'lunch':     '12:00',
        'snacks':    '16:00',
        'dinner':    '19:00'
    };

    const defaultTime = defaultTimes[mealTypeName] || '12:00';
    document.getElementById('meal-time').value = defaultTime;
    selectedMealTime = defaultTime;
}

async function getUserLocation() {
    if (navigator.geolocation) {
        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject);
            });
            userLatitude  = position.coords.latitude;
            userLongitude = position.coords.longitude;
        } catch (error) {
            console.warn("Location denied or unavailable", error);
        }
    }
}

function setDateRange() {
    const dateInput = document.getElementById("meal-date");
    const today     = new Date();
    const tomorrow  = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const maxDate = new Date(today);
    maxDate.setDate(today.getDate() + 7);

    dateInput.min   = tomorrow.toISOString().split("T")[0];
    dateInput.max   = maxDate.toISOString().split("T")[0];
    dateInput.value = tomorrow.toISOString().split("T")[0];
}

async function loadMealTypes() {
    const buttonContainer = document.getElementById('meal-type-buttons');
    buttonContainer.innerHTML = `<div class="text-muted">Loading meal types...</div>`;

    try {
        const res = await axios.get('/get/meal-types');
        if (res.status === 200 && res.data.status === 'success' && res.data.data.length > 0) {
            buttonContainer.innerHTML = '';
            res.data.data.forEach(mealType => {
                const btn       = document.createElement('button');
                btn.className   = 'btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold text-capitalize shadow-sm hover-effect';
                btn.textContent = mealType.name;
                btn.onclick     = async (event) => {
                    await selectMealType(event, mealType.id);
                };
                buttonContainer.appendChild(btn);
            });
        } else {
            buttonContainer.innerHTML = `<div class="text-danger">No meal types found.</div>`;
        }
    } catch (error) {
        buttonContainer.innerHTML = `<div class="text-danger">Failed to load meal types.</div>`;
    }
}

async function selectMealType(event, mealTypeId) {
    document.querySelectorAll('#meal-type-buttons button').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    selectedMealTypeId = mealTypeId;
    await updateTimeForSelectedMealType();
    await loadMealKeywords(mealTypeId);
}

async function loadMealKeywords(mealTypeId) {
    const keywordSection    = document.getElementById('keyword-section');
    const keywordContainer  = document.getElementById('keyword-checkboxes');
    keywordSection.style.display = 'block';
    keywordContainer.innerHTML   = `<div class="text-muted">Loading keywords...</div>`;

    try {
        showLoader();
        const res = await axios.get(`/get/meal-keywords/${mealTypeId}`);
        if (res.status === 200 && res.data.status === 'success' && res.data.data.length > 0) {
            keywordContainer.innerHTML = '';
            res.data.data.forEach(keyword => {
                const div       = document.createElement('div');
                div.className   = 'form-check';
                div.innerHTML   = `
                    <input class="form-check-input keyword-checkbox" type="checkbox" value="${keyword.name}" id="kw-${keyword.id}">
                    <label class="form-check-label" for="kw-${keyword.id}">${keyword.name}</label>
                `;
                keywordContainer.appendChild(div);
            });

            document.querySelectorAll('.keyword-checkbox').forEach(chk => {
                chk.addEventListener('change', handleKeywordSelection);
            });
        } else {
            keywordContainer.innerHTML = `<div class="text-danger">No keywords found for this meal type.</div>`;
        }
    } catch (error) {
        keywordContainer.innerHTML = `<div class="text-danger">Failed to load keywords.</div>`;
        handleError(error);
    } finally {
        hideLoader();
    }
}

function handleKeywordSelection() {
    selectedKeywords = Array.from(document.querySelectorAll('.keyword-checkbox:checked')).map(cb => cb.value);
    document.getElementById('keyword-input').value = selectedKeywords.join(', ');
}

document.getElementById('search-btn').addEventListener('click', async () => {
    const errorMsg = document.getElementById('keyword-error');
    errorMsg.style.display = 'none';
    errorMsg.textContent   = '';

    if (!selectedMealTypeId) {
        errorMsg.textContent   = 'Please select a meal type.';
        errorMsg.style.display = 'block';
        return;
    }

    if (selectedKeywords.length === 0) {
        errorMsg.textContent   = 'Please select at least one keyword.';
        errorMsg.style.display = 'block';
        return;
    }

    await searchProducts(selectedKeywords);
});

async function searchProducts(keywords, page = 1) {
    const productList       = document.getElementById('product-list');
    productList.innerHTML   = `<div class="text-muted text-center py-3">Searching products...</div>`;

    try {
        showLoader();
        const res = await axios.post(`/search/products?page=${page}`, {
            keywords,
            meal_type_id: selectedMealTypeId,
            latitude:     userLatitude,
            longitude:    userLongitude
        });

        if (res.status === 200 && res.data.status === 'success' && res.data.products.data.length > 0) {
            renderProducts(res.data.products.data);
            updatePagination(res.data.products);
        } else {
            productList.innerHTML = `<div class="text-danger text-center py-3">No products found.</div>`;
            document.querySelector('.pagination').innerHTML = '';
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function renderProducts(products) {
    allProducts             = products;
    const productList       = document.getElementById('product-list');
    productList.innerHTML   = '';

    products.forEach((p, index) => {
        const mealTypesHTML = (p.meal_types && p.meal_types.length > 0)
            ? p.meal_types.map(mt => `
                <span class="badge rounded-pill bg-primary text-white me-1 mb-1">${mt.name}</span>
              `).join('')
            : '<span class="text-muted">N/A</span>';

        const category   = p.category?.name ?? 'N/A';
        const providedBy = (p.client_info && p.client_info.last_name)
            ? `<p class="text-muted small mb-2"><strong>Provided by:</strong> ${p.client_info.first_name} ${p.client_info.last_name}</p>`
            : '';

        const caloriesInfo = (p.nutrients && p.nutrients.calories)
            ? `<p class="text-success small mb-2"><strong>Calories:</strong> ${p.nutrients.calories} ${p.nutrients.calories_unit}</p>`
            : '';

        const productName  = p.name.replace(/\b\w/g, l => l.toUpperCase());
        const hasNutrient  = p.nutrients && Object.values(p.nutrients).some(val => val !== null && val !== '');

        const card = `
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <a href="/meal/details/${p.id}">
                        <img src="{{ asset('upload/product/medium') }}/${p.image}" 
                             class="card-img-top rounded-top-3" 
                             alt="${p.name}" 
                             style="height: 200px; object-fit: cover;">
                    </a>
                    <div class="card-body text-center">
                        <h6 class="fw-bold mb-2">${productName}</h6>
                        ${caloriesInfo}
                        <p class="text-muted mb-1"><strong>Category:</strong> ${category}</p>
                        <div class="mb-2">
                            <strong>Available for:</strong><br>${mealTypesHTML}
                        </div>
                        ${providedBy}
                        <p class="fw-semibold text-primary mb-3">$${p.price}</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-outline-primary rounded-pill px-3" 
                                onclick="openAddMealModal(${index})">
                                Add to Meal Plan
                            </button>
                            ${hasNutrient
                                ? `<button class="btn btn-outline-success rounded-pill px-3" 
                                      onclick="openNutrientModal(${index})">
                                      View Nutrient
                                   </button>`
                                : ''
                            }
                        </div>
                    </div>
                </div>
            </div>
        `;

        productList.insertAdjacentHTML('beforeend', card);
    });
}

function openNutrientModal(index) {
    const product       = allProducts[index];
    const nutrientList  = document.getElementById('nutrient-list');
    nutrientList.innerHTML = '';

    if (!product.nutrients) return;

    Object.keys(product.nutrients).forEach(key => {
        if (key.endsWith('_unit')) return;

        const value = product.nutrients[key];
        const unit  = product.nutrients[key + '_unit'] ?? '';

        if (value !== null && value !== '') {
            const div = document.createElement('div');
            div.classList.add('col-6');
            div.innerHTML = `<strong>${formatNutrientName(key)}:</strong> ${value} ${unit}`;
            nutrientList.appendChild(div);
        }
    });

    new bootstrap.Modal(document.getElementById('viewNutrientModal')).show();
}

function formatNutrientName(key) {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function openAddMealModal(index) {
    const product    = allProducts[index];
    selectedProduct  = product;
    const modal      = new bootstrap.Modal(document.getElementById('addMealPlanModal'));

    document.getElementById('modal-product-name').textContent = product.name || 'N/A';

    const providedByHTML = (product.client_info && product.client_info.last_name)
        ? `<p class="text-muted small mb-2"><strong>Provided by:</strong> 
            ${product.client_info.first_name} ${product.client_info.last_name}</p>`
        : '';
    document.getElementById('modal-provided-by').innerHTML = providedByHTML;

    const mealTypeSelect        = document.getElementById('modal-meal-type');
    mealTypeSelect.innerHTML    = '<option value="">Select a meal type</option>';
    if (product.meal_types && product.meal_types.length > 0) {
        product.meal_types.forEach(mt => {
            const selected = (mt.id === selectedMealTypeId) ? 'selected' : '';
            mealTypeSelect.insertAdjacentHTML('beforeend',
                `<option value="${mt.id}" ${selected}>${mt.name}</option>`
            );
        });
    }

    const mainDateInput = document.getElementById('meal-date');
    const modalDateInput = document.getElementById('modal-meal-date');
    const mainTimeInput = document.getElementById('meal-time');
    const modalTimeInput = document.getElementById('modal-meal-time');

    const today    = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const maxDate = new Date(today);
    maxDate.setDate(today.getDate() + 7);

    modalDateInput.min   = tomorrow.toISOString().split("T")[0];
    modalDateInput.max   = maxDate.toISOString().split("T")[0];
    modalDateInput.value = mainDateInput.value || tomorrow.toISOString().split("T")[0];
    modalTimeInput.value = mainTimeInput.value || '12:00';

    const imageElement = document.getElementById('modal-product-image');
    imageElement.src   = product.image
        ? `{{ asset('upload/product/small') }}/${product.image}`
        : `{{ asset('upload/product/small/no_image.jpg') }}`;

    modal.show();
}

document.getElementById('confirm-add-meal').addEventListener('click', async () => {
    const mealTypeSelect = document.getElementById('modal-meal-type');
    const mealDateInput  = document.getElementById('modal-meal-date');
    const mealTimeInput  = document.getElementById('modal-meal-time');
    const quantityInput  = document.getElementById('meal-quantity');

    const mealTypeId    = parseInt(mealTypeSelect.value);
    const selectedDate  = mealDateInput.value;
    const selectedTime  = mealTimeInput.value;
    const quantity      = parseInt(quantityInput.value);

    if (!selectedProduct)          return errorToast('No product selected.');
    if (!mealTypeId)               return errorToast('Please select a meal type.');
    if (!selectedDate)             return errorToast('Please choose a date.');
    if (!selectedTime)             return errorToast('Please choose a time.');
    if (!quantity || quantity <= 0) return errorToast('Invalid quantity.');

    const key          = `${selectedDate}_${mealTypeId}`;
    const existingTime = customerMealTimes[key];

    if (existingTime && existingTime !== selectedTime) {
        let mealTypeName      = 'this meal type';
        const selectedOption  = mealTypeSelect.options[mealTypeSelect.selectedIndex];
        if (selectedOption) mealTypeName = selectedOption.text;
        return errorToast(`For ${selectedDate}, all ${mealTypeName} items must have the same time. You already selected ${mealTypeName} at ${existingTime}. Please use ${existingTime} or remove existing items first.`);
    }

    try {
        showLoader();
        const res = await axios.post('/store/meal-cart', {
            product_id:   selectedProduct.id,
            meal_type_id: mealTypeId,
            meal_date:    selectedDate,
            meal_time:    selectedTime,
            quantity:     quantity
        });

        if (res.status === 201 && res.data.status === 'success') {
            successToast(res.data.message || 'Meal added successfully!');
            customerMealTimes[key] = selectedTime;
            bootstrap.Modal.getInstance(document.getElementById('addMealPlanModal')).hide();
            await updateMealCartCount();
        } else {
            errorToast(res.data.message || 'Failed to add to meal plan.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

function updatePagination(paginationData) {
    const paginationContainer   = document.querySelector('.pagination');
    paginationContainer.innerHTML = '';

    const { current_page, last_page } = paginationData;

    const createPageItem = (label, page, disabled = false, active = false) => {
        const li    = document.createElement('li');
        li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;
        const a     = document.createElement('a');
        a.className = 'page-link';
        a.href      = '#';
        a.textContent = label;
        if (!disabled && !active) {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                searchProducts(selectedKeywords, page);
            });
        }
        li.appendChild(a);
        return li;
    };

    paginationContainer.appendChild(createPageItem('« Prev', current_page - 1, current_page === 1));
    for (let i = 1; i <= last_page; i++) {
        paginationContainer.appendChild(createPageItem(i, i, false, i === current_page));
    }
    paginationContainer.appendChild(createPageItem('Next »', current_page + 1, current_page === last_page));
}
</script>

<style>
#meal-type-buttons button {
    transition: all 0.3s ease;
}
#meal-type-buttons button:hover {
    background-color: #0d6efd;
    color: white;
    transform: translateY(-2px);
}
#meal-type-buttons button.active {
    background-color: #0d6efd;
    color: white;
    box-shadow: 0 0 10px rgba(13, 110, 253, 0.4);
}
.form-check-label {
    text-transform: capitalize;
}
</style>

@endsection