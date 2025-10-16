<section class="section-py bg-body first-section-pt">
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
                        <input type="radio" class="form-check-input" name="deliveryOption" value="myself" id="myself"
                        checked />
                        <span class="custom-option-header">
                          <span class="h6 mb-0">Myself</span>
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
                          <span>$ 0.00</span>
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
              </div>
            </div>
            <!--End Payment Section -->

          </div>

          <!--Strat Order Summary Section -->
          <div class="col-lg-5 card-body">
            <h4 class="mb-2">Order Summary (<span class="text-danger" id="checkout-item-count">0</span> Items)</h4><hr>
            <div class="bg-lighter p-4 rounded mt-4" id="checkout-cart-items">

            </div>
            <div id="checkout-load-more-container" class="text-center mt-3"></div>
            <div id="checkout-cart-summary">

            </div>
            <div class="d-grid mt-3">
              <button type="submit" class="btn btn-success">
                <span class="me-2">Proceed with Payment</span>
                <i class="mdi mdi-arrow-right scaleX-n1-rtl"></i>
              </button>
            </div>
          </div>
          <!--End Order Summary Section -->
        </div>
      </form>
    </div>
  </div>
</section>


<script>
let checkoutCurrentPage = 1;
let checkoutHasMore = false;
let checkoutTotalItems = 0;
let summary = {}; 
let deliveryCharge = 0; 

document.addEventListener("DOMContentLoaded", async function () {
  // Initialize Stripe Elements
  const stripe = Stripe('pk_test_51IUTWzALc6pn5BvMAUegqRHV0AAokjG7ZuV6RWcj5rxB9KCAwamgtWpw9T4maGAe34WmDkD6LSn1Yge3nzex6gYk004pILHsNh');
  const elements = stripe.elements();
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

  const card = elements.create('card', { style: style });
  card.mount('#card-element');

  // Handle real-time validation errors
  card.on('change', (event) => {
    const displayError = document.getElementById('card-errors');
    event.error ? displayError.textContent = event.error.message : displayError.textContent = '';
  });

  // Main initialization logic
  if (isTokenValid()) {
    try {
      showLoader();
      const [userResponse, cartResponse] = await Promise.all([
        axios.get('/user/auth-user-info'),
        axios.get('/user/get-cart-product', {
          params: {
            page: checkoutCurrentPage,
            per_page: 3
          }
        })
      ]);

      userEmail = userResponse.data.data.email; // store email globally
      await initializeAddressForm(userResponse.data.data);
      await checkExistingAddresses(userEmail);
      
      if (cartResponse.data.status === 'success') {
        const cartData = cartResponse.data.data;
        checkoutTotalItems = cartData.pagination.total_items;
        checkoutHasMore = cartData.pagination.has_more;
        summary = cartData.summary; // store globally
        renderCheckoutItems(cartData.cart_items);
        updateCheckoutSummary(summary, deliveryCharge);
        updateCheckoutLoadMoreButton();
      }
    } catch (error) {
      errorToast('Failed to load checkout data');
    } finally {
      hideLoader();
    }
  }

  // Delivery option change handler
  document.querySelectorAll('input[name="deliveryOption"]').forEach(radio => {
    radio.addEventListener('change', async function () {
      const selectedOption = this.value;
      const cityId = document.getElementById('city').value;

      const courierSpan = document.querySelector('#courier').closest('.custom-option-content').querySelector('.custom-option-header span:last-child');
      const myselfSpan = document.querySelector('#myself').closest('.custom-option-content').querySelector('.custom-option-header span:last-child');

      if (selectedOption === 'myself') {
        deliveryCharge = 0;
        courierSpan.textContent = '$0.00';
        myselfSpan.textContent = 'Free';
        updateCheckoutSummary(summary, deliveryCharge);
        return;
      }

      if (selectedOption === 'courier') {
        if (!cityId) {
          errorToast('You must select city');
          this.checked = false;
          return;
        }

        try {
          showLoader();
          const response = await axios.get('/user/get-courier-charge', { params: { city_id: cityId } });
          if (response.data.status === 'success') {
            deliveryCharge = parseFloat(response.data.charge);
            courierSpan.textContent = `$${deliveryCharge.toFixed(2)}`;
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
    });
  });


  // Payment method toggle handler
  document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
    radio.addEventListener('change', function() {
      const isStripe = this.value === 'stripe';
      document.getElementById('form-credit-card').style.display = isStripe ? 'block' : 'none';
      document.getElementById('form-cash').style.display = this.value === 'cash' ? 'block' : 'none';
    });
  });

});

async function processPayment(event) {
  event.preventDefault();
  let submitBtn = null;
  let isValid = true;
  document.querySelectorAll('.error-message').forEach(span => span.textContent = '');

  // New T&C validation
  const acceptTnc = document.getElementById('accept_tnc');
  const tncError = document.getElementById('tnc-error');
  if (tncError) tncError.textContent = '';

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
    accept_tnc: acceptTnc ? acceptTnc.checked : false,
    delivery_option: document.querySelector('input[name="deliveryOption"]:checked')?.value || null,
    payment_method: getSelectedPaymentMethod()
  };

  // Validation
  if (!formData.name) { showError('name-error', 'Name is required'); isValid = false; }

  if (!formData.email) { 
      showError('email-error', 'Email is required'); isValid = false; 
  } else if (!isValidEmail(formData.email)) { 
      showError('email-error', 'Invalid email format');isValid = false;
  }

  if (!formData.phone) { showError('phone-error', 'Phone is required'); isValid = false; }
  if (!formData.address1) { showError('address1-error', 'Address1 is required'); isValid = false; }
  if (!formData.zip_code) { showError('zip_code-error', 'Zip Code is required'); isValid = false; }
  if (!formData.country_id) { showError('country-error', 'Country is required'); isValid = false; }
  if (!formData.county_id) { showError('county-error', 'County is required'); isValid = false; }
  if (!formData.city_id) { showError('city-error', 'City is required'); isValid = false; }

  if (!formData.accept_tnc) {
    if (tncError) {
      tncError.textContent = 'You must accept T&C and Privacy Policy';
      tncError.style.display = 'block';
    }
    isValid = false;
  }

  if (!isValid) return;

  try {
    showLoader();
    const submitBtn = document.querySelector('#checkout-form button[type="submit"]');
    submitBtn.disabled = true;

    let endpoint = '/user/cash/order';
    const requestData = { ...formData };

    if (formData.payment_method === 'stripe') {
      endpoint = '/user/stripe/order';
      
      // 1. Create Payment Intent (Fixed headers)
      const { data: intentData } = await axios.post('/user/create-payment-intent');
      
      // 2. Confirm Card Payment (Fixed client_secret access)
      const { paymentIntent, error } = await stripe.confirmCardPayment(
        intentData.data.client_secret, // Access nested data
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

      // Enhanced error handling
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
    }

    // 3. Submit Order
    const response = await axios.post(endpoint, requestData);
    
    if (response.data.status === 'success') {
      successToast('Order placed successfully!');
      window.location.href = response.data.redirect_url || '/user/order-confirmation';
    }

 } catch (error) {
    // Improved error feedback
    if (error.message.includes('Card declined')) {
      document.getElementById('card-errors').textContent = error.message;
    } else {
      handleApiError(error);
    }
  } finally {
    hideLoader();
    if (submitBtn) submitBtn.disabled = false;
  }
}

async function initializeAddressForm(userData) {
  // Populate basic fields
  document.getElementById('billings-name').value = `${userData.firstName} ${userData.lastName}`;
  document.getElementById('billings-email').value = userData.email;
  document.getElementById('billings-phone').value = userData.mobile;
  document.getElementById('billings-address1').value = userData.address1;
  document.getElementById('billings-address2').value = userData.address2;
  document.getElementById('billings-zip_code').value = userData.zip_code;

  // Load geographical data
  await loadCountries(userData.country_id);
  await loadCounties(userData.country_id, userData.county_id);
  await loadCities(userData.county_id, userData.city_id);

  // Setup dropdown change handlers
  document.getElementById('country').addEventListener('change', async function() {
    await loadCounties(this.value);
    document.getElementById('city').innerHTML = '<option value="">Select City</option>';
  });

  document.getElementById('county').addEventListener('change', async function() {
    await loadCities(this.value);
  });

  // ✅ City change handler (not nested inside county)
  document.getElementById('city').addEventListener('change', async function() {
    // Check if courier is selected
    const courierRadio = document.getElementById('courier');
    if (courierRadio.checked) {
      const cityId = this.value;

      if (!cityId) {
        errorToast('You must select city');
        deliveryCharge = 0;
        updateCheckoutSummary(summary, deliveryCharge);
        return;
      }

      try {
        showLoader();
        const response = await axios.get('/user/get-courier-charge', {
          params: { city_id: cityId }
        });

        if (response.data.status === 'success') {
          deliveryCharge = parseFloat(response.data.charge);

          // ✅ Update courier option span here
          const courierSpan = document.querySelector('#courier')
            .closest('.custom-option-content')
            .querySelector('.custom-option-header span:last-child');

          courierSpan.textContent = `$${deliveryCharge.toFixed(2)}`;

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
  });
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
    const response = await axios.get('/user/shipping-addresses', {
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
        const response = await axios.get('/user/shipping-addresses', {
          headers: { 'email': userEmail }
        });
        if (response.data.data.length > 0) {
          const latestAddress = response.data.data[0];
          populateAddressForm(latestAddress);

          if (document.getElementById('courier').checked) {
            await recalcDeliveryCharge(latestAddress.city_id);
          }
        }
      } else {
        const response = await axios.get('/user/auth-user-info');
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

async function recalcDeliveryCharge(cityId) {
  const courierSpan = document.querySelector('#courier')
    .closest('.custom-option-content')
    .querySelector('.custom-option-header span:last-child');

  if (!cityId) {
    deliveryCharge = 0;
    courierSpan.textContent = '$0.00';
    updateCheckoutSummary(summary, deliveryCharge);
    return;
  }

  try {
    showLoader();
    const response = await axios.get('/user/get-courier-charge', {
      params: { city_id: cityId }
    });

    if (response.data.status === 'success') {
      deliveryCharge = parseFloat(response.data.charge);
      courierSpan.textContent = `$${deliveryCharge.toFixed(2)}`;
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

function renderCheckoutItems(cartItems, append = false) {
    const container = document.getElementById('checkout-cart-items');
    let html = '';

    if (!append) {
        container.innerHTML = ''; // Clear existing items if not appending
    }

    if (cartItems.length === 0 && !append) {
        html = `<div class="alert alert-info">Your cart is empty</div>`;
        container.innerHTML = html;
        return;
    }

    html += `<ul class="list-unstyled mb-0">`;
    cartItems.forEach(item => {
        const product = item.product;
        const variant = item.product_variant;
        const imagePath = product.image ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg';
        const price = parseFloat(item.price).toFixed(2);
        const originalPrice = parseFloat(product.price).toFixed(2);

        html += `
        <li class="d-flex gap-3 mb-3">
            <div class="flex-shrink-0">
                <img src="${imagePath}" alt="${product.name}" class="w-px-60" />
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">${product.name}</h6>
                ${variant ? `
                <div class="text-muted small">
                    ${variant.color ? `<span class="me-2">Color: ${variant.color}</span>` : ''}
                    ${variant.size ? `<span>Size: ${variant.size}</span>` : ''}
                </div>` : ''}
                <div class="text-muted small">Quantity: ${item.quantity}</div>
                <div class="mt-1">
                    ${item.price < product.price ? 
                        `<span class="text-primary">$${price}</span>
                         <span class="text-muted text-decoration-line-through">$${originalPrice}</span>` : 
                        `<span class="text-primary">$${price}</span>`}
                </div>
            </div>
        </li>`;
    });
    html += `</ul>`;
    
    if (append) {
        container.insertAdjacentHTML('beforeend', html);
    } else {
        container.innerHTML = html;
    }

    // Apply scroll after 6 items
    if (checkoutCurrentPage >= 2) {
        container.style.maxHeight = '400px';
        container.style.overflowY = 'auto';
    }
}

async function loadMoreCheckoutItems() {
    try {
        showLoader();
        checkoutCurrentPage++;
        const response = await axios.get('/user/get-cart-product', {
            params: {
                page: checkoutCurrentPage,
                per_page: 3
            }
        });

        if (response.data.status === 'success') {
            const cartData = response.data.data;
            checkoutHasMore = cartData.pagination.has_more;
            checkoutTotalItems = cartData.pagination.total_items;
            renderCheckoutItems(cartData.cart_items, true);
            updateCheckoutLoadMoreButton();
        }
    } catch (error) {
        checkoutCurrentPage--;
        console.error('Error loading more items:', error);
        errorToast('Failed to load more items');
    } finally {
        hideLoader();
    }
}

function updateCheckoutLoadMoreButton() {
    const loadMoreContainer = document.getElementById('checkout-load-more-container');
    if (!loadMoreContainer) return;

    if (checkoutHasMore) {
        loadMoreContainer.innerHTML = `
            <button type="button" class="btn btn-primary" onclick="loadMoreCheckoutItems()" id="checkout-load-more-btn">
                Load More
            </button>`;
    } else {
        loadMoreContainer.innerHTML = '';
    }
}

function updateCheckoutSummary(summary, deliveryCharge = 0) {
    document.getElementById('checkout-item-count').textContent = summary.item_count;
    const container = document.getElementById('checkout-cart-summary');

    const totalPayable = summary.total + deliveryCharge;

    let html = `
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="mb-0">Bag Total</p>
            <h6 class="mb-0">$${summary.subtotal.toFixed(2)}</h6>
        </div>`;

    if (summary.coupon_discount > 0) {
        html += `
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="mb-0">Coupon Discount</p>
            <h6 class="mb-0 text-danger">-$${summary.coupon_discount.toFixed(2)}</h6>
        </div>`;
    }

    html += `
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="mb-0">Tax (${(summary.taxRate * 100).toFixed(0)}%)</p>
            <h6 class="mb-0">$${summary.tax.toFixed(2)}</h6>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="mb-0">Order Total</p>
            <h6 class="mb-0">$${summary.total.toFixed(2)}</h6>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="mb-0">Delivery Charges</p>
            <h6 class="mb-0">${deliveryCharge > 0 ? `$${deliveryCharge.toFixed(2)}` : '<span class="badge bg-label-success rounded-pill">Free</span>'}</h6>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-center mt-3 pb-1">
            <p class="mb-0">Total Payable</p>
            <h6 class="mb-0">$${totalPayable.toFixed(2)}</h6>
        </div>

        <div class="form-check mt-4 pt-2">
            <input class="form-check-input" type="checkbox" value="" id="accept_tnc" />
            <label class="form-check-label" for="accept_tnc">
                <a href="/user/order/terms-conditions/customer_order" target="_blank">
                    Accept Terms of Conditions and Privacy Policy
                </a>
                <span class="text-danger">*</span>
            </label>
            <span class="error-message text-danger d-block" id="tnc-error"></span>
        </div>        
        <p class="mt-4 pt-2"> 
            <strong>Please note that payments are non-refundable</strong>.
        </p>`;

    container.innerHTML = html;
}

// Helper functions
function getSelectedPaymentMethod() {
  if (document.getElementById('stripe').checked) return 'stripe';
  //if (document.getElementById('paypal').checked) return 'paypal';
  if (document.getElementById('cash').checked) return 'cash';
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

<style>
.StripeElement {
  box-sizing: border-box;
  height: 40px;
  padding: 10px 12px;
  border: 1px solid transparent;
  border-radius: 4px;
  background-color: white;
  box-shadow: 0 1px 3px 0 #e6ebf1;
  -webkit-transition: box-shadow 150ms ease;
  transition: box-shadow 150ms ease;
}
.StripeElement--focus {
  box-shadow: 0 1px 3px 0 #cfd7df;
}
.StripeElement--invalid {
  border-color: #fa755a;
}
.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;}
</style>

