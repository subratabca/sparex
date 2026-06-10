<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-semibold" id="paymentModalLabel">Add Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="ledgerID">
                <input type="hidden" id="deiveryChargeID">

                <!-- Payment Method -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Payment Method:</label>
                    <div class="d-flex gap-4 mt-2">
                        <label class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="credit_payment" value="cash" id="creditCash" checked>
                            Cash
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="credit_payment" value="stripe" id="creditStripe">
                            Stripe
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="credit_payment" value="paypal" id="creditPaypal">
                            PayPal
                        </label>
                    </div>
                </div>

                <!-- Cash Section -->
                <div id="cashAmountField" class="mb-3">
                    <label class="form-label fw-semibold">Enter Amount:</label>
                    <input type="number" id="creditAmount" class="form-control"
                           placeholder="Enter amount (e.g. 100, 200)" min="1" step="0.01">
                    <small id="amountError" class="text-danger" style="display:none;"></small>
                </div>

                <!-- Stripe Section -->
                <div id="stripePaymentField" class="mb-3" style="display:none;">
                    <h6 class="my-3 pt-2">Credit Card Info</h6><hr>
                    <div class="row g-4">
                        <div class="col-12">
                            <label for="credit-card-element"></label>
                            <div id="credit-card-element"></div>
                            <div id="credit-card-errors" role="alert"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="stripeCreditAmount"
                                       placeholder="Enter amount" min="1" step="0.01">
                                <label for="stripeCreditAmount">
                                    Amount to Pay ($)<span class="text-danger">*</span>
                                </label>
                            </div>
                            <small id="stripeAmountError" class="text-danger" style="display:none;"></small>
                        </div>
                    </div>

                    <!-- Billing Info -->
                    <div id="creditBillingSection" class="mt-4">
                        <h6 class="my-3 pt-2">Billing Information (Required for Stripe)</h6><hr>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                           id="credit-billings-name" placeholder="Enter name">
                                    <label for="credit-billings-name">
                                        Name<span class="text-danger">*</span>
                                    </label>
                                    <span class="error-message text-danger" id="credit-name-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                           id="credit-billings-email" placeholder="Enter email">
                                    <label for="credit-billings-email">
                                        Email Address<span class="text-danger">*</span>
                                    </label>
                                    <span class="error-message text-danger" id="credit-email-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                           id="credit-billings-phone" placeholder="Enter phone">
                                    <label for="credit-billings-phone">
                                        Phone<span class="text-danger">*</span>
                                    </label>
                                    <span class="error-message text-danger" id="credit-phone-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                           id="credit-billings-address1" placeholder="Enter address1">
                                    <label for="credit-billings-address1">
                                        Address Line 1<span class="text-danger">*</span>
                                    </label>
                                    <span class="error-message text-danger" id="credit-address1-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                           id="credit-billings-address2" placeholder="Enter address2">
                                    <label for="credit-billings-address2">Address Line 2</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control"
                                           id="credit-billings-zip_code" placeholder="Enter Zip Code">
                                    <label for="credit-billings-zip_code">
                                        Zip/Postal Code<span class="text-danger">*</span>
                                    </label>
                                    <span class="error-message text-danger" id="credit-zip_code-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <select id="credit-country" class="form-select">
                                        <option value="">Select Country</option>
                                    </select>
                                    <label for="credit-country">
                                        Country<span class="text-danger">*</span>
                                    </label>
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

                <!-- PayPal Section -->
                <div id="paypalPaymentField" class="mb-3" style="display:none;">
                    <div class="alert alert-info">
                        <p>PayPal integration will be implemented here. For now, please use Cash or Stripe payment methods.</p>
                    </div>
                </div>

                <div class="text-end">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-info" id="submitCreditBtn" onclick="submitCredit()">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ===== State =====
let creditStripe      = null;
let creditCardElement = null;
let creditElements    = null;

// ===== Helpers =====
function getDeliveryCharge() {
    return document.getElementById('deiveryChargeID').value || '0';
}

function getLedgerId() {
    return document.getElementById('ledgerID').value || '';
}

function showSection(method) {
    document.getElementById('cashAmountField').style.display    = method === 'cash'   ? 'block' : 'none';
    document.getElementById('stripePaymentField').style.display = method === 'stripe' ? 'block' : 'none';
    document.getElementById('paypalPaymentField').style.display = method === 'paypal' ? 'block' : 'none';
}

function clearErrors() {
    document.querySelectorAll('#creditBillingSection .error-message').forEach(el => el.textContent = '');
    document.getElementById('amountError').style.display       = 'none';
    document.getElementById('stripeAmountError').style.display = 'none';
    const cardErr = document.getElementById('credit-card-errors');
    if (cardErr) { cardErr.textContent = ''; cardErr.style.display = 'none'; }
}

// ===== Modal Shown =====
document.getElementById('paymentModal').addEventListener('shown.bs.modal', function () {
    // Reset to cash by default
    document.getElementById('creditCash').checked = true;
    showSection('cash');
    clearErrors();

    // Pre-fill amount
    const amount = getDeliveryCharge();
    document.getElementById('creditAmount').value       = amount;
    document.getElementById('stripeCreditAmount').value = amount;

    // Init Stripe once
    if (!creditStripe) {
        creditStripe   = Stripe('pk_test_51TYgIWSRug15tSRKihjjPttQK135An7gFYp6zrHNiHKU7riwQuPTV6YMHwweBxCIRFlwBgYkrcx3Mjwy8StQVR7h00BtBalIe8');
        creditElements = creditStripe.elements();

        creditCardElement = creditElements.create('card', {
            style: {
                base: {
                    color:           '#32325d',
                    fontFamily:      '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing:   'antialiased',
                    fontSize:        '16px',
                    '::placeholder': { color: '#aab7c4' }
                },
                invalid: { color: '#fa755a', iconColor: '#fa755a' }
            },
            hidePostalCode: true
        });

        creditCardElement.mount('#credit-card-element');

        creditCardElement.on('change', (event) => {
            const el = document.getElementById('credit-card-errors');
            if (event.error) {
                el.textContent   = event.error.message;
                el.style.display = 'block';
            } else {
                el.textContent   = '';
                el.style.display = 'none';
            }
        });
    }
});

// ===== Payment Method Toggle =====
document.querySelectorAll('input[name="credit_payment"]').forEach(radio => {
    radio.addEventListener('change', async function () {
        showSection(this.value);
        clearErrors();

        const amount = getDeliveryCharge();

        if (this.value === 'cash') {
            document.getElementById('creditAmount').value = amount;

        } else if (this.value === 'stripe') {
            document.getElementById('stripeCreditAmount').value = amount;

            // Force Stripe resize after visibility change
            if (creditCardElement) {
                setTimeout(() => creditCardElement.update({}), 100);
            }

            // Load admin profile for billing
            await loadAdminProfileForBilling();

        } else if (this.value === 'paypal') {
            // PayPal — nothing to pre-fill
        }
    });
});

// ===== Country/County/City — modal-specific IDs =====
async function loadCreditCountries(selectedId = '') {
    try {
        const res      = await axios.get('/countries');
        const dropdown = document.getElementById('credit-country');
        dropdown.innerHTML = '<option value="">Select Country</option>';
        res.data.data.forEach(c => {
            const opt    = new Option(c.name, c.id);
            opt.selected = parseInt(c.id) === parseInt(selectedId);
            dropdown.add(opt);
        });
    } catch (error) {
        handleError(error);
    }
}

async function loadCreditCounties(countryId, selectedId = '') {
    try {
        const dropdown = document.getElementById('credit-county');
        dropdown.innerHTML = '<option value="">Select State/Province</option>';
        if (!countryId) return;
        const res = await axios.get(`/counties/${countryId}`);
        res.data.data.forEach(c => {
            const opt    = new Option(c.name, c.id);
            opt.selected = parseInt(c.id) === parseInt(selectedId);
            dropdown.add(opt);
        });
    } catch (error) {
        handleError(error);
    }
}

async function loadCreditCities(countyId, selectedId = '') {
    try {
        const dropdown = document.getElementById('credit-city');
        dropdown.innerHTML = '<option value="">Select City</option>';
        if (!countyId) return;
        const res = await axios.get(`/cities/${countyId}`);
        res.data.data.forEach(c => {
            const opt    = new Option(c.name, c.id);
            opt.selected = parseInt(c.id) === parseInt(selectedId);
            dropdown.add(opt);
        });
    } catch (error) {
        handleError(error);
    }
}

// Attach cascade events once
document.getElementById('credit-country').addEventListener('change', async function () {
    await loadCreditCounties(this.value);
    document.getElementById('credit-city').innerHTML = '<option value="">Select City</option>';
});

document.getElementById('credit-county').addEventListener('change', async function () {
    await loadCreditCities(this.value);
});

// ===== Load Admin Profile for Billing =====
async function loadAdminProfileForBilling() {
    try {
        showLoader();
        const res = await axios.get('/admin/profile/info');
        if (res.data && res.data.data) {
            const u = res.data.data;
            document.getElementById('credit-billings-name').value     = `${u.firstName || ''} ${u.lastName || ''}`.trim();
            document.getElementById('credit-billings-email').value    = u.email    || '';
            document.getElementById('credit-billings-phone').value    = u.mobile   || '';
            document.getElementById('credit-billings-address1').value = u.address1 || '';
            document.getElementById('credit-billings-address2').value = u.address2 || '';
            document.getElementById('credit-billings-zip_code').value = u.zip_code || '';

            await loadCreditCountries(u.country_id);
            if (u.county_id) await loadCreditCounties(u.country_id, u.county_id);
            if (u.city_id)   await loadCreditCities(u.county_id, u.city_id);
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// ===== Get Billing Data =====
function getCreditBillingData() {
    const countrySelect = document.getElementById('credit-country');
    const countryName   = countrySelect.options[countrySelect.selectedIndex]?.text || '';
    const countryCode   = getCountryCode(countryName); // global config.js

    return {
        name:         document.getElementById('credit-billings-name').value.trim(),
        email:        document.getElementById('credit-billings-email').value.trim(),
        phone:        document.getElementById('credit-billings-phone').value.trim(),
        address1:     document.getElementById('credit-billings-address1').value.trim(),
        address2:     document.getElementById('credit-billings-address2').value.trim() || '',
        zip_code:     document.getElementById('credit-billings-zip_code').value.trim(),
        city:         document.getElementById('credit-city').options[document.getElementById('credit-city').selectedIndex]?.text || '',
        state:        document.getElementById('credit-county').options[document.getElementById('credit-county').selectedIndex]?.text || '',
        country:      countryName,
        country_code: countryCode,
    };
}

// ===== Validate Billing =====
function validateCreditBillingForm(d) {
    let isValid = true;
    document.querySelectorAll('#creditBillingSection .error-message').forEach(el => el.textContent = '');

    if (!d.name)         { document.getElementById('credit-name-error').textContent     = 'Name is required';        isValid = false; }
    if (!d.email)        { document.getElementById('credit-email-error').textContent    = 'Email is required';       isValid = false; }
    else if (!isValidEmail(d.email)) { document.getElementById('credit-email-error').textContent = 'Invalid email';  isValid = false; }
    if (!d.phone)        { document.getElementById('credit-phone-error').textContent    = 'Phone is required';       isValid = false; }
    if (!d.address1)     { document.getElementById('credit-address1-error').textContent = 'Address is required';     isValid = false; }
    if (!d.zip_code)     { document.getElementById('credit-zip_code-error').textContent = 'Zip code is required';    isValid = false; }
    if (!d.country_code) { document.getElementById('credit-country-error').textContent  = 'Country is required';     isValid = false; }

    return isValid;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ===== Submit =====
async function submitCredit() {
    const method    = document.querySelector('input[name="credit_payment"]:checked');
    const submitBtn = document.getElementById('submitCreditBtn');
    const origHtml  = submitBtn.innerHTML;

    if (!method) {
        errorToast('Please select a payment method.');
        return;
    }

    submitBtn.disabled  = true;
    submitBtn.innerHTML = '<span class="me-2">Processing...</span><i class="mdi mdi-loading mdi-spin"></i>';
    showLoader();

    try {
        if (method.value === 'cash') {
            await processCashPayment();
        } else if (method.value === 'stripe') {
            await processStripePayment();
        } else if (method.value === 'paypal') {
            infoToast('PayPal payment will be implemented soon. Please use Cash or Stripe.');
        }
    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
        submitBtn.disabled  = false;
        submitBtn.innerHTML = origHtml;
    }
}

// ===== Cash Payment =====
async function processCashPayment() {
    const amount  = document.getElementById('creditAmount').value.trim();
    const errorEl = document.getElementById('amountError');

    if (!amount || isNaN(amount) || parseFloat(amount) <= 0) {
        errorEl.textContent   = 'Please enter a valid amount.';
        errorEl.style.display = 'block';
        return;
    }
    if (parseFloat(amount) < 1) {
        errorEl.textContent   = 'Minimum amount is $1.00';
        errorEl.style.display = 'block';
        return;
    }
    errorEl.style.display = 'none';

    const res = await axios.post('/admin/store/meal-delivery/payment/by/cash', {
        ledger_id: getLedgerId(),
        amount:    parseFloat(amount).toFixed(2),
    });

    if (res.data.status === 'success') {
        successToast(res.data.message || 'Cash payment recorded successfully!');
        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
        await loadDeliveryList(); // ← refresh table without full page reload
    } else {
        errorToast(res.data.message || 'Payment failed.');
    }
}

// ===== Stripe Payment =====
async function processStripePayment() {
    const amount   = document.getElementById('stripeCreditAmount').value.trim();
    const errorEl  = document.getElementById('stripeAmountError');
    const cardErr  = document.getElementById('credit-card-errors');

    if (!amount || isNaN(amount) || parseFloat(amount) <= 0) {
        errorEl.textContent   = 'Please enter a valid amount.';
        errorEl.style.display = 'block';
        return;
    }
    if (parseFloat(amount) < 1) {
        errorEl.textContent   = 'Minimum amount is $1.00';
        errorEl.style.display = 'block';
        return;
    }
    errorEl.style.display = 'none';

    const billing = getCreditBillingData();
    if (!validateCreditBillingForm(billing)) return;

    // Validate card element
    const { error: cardError } = await creditStripe.createPaymentMethod({
        type: 'card',
        card: creditCardElement,
        billing_details: {
            name:    billing.name,
            email:   billing.email,
            phone:   billing.phone,
            address: {
                line1:       billing.address1,
                line2:       billing.address2,
                city:        billing.city,
                state:       billing.state,
                country:     billing.country_code,
                postal_code: billing.zip_code,
            }
        }
    });

    if (cardError) {
        cardErr.textContent   = cardError.message;
        cardErr.style.display = 'block';
        return;
    }
    cardErr.textContent   = '';
    cardErr.style.display = 'none';

    // Create payment intent
    const piRes = await axios.post('/admin/meal-delivery/create-payment-intent', {
        amount:      Math.round(parseFloat(amount) * 100),
        currency:    'usd',
        description: `Delivery Payment - $${parseFloat(amount).toFixed(2)}`,
        metadata: {
            payment_type:   'delivery_payment',
            customer_name:  billing.name,
            customer_email: billing.email,
        }
    });

    if (piRes.data.status !== 'success') {
        errorToast(piRes.data.message || 'Failed to create payment intent.');
        return;
    }

    const clientSecret    = piRes.data.data.client_secret;
    const paymentIntentId = piRes.data.data.payment_intent_id;

    if (!clientSecret) {
        errorToast('Payment server error: Missing client secret.');
        return;
    }

    // Confirm card payment
    const { error: confirmError, paymentIntent } = await creditStripe.confirmCardPayment(clientSecret, {
        payment_method: {
            card:            creditCardElement,
            billing_details: {
                name:    billing.name,
                email:   billing.email,
                phone:   billing.phone,
                address: {
                    line1:       billing.address1,
                    line2:       billing.address2,
                    city:        billing.city,
                    state:       billing.state,
                    country:     billing.country_code,
                    postal_code: billing.zip_code,
                }
            }
        }
    });

    if (confirmError) {
        let msg = confirmError.message || 'Payment failed.';
        if (confirmError.code === 'card_declined')      msg = 'Your card was declined.';
        if (confirmError.code === 'expired_card')       msg = 'Your card has expired.';
        if (confirmError.code === 'insufficient_funds') msg = 'Insufficient funds.';
        if (confirmError.code === 'incorrect_cvc')      msg = 'Incorrect CVC code.';
        cardErr.textContent   = msg;
        cardErr.style.display = 'block';
        return;
    }

    if (paymentIntent?.status === 'succeeded') {
        const res = await axios.post('/admin/store/meal-delivery/payment/by/stripe', {
            ledger_id:             getLedgerId(),
            amount:                parseFloat(amount).toFixed(2),
            stripe_payment_id:     paymentIntent.id,
            stripe_payment_method: paymentIntent.payment_method,
            payment_intent_id:     paymentIntentId,
            billing_name:          billing.name,
            billing_email:         billing.email,
        });

        if (res.data.status === 'success') {
            successToast(res.data.message || `$${parseFloat(amount).toFixed(2)} payment successful!`);
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            await loadDeliveryList(); // ← refresh table without full page reload
        } else {
            errorToast(res.data.message || 'Payment failed.');
        }
    } else {
        errorToast('Payment not completed. Status: ' + (paymentIntent?.status || 'unknown'));
    }
}

// ===== infoToast — Toastify =====
function infoToast(msg) {
    Toastify({
        gravity:   'top',
        position:  'right',
        text:      msg,
        className: 'mb-5',
        style:     { background: '#0dcaf0' }
    }).showToast();
}
</script>

<style>
#credit-card-errors {
    padding:          10px;
    margin:           10px 0;
    border-radius:    4px;
    background-color: #f8d7da;
    border:           1px solid #f5c6cb;
    color:            #721c24;
    display:          none;
}

#credit-card-element {
    box-sizing:       border-box;
    height:           40px;
    padding:          10px 12px;
    border:           1px solid #ced4da;
    border-radius:    4px;
    background-color: white;
    transition:       border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.error-message {
    color:      #dc3545;
    font-size:  0.875rem;
    margin-top: 0.25rem;
    display:    block;
}

.modal-lg {
    max-width: 800px;
}
</style>