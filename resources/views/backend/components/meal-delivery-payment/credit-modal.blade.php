<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-semibold" id="paymentModalLabel">Add Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="ledgerID"/>
                <input type="hidden" id="deiveryChargeID"/>

                <!-- Payment Method -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Payment Method:</label>
                    <div class="d-flex gap-4 mt-2">
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="credit_payment" value="cash" id="creditCash" checked>
                            Cash
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="credit_payment" value="stripe" id="creditStripe">
                            Stripe
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="credit_payment" value="paypal" id="creditPaypal">
                            PayPal
                        </label>
                    </div>
                </div>

                <!-- 💰 Cash Amount Input -->
                <div id="cashAmountField" class="mb-3">
                    <label class="form-label fw-semibold">Enter Amount:</label>
                    <input type="number" id="creditAmount" class="form-control" placeholder="Enter amount (e.g. 100, 200)" min="1" step="0.01">
                    <small id="amountError" class="text-danger" style="display:none;"></small>
                </div>

                <!-- 💳 Stripe Payment Section (Hidden initially) -->
                <div id="stripePaymentField" class="mb-3" style="display: none;">
                    <h6 class="my-3 pt-2">Credit Card Info</h6><hr>
                    <div class="row g-4">
                        <div class="form-row">
                            <label for="credit-card-element"></label>
                            <div id="credit-card-element"></div>
                            <div id="credit-card-errors" role="alert"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="stripeCreditAmount" placeholder="Enter amount" min="1" step="0.01">
                                <label for="stripeCreditAmount">Amount to Pay ($)<span class="text-danger">*</span></label>
                            </div>
                            <small id="stripeAmountError" class="text-danger" style="display:none;"></small>
                        </div>
                    </div>
                    
                    <!-- Billing Information Section for Stripe -->
                    <div id="creditBillingSection" class="mt-4">
                        <h6 class="my-3 pt-2">Billing Information (Required for Stripe)</h6><hr>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="credit-billings-name" placeholder="Enter name" />
                                    <label for="credit-billings-name">Name<span class="text-danger">*</span></label>
                                    <span class="error-message text-danger" id="credit-name-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="credit-billings-email" placeholder="Enter email" />
                                    <label for="credit-billings-email">Email Address<span class="text-danger">*</span></label>
                                    <span class="error-message text-danger" id="credit-email-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="credit-billings-phone" placeholder="Enter phone" />
                                    <label for="credit-billings-phone">Phone<span class="text-danger">*</span></label>
                                    <span class="error-message text-danger" id="credit-phone-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="credit-billings-address1" placeholder="Enter address1" />
                                    <label for="credit-billings-address1">Address Line 1<span class="text-danger">*</span></label>
                                    <span class="error-message text-danger" id="credit-address1-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="credit-billings-address2" placeholder="Enter address2" />
                                    <label for="credit-billings-address2">Address Line 2</label>
                                    <span class="error-message text-danger" id="credit-address2-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" id="credit-billings-zip_code" placeholder="Enter Zip Code" />
                                    <label for="credit-billings-zip_code">Zip/Postal Code<span class="text-danger">*</span></label>
                                    <span class="error-message text-danger" id="credit-zip_code-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <select id="credit-country" class="form-select">
                                        <option value="">Select Country</option>
                                    </select>
                                    <label for="credit-country">Country<span class="text-danger">*</span></label>
                                </div>
                                <span class="error-message text-danger" id="credit-country-error"></span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <select id="credit-county" class="form-select">
                                        <option value="">Select State/Province</option>
                                    </select>
                                    <label for="credit-county">State/Province</label>
                                </div>
                                <span class="error-message text-danger" id="credit-county-error"></span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <select id="credit-city" class="form-select">
                                        <option value="">Select City</option>
                                    </select>
                                    <label for="credit-city">City</label>
                                </div>
                                <span class="error-message text-danger" id="credit-city-error"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 💳 PayPal Payment Section (Hidden initially) -->
                <div id="paypalPaymentField" class="mb-3" style="display: none;">
                    <div class="alert alert-info">
                        <p>PayPal integration will be implemented here. For now, please use Cash or Stripe payment methods.</p>
                    </div>
                </div>

                <div class="text-end">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-info" onclick="submitCredit()" id="submitCreditBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let creditStripe = null;
let creditCardElement = null;
let creditElements = null;
let creditCountryCodeMap = {};
let userProfileData = null;

// Helper to get delivery charge from hidden field
function getDeliveryCharge() {
    return document.getElementById('deiveryChargeID').value || '';
}

// Helper to get ledger ID
function getLedgerId() {
    return document.getElementById('ledgerID').value || '';
}

// Initialize Stripe for credit modal when modal is shown
document.getElementById('paymentModal').addEventListener('shown.bs.modal', function () {
    // Set default cash payment and pre‑fill amount
    document.getElementById('creditCash').checked = true;
    document.getElementById('cashAmountField').style.display = "block";
    document.getElementById('stripePaymentField').style.display = "none";
    document.getElementById('paypalPaymentField').style.display = "none";

    const deliveryCharge = getDeliveryCharge();
    document.getElementById('creditAmount').value = deliveryCharge;

    // Initialize Stripe only once
    if (!creditStripe) {
        creditStripe = Stripe('pk_test_51JHjNRSDYO1wlylS2t9Mdffvf6gXo7BhEkupXMj17tAoMteZHKKlP1ZooX6eaEZjOf6SHp8rJ2141rsuapAFLB3i00vysBKwyd');
        
        creditElements = creditStripe.elements();
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

        creditCardElement = creditElements.create('card', { 
            style: style,
            hidePostalCode: true
        });
        creditCardElement.mount('#credit-card-element');

        creditCardElement.on('change', (event) => {
            const displayError = document.getElementById('credit-card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
                displayError.style.color = '#dc3545';
                displayError.style.display = 'block';
            } else {
                displayError.textContent = '';
                displayError.style.display = 'none';
            }
        });
    }
    
    // Pre-load countries
    loadCreditCountries();
});

// Handle radio button changes
document.querySelectorAll('input[name="credit_payment"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const cashAmountField = document.getElementById('cashAmountField');
        const stripePaymentField = document.getElementById('stripePaymentField');
        const paypalPaymentField = document.getElementById('paypalPaymentField');
        const deliveryCharge = getDeliveryCharge();
        
        // Reset all fields
        document.getElementById('creditAmount').value = deliveryCharge;   // pre‑fill cash field
        document.getElementById('stripeCreditAmount').value = '';        // will be set if stripe selected
        document.getElementById('amountError').style.display = 'none';
        document.getElementById('stripeAmountError').style.display = 'none';
        document.getElementById('credit-card-errors').textContent = '';
        document.getElementById('credit-card-errors').style.display = 'none';
        
        // Clear billing form errors
        document.querySelectorAll('#creditBillingSection .error-message').forEach(span => span.textContent = '');

        // Show/hide relevant fields
        if (this.value === "cash") {
            cashAmountField.style.display = "block";
            stripePaymentField.style.display = "none";
            paypalPaymentField.style.display = "none";
        } else if (this.value === "stripe") {
            cashAmountField.style.display = "none";
            stripePaymentField.style.display = "block";
            paypalPaymentField.style.display = "none";
            
            // Pre‑fill the Stripe amount field with the delivery charge
            document.getElementById('stripeCreditAmount').value = deliveryCharge;
            
            // Force Stripe element to resize after container becomes visible
            if (creditCardElement) {
                setTimeout(() => creditCardElement.update({}), 100);
            }
            
            loadUserProfileForCredit();
        } else if (this.value === "paypal") {
            cashAmountField.style.display = "none";
            stripePaymentField.style.display = "none";
            paypalPaymentField.style.display = "block";
        }
    });
});

// Load user profile data and populate credit billing form
async function loadUserProfileForCredit() {
    try {
        showLoader();
        const userResponse = await axios.get('/admin/profile/info');
        
        if (userResponse.data && userResponse.data.data) {
            userProfileData = userResponse.data.data;
            await initializeCreditAddressForm(userProfileData);
        }
    } catch (error) {
        console.error('Error loading user profile:', error);
        errorToast('Failed to load user profile. Please fill the form manually.');
    } finally {
        hideLoader();
    }
}

// Initialize credit billing form with user data
async function initializeCreditAddressForm(userData) {
    // Populate text fields
    document.getElementById('credit-billings-name').value = `${userData.firstName || ''} ${userData.lastName || ''}`.trim();
    document.getElementById('credit-billings-email').value = userData.email || '';
    document.getElementById('credit-billings-phone').value = userData.mobile || '';
    document.getElementById('credit-billings-address1').value = userData.address1 || '';
    document.getElementById('credit-billings-address2').value = userData.address2 || '';
    document.getElementById('credit-billings-zip_code').value = userData.zip_code || '';

    // Load and set country
    if (userData.country_id) {
        await loadCreditCountries(userData.country_id);
        if (userData.county_id) {
            await loadCreditCounties(userData.country_id, userData.county_id);
            if (userData.city_id) {
                await loadCreditCities(userData.county_id, userData.city_id);
            }
        }
    }
}

// Load countries for credit modal
async function loadCreditCountries(selectedId = '') {
    try {
        const response = await axios.get('/countries');
        const dropdown = document.getElementById('credit-country');
        dropdown.innerHTML = '<option value="">Select Country</option>';
        
        response.data.data.forEach(country => {
            const option = new Option(country.name, country.id);
            option.selected = country.id == selectedId;
            option.dataset.countryCode = country.country_code || getCountryCode(country.name);
            dropdown.add(option);
            creditCountryCodeMap[country.id] = country.country_code || getCountryCode(country.name);
        });
        
        dropdown.addEventListener('change', async function() {
            await loadCreditCounties(this.value);
            document.getElementById('credit-city').innerHTML = '<option value="">Select City</option>';
        });
    } catch (error) {
        console.error('Error loading countries:', error);
    }
}

// Load counties for credit modal
async function loadCreditCounties(countryId, selectedId = '') {
    const dropdown = document.getElementById('credit-county');
    dropdown.innerHTML = '<option value="">Select State/Province</option>';
    
    if (countryId) {
        try {
            const response = await axios.get(`/counties/${countryId}`);
            response.data.data.forEach(county => {
                const option = new Option(county.name, county.id);
                option.selected = county.id == selectedId;
                dropdown.add(option);
            });
            
            dropdown.addEventListener('change', async function() {
                await loadCreditCities(this.value);
            });
        } catch (error) {
            console.error('Error loading counties:', error);
        }
    }
}

// Load cities for credit modal
async function loadCreditCities(countyId, selectedId = '') {
    const dropdown = document.getElementById('credit-city');
    dropdown.innerHTML = '<option value="">Select City</option>';
    
    if (countyId) {
        try {
            const response = await axios.get(`/cities/${countyId}`);
            response.data.data.forEach(city => {
                const option = new Option(city.name, city.id);
                option.selected = city.id == selectedId;
                dropdown.add(option);
            });
        } catch (error) {
            console.error('Error loading cities:', error);
        }
    }
}

// Populate credit form from checkout form if available (fallback)
function populateCreditFormFromCheckout() {
    const checkoutFields = ['billings-name', 'billings-email', 'billings-phone', 'billings-address1', 
                           'billings-address2', 'billings-zip_code'];
    
    checkoutFields.forEach(fieldId => {
        const checkoutField = document.getElementById(fieldId);
        const creditField = document.getElementById('credit-' + fieldId);
        if (checkoutField && checkoutField.value && creditField) {
            creditField.value = checkoutField.value;
        }
    });
    
    const checkoutCountry = document.getElementById('country');
    const creditCountry = document.getElementById('credit-country');
    if (checkoutCountry && checkoutCountry.value && creditCountry) {
        creditCountry.value = checkoutCountry.value;
        const event = new Event('change');
        creditCountry.dispatchEvent(event);
        
        setTimeout(async () => {
            const checkoutCounty = document.getElementById('county');
            const creditCounty = document.getElementById('credit-county');
            if (checkoutCounty && checkoutCounty.value && creditCounty) {
                creditCounty.value = checkoutCounty.value;
                const countyEvent = new Event('change');
                creditCounty.dispatchEvent(countyEvent);
                
                setTimeout(() => {
                    const checkoutCity = document.getElementById('city');
                    const creditCity = document.getElementById('credit-city');
                    if (checkoutCity && checkoutCity.value && creditCity) {
                        creditCity.value = checkoutCity.value;
                    }
                }, 500);
            }
        }, 500);
    }
}

async function submitCredit() {
    const method = document.querySelector('input[name="credit_payment"]:checked');
    const submitBtn = document.getElementById('submitCreditBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    if (!method) {
        errorToast("Please select a payment method");
        return;
    }

    showLoader();
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="me-2">Processing...</span><i class="mdi mdi-loading mdi-spin"></i>';

    try {
        if (method.value === "cash") {
            await processCashPayment();
        } else if (method.value === "stripe") {
            await processStripePayment();
        } else if (method.value === "paypal") {
            infoToast("PayPal payment will be implemented soon. Please use Cash or Stripe.");
        }
    } catch (error) {
        console.error('Credit payment error:', error);
        errorToast(error.message || 'Payment failed. Please try again.');
    } finally {
        hideLoader();
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
}

// Helper function to get user data from credit modal billing form
function getCreditBillingData() {
    const countrySelect = document.getElementById('credit-country');
    const selectedCountryOption = countrySelect?.options[countrySelect.selectedIndex];
    const countryName = selectedCountryOption ? selectedCountryOption.text : '';
    const countryCode = getCountryCode(countryName);
    
    return {
        name: document.getElementById('credit-billings-name')?.value.trim() || '',
        email: document.getElementById('credit-billings-email')?.value.trim() || '',
        phone: document.getElementById('credit-billings-phone')?.value.trim() || '',
        address1: document.getElementById('credit-billings-address1')?.value.trim() || '',
        address2: document.getElementById('credit-billings-address2')?.value.trim() || '',
        zip_code: document.getElementById('credit-billings-zip_code')?.value.trim() || '',
        city: document.getElementById('credit-city')?.options[document.getElementById('credit-city').selectedIndex]?.text || '',
        state: document.getElementById('credit-county')?.options[document.getElementById('credit-county').selectedIndex]?.text || '',
        country: countryName,
        country_code: countryCode
    };
}

// Validate credit billing form
function validateCreditBillingForm(billingData) {
    let isValid = true;
    document.querySelectorAll('#creditBillingSection .error-message').forEach(span => span.textContent = '');
    
    if (!billingData.name) { 
        document.getElementById('credit-name-error').textContent = 'Name is required'; 
        isValid = false; 
    }
    if (!billingData.email) { 
        document.getElementById('credit-email-error').textContent = 'Email is required'; 
        isValid = false; 
    } else if (!isValidEmail(billingData.email)) { 
        document.getElementById('credit-email-error').textContent = 'Please enter a valid email address'; 
        isValid = false;
    }
    if (!billingData.phone) { 
        document.getElementById('credit-phone-error').textContent = 'Phone number is required'; 
        isValid = false; 
    }
    if (!billingData.address1) { 
        document.getElementById('credit-address1-error').textContent = 'Address is required'; 
        isValid = false; 
    }
    if (!billingData.zip_code) { 
        document.getElementById('credit-zip_code-error').textContent = 'Zip/Postal code is required'; 
        isValid = false; 
    }
    if (!billingData.country) { 
        document.getElementById('credit-country-error').textContent = 'Please select a country'; 
        isValid = false; 
    }
    return isValid;
}

// Email validation helper
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

async function processCashPayment() {
    const ledgerID = getLedgerId();
    const amount = document.getElementById('creditAmount').value.trim();
    const errorText = document.getElementById('amountError');

    if (amount === "" || amount <= 0 || isNaN(amount)) {
        errorText.style.display = "block";
        errorText.textContent = "Please enter a valid amount.";
        throw new Error("Invalid amount");
    }
    if (parseFloat(amount) < 1) {
        errorText.style.display = "block";
        errorText.textContent = "Minimum amount is $1.00";
        throw new Error("Amount too low");
    }
    errorText.style.display = "none";

    const response = await axios.post("/admin/store/meal-delivery/payment/by/cash", {
        payment_method: 'cash',
        ledger_id: ledgerID,
        amount: parseFloat(amount).toFixed(2)
    });

    if (response.status === 200) {
        successToast("Payment submitted successfully! The amount will be added after verification.");
        document.getElementById('paymentModal').querySelector(".btn-close").click();
        window.location.reload(); 
    }
}

async function processStripePayment() {
    const ledgerID = getLedgerId();                     // <-- get ledger ID
    const amount = document.getElementById('stripeCreditAmount').value.trim();
    const errorText = document.getElementById('stripeAmountError');

    if (amount === "" || amount <= 0 || isNaN(amount)) {
        errorText.style.display = "block";
        errorText.textContent = "Please enter a valid amount.";
        throw new Error("Invalid amount");
    }
    if (parseFloat(amount) < 1) {
        errorText.style.display = "block";
        errorText.textContent = "Minimum amount is $1.00";
        throw new Error("Amount too low");
    }
    errorText.style.display = "none";

    const billingData = getCreditBillingData();
    if (!validateCreditBillingForm(billingData)) {
        throw new Error("Please fill in all required billing information");
    }
    const hasMinimalBillingData = billingData.name && billingData.email && billingData.address1 && billingData.country_code;
    if (!hasMinimalBillingData) {
        throw new Error("Complete billing information is required for Stripe payments");
    }

    const cardErrors = document.getElementById('credit-card-errors');
    const { error: cardError } = await creditStripe.createPaymentMethod({
        type: 'card',
        card: creditCardElement,
        billing_details: {
            name: billingData.name,
            email: billingData.email,
            phone: billingData.phone,
            address: {
                line1: billingData.address1,
                line2: billingData.address2 || '',
                city: billingData.city || '',
                state: billingData.state || '',
                country: billingData.country_code,
                postal_code: billingData.zip_code
            }
        }
    });

    if (cardError) {
        cardErrors.textContent = cardError.message;
        cardErrors.style.color = '#dc3545';
        cardErrors.style.display = 'block';
        throw new Error(cardError.message);
    }

    cardErrors.textContent = '';
    cardErrors.style.display = 'none';

    // Create payment intent
    const paymentAmount = Math.round(parseFloat(amount) * 100);
    const metadata = {
        payment_type: 'credit_topup',
        customer_name: billingData.name,
        customer_email: billingData.email,
        customer_phone: billingData.phone
    };

    const paymentIntentResponse = await axios.post('/admin/meal-delivery/create-payment-intent', {
        amount: paymentAmount,
        currency: 'usd',
        description: `Payment - $${parseFloat(amount).toFixed(2)}`,
        metadata: metadata
    });

    if (paymentIntentResponse.data.status !== 'success') {
        throw new Error(paymentIntentResponse.data.message || 'Failed to create payment intent');
    }

    const clientSecret = paymentIntentResponse.data.data?.client_secret || paymentIntentResponse.data.client_secret;
    const paymentIntentId = paymentIntentResponse.data.data?.payment_intent_id || paymentIntentResponse.data.payment_intent_id;

    if (!clientSecret) {
        throw new Error('Payment server error: Missing client secret');
    }

    const confirmOptions = {
        payment_method: {
            card: creditCardElement,
            billing_details: {
                name: billingData.name,
                email: billingData.email,
                phone: billingData.phone,
                address: {
                    line1: billingData.address1,
                    line2: billingData.address2 || '',
                    city: billingData.city || '',
                    state: billingData.state || '',
                    country: billingData.country_code,
                    postal_code: billingData.zip_code
                }
            }
        }
    };

    const { error: confirmError, paymentIntent } = await creditStripe.confirmCardPayment(clientSecret, confirmOptions);

    if (confirmError) {
        let errorMessage = 'Payment failed. Please try again.';
        if (confirmError.code === 'card_declined') {
            errorMessage = 'Your card was declined. Please try a different card.';
        } else if (confirmError.code === 'expired_card') {
            errorMessage = 'Your card has expired. Please use a different card.';
        } else if (confirmError.code === 'insufficient_funds') {
            errorMessage = 'Insufficient funds. Please use a different card or payment method.';
        } else if (confirmError.message.includes('Indian regulations')) {
            errorMessage = 'Indian regulations require complete billing information.';
        }
        cardErrors.textContent = errorMessage;
        cardErrors.style.color = '#dc3545';
        cardErrors.style.display = 'block';
        throw new Error(errorMessage);
    }

    if (paymentIntent && paymentIntent.status === 'succeeded') {
        // Store the payment – include ledger_id now
        const response = await axios.post("/admin/store/meal-delivery/payment/by/stripe", {
            payment_method: 'stripe',
            amount: parseFloat(amount).toFixed(2),
            stripe_payment_id: paymentIntent.id,
            stripe_payment_method: paymentIntent.payment_method,
            payment_intent_id: paymentIntentId,
            billing_name: billingData.name,
            billing_email: billingData.email,
            ledger_id: ledgerID   // <-- send ledger_id
        });

        if (response.status === 200) {
            successToast(`$${parseFloat(amount).toFixed(2)} payment successful!`);
            document.getElementById('paymentModal').querySelector(".btn-close").click();
            window.location.reload();
        }
    } else {
        throw new Error('Payment not completed successfully');
    }
}

// Function to show info toast (add this if not exists)
function infoToast(message) {
    toastr.info(message, 'Information', {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000
    });
}
</script>

<style>
#credit-card-errors {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    display: none;
}

#credit-card-element {
    box-sizing: border-box;
    height: 40px;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background-color: white;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#credit-card-element--focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

#credit-card-element--invalid {
    border-color: #dc3545;
}

.error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.modal-lg {
    max-width: 800px;
}
</style>