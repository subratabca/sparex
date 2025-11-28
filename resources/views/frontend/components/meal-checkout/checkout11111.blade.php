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

function toTitleCase(str) {
    if (!str) return "";
    return str.trim().toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
}

document.addEventListener("DOMContentLoaded", async function () {

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
            const summary = response.data.data.summary;

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

            mealTypeHtml += `
                <h6 class="mt-3">${typeTitle} (${items.length} items)</h6>
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

                    // In renderMealCart function, update the meal item HTML to include data attributes:
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

async function loadCountries(selectedId = '') {
  const response = await axios.get('/countries');
  const dropdown = document.getElementById('country');
  dropdown.innerHTML = '<option value="" disabled>Select Country</option>';
  
  response.data.data.forEach(country => {
    const option = new Option(country.name, country.id);
    option.selected = country.id === selectedId;
    dropdown.add(option);
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
        // ✅ Always update credit message when showing the form
        updateCreditMessage(total);
    }
}

function updateCreditMessage(total) {
    const messageEl = document.getElementById("credit-message");
    if (!messageEl) return;

    // Case 1: User has enough credit to cover total
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
            // Refresh the total calculation when switching payment methods
            const total = getCurrentTotal();
            
            togglePaymentForms(total);
            togglePaymentButton(total);

            if (creditRadio.checked) {
                await checkCreditEligibility(total); // This will refresh all data
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
            // User has enough credit - enable button
            submitButton.disabled = false;
            submitButton.textContent = 'Pay with Credit';
            submitButton.classList.remove('btn-secondary');
            submitButton.classList.add('btn-success');
        } else {
            // User doesn't have enough credit - disable button
            submitButton.disabled = true;
            submitButton.textContent = 'Insufficient Credit';
            submitButton.classList.remove('btn-success');
            submitButton.classList.add('btn-secondary');
        }
    } else {
        // For other payment methods - enable button
        submitButton.disabled = false;
        submitButton.textContent = 'Proceed with Payment';
        submitButton.classList.remove('btn-secondary');
        submitButton.classList.add('btn-success');
    }
}

// Helper functions
function getCurrentTotal() {
    // Try to get the total from the displayed summary first
    const summaryElement = document.getElementById('meal-summary');
    if (summaryElement) {
        const totalElement = summaryElement.querySelector('.fw-bold span');
        if (totalElement) {
            const totalText = totalElement.textContent.replace('$', '');
            return parseFloat(totalText) || 0;
        }
    }
    
    // Fallback to calculated total
    const subtotal = parseFloat(summary.subtotal || 0);
    const tax = parseFloat(summary.tax || 0);
    const total = subtotal + tax + parseFloat(deliveryCharge || 0);
    return total;
}

function getSelectedPaymentMethod() {
  if (document.getElementById('stripe').checked) return 'stripe';
  if (document.getElementById('cash').checked) return 'cash';
  if (document.getElementById('credit').checked) return 'credit';
  return null;
}

async function processPayment(event) {
    event.preventDefault();
    let submitBtn = null;
    let isValid = true;
    document.querySelectorAll('.error-message').forEach(span => span.textContent = '');

    // Collect form data
    const formData = {
        name: document.getElementById('billings-name').value.trim(),
        email: document.getElementById('billings-email').value.trim(),
        phone: document.getElementById('billings-phone').value.trim(),
        address1: document.getElementById('billings-address1').value.trim(),
        address2: document.getElementById('billings-address2').value.trim(),
        zip_code: document.getElementById('billings-zip_code').value.trim(),
        country_id: document.getElementById('country').value,
        county_id: document.getElementById('county').value,
        city_id: document.getElementById('city').value,
        delivery_option: document.querySelector('input[name="deliveryOption"]:checked')?.value || null,
        payment_method: getSelectedPaymentMethod()
    };

    // Validation
    if (!formData.name) { showError('name-error', 'Name is required'); isValid = false; }
    if (!formData.email) { 
        showError('email-error', 'Email is required'); isValid = false; 
    } else if (!isValidEmail(formData.email)) { 
        showError('email-error', 'Invalid email format'); isValid = false;
    }
    if (!formData.phone) { showError('phone-error', 'Phone is required'); isValid = false; }
    if (!formData.address1) { showError('address1-error', 'Address1 is required'); isValid = false; }
    if (!formData.zip_code) { showError('zip_code-error', 'Zip Code is required'); isValid = false; }
    if (!formData.country_id) { showError('country-error', 'Country is required'); isValid = false; }
    if (!formData.county_id) { showError('county-error', 'County is required'); isValid = false; }
    if (!formData.city_id) { showError('city-error', 'City is required'); isValid = false; }

    if (!isValid) return;

    try {
        showLoader();
        submitBtn = document.querySelector('#checkout-form button[type="submit"]');
        submitBtn.disabled = true;

        // Extract meal orders data from the rendered checkout page
        const mealOrders = extractMealOrdersFromCheckout();

        if (mealOrders.length === 0) {
            errorToast('Your meal cart is empty');
            return;
        }

        // Prepare final request data
        const requestData = {
            ...formData,
            meal_orders: mealOrders,
            subtotal: summary.subtotal || 0,
            tax: summary.tax || 0,
            delivery_charge: deliveryCharge,
            total_amount: getCurrentTotal()
        };

        let endpoint = '/user/store/meal-order';
        let response;

        // Handle different payment methods
        if (formData.payment_method === 'stripe') {
            // Stripe payment handling
            endpoint = '/user/store/meal-order';
            
            // Create Payment Intent
            const { data: intentData } = await axios.post('/user/create-payment-intent', {
                amount: Math.round(getCurrentTotal() * 100) // Convert to cents
            });
            
            // Confirm Card Payment
            const { paymentIntent, error } = await stripe.confirmCardPayment(
                intentData.client_secret, 
                {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: formData.name,
                            email: formData.email,
                            address: {
                                line1: formData.address1,
                                line2: formData.address2,
                                postal_code: formData.zip_code
                            }
                        }
                    }
                }
            );

            if (error) {
                if (error.code === 'card_declined') {
                    throw new Error(`Card declined: ${error.message}`);
                }
                throw error;
            }

            if (!paymentIntent || paymentIntent.status !== 'succeeded') {
                throw new Error('Payment authorization failed');
            }

            requestData.payment_intent_id = paymentIntent.id;
            requestData.payment_method = 'stripe';
            
            // Submit stripe order
            response = await axios.post(endpoint, requestData);
            
        } else if (formData.payment_method === 'credit') {
            // ✅ CREDIT PAYMENT HANDLING
            const totalAmount = getCurrentTotal();
            
            // Double-check credit balance before proceeding
            const creditResponse = await axios.get('/user/credit-balance');
            const currentBalance = parseFloat(creditResponse.data.balance || 0);
            
            if (totalAmount > currentBalance) {
                throw new Error(`Insufficient credit balance. You have $${currentBalance.toFixed(2)} but need $${totalAmount.toFixed(2)}`);
            }

            requestData.payment_method = 'credit';
            requestData.credit_amount_used = totalAmount;
            requestData.user_credit_balance = currentBalance;

            // Submit credit order
            response = await axios.post(endpoint, requestData);
            
        } else if (formData.payment_method === 'cash') {
            // ✅ CASH PAYMENT HANDLING
            requestData.payment_method = 'cash';
            
            // Submit cash order
            response = await axios.post(endpoint, requestData);
        }

        // Handle successful response
        if (response.data.status === 'success') {
            successToast('Meal order placed successfully!');
            
            // Clear meal cart after successful order
            try {
                await axios.post('/user/meal-cart/clear');
            } catch (clearError) {
                console.log('Note: Meal cart clear failed, but order was placed successfully');
            }
            
            // Redirect to order confirmation
            window.location.href = response.data.redirect_url || '/user/meal-order';
        } else {
            throw new Error(response.data.message || 'Failed to place order');
        }

    } catch (error) {
        console.error('Payment processing error:', error);
        
        // Enhanced error handling
        if (error.response?.data?.errors) {
            // Handle validation errors from Laravel
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(field => {
                const errorElement = document.getElementById(`${field}-error`);
                if (errorElement) errorElement.textContent = errors[field][0];
            });
        } else if (error.message.includes('Card declined')) {
            document.getElementById('card-errors').textContent = error.message;
        } else if (error.message.includes('Insufficient credit')) {
            errorToast(error.message);
            // Update credit message to reflect the error
            const creditRadio = document.getElementById('credit');
            if (creditRadio.checked) {
                updateCreditMessage(getCurrentTotal());
            }
        } else {
            errorToast(error.response?.data?.message || error.message || 'Payment processing failed');
        }
    } finally {
        hideLoader();
        if (submitBtn) submitBtn.disabled = false;
    }
}

// New function to extract meal orders from the checkout page UI
function extractMealOrdersFromCheckout() {
    const mealOrders = [];
    const accordionItems = document.querySelectorAll('#mealCartAccordion .accordion-item');

    accordionItems.forEach(accordionItem => {
        // Extract date from accordion header
        const headerButton = accordionItem.querySelector('.accordion-button');
        const dateText = headerButton.textContent.trim();
        
        // Parse the date (format: "Wednesday, Jan 15")
        const mealDate = parseDateFromHeader(dateText);
        
        if (!mealDate) return;

        // Find all meal items in this accordion
        const mealItems = accordionItem.querySelectorAll('.list-group-item');
        
        mealItems.forEach(mealItem => {
            // Extract product name
            const productNameElement = mealItem.querySelector('strong');
            const productName = productNameElement ? productNameElement.textContent.trim() : '';
            
            // Extract price
            const priceText = mealItem.querySelector('div > div: nth-child(2)')?.textContent || '';
            const unitPriceMatch = priceText.match(/\$([0-9.]+)/);
            const unitPrice = unitPriceMatch ? parseFloat(unitPriceMatch[1]) : 0;
            
            // Extract quantity
            const quantityInput = mealItem.querySelector('input[type="number"]');
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
            
            // Extract meal type from the section header
            const sectionHeader = mealItem.closest('.accordion-body').querySelector('h6');
            const mealTypeText = sectionHeader ? sectionHeader.textContent.trim() : '';
            const mealTypeName = mealTypeText.split(' (')[0]; // Extract "Breakfast" from "Breakfast (2 items)"
            
            // Extract client/provider name
            const providerText = mealItem.querySelector('small.text-muted')?.textContent || '';
            const clientName = providerText.replace('Provider: ', '').trim();
            
            // We need to get meal_type_id and product_id from data attributes
            // Add these data attributes to your HTML in renderMealCart function
            const productId = mealItem.dataset.productId;
            const mealTypeId = mealItem.dataset.mealTypeId;
            const clientId = mealItem.dataset.clientId;

            if (productId && mealTypeId) {
                mealOrders.push({
                    meal_date: mealDate,
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
    });

    return mealOrders;
}

// Helper function to parse date from accordion header
function parseDateFromHeader(dateText) {
    try {
        // Format: "Wednesday, Jan 15" - convert to YYYY-MM-DD
        const currentYear = new Date().getFullYear();
        const dateParts = dateText.split(', ');
        if (dateParts.length === 2) {
            const monthDay = dateParts[1]; // "Jan 15"
            const date = new Date(`${monthDay}, ${currentYear}`);
            return date.toISOString().split('T')[0]; // Return YYYY-MM-DD
        }
    } catch (error) {
        console.error('Error parsing date:', error);
    }
    return null;
}

function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function showError(elementId, message) {
  const element = document.getElementById(elementId);
  if (element) element.textContent = message;
}

function handleApiError(error) {
  if (error.response?.status === 422) {
    const errors = error.response.data.errors;
    Object.keys(errors).forEach(field => {
      const errorElement = document.getElementById(`${field}-error`);
      if (errorElement) errorElement.textContent = errors[field][0];
    });
  } else {
    errorToast(error.response?.data?.message || 'Payment processing failed');
  }
}
</script>

@endsection