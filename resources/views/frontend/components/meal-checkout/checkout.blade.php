@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="container">               
    <div class="card px-3">
        <form id="checkout-form" onsubmit="return processPayment(event)">
            @csrf
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-7 card-body border-end">

                    <!-- Shipping Section -->
                    <div class="card mb-4">
                        <h5 class="card-header">Shipping Details</h5>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="billings-name" placeholder="Enter shipping name" />
                                        <label for="billings-name">Name<span class="text-danger">*</span></label>
                                        <span class="error-message text-danger" id="name-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="billings-email" placeholder="Enter shipping email" />
                                        <label for="billings-email">Email Address<span class="text-danger">*</span></label>
                                        <span class="error-message text-danger" id="email-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="billings-phone" placeholder="Enter shipping phone" />
                                        <label for="billings-phone">Phone<span class="text-danger">*</span></label>
                                        <span class="error-message text-danger" id="phone-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="billings-address1" placeholder="Enter shipping address1" />
                                        <label for="billings-address1">Address1<span class="text-danger">*</span></label>
                                        <span class="error-message text-danger" id="address1-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="billings-address2" placeholder="Enter shipping address2" />
                                        <label for="billings-address2">Address2</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" id="billings-zip_code" placeholder="Enter Zip Code" />
                                        <label for="billings-zip_code">Zip Code<span class="text-danger">*</span></label>
                                        <span class="error-message text-danger" id="zip_code-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select id="country" class="form-select">
                                            <option value="">Select Country</option>
                                        </select>
                                        <label for="country">Country<span class="text-danger">*</span></label>
                                    </div>
                                    <span class="error-message text-danger" id="country-error"></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select id="county" class="form-select">
                                            <option value="">Select County</option>
                                        </select>
                                        <label for="county">County<span class="text-danger">*</span></label>
                                    </div>
                                    <span class="error-message text-danger" id="county-error"></span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating form-floating-outline">
                                        <select id="city" class="form-select">
                                            <option value="">Select City</option>
                                        </select>
                                        <label for="city">City<span class="text-danger">*</span></label>
                                    </div>
                                    <span class="error-message text-danger" id="city-error"></span>
                                </div>
                            </div>

                            <div class="row py-4 mx-2">
                                <div class="form-check mt-4 pt-2">
                                    <input class="form-check-input" type="checkbox" id="existingAddress" />
                                    <label class="form-check-label" for="existingAddress">
                                        Use my existing shipping address
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Section -->
                    <div class="card mb-4">
                        <h5 class="card-header">Select Delivery Option</h5>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md mb-md-0 mb-2">
                                    <div class="form-check custom-option custom-option-label custom-option-basic">
                                        <label class="form-check-label custom-option-content" for="self_pickup">
                                            <input type="radio" class="form-check-input" name="deliveryOption" value="self_pickup" id="self_pickup" checked />
                                            <span class="custom-option-header">
                                                <span class="h6 mb-0">Self Pickup</span>
                                                <span>Free</span>
                                            </span>
                                            <span class="custom-option-body">
                                                <small>In this case, the customer will need to come and collect the products.</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-check custom-option custom-option-label custom-option-basic">
                                        <label class="form-check-label custom-option-content" for="courier">
                                            <input type="radio" class="form-check-input" name="deliveryOption" value="courier" id="courier" />
                                            <span class="custom-option-header">
                                                <span class="h6 mb-0">Online Pickup</span>
                                                <span id="courier-charge-display">$0.00</span>
                                            </span>
                                            <span class="custom-option-body">
                                                <small>In this case, the customer will receive the product through a courier service.</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="card mb-4">
                        <h5 class="card-header">Select Payment Method</h5>
                        <div class="card-body">
                            <div class="row py-2">
                                <div class="col-md mb-md-0 mb-2">
                                    <div class="form-check custom-option custom-option-basic checked">
                                        <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="stripe">
                                            <input type="radio" name="paymentMethod" class="form-check-input" value="stripe" id="stripe" checked />
                                            <span class="custom-option-body">
                                                <img src="{{ asset('upload/payment/stripe.png') }}" alt="stripe" width="58" height="30" />
                                                <span class="ms-3">Stripe</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md mb-md-0 mb-2">
                                    <div class="form-check custom-option custom-option-basic">
                                        <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="cash">
                                            <input type="radio" name="paymentMethod" class="form-check-input" value="cash" id="cash" />
                                            <span class="custom-option-body">
                                                <img src="{{ asset('upload/payment/cash.jpg') }}" alt="cash" width="58" height="30" />
                                                <span class="ms-3">Cash</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md mb-md-0 mb-2">
                                    <div class="form-check custom-option custom-option-basic">
                                        <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="credit">
                                            <input type="radio" name="paymentMethod" class="form-check-input" value="credit" id="credit" />
                                            <span class="custom-option-body">
                                                <span class="ms-3">Credit</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Stripe Form -->
                            <div id="form-credit-card">
                                <h6 class="my-3 pt-2">Credit Card Info</h6><hr>
                                <div class="form-row">
                                    <div id="card-element"></div>
                                    <div id="card-errors" role="alert"></div>
                                </div>
                            </div>

                            <!-- Cash Form -->
                            <div id="form-cash" style="display: none;">
                                <h6 class="my-3 pt-2">Cash on Delivery</h6><hr>
                                <p class="text-muted">Cash on Delivery is a type of payment method where the recipient makes payment for the order at the time of delivery rather than in advance.</p>
                            </div>

                            <!-- Credit Form -->
                            <div id="form-credit" style="display: none;">
                                <h6 class="my-3 pt-2">Payment From Credit</h6><hr>
                                <div id="credit-message"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column — Order Summary -->
                <div class="col-lg-5 card-body">
                    <h4 class="mb-2" id="mealPlanTitle">My Weekly Meal Plan (0 items)</h4>
                    <hr>
                    <div class="accordion mt-3" id="mealCartAccordion"></div>
                    <div id="meal-summary" class="mt-4"></div>
                    <div class="d-grid mt-3">
                        <button type="submit" id="submit-btn" class="btn btn-success">
                            <span class="me-2">Proceed with Payment</span>
                            <i class="mdi mdi-arrow-right scaleX-n1-rtl"></i>
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
// ===== Global State =====
let summary          = {};
let deliveryCharge   = 0;
let userEmail        = '';
let userCreditBalance = 0;
let stripe           = null;
let cardElement      = null;
let elements         = null;
let cartData         = []; // Stores flat cart items for order submission

function toTitleCase(str) {
    if (!str) return '';
    return str.trim().toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

// ===== DOMContentLoaded =====
document.addEventListener('DOMContentLoaded', async function () {
    try {
        showLoader();

        // Initialize Stripe
        stripe   = Stripe('pk_test_51JHjNRSDYO1wlylS2t9Mdffvf6gXo7BhEkupXMj17tAoMteZHKKlP1ZooX6eaEZjOf6SHp8rJ2141rsuapAFLB3i00vysBKwyd');
        elements = stripe.elements();

        cardElement = elements.create('card', {
            style: {
                base: {
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': { color: '#aab7c4' }
                },
                invalid: { color: '#fa755a', iconColor: '#fa755a' }
            },
            hidePostalCode: true
        });

        cardElement.mount('#card-element');

        cardElement.on('change', (event) => {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent    = event.error.message;
                displayError.style.display  = 'block';
            } else {
                displayError.textContent    = '';
                displayError.style.display  = 'none';
            }
        });

        // Load user profile and cart
        await loadUserAndCheckout();
        await loadMealCart();

        // Initialize handlers
        initializeDeliveryOptionHandler();
        initializePaymentToggleHandler();

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

// ===== Load User Profile =====
async function loadUserAndCheckout() {
    try {
        showLoader();
        const userResponse = await axios.get('/user/get/profile/info');
        const userData     = userResponse.data.data;
        userEmail          = userData.email;

        await initializeAddressForm(userData);
        await checkExistingAddresses();

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// ===== Initialize Address Form =====
async function initializeAddressForm(userData) {
    try {
        showLoader();

        document.getElementById('billings-name').value     = `${userData.firstName ?? ''} ${userData.lastName ?? ''}`.trim();
        document.getElementById('billings-email').value    = userData.email    ?? '';
        document.getElementById('billings-phone').value    = userData.mobile   ?? '';
        document.getElementById('billings-address1').value = userData.address1 ?? '';
        document.getElementById('billings-address2').value = userData.address2 ?? '';
        document.getElementById('billings-zip_code').value = userData.zip_code ?? '';

        // Load location dropdowns using global config.js functions
        await loadCountries(userData.country_id);
        await loadCounties(userData.country_id, userData.county_id);
        await loadCities(userData.county_id, userData.city_id);

        // Country change
        document.getElementById('country').addEventListener('change', async function () {
            await loadCounties(this.value);
            document.getElementById('city').innerHTML = '<option value="">Select City</option>';
            updateCourierAvailability();
            await recalcCourierChargeIfNeeded();
        });

        // County change
        document.getElementById('county').addEventListener('change', async function () {
            await loadCities(this.value);
            updateCourierAvailability();
            await recalcCourierChargeIfNeeded();
        });

        // City change
        document.getElementById('city').addEventListener('change', async function () {
            updateCourierAvailability();
            await recalcCourierChargeIfNeeded();
        });

        // Address1 change
        document.getElementById('billings-address1').addEventListener('input', async function () {
            updateCourierAvailability();
            await recalcCourierChargeIfNeeded();
        });

        // Zip code change
        document.getElementById('billings-zip_code').addEventListener('input', async function () {
            updateCourierAvailability();
            await recalcCourierChargeIfNeeded();
        });

        updateCourierAvailability();

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// ===== Check Existing Addresses =====
async function checkExistingAddresses() {
    try {
        const response = await axios.get('/user/meal/shipping-addresses');
        if (response.data.data && response.data.data.length > 0) {
            setupAddressCheckbox(response.data.data);
        }
    } catch (error) {
        console.warn('No existing shipping addresses found.');
    }
}

// ===== Setup Address Checkbox =====
function setupAddressCheckbox(existingAddresses) {
    const checkbox = document.getElementById('existingAddress');

    checkbox.addEventListener('change', async function () {
        try {
            showLoader();
            if (this.checked) {
                // Use latest existing address
                const latestAddress = existingAddresses[0];
                await populateAddressForm(latestAddress);
            } else {
                // Revert to profile address
                const userResponse = await axios.get('/user/get/profile/info');
                await populateAddressForm(userResponse.data.data, true);
            }

            updateCourierAvailability();
            await recalcCourierChargeIfNeeded();

        } catch (error) {
            handleError(error);
        } finally {
            hideLoader();
        }
    });
}

// ===== Populate Address Form =====
async function populateAddressForm(address, isProfile = false) {
    if (isProfile) {
        document.getElementById('billings-name').value     = `${address.firstName ?? ''} ${address.lastName ?? ''}`.trim();
        document.getElementById('billings-phone').value    = address.mobile   ?? '';
        document.getElementById('billings-address1').value = address.address1 ?? '';
        document.getElementById('billings-address2').value = address.address2 ?? '';
        document.getElementById('billings-zip_code').value = address.zip_code ?? '';
        document.getElementById('billings-email').value    = address.email    ?? '';
    } else {
        document.getElementById('billings-name').value     = address.name     ?? '';
        document.getElementById('billings-email').value    = address.email    ?? '';
        document.getElementById('billings-phone').value    = address.phone    ?? '';
        document.getElementById('billings-address1').value = address.address1 ?? '';
        document.getElementById('billings-address2').value = address.address2 ?? '';
        document.getElementById('billings-zip_code').value = address.zip_code ?? '';
    }

    // Reload dropdowns using global config.js functions
    await loadCountries(address.country_id);
    await loadCounties(address.country_id, address.county_id);
    await loadCities(address.county_id, address.city_id);
}

// ===== Load Meal Cart =====
async function loadMealCart() {
    try {
        showLoader();
        const response = await axios.get('/user/get/meal-cart');

        if (response.status === 200 && response.data.status === 'success') {
            const mealCart = response.data.data.meal_cart;
            summary        = response.data.data.summary;

            // Store flat cart data for order submission
            cartData = [];
            Object.keys(mealCart).forEach(date => {
                Object.keys(mealCart[date]).forEach(mealTypeId => {
                    Object.values(mealCart[date][mealTypeId]).forEach(item => {
                        cartData.push({
                            meal_date:    date,
                            meal_time:    item.meal_time,
                            meal_type_id: item.meal_type?.id,
                            product_id:   item.product?.id,
                            client_id:    item.client?.id || null,
                            quantity:     item.quantity,
                            unit_price:   item.unit_price,
                            total_price:  item.total_price,
                        });
                    });
                });
            });

            document.getElementById('mealPlanTitle').textContent =
                `My Weekly Meal Plan (${summary.total_items} items)`;

            renderMealCart(mealCart);
            updateCheckoutSummary(summary, deliveryCharge);

        } else {
            document.getElementById('mealCartAccordion').innerHTML =
                `<div class="alert alert-info">No meals found.</div>`;
            document.getElementById('meal-summary').innerHTML = '';
            document.getElementById('mealPlanTitle').textContent = 'My Weekly Meal Plan (0 items)';
        }

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// ===== Render Meal Cart =====
function renderMealCart(mealCart) {
    const container = document.getElementById('mealCartAccordion');
    container.innerHTML = '';

    const dates = Object.keys(mealCart);
    if (dates.length === 0) {
        container.innerHTML = `<div class="alert alert-info">Your meal cart is empty.</div>`;
        return;
    }

    dates.forEach((date, index) => {
        const mealTypeGroups = mealCart[date];
        const collapseId     = `mealDay${index}`;

        const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
            weekday: 'long',
            month:   'short',
            day:     'numeric'
        });

        let totalItemsForDate = 0;
        Object.values(mealTypeGroups).forEach(typeItems => {
            totalItemsForDate += Object.values(typeItems).length;
        });

        let mealTypeHtml = '';

        Object.keys(mealTypeGroups).forEach(mealTypeId => {
            const items     = Object.values(mealTypeGroups[mealTypeId]);
            const typeTitle = toTitleCase(items[0]?.meal_type?.name || 'Unknown');
            const mealTime  = items[0]?.meal_time || null;

            mealTypeHtml += `
                <h6 class="mt-3 d-flex align-items-center flex-wrap gap-2">
                    <span>${typeTitle} (${items.length} items)</span>
                    ${mealTime ? `<span class="text-muted">|| Delivery Time: ${mealTime}</span>` : ''}
                </h6>
                <ul class="list-group mb-3">
            `;

            items.forEach(item => {
                const productName = toTitleCase(item.product?.name || '');
                const img         = item.product?.image
                    ? `/upload/product/small/${item.product.image}`
                    : '/upload/no_image.jpg';

                const clientName = item.client
                    ? toTitleCase([item.client.firstName, item.client.lastName].filter(Boolean).join(' '))
                    : '';

                mealTypeHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center"
                        data-cart-item-id="${item.id}">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${img}" alt="${productName}"
                                class="rounded" style="width:60px;height:60px;object-fit:cover;">
                            <div>
                                <strong>${productName}</strong><br>
                                <small>$${parseFloat(item.unit_price).toFixed(2)} each</small><br>
                                ${clientName ? `<small class="text-muted">Provider: ${clientName}</small>` : ''}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number"
                                class="form-control form-control-sm"
                                style="width:70px;"
                                value="${item.quantity}"
                                min="1"
                                onchange="handleQtyChange(this, ${item.id})">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="removeMealItem(${item.id})">&times;</button>
                        </div>
                    </li>
                `;
            });

            mealTypeHtml += `</ul>`;
        });

        const block = `
            <div class="accordion-item shadow-sm mb-3">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index !== 0 ? 'collapsed' : ''}"
                        type="button" data-bs-toggle="collapse"
                        data-bs-target="#${collapseId}">
                        <div class="d-flex justify-content-between w-100 me-2">
                            <span>${formattedDate}</span>
                            <span class="badge bg-primary">${totalItemsForDate} items</span>
                        </div>
                    </button>
                </h2>
                <div id="${collapseId}"
                    class="accordion-collapse collapse ${index === 0 ? 'show' : ''}"
                    data-bs-parent="#mealCartAccordion">
                    <div class="accordion-body">${mealTypeHtml}</div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', block);
    });
}

// ===== Qty Debounce =====
let qtyTimeout = null;
function handleQtyChange(input, itemId) {
    clearTimeout(qtyTimeout);
    qtyTimeout = setTimeout(() => {
        updateMealItem(itemId, input.value);
    }, 500);
}

// ===== Update Meal Item =====
async function updateMealItem(id, quantity) {
    if (quantity < 1) return errorToast('Quantity must be at least 1');
    try {
        showLoader();
        await axios.post('/user/meal-cart/update', { meal_item_id: id, quantity });
        await loadMealCart();
        await updateMealCartCount();
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// ===== Remove Meal Item =====
async function removeMealItem(id) {
    if (!confirm('Are you sure you want to remove this item?')) return;
    try {
        showLoader();
        await axios.post('/user/meal-cart/remove', { meal_item_id: id });
        await loadMealCart();
        await updateMealCartCount();
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// ===== Delivery Option Handler =====
function initializeDeliveryOptionHandler() {
    document.querySelectorAll('input[name="deliveryOption"]').forEach(option => {
        option.addEventListener('change', async function () {
            if (this.value === 'self_pickup') {
                deliveryCharge = 0;
                document.getElementById('courier-charge-display').textContent = '$0.00';
                updateCheckoutSummary(summary, deliveryCharge);
                return;
            }

            if (this.value === 'courier') {
                await recalcCourierChargeIfNeeded();
            }
        });
    });
}

// ===== Recalc Courier Charge =====
async function recalcCourierChargeIfNeeded() {
    const courierRadio = document.getElementById('courier');
    if (!courierRadio.checked) return;

    const cityId   = document.getElementById('city')?.value;
    const address1 = document.getElementById('billings-address1')?.value.trim();
    const zipCode  = document.getElementById('billings-zip_code')?.value.trim();

    if (!cityId || !address1 || !zipCode) {
        deliveryCharge = 0;
        document.getElementById('courier-charge-display').textContent = '$0.00';
        updateCheckoutSummary(summary, deliveryCharge);
        return;
    }

    try {
        showLoader();
        const response = await axios.get('/user/get/meal/courier-charge', {
            params: { city_id: cityId, address1: address1, zip_code: zipCode }
        });

        if (response.data.status === 'success') {
            deliveryCharge = parseFloat(response.data.total_charge);
            document.getElementById('courier-charge-display').textContent = `$${deliveryCharge.toFixed(2)}`;
            updateCheckoutSummary(summary, deliveryCharge);
        } else {
            errorToast(response.data.message || 'Failed to fetch courier charge');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// ===== Courier Availability =====
function updateCourierAvailability() {
    const courierRadio = document.getElementById('courier');
    const pickupRadio  = document.getElementById('self_pickup');
    const address1     = document.getElementById('billings-address1').value.trim();
    const zip          = document.getElementById('billings-zip_code').value.trim();

    if (!address1 || !zip) {
        courierRadio.checked  = false;
        courierRadio.disabled = true;
        pickupRadio.checked   = true;
        deliveryCharge        = 0;
        updateCheckoutSummary(summary, deliveryCharge);
    } else {
        courierRadio.disabled = false;
    }
}

// ===== Update Checkout Summary =====
function updateCheckoutSummary(currentSummary, currentDeliveryCharge = 0) {
    const container = document.getElementById('meal-summary');

    if (!currentSummary || !currentSummary.subtotal) {
        container.innerHTML = `<div class="alert alert-info">No summary available.</div>`;
        return;
    }

    const subtotal    = parseFloat(currentSummary.subtotal)    || 0;
    const tax         = parseFloat(currentSummary.tax)         || 0;
    const serviceFee  = parseFloat(currentSummary.service_fee) || 0;
    const feeRate     = currentSummary.service_fee_rate        || 0.05;
    const delivery    = parseFloat(currentDeliveryCharge)      || 0;
    const total       = subtotal + tax + serviceFee + delivery;

    container.innerHTML = `
        <h5 class="mb-3">Price Summary</h5>
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between">
                Subtotal <span>$${subtotal.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                Tax <span>$${tax.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between text-info">
                Service Fee (${(feeRate * 100).toFixed(0)}%)
                <span>$${serviceFee.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                Delivery Charge <span>$${delivery.toFixed(2)}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between fw-bold">
                Total <span id="checkout-total">$${total.toFixed(2)}</span>
            </li>
        </ul>
    `;

    checkCreditEligibility(total);
}

// ===== Get Current Total =====
function getCurrentTotal() {
    const totalEl = document.getElementById('checkout-total');
    return totalEl ? parseFloat(totalEl.textContent.replace('$', '')) || 0 : 0;
}

// ===== Check Credit Eligibility =====
async function checkCreditEligibility(total) {
    try {
        const creditResponse  = await axios.get('/user/credit-balance');
        userCreditBalance     = parseFloat(creditResponse.data.balance || 0);
    } catch (error) {
        userCreditBalance = 0;
    }

    togglePaymentForms(total);
    togglePaymentButton(total);
}

// ===== Toggle Payment Forms =====
function togglePaymentForms(total = 0) {
    document.getElementById('form-credit-card').style.display = 'none';
    document.getElementById('form-cash').style.display        = 'none';
    document.getElementById('form-credit').style.display      = 'none';

    const selected = document.querySelector('input[name="paymentMethod"]:checked')?.value;

    if (selected === 'stripe') {
        document.getElementById('form-credit-card').style.display = 'block';
    } else if (selected === 'cash') {
        document.getElementById('form-cash').style.display = 'block';
    } else if (selected === 'credit') {
        document.getElementById('form-credit').style.display = 'block';
        updateCreditMessage(total);
    }
}

// ===== Update Credit Message =====
function updateCreditMessage(total) {
    const messageEl = document.getElementById('credit-message');
    if (!messageEl) return;

    if (total > 0 && total <= userCreditBalance) {
        messageEl.innerHTML = `
            <p>You have sufficient credit. Your balance is <strong>$${userCreditBalance.toFixed(2)}</strong>.</p>
            <div class="mt-2">
                <label class="form-label">Amount to Pay from Credit</label>
                <input type="text" class="form-control" value="${total.toFixed(2)}" readonly>
            </div>
        `;
    } else {
        messageEl.innerHTML = `
            <p class="text-danger">
                Your credit balance <strong>$${userCreditBalance.toFixed(2)}</strong> 
                is insufficient for <strong>$${total.toFixed(2)}</strong>.
            </p>
            <button type="button" class="btn btn-success"
                data-bs-toggle="modal" data-bs-target="#creditLimitModal">
                <span class="tf-icon mdi mdi-cash-plus me-1"></span>Add Credit
            </button>
        `;
    }
}

// ===== Toggle Payment Button =====
function togglePaymentButton(total = 0) {
    const submitBtn    = document.getElementById('submit-btn');
    const creditRadio  = document.getElementById('credit');

    if (!submitBtn) return;

    if (creditRadio && creditRadio.checked) {
        if (total > 0 && total <= userCreditBalance) {
            submitBtn.disabled     = false;
            submitBtn.innerHTML    = 'Pay with Credit <i class="mdi mdi-arrow-right ms-1"></i>';
            submitBtn.className    = 'btn btn-success';
        } else {
            submitBtn.disabled     = true;
            submitBtn.innerHTML    = 'Insufficient Credit';
            submitBtn.className    = 'btn btn-secondary';
        }
    } else {
        submitBtn.disabled         = false;
        submitBtn.innerHTML        = 'Proceed with Payment <i class="mdi mdi-arrow-right ms-1"></i>';
        submitBtn.className        = 'btn btn-success';
    }
}

// ===== Payment Toggle Handler =====
function initializePaymentToggleHandler() {
    document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
        radio.addEventListener('change', async () => {
            const total = getCurrentTotal();
            togglePaymentForms(total);
            togglePaymentButton(total);

            if (document.getElementById('credit').checked) {
                await checkCreditEligibility(total);
            }
        });
    });
}

// ===== Collect Billing Form Data =====
function collectBillingFormData() {
    return {
        name:            document.getElementById('billings-name').value.trim(),
        email:           document.getElementById('billings-email').value.trim(),
        phone:           document.getElementById('billings-phone').value.trim(),
        address1:        document.getElementById('billings-address1').value.trim(),
        address2:        document.getElementById('billings-address2').value.trim(),
        zip_code:        document.getElementById('billings-zip_code').value.trim(),
        country_id:      document.getElementById('country')?.value || '',
        county_id:       document.getElementById('county')?.value  || '',
        city_id:         document.getElementById('city')?.value    || '',
        delivery_option: document.querySelector('input[name="deliveryOption"]:checked')?.value || null,
    };
}

// ===== Validate Billing Form =====
function validateBillingForm(formData) {
    let isValid = true;
    document.querySelectorAll('.error-message').forEach(span => span.textContent = '');

    if (!formData.name)     { showFieldError('name-error',     'Name is required');            isValid = false; }
    if (!formData.email)    { showFieldError('email-error',    'Email is required');           isValid = false; }
    else if (!isValidEmail(formData.email)) { showFieldError('email-error', 'Invalid email'); isValid = false; }
    if (!formData.phone)    { showFieldError('phone-error',    'Phone is required');           isValid = false; }
    if (!formData.address1) { showFieldError('address1-error', 'Address is required');        isValid = false; }
    if (!formData.zip_code) { showFieldError('zip_code-error', 'Zip Code is required');       isValid = false; }
    if (!formData.country_id) { showFieldError('country-error', 'Please select a country');  isValid = false; }
    if (!formData.county_id)  { showFieldError('county-error',  'Please select a county');   isValid = false; }
    if (!formData.city_id)    { showFieldError('city-error',    'Please select a city');      isValid = false; }
    if (!formData.delivery_option) { errorToast('Please select a delivery option');           isValid = false; }
    if (!getSelectedPaymentMethod()) { errorToast('Please select a payment method');          isValid = false; }

    return isValid;
}

function showFieldError(elementId, message) {
    const el = document.getElementById(elementId);
    if (el) el.textContent = message;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function getSelectedPaymentMethod() {
    return document.querySelector('input[name="paymentMethod"]:checked')?.value || null;
}

// ===== Process Payment =====
async function processPayment(event) {
    event.preventDefault();

    const submitBtn        = document.getElementById('submit-btn');
    const originalBtnHtml  = submitBtn.innerHTML;

    // Clear errors
    document.querySelectorAll('.error-message').forEach(span => span.textContent = '');
    document.getElementById('card-errors').textContent   = '';
    document.getElementById('card-errors').style.display = 'none';

    // Validate form
    const billingFormData = collectBillingFormData();
    if (!validateBillingForm(billingFormData)) return;

    // Check cart
    if (cartData.length === 0) {
        errorToast('Your meal cart is empty');
        return;
    }

    const paymentMethod = getSelectedPaymentMethod();
    const totalAmount   = getCurrentTotal();

    submitBtn.disabled  = true;
    submitBtn.innerHTML = '<span>Processing...</span><i class="mdi mdi-loading mdi-spin ms-1"></i>';
    showLoader();

    // Base request data
    let requestData = {
        meal_orders:     cartData,
        subtotal:        summary.subtotal    || 0,
        tax:             summary.tax         || 0,
        delivery_charge: deliveryCharge,
        total_amount:    totalAmount,
        customer_email:  userEmail,
        payment_method:  paymentMethod,
        ...billingFormData
    };

    let response = null;

    try {
        switch (paymentMethod) {

            case 'stripe':
                if (!stripe || !cardElement) throw new Error('Stripe not initialized. Please refresh.');

                const piResponse = await axios.post('/user/create-payment-intent', {
                    amount:      Math.round(totalAmount * 100),
                    currency:    'usd',
                    description: `Meal Order - ${cartData.length} items`,
                    metadata:    { customer_email: userEmail, order_type: 'meal_order' }
                });

                if (piResponse.data.status !== 'success') {
                    throw new Error(piResponse.data.message || 'Failed to create payment intent');
                }

                const piData          = piResponse.data.data || piResponse.data;
                const client_secret   = piData.client_secret;
                const payment_intent_id = piData.payment_intent_id || piData.id;

                if (!client_secret)       throw new Error('Missing client secret');
                if (!payment_intent_id)   throw new Error('Missing payment intent ID');

                // Get country code from global config.js function
                const countrySelect   = document.getElementById('country');
                const countryName     = countrySelect.options[countrySelect.selectedIndex]?.text || '';
                const countryCode     = getCountryCode(countryName);

                const { error, paymentIntent } = await stripe.confirmCardPayment(client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name:    billingFormData.name,
                            email:   billingFormData.email,
                            phone:   billingFormData.phone,
                            address: {
                                line1:       billingFormData.address1,
                                line2:       billingFormData.address2 || '',
                                city:        document.getElementById('city').options[document.getElementById('city').selectedIndex]?.text || '',
                                state:       document.getElementById('county').options[document.getElementById('county').selectedIndex]?.text || '',
                                country:     countryCode,
                                postal_code: billingFormData.zip_code
                            }
                        }
                    },
                    return_url: window.location.origin + '/user/meal-order'
                });

                if (error) {
                    document.getElementById('card-errors').textContent   = error.message;
                    document.getElementById('card-errors').style.display = 'block';
                    throw new Error(error.message);
                }

                if (paymentIntent?.status === 'succeeded') {
                    requestData.payment_intent_id      = payment_intent_id;
                    requestData.stripe_payment_id      = paymentIntent.id;
                    requestData.stripe_payment_method  = paymentIntent.payment_method;
                    response = await axios.post('/user/store/meal-order/by/stripe', requestData);
                } else {
                    throw new Error('Payment not completed. Status: ' + (paymentIntent?.status || 'unknown'));
                }
                break;

            case 'credit':
                const creditRes       = await axios.get('/user/credit-balance');
                const currentBalance  = parseFloat(creditRes.data.balance || 0);

                if (totalAmount > currentBalance) {
                    throw new Error(`Insufficient credit. Balance: $${currentBalance.toFixed(2)}, Required: $${totalAmount.toFixed(2)}`);
                }

                requestData.credit_amount_used  = totalAmount;
                requestData.user_credit_balance = currentBalance;
                response = await axios.post('/user/store/meal-order/by/credit', requestData);
                break;

            case 'cash':
                response = await axios.post('/user/store/meal-order/by/cash', requestData);
                break;

            default:
                throw new Error('Please select a valid payment method');
        }

        // Handle success
        if (response && response.data.status === 'success') {
            try {
                await axios.post('/user/meal-cart/clear');
            } catch (clearError) {
                console.warn('Cart clear failed:', clearError.message);
            }

            successToast('Payment successful! Your order has been placed.');

            setTimeout(() => {
                window.location.href = response.data.redirect_url || '/user/meal-order';
            }, 2000);

        } else if (response) {
            throw new Error(response.data.message || 'Failed to place order');
        }

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
        submitBtn.disabled  = false;
        submitBtn.innerHTML = originalBtnHtml;
    }
}
</script>

<style>
#card-errors {
    padding:          10px;
    margin:           10px 0;
    border-radius:    4px;
    background-color: #f8d7da;
    border:           1px solid #f5c6cb;
    color:            #721c24;
    display:          none;
}

.error-message {
    color:      #dc3545;
    font-size:  0.875rem;
    margin-top: 0.25rem;
    display:    block;
}
</style>

@endsection