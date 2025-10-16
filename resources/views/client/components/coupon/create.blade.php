<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Add New Coupon</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <div class="col-md-4">
              <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" id="coupon_name" placeholder="Enter coupon name" />
                <label for="coupon_name">Coupon Name:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="coupon_name-error"></span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="coupon_discount" min="0" step="0.01" placeholder="Enter coupon discount" />
                <label for="coupon_discount">Coupon Discount<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="coupon_discount-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="date" class="form-control" id="expire_date" placeholder="Enter coupon expire date" />
                <label for="expire_date">Coupon Expire Date<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="expire_date-error"></span>
              </div>
            </div>
          </div>
        </form>
        <button onclick="Save()" class="btn btn-primary btn-lg">
          <i class="mdi mdi-check me-2"></i>Confirm
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const form = document.getElementById('save-form');
  form.addEventListener('submit', async function(event) {
    event.preventDefault();
    await Save();
  });
});

function resetCreateForm() {
  document.getElementById('save-form').reset();
  document.getElementById('coupon_name-error').innerText = '';
  document.getElementById('coupon_discount-error').innerText = '';
  document.getElementById('expire_date-error').innerText = '';
}

async function Save() {
  const couponName = document.getElementById('coupon_name').value.trim();
  const couponDiscount = document.getElementById('coupon_discount').value.trim();
  const expireDate = document.getElementById('expire_date').value;

  // Clear previous error messages
  document.getElementById('coupon_name-error').innerText = '';
  document.getElementById('coupon_discount-error').innerText = '';
  document.getElementById('expire_date-error').innerText = '';

  let isValid = true;

  // Validate Coupon Name
  if (!couponName) {
    document.getElementById('coupon_name-error').innerText = 'Coupon name is required!';
    isValid = false;
  }

  // Validate Coupon Discount
  if (!couponDiscount) {
    document.getElementById('coupon_discount-error').innerText = 'Discount is required!';
    isValid = false;
  } else {
    const discountValue = parseFloat(couponDiscount);
    if (isNaN(discountValue)) {
      document.getElementById('coupon_discount-error').innerText = 'Invalid discount value!';
      isValid = false;
    } else if (discountValue <= 0) {
      document.getElementById('coupon_discount-error').innerText = 'Discount must be greater than 0!';
      isValid = false;
    }
  }

  // Validate Expire Date
  if (!expireDate) {
    document.getElementById('expire_date-error').innerText = 'Expire date is required!';
    isValid = false;
  } else {
    const selectedDate = new Date(expireDate);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (selectedDate <= today) {
      document.getElementById('expire_date-error').innerText = 'Expire date must be in the future!';
      isValid = false;
    }
  }

  if (!isValid) return;

  const formData = new FormData();
  formData.append('coupon_name', couponName);
  formData.append('coupon_discount', couponDiscount);
  formData.append('expire_date', expireDate);

  try {
    const res = await axios.post('/client/store/coupon', formData, {
      headers: { 'content-type': 'multipart/form-data' }
    });

    if (res.status === 201) {
      successToast(res.data.message || 'Coupon created successfully!');
      window.location.href = '/client/coupon/page';
      resetCreateForm();
    } else {
      errorToast(res.data.message || "Failed to create coupon");
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(`${field}-error`);
        if (errorElement) {
          errorElement.innerText = errors[field][0];
        }
      });
    } else if (error.response) {
      errorToast(error.response.data.message || "An error occurred");
    } else {
      errorToast("Network error. Please try again.");
    }
  }
}
</script>