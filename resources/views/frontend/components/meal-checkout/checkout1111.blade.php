@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="container">
  <div class="card px-3">
    <form id="checkout-form" onsubmit="return processPayment(event)">
      @csrf
      <div class="row">
        <div class="col-lg-7 card-body border-end">
          <!--Start Shipping Section -->
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
                    <span class="error-message text-danger" id="address2-error"></span>
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
                    <label for="country">Country</label>
                  </div>
                  <span class="error-message text-danger" id="country-error"></span>
                </div>
                <div class="col-md-4">
                  <div class="form-floating form-floating-outline">
                    <select id="county" class="form-select">
                      <option value="">Select County</option>
                    </select>
                    <label for="county">County</label>
                  </div>
                  <span class="error-message text-danger" id="county-error"></span>
                </div>
                <div class="col-md-4">
                  <div class="form-floating form-floating-outline">
                    <select id="city" class="form-select">
                      <option value="">Select City</option>
                    </select>
                    <label for="city">City</label>
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
                  <span class="error-message text-danger d-block" id="address-error"></span>
                </div>
              </div>

            </div>
          </div>
          <!--End Shipping Section -->

          <!-- Start Delivery Section -->
          <div class="card mb-4">
            <h5 class="card-header">Select Delivery Option</h5>
            <div class="card-body">
              <div class="row">
                <div class="col-md mb-md-0 mb-2">
                  <div class="form-check custom-option custom-option-label custom-option-basic">
                    <label class="form-check-label custom-option-content" for="customRadioTemp5">
                      <input type="radio" class="form-check-input" name="deliveryOption" value="self_pickup" id="self_pickup"
                      checked />
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
                    <label class="form-check-label custom-option-content" for="customRadioTemp6">
                      <input type="radio" class="form-check-input" name="deliveryOption" value="courier" id="courier" />
                      <span class="custom-option-header">
                        <span class="h6 mb-0">Courier Service</span>
                        <span>$0.00</span>
                      </span>
                      <span class="custom-option-body">
                        <small>In this case, the customer will receive the product through a courier service and will be required to make payment.</small>
                      </span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- End Delivery Section -->

          <!--Start Payment Section -->
          <div class="card mb-4">
            <h5 class="card-header">Select Payment Method</h5>
            <div class="card-body">
              <div class="row py-2">
                <div class="col-md mb-md-0 mb-2">
                  <div class="form-check custom-option custom-option-basic checked">
                    <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="stripe">
                      <input type="radio" name="paymentMethod" class="form-check-input" value="stripe" id="stripe" checked />
                      <span class="custom-option-body">
                        <img src="{{ asset('upload/payment/stripe.png') }}" alt="stripe" width="58" height="30" 
                        data-app-light-img="{{ asset('upload/payment/stripe.png') }}"
                        data-app-dark-img="{{ asset('upload/payment/stripe.png') }}" />
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
                        <img src="{{ asset('upload/payment/cash.jpg') }}" alt="cash" width="58" height="30"
                        data-app-light-img="{{ asset('upload/payment/cash.jpg') }}"
                        data-app-dark-img="{{ asset('upload/payment/cash.jpg') }}" />
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

              <!-- Add stripe payment description section -->
              <div id="form-credit-card">
                <h6 class="my-3 pt-2">Credit Card Info</h6><hr>
                <div class="row g-4">
                  <div class="form-row">
                    <label for="card-element"></label>
                    <div id="card-element">
                        <!-- A Stripe Element will be inserted here. -->
                    </div>

                    <!-- Used to display form errors. -->
                    <div id="card-errors" role="alert"></div>
                  </div>
                </div>
              </div>
              <!-- End stripe payment description section -->

              <!-- Add cash payment description section -->
              <div id="form-cash" style="display: none;">
                <h6 class="my-3 pt-2">Cash on Delivery</h6><hr>
                <div class="row g-4">
                  <div class="col-12">
                    <p class="text-muted">
                      Cash on Delivery is a type of payment method where the recipient makes payment for the order
                      at the time of delivery rather than in advance.
                    </p>
                  </div>
                </div>
              </div>
              <!-- End cash payment description section -->

              <!-- Add credit payment description section -->
              <div id="form-credit" style="display: none;">
                  <h6 class="my-3 pt-2">Payment From Credit</h6><hr>
                  <div class="row g-4">
                      <div class="col-12">
                          <p class="text-muted" id="credit-message">
                              
                          </p>
                      </div>
                  </div>
              </div>


              <!-- End credit payment description section -->

            </div>
          </div>
          <!--End Payment Section -->

        </div>
        <!-- Start Order Summary Section -->
        <div class="col-lg-5 card-body">

            <h4 class="mb-2" id="mealPlanTitle">My Weekly Meal Plan (0 items)</h4>
            <hr>

            <div class="accordion mt-3" id="mealCartAccordion"></div>

            <div id="meal-summary" class="mt-4"></div>

            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-success">
                    <span class="me-2">Proceed with Payment</span>
                    <i class="mdi mdi-arrow-right scaleX-n1-rtl"></i>
                </button>
            </div>
        </div>
        <!-- End Order Summary Section -->
      </div>
    </form>
  </div>
</div>


<script>
let summary = {}; 
let deliveryCharge = 0; 
let userEmail = "";
let userCreditBalance = 0;
let stripe = null;
let cardElement = null;
let elements = null;
let countryCodeMap = {}; // Store country codes

function toTitleCase(str) {
    if (!str) return "";
    return str.trim().toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

document.addEventListener("DOMContentLoaded", async function () {
    // Initialize Stripe with your publishable key
    stripe = Stripe('pk_test_51JHjNRSDYO1wlylS2t9Mdffvf6gXo7BhEkupXMj17tAoMteZHKKlP1ZooX6eaEZjOf6SHp8rJ2141rsuapAFLB3i00vysBKwyd');
    
    // Create Stripe Elements
    elements = stripe.elements();
    const style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': { color: '#aab7c4' }
        },
        invalid: { color: '#fa755a', iconColor: '#fa755a' }
    };

    cardElement = elements.create('card', { 
        style: style,
        hidePostalCode: true
    });
    
    cardElement.mount('#card-element');

    // Handle real-time validation errors
    cardElement.on('change', (event) => {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
            displayError.style.color = '#dc3545';
            displayError.style.display = 'block';
        } else {
            displayError.textContent = '';
            displayError.style.display = 'none';
        }
    });

    try {
        showLoader();
        await loadUserAndCheckout();
        await loadMealCart();

    } catch (error) {
        errorToast("Something went wrong while loading data");
    } finally {
        hideLoader();
    }

    initializeDeliveryOptionHandler();
    initializePaymentToggleHandler();
});

async function loadUserAndCheckout() {
    const userResponse = await axios.get('/user/get/profile/info');

    userEmail = userResponse.data.data.email;
    await initializeAddressForm(userResponse.data.data);
    await checkExistingAddresses(userEmail);
}

async function loadMealCart() {
    try {
        const response = await axios.get('/user/get/meal-cart');

        if (response.status === 200 && response.data.status === 'success') {

            const mealCart = response.data.data.meal_cart;
            //const summary = response.data.data.summary;
            summary = response.data.data.summary;

            document.getElementById('mealPlanTitle').textContent =
                `My Weekly Meal Plan (${summary.total_items} items)`;

            renderMealCart(mealCart);
            updateCheckoutSummary(summary, deliveryCharge)

        } else {
            document.getElementById('mealCartAccordion').innerHTML =
                `<div class="alert alert-info">No meals found.</div>`;
            document.getElementById('meal-summary').innerHTML = '';
            document.getElementById('mealPlanTitle').textContent =
                'My Weekly Meal Plan (0 items)';
        }

    } catch (error) {
        errorToast("Failed to load meal cart");
    }
}

// Function to get country code from country name
function getCountryCode(countryName) {
    const countryCodes = {
        'United States': 'US',
        'United Kingdom': 'GB',
        'Canada': 'CA',
        'Australia': 'AU',
        'Germany': 'DE',
        'France': 'FR',
        'Spain': 'ES',
        'Italy': 'IT',
        'Japan': 'JP',
        'China': 'CN',
        'India': 'IN',
        'Brazil': 'BR',
        'Mexico': 'MX',
        'Russia': 'RU',
        'South Korea': 'KR',
        'Netherlands': 'NL',
        'Switzerland': 'CH',
        'Sweden': 'SE',
        'Norway': 'NO',
        'Denmark': 'DK',
        'Finland': 'FI',
        'Ireland': 'IE',
        'Poland': 'PL',
        'Portugal': 'PT',
        'Austria': 'AT',
        'Belgium': 'BE',
        'Greece': 'GR',
        'Turkey': 'TR',
        'Saudi Arabia': 'SA',
        'United Arab Emirates': 'AE',
        'South Africa': 'ZA',
        'Egypt': 'EG',
        'Nigeria': 'NG',
        'Kenya': 'KE',
        'Argentina': 'AR',
        'Chile': 'CL',
        'Colombia': 'CO',
        'Peru': 'PE',
        'Venezuela': 'VE',
        'New Zealand': 'NZ',
        'Singapore': 'SG',
        'Malaysia': 'MY',
        'Thailand': 'TH',
        'Vietnam': 'VN',
        'Philippines': 'PH',
        'Indonesia': 'ID',
        'Pakistan': 'PK',
        'Bangladesh': 'BD',
        'Sri Lanka': 'LK',
        'Israel': 'IL',
        'Iran': 'IR',
        'Iraq': 'IQ',
        'Afghanistan': 'AF',
        'England': 'GB', // England is part of United Kingdom
        'Scotland': 'GB', // Scotland is part of United Kingdom
        'Wales': 'GB', // Wales is part of United Kingdom
        'Northern Ireland': 'GB' // Northern Ireland is part of United Kingdom
    };
    
    return countryCodes[countryName] || '';
}

async function loadCountries(selectedId = '') {
    const response = await axios.get('/countries');
    const dropdown = document.getElementById('country');
    dropdown.innerHTML = '<option value="" disabled>Select Country</option>';
    
    response.data.data.forEach(country => {
        const option = new Option(country.name, country.id);
        option.selected = country.id === selectedId;
        option.dataset.countryCode = country.country_code || getCountryCode(country.name);
        dropdown.add(option);
        countryCodeMap[country.id] = country.country_code || getCountryCode(country.name);
    });
}

async function loadCounties(countryId, selectedId = '') {
    const dropdown = document.getElementById('county');
    dropdown.innerHTML = '<option value="" disabled>Select County</option>';
    
    if (countryId) {
        const response = await axios.get(`/counties/${countryId}`);
        response.data.data.forEach(county => {
            const option = new Option(county.name, county.id);
            option.selected = county.id === selectedId;
            dropdown.add(option);
        });
    }
}

async function loadCities(countyId, selectedId = '') {
    const dropdown = document.getElementById('city');
    dropdown.innerHTML = '<option value="" disabled>Select City</option>';
    
    if (countyId) {
        const response = await axios.get(`/cities/${countyId}`);
        response.data.data.forEach(city => {
            const option = new Option(city.name, city.id);
            option.selected = city.id === selectedId;
            dropdown.add(option);
        });
    }
}

async function checkExistingAddresses(email) {
    try {
        const response = await axios.get('/user/meal/shipping-addresses', {
            headers: { 'email': email }
        });
        
        if (response.data.data.length > 0) {
            setupAddressCheckbox();
        }
    } catch (error) {
        console.error('Error checking addresses:', error);
    }
}

function setupAddressCheckbox() {
    const checkbox = document.getElementById('existingAddress');
    
    checkbox.addEventListener('change', async function() {
        try {
            if (this.checked) {
                const response = await axios.get('/user/meal/shipping-addresses');
                if (response.data.data.length > 0) {
                    const latestAddress = response.data.data[0];
                    populateAddressForm(latestAddress);

                    if (document.getElementById('courier').checked) {
                        await recalcDeliveryCharge(latestAddress.city_id);
                    }
                }
            } else {
                const response = await axios.get('/user/get/profile/info');
                populateAddressForm(response.data.data);

                if (document.getElementById('courier').checked) {
                    await recalcDeliveryCharge(response.data.data.city_id);
                }
            }
        } catch (error) {
            console.error("Error toggling address:", error);
        }
    });
}

function populateAddressForm(address) {
    document.getElementById('billings-name').value = address.name;
    document.getElementById('billings-email').value = address.email;
    document.getElementById('billings-phone').value = address.phone;
    document.getElementById('billings-address1').value = address.address1;
    document.getElementById('billings-address2').value = address.address2;
    document.getElementById('billings-zip_code').value = address.zip_code;

    // Update geographical dropdowns
    loadCountries(address.country_id).then(() => {
        loadCounties(address.country_id, address.county_id).then(() => {
            loadCities(address.county_id, address.city_id);
        });
    });
}

async function initializeAddressForm(userData) {
    document.getElementById('billings-name').value = `${userData.firstName} ${userData.lastName}`;
    document.getElementById('billings-email').value = userData.email;
    document.getElementById('billings-phone').value = userData.mobile;
    document.getElementById('billings-address1').value = userData.address1;
    document.getElementById('billings-address2').value = userData.address2;
    document.getElementById('billings-zip_code').value = userData.zip_code;

    await loadCountries(userData.country_id);
    await loadCounties(userData.country_id, userData.county_id);
    await loadCities(userData.county_id, userData.city_id);

    // 👇 Country change
    document.getElementById('country').addEventListener('change', async function () {
        await loadCounties(this.value);
        document.getElementById('city').innerHTML = '<option value="">Select City</option>';
        await recalcCourierChargeIfNeeded();
        updateCourierAvailability();
    });

    // 👇 County change
    document.getElementById('county').addEventListener('change', async function () {
        await loadCities(this.value);
        await recalcCourierChargeIfNeeded();
        updateCourierAvailability();
    });

    // 👇 City change
    document.getElementById('city').addEventListener('change', () => {
        recalcCourierChargeIfNeeded();
        updateCourierAvailability();
    });

    // 👇 Address1 required for enabling courier
    document.getElementById('billings-address1').addEventListener('input', () => {
        recalcCourierChargeIfNeeded();
        updateCourierAvailability();
    });

    // 👇 Zip code required for enabling courier
    document.getElementById('billings-zip_code').addEventListener('input', () => {
        recalcCourierChargeIfNeeded();
        updateCourierAvailability();
    });

    // Initial check when page loads
    updateCourierAvailability();
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

        const mealTypes = [...new Set(dayItems.map(i => i.meal_type?.name))].filter(Boolean);

        let mealTypeHtml = '';

        mealTypes.forEach(type => {
            const typeTitle = toTitleCase(type);
            const items = dayItems.filter(i => i.meal_type?.name === type);
            
            // Get meal time for this meal type (assuming all items of same type have same meal time)
            const mealTime = items[0]?.meal_time ? items[0].meal_time : null;
            
            // Format meal time if it exists
            let deliveryTimeHtml = '';
            if (mealTime) {
                deliveryTimeHtml = `<span class="text-muted ms-2">|| Delivery Time: ${mealTime}</span>`;
            }

            mealTypeHtml += `
                <h6 class="mt-3 d-flex align-items-center">
                    <span>${typeTitle} (${items.length} items)</span>
                    ${deliveryTimeHtml}
                </h6>
                <ul class="list-group mb-3">
            `;

            items.forEach(item => {
                const productName = toTitleCase(item.product?.name || '');
                const img = item.product?.image
                    ? `/upload/product/small/${item.product.image}`
                    : '/upload/no_image.jpg';

                const clientName = item.client
                    ? toTitleCase(
                        [item.client.firstName, item.client.lastName]
                            .filter(Boolean)
                            .join(" ")
                      )
                    : "";

                mealTypeHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center"
                        data-product-id="${item.product?.id}"
                        data-meal-type-id="${item.meal_type?.id}"
                        data-client-id="${item.client?.id || ''}">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${img}" alt="${productName}"
                                class="rounded" style="width:60px;height:60px;object-fit:cover;">
                            <div>
                                <strong>${productName}</strong><br>
                                $${parseFloat(item.unit_price).toFixed(2)}<br>
                                ${clientName ? `<small class="text-muted">Provider: ${clientName}</small>` : ''}
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <input type="number"
                                class="form-control form-control-sm w-25"
                                value="${item.quantity}" min="1"
                                onchange="updateMealItem(${item.id}, this.value)">
                            <button class="btn btn-sm btn-outline-danger"
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
                        ${formattedDate}
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

async function updateMealItem(id, quantity) {
    if (quantity < 1) return errorToast('Quantity must be at least 1');

    try {
        await axios.post('/user/meal-cart/update', {
            meal_item_id: id,
            quantity
        });

        await loadMealCart();

    } catch (error) {
        errorToast('Failed to update item');
    }
}

async function removeMealItem(id) {
    try {
        await axios.post('/user/meal-cart/remove', {
            meal_item_id: id
        });

        await loadMealCart();

    } catch (error) {
        errorToast('Failed to remove item');
    }
}

function initializeDeliveryOptionHandler() {
    document.querySelectorAll('input[name="deliveryOption"]').forEach(option => {
        option.addEventListener("change", async function () {
            if (this.value === "self_pickup") {
                deliveryCharge = 0;

                const courierSpan = document.querySelector('#courier')
                    ?.closest('.custom-option-content')
                    ?.querySelector('.custom-option-header span:last-child');

                if (courierSpan) courierSpan.textContent = '$0.00';

                updateCheckoutSummary(summary, deliveryCharge);
                return;
            }

            if (this.value === "courier") {
                await recalcCourierChargeIfNeeded();
            }

            try {
                const response = await axios.get("/user/get/meal-cart");
                if (response.status === 200) {
                    summary = response.data.data.summary;
                    updateCheckoutSummary(summary, deliveryCharge);
                }
            } catch (e) {
                console.log(e);
            }
        });
    });
}

async function recalcCourierChargeIfNeeded() {
    const courierRadio = document.getElementById('courier');
    if (!courierRadio.checked) return;

    const cityId = document.getElementById('city')?.value;
    const address1 = document.getElementById('billings-address1')?.value.trim();
    const zipCode = document.getElementById('billings-zip_code')?.value.trim();

    if (!cityId || !address1 || !zipCode) {
        errorToast('City, Address1, and Zip Code are required for courier delivery.');
        deliveryCharge = 0;

        const courierSpan = document.querySelector('#courier')
            ?.closest('.custom-option-content')
            ?.querySelector('.custom-option-header span:last-child');

        if (courierSpan) courierSpan.textContent = '$0.00';

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

            const courierSpan = document.querySelector('#courier')
                ?.closest('.custom-option-content')
                ?.querySelector('.custom-option-header span:last-child');

            if (courierSpan) courierSpan.textContent = `$${deliveryCharge.toFixed(2)}`;

            updateCheckoutSummary(summary, deliveryCharge);
        } else {
            errorToast(response.data.message || 'Failed to fetch courier charge');
        }
    } catch (error) {
        errorToast('Error fetching courier charge');
    } finally {
        hideLoader();
    }
}

function updateCourierAvailability() {
    const courierRadio = document.getElementById('courier');
    const pickupRadio = document.getElementById('self_pickup');

    const address1 = document.getElementById('billings-address1').value.trim();
    const zip = document.getElementById('billings-zip_code').value.trim();

    const canEnableCourier = address1 && zip;

    if (!canEnableCourier) {
        courierRadio.checked = false;
        courierRadio.disabled = true;

        pickupRadio.checked = true;

        deliveryCharge = 0;
        updateCheckoutSummary(summary, deliveryCharge);
    } else {
        courierRadio.disabled = false;
    }
}

function updateCheckoutSummary(currentSummary, currentDeliveryCharge = 0) {
    const container = document.getElementById('meal-summary');

    if (!currentSummary) {
        container.innerHTML = `<div class="alert alert-info">No summary available.</div>`;
        return;
    }

    const subtotal = parseFloat(currentSummary.subtotal) || 0;
    const tax = parseFloat(currentSummary.tax) || 0;
    const total = subtotal + tax + parseFloat(currentDeliveryCharge || 0);

    container.innerHTML = `
        <h5 class="mb-3">Price Summary</h5>
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between">Subtotal <span>$${subtotal.toFixed(2)}</span></li>
            <li class="list-group-item d-flex justify-content-between">Tax <span>$${tax.toFixed(2)}</span></li>
            <li class="list-group-item d-flex justify-content-between">Delivery Charge <span>$${parseFloat(currentDeliveryCharge).toFixed(2)}</span></li>
            <li class="list-group-item d-flex justify-content-between fw-bold">Total <span>$${total.toFixed(2)}</span></li>
        </ul>
    `;

    checkCreditEligibility(total);
}

async function checkCreditEligibility(total) {
    try {
        // Refresh cart summary to ensure we have latest data
        const response = await axios.get('/user/get/meal-cart');
        if (response.status === 200 && response.data.status === 'success') {
            summary = response.data.data.summary;
        }
        
        const creditResponse = await axios.get('/user/credit-balance');
        userCreditBalance = parseFloat(creditResponse.data.balance || 0);
    } catch (error) {
        console.error('Error fetching data:', error);
        userCreditBalance = 0;
    }

    // Use the provided total or calculate fresh
    const currentTotal = total || getCurrentTotal();
    
    togglePaymentForms(currentTotal);
    togglePaymentButton(currentTotal);

    // Update credit message if credit is selected
    const creditRadio = document.getElementById('credit');
    if (creditRadio.checked) {
        updateCreditMessage(currentTotal);
    }
}

function togglePaymentForms(total = 0) {
    const stripeRadio = document.getElementById("stripe");
    const cashRadio = document.getElementById("cash");
    const creditRadio = document.getElementById("credit");

    const stripeForm = document.getElementById("form-credit-card");
    const cashForm = document.getElementById("form-cash");
    const creditForm = document.getElementById("form-credit");

    stripeForm.style.display = "none";
    cashForm.style.display = "none";
    creditForm.style.display = "none";

    if (stripeRadio.checked) {
        stripeForm.style.display = "block";
    } else if (cashRadio.checked) {
        cashForm.style.display = "block";
    } else if (creditRadio.checked) {
        creditForm.style.display = "block";
        updateCreditMessage(total);
    }
}

function updateCreditMessage(total) {
    const messageEl = document.getElementById("credit-message");
    if (!messageEl) return;

    if (total > 0 && total <= userCreditBalance) {
        messageEl.innerHTML = `
            <p>
                You have sufficient credit to complete this payment.
                Your current credit balance is <strong>$${userCreditBalance.toFixed(2)}</strong>.
            </p>

            <div class="mt-2">
                <label class="form-label">Amount to Pay from Credit</label>
                <input type="text"
                       class="form-control"
                       value="${total.toFixed(2)}"
                       readonly>
            </div>
        `;
    } else {
        messageEl.innerHTML = `
            <p>
                Your current credit balance is <strong>$${userCreditBalance.toFixed(2)}</strong>.
                This is not sufficient to cover the total payment of <strong>$${total.toFixed(2)}</strong>.
            </p>

            <button type="button" class="btn btn-success waves-effect waves-light"
                    data-bs-toggle="modal" data-bs-target="#creditLimitModal">
                <span class="tf-icon mdi mdi-cash-plus me-1"></span>Add Credit Limit
            </button>
        `;
    } 
}

function initializePaymentToggleHandler() {
    const stripeRadio = document.getElementById("stripe");
    const cashRadio = document.getElementById("cash");
    const creditRadio = document.getElementById("credit");

    const radios = [stripeRadio, cashRadio, creditRadio];

    radios.forEach(radio => {
        radio.addEventListener("change", async () => {
            const total = getCurrentTotal();
            togglePaymentForms(total);
            togglePaymentButton(total);

            if (creditRadio.checked) {
                await checkCreditEligibility(total);
            }
        });
    });
}

function togglePaymentButton(total = 0) {
    const submitButton = document.querySelector('button[type="submit"]');
    if (!submitButton) return;

    const creditRadio = document.getElementById('credit');
    
    if (creditRadio && creditRadio.checked) {
        if (total > 0 && total <= userCreditBalance) {
            submitButton.disabled = false;
            submitButton.textContent = 'Pay with Credit';
            submitButton.classList.remove('btn-secondary');
            submitButton.classList.add('btn-success');
        } else {
            submitButton.disabled = true;
            submitButton.textContent = 'Insufficient Credit';
            submitButton.classList.remove('btn-success');
            submitButton.classList.add('btn-secondary');
        }
    } else {
        submitButton.disabled = false;
        submitButton.textContent = 'Proceed with Payment';
        submitButton.classList.remove('btn-secondary');
        submitButton.classList.add('btn-success');
    }
}

// Helper functions
function getCurrentTotal() {
    const totalElement = document.querySelector('#meal-summary .fw-bold span');
    if (totalElement) {
        return parseFloat(totalElement.textContent.replace('$', '')) || 0;
    }
    return (summary.subtotal || 0) + (summary.tax || 0) + deliveryCharge;
}

function getSelectedPaymentMethod() {
    const selectedRadio = document.querySelector('input[name="paymentMethod"]:checked');
    return selectedRadio ? selectedRadio.value : null;
}

async function processPayment(event) {
    event.preventDefault();
    
    let isValid = true;
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(span => span.textContent = '');
    document.getElementById('card-errors').textContent = '';
    document.getElementById('card-errors').style.display = 'none';

    try {
        // Step 1: Collect and validate form data
        const billingFormData = collectBillingFormData();
        isValid = validateBillingForm(billingFormData);
        
        if (!isValid) {
            errorToast('Please fill in all required fields correctly.');
            return;
        }

        // Step 2: Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="me-2">Processing...</span><i class="mdi mdi-loading mdi-spin"></i>';
        showLoader();

        // Step 3: Extract meal orders from checkout page
        const mealOrders = extractMealOrdersFromCheckout();

        if (mealOrders.length === 0) {
            errorToast('Your meal cart is empty');
            hideLoader();
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            return;
        }

        // Step 4: Get payment method and total amount
        const paymentMethod = getSelectedPaymentMethod();
        const totalAmount = getCurrentTotal();
        
        console.log('Processing payment:', {
            paymentMethod,
            totalAmount,
            mealOrdersCount: mealOrders.length
        });

        // Step 5: Prepare base request data
        let requestData = {
            meal_orders: mealOrders,
            subtotal: summary.subtotal || 0,
            tax: summary.tax || 0,
            delivery_charge: deliveryCharge,
            total_amount: totalAmount,
            customer_email: userEmail,
            payment_method: paymentMethod,
            ...billingFormData
        };

        let response = null;
        let endpoint = '';

        // Step 6: Handle different payment methods
        switch (paymentMethod) {
            case 'stripe':
                // ✅ FIXED STRIPE PAYMENT HANDLING WITH COUNTRY CODE
                endpoint = '/user/store/meal-order/by/stripe';
                
                try {
                    // Validate Stripe initialization
                    if (!stripe || !cardElement) {
                        throw new Error('Stripe not properly initialized. Please refresh the page.');
                    }

                    // Create Payment Intent
                    console.log('Creating payment intent for amount:', totalAmount);
                    const paymentIntentResponse = await axios.post('/user/create-payment-intent', {
                        amount: Math.round(totalAmount * 100), // Convert to cents
                        currency: 'usd',
                        description: `Meal Order - ${mealOrders.length} items`,
                        metadata: {
                            customer_email: userEmail,
                            customer_name: billingFormData.name,
                            order_type: 'meal_order'
                        }
                    });

                    console.log('Payment Intent Response:', paymentIntentResponse.data);
                    
                    // Handle response structure
                    const responseData = paymentIntentResponse.data;
                    
                    if (responseData.status !== 'success') {
                        throw new Error(responseData.message || 'Failed to create payment intent');
                    }

                    // Extract client_secret and payment_intent_id from response
                    let client_secret, payment_intent_id;
                    
                    if (responseData.data) {
                        client_secret = responseData.data.client_secret;
                        payment_intent_id = responseData.data.payment_intent_id || responseData.data.id;
                    } else {
                        client_secret = responseData.client_secret;
                        payment_intent_id = responseData.payment_intent_id || responseData.id;
                    }
                    
                    if (!client_secret) {
                        throw new Error('Payment server error: Missing client secret');
                    }

                    if (!payment_intent_id) {
                        throw new Error('Payment server error: Missing payment intent ID');
                    }

                    // ✅ FIXED: Get country code instead of country name
                    const countrySelect = document.getElementById('country');
                    const selectedCountryOption = countrySelect.options[countrySelect.selectedIndex];
                    const countryName = selectedCountryOption ? selectedCountryOption.text : '';
                    const countryCode = getCountryCode(countryName);
                    
                    console.log('Country details:', {
                        name: countryName,
                        code: countryCode,
                        id: billingFormData.country_id
                    });

                    // ✅ FIXED: Use country code, not country name
                    console.log('Confirming card payment...');
                    const { error, paymentIntent } = await stripe.confirmCardPayment(client_secret, {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: billingFormData.name,
                                email: billingFormData.email,
                                phone: billingFormData.phone,
                                address: {
                                    line1: billingFormData.address1,
                                    line2: billingFormData.address2 || '',
                                    city: document.getElementById('city').options[document.getElementById('city').selectedIndex]?.text || '',
                                    state: document.getElementById('county').options[document.getElementById('county').selectedIndex]?.text || '',
                                    country: countryCode || '', // ✅ Use 2-letter country code
                                    postal_code: billingFormData.zip_code
                                }
                            }
                        },
                        return_url: window.location.origin + '/user/meal-order'
                    });

                    // Handle payment result
                    if (error) {
                        console.error('Stripe payment error:', error);
                        
                        let errorMessage = 'Payment failed. Please try again.';
                        if (error.code === 'card_declined') {
                            errorMessage = 'Your card was declined. Please try a different card.';
                        } else if (error.code === 'expired_card') {
                            errorMessage = 'Your card has expired. Please use a different card.';
                        } else if (error.code === 'insufficient_funds') {
                            errorMessage = 'Insufficient funds. Please use a different card or payment method.';
                        } else if (error.code === 'incorrect_cvc' || error.code === 'invalid_cvc') {
                            errorMessage = 'The CVC code is incorrect. Please check and try again.';
                        } else if (error.code === 'incorrect_number' || error.code === 'invalid_number') {
                            errorMessage = 'The card number is invalid. Please check and try again.';
                        } else if (error.message.includes('Country')) {
                            errorMessage = 'Invalid country format. Please select a valid country.';
                        }
                        
                        document.getElementById('card-errors').textContent = errorMessage;
                        document.getElementById('card-errors').style.color = '#dc3545';
                        document.getElementById('card-errors').style.display = 'block';
                        throw new Error(errorMessage);
                    }

                    if (paymentIntent && paymentIntent.status === 'succeeded') {
                        console.log('✅ Payment successful! Payment Intent ID:', paymentIntent.id);
                        
                        // Add Stripe details to request data
                        requestData.payment_intent_id = payment_intent_id;
                        requestData.stripe_payment_id = paymentIntent.id;
                        requestData.stripe_payment_method = paymentIntent.payment_method;
                        
                        // Submit the order
                        console.log('Submitting order to backend...');
                        response = await axios.post(endpoint, requestData);
                        
                    } else if (paymentIntent && (paymentIntent.status === 'requires_action' || paymentIntent.status === 'requires_confirmation')) {
                        console.log('Payment requires 3D Secure authentication');
                        return;
                    } else {
                        throw new Error('Payment not completed successfully. Status: ' + (paymentIntent?.status || 'unknown'));
                    }
                    
                } catch (stripeError) {
                    if (stripeError.response?.data?.message) {
                        errorToast('Payment Error: ' + stripeError.response.data.message);
                    } else if (stripeError.message) {
                        errorToast(stripeError.message);
                    } else {
                        errorToast('Stripe payment failed. Please try again.');
                    }
                    
                    hideLoader();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    return;
                }
                break;
                
            case 'credit':
                endpoint = '/user/store/meal-order/by/credit';
                
                try {
                    const creditResponse = await axios.get('/user/credit-balance');
                    const currentBalance = parseFloat(creditResponse.data.balance || 0);
                    
                    if (totalAmount > currentBalance) {
                        throw new Error(`Insufficient credit balance. You have $${currentBalance.toFixed(2)} but need $${totalAmount.toFixed(2)}`);
                    }

                    requestData.credit_amount_used = totalAmount;
                    requestData.user_credit_balance = currentBalance;

                    response = await axios.post(endpoint, requestData);
                    
                } catch (creditError) {
                    errorToast(creditError.message || 'Credit payment failed');
                    hideLoader();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    return;
                }
                break;
                
            case 'cash':
                endpoint = '/user/store/meal-order/by/cash';
                
                try {
                    response = await axios.post(endpoint, requestData);
                } catch (cashError) {
                    errorToast(cashError.message || 'Cash payment failed');
                    hideLoader();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    return;
                }
                break;
                
            default:
                errorToast('Please select a valid payment method');
                hideLoader();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                return;
        }

        // Step 7: Handle successful response
        if (response && response.data.status === 'success') {
            console.log('✅ Order placed successfully:', response.data);
            
            try {
                await axios.post('/user/meal-cart/clear');
                console.log('✅ Meal cart cleared');
            } catch (clearError) {
                console.log('⚠️ Cart clear failed (order still placed):', clearError.message);
            }
            
            if (response.data.data && response.data.data.meal_order) {
                localStorage.setItem('lastOrderId', response.data.data.meal_order.id);
                localStorage.setItem('lastOrderNumber', response.data.data.meal_order.order_number);
            }
            
            successToast('Payment successful! Your order has been placed.');
            
            setTimeout(() => {
                window.location.href = response.data.redirect_url || '/user/meal-order';
            }, 2000);
            
        } else if (response) {
            throw new Error(response.data.message || 'Failed to place order');
        }

    } catch (error) {
        console.error('❌ Payment processing error:', error);
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(field => {
                const errorElement = document.getElementById(`${field}-error`);
                if (errorElement) {
                    errorElement.textContent = errors[field][0];
                    errorElement.style.color = '#dc3545';
                }
            });
            errorToast('Please check the form for errors');
        } else if (error.response?.data?.message) {
            errorToast('Server Error: ' + error.response.data.message);
        } else if (error.message) {
            errorToast(error.message);
        } else {
            errorToast('Payment processing failed. Please try again.');
        }
    } finally {
        hideLoader();
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    }
}

// Helper function to collect billing form data
function collectBillingFormData() {
    const countrySelect = document.getElementById('country');
    const countySelect = document.getElementById('county');
    const citySelect = document.getElementById('city');
    
    return {
        name: document.getElementById('billings-name').value.trim(),
        email: document.getElementById('billings-email').value.trim(),
        phone: document.getElementById('billings-phone').value.trim(),
        address1: document.getElementById('billings-address1').value.trim(),
        address2: document.getElementById('billings-address2').value.trim(),
        zip_code: document.getElementById('billings-zip_code').value.trim(),
        country_id: countrySelect ? countrySelect.value : '',
        county_id: countySelect ? countySelect.value : '',
        city_id: citySelect ? citySelect.value : '',
        delivery_option: document.querySelector('input[name="deliveryOption"]:checked')?.value || null
    };
}

// Helper function to validate billing form
function validateBillingForm(formData) {
    let isValid = true;
    
    document.querySelectorAll('.error-message').forEach(span => span.textContent = '');
    
    if (!formData.name) { 
        showError('name-error', 'Name is required'); 
        isValid = false; 
    }
    
    if (!formData.email) { 
        showError('email-error', 'Email is required'); 
        isValid = false; 
    } else if (!isValidEmail(formData.email)) { 
        showError('email-error', 'Please enter a valid email address'); 
        isValid = false;
    }
    
    if (!formData.phone) { 
        showError('phone-error', 'Phone number is required'); 
        isValid = false; 
    } else if (formData.phone.length < 10) {
        showError('phone-error', 'Please enter a valid phone number');
        isValid = false;
    }
    
    if (!formData.address1) { 
        showError('address1-error', 'Address is required'); 
        isValid = false; 
    }
    
    if (!formData.zip_code) { 
        showError('zip_code-error', 'Zip/Postal code is required'); 
        isValid = false; 
    }
    
    if (!formData.country_id) { 
        showError('country-error', 'Please select a country'); 
        isValid = false; 
    }
    
    if (!formData.county_id) { 
        showError('county-error', 'Please select a county/state'); 
        isValid = false; 
    }
    
    if (!formData.city_id) { 
        showError('city-error', 'Please select a city'); 
        isValid = false; 
    }
    
    if (!formData.delivery_option) { 
        errorToast('Please select a delivery option'); 
        isValid = false; 
    }
    
    const paymentMethod = getSelectedPaymentMethod();
    if (!paymentMethod) {
        errorToast('Please select a payment method');
        isValid = false;
    }
    
    return isValid;
}

// Helper function to display error messages
function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.style.color = '#dc3545';
        element.style.fontSize = '0.875rem';
        element.style.marginTop = '0.25rem';
    }
}

// New function to extract meal orders from the checkout page UI
function extractMealOrdersFromCheckout() {
    const mealOrders = [];
    const accordionItems = document.querySelectorAll('#mealCartAccordion .accordion-item');

    accordionItems.forEach(accordionItem => {
        const headerButton = accordionItem.querySelector('.accordion-button');
        const dateText = headerButton.textContent.trim();
        const mealDate = parseDateFromHeader(dateText);
        
        if (!mealDate) return;

        const mealTypeSections = accordionItem.querySelectorAll('.accordion-body > h6');
        
        mealTypeSections.forEach(section => {
            const sectionText = section.textContent.trim();
            const deliveryTimeSpan = section.querySelector('.text-muted');
            let mealTime = null;
            
            if (deliveryTimeSpan) {
                const timeText = deliveryTimeSpan.textContent;
                // Extract time from "|| Delivery Time: 8:00 AM"
                const timeMatch = timeText.match(/Delivery Time:\s*(.+)/i);
                if (timeMatch) {
                    mealTime = timeMatch[1].trim();
                }
            }
            
            const mealTypeMatch = sectionText.match(/^([^(]+)/);
            const mealTypeName = mealTypeMatch ? mealTypeMatch[0].trim() : '';
            
            // Find all meal items under this meal type section
            let nextElement = section.nextElementSibling;
            while (nextElement && nextElement.tagName === 'UL') {
                const mealItems = nextElement.querySelectorAll('.list-group-item');
                
                mealItems.forEach(mealItem => {
                    const productNameElement = mealItem.querySelector('strong');
                    const productName = productNameElement ? productNameElement.textContent.trim() : '';
                    
                    const priceText = mealItem.querySelector('div > div:nth-child(2)')?.textContent || '';
                    const unitPriceMatch = priceText.match(/\$([0-9.]+)/);
                    const unitPrice = unitPriceMatch ? parseFloat(unitPriceMatch[1]) : 0;
                    
                    const quantityInput = mealItem.querySelector('input[type="number"]');
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                    
                    const providerText = mealItem.querySelector('small.text-muted')?.textContent || '';
                    const clientName = providerText.replace('Provider: ', '').trim();
                    
                    const productId = mealItem.dataset.productId;
                    const mealTypeId = mealItem.dataset.mealTypeId;
                    const clientId = mealItem.dataset.clientId;

                    if (productId && mealTypeId) {
                        mealOrders.push({
                            meal_date: mealDate,
                            meal_time: mealTime, // Add meal_time here
                            meal_type_id: parseInt(mealTypeId),
                            product_id: parseInt(productId),
                            client_id: clientId ? parseInt(clientId) : null,
                            quantity: quantity,
                            unit_price: unitPrice,
                            total_price: unitPrice * quantity,
                            product_name: productName,
                            meal_type_name: mealTypeName,
                            client_name: clientName
                        });
                    }
                });
                
                nextElement = nextElement.nextElementSibling;
            }
        });
    });

    return mealOrders;
}


function parseDateFromHeader(dateText) {
    try {
        const currentYear = new Date().getFullYear();
        const dateParts = dateText.split(', ');

        if (dateParts.length === 2) {

            const monthDay = dateParts[1];

            // Create local date safely
            const date = new Date(`${monthDay}, ${currentYear}`);

            // Format without UTC conversion
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

    } catch (error) {
        console.error('Error parsing date:', error);
    }

    return null;
}

// Helper function to parse date from accordion header
// function parseDateFromHeader(dateText) {
//     try {
//         const currentYear = new Date().getFullYear();
//         const dateParts = dateText.split(', ');
//         if (dateParts.length === 2) {
//             const monthDay = dateParts[1];
//             const date = new Date(`${monthDay}, ${currentYear}`);
//             return date.toISOString().split('T')[0];
//         }
//     } catch (error) {
//         console.error('Error parsing date:', error);
//     }
//     return null;
// }

// Helper function to check if email is valid
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}
</script>

<style>
#card-errors {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    display: none;
}

.error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}
</style>
@endsection




