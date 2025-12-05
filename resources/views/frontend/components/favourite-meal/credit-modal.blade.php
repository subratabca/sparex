<!--Add Credit Limit Modal -->
<div class="modal fade" id="creditLimitModal" tabindex="-1" aria-labelledby="creditLimitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-semibold" id="creditLimitModalLabel">Add Credit Limit2222</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Payment Method -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Payment Method:</label>

                    <div class="d-flex gap-4 mt-2">

                        <!-- Cash -->
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="credit_payment" value="cash" id="creditCash">
                            Cash
                        </label>

                        <!-- Stripe -->
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="credit_payment" value="stripe" id="creditStripe">
                            Stripe
                        </label>

                        <!-- PayPal -->
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="credit_payment" value="paypal" id="creditPaypal">
                            PayPal
                        </label>

                    </div>
                </div>

                <!-- 💰 Amount Input (Hidden initially) -->
                <div id="amountField" class="mb-3" style="display: none;">
                    <label class="form-label fw-semibold">Enter Credit Amount:</label>
                    <input type="number" id="creditAmount" class="form-control" placeholder="Enter amount (e.g. 100, 200)">
                    <small id="amountError" class="text-danger" style="display:none;"></small>
                </div>

                <div class="text-end">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-info" onclick="submitCredit()">Submit</button>
                </div>

            </div>
        </div>
    </div>
</div>


<script>
document.querySelectorAll('input[name="credit_payment"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const amountField = document.getElementById('amountField');

        if (this.value === "cash") {
            amountField.style.display = "block"; // Show input
        } else {
            amountField.style.display = "none";  // Hide input
        }
    });
});

async function submitCredit() {
    const method = document.querySelector('input[name="credit_payment"]:checked');
    const amount = document.getElementById('creditAmount').value.trim();
    const errorText = document.getElementById('amountError');

    // Validation
    if (!method) {
        errorToast("Please select a payment method");
        return;
    }

    if (method.value === "cash" && (amount === "" || amount <= 0)) {
        errorText.style.display = "block";
        errorText.textContent = "Please enter a valid amount.";
        return;
    } else {
        errorText.style.display = "none";
    }

    showLoader();
    try {
        const response = await axios.post("/user/store/credit", {
            payment_method: method.value,
            amount: method.value === "cash" ? amount : null
        });

        if (response.status === 200) {
            successToast("Credit added successfully!");
            document.getElementById('creditLimitModal').querySelector(".btn-close").click();
            window.location.href = '/user/credit';
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}
</script>