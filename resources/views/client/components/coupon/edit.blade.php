<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Update Coupon</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="updateID">
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
        <button onclick="updateCouponInfo()" class="btn btn-primary btn-lg">
          <i class="mdi mdi-check me-2"></i>Update
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    CouponInfo();

    const form = document.getElementById('save-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        await updateCouponInfo();
    });
});

async function CouponInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split('/');
        //let id = segments[segments.length - 1];
        let id = new URL(window.location.href).pathname.split('/').pop();

        let res = await axios.get("/client/show/coupon/info/" + id);
        document.getElementById('updateID').value = id;
        document.getElementById('coupon_name').value = res.data.data['coupon_name'];
        document.getElementById('coupon_discount').value = res.data.data['coupon_discount'];
        document.getElementById('expire_date').value = res.data.data['expire_date'];
    } catch (error) {
        if (error.response) {
            if (error.response.status === 404) {
                errorToast(error.response.data.message || "Data not found.");
            } 
            else if (error.response.status === 500) {
                errorToast(error.response.data.error || "An internal server error occurred."); 
            } 
            else {
                errorToast("Request failed!");
            }
        } else {
            errorToast("Request failed! Please check your internet connection or try again later.");
        }
    } finally{
        hideLoader();
    }
}

function resetCreateForm() {
  document.getElementById('save-form').reset();
  document.getElementById('coupon_name-error').innerText = '';
  document.getElementById('coupon_discount-error').innerText = '';
  document.getElementById('expire_date-error').innerText = '';
}

async function updateCouponInfo() {
  const couponName = document.getElementById('coupon_name').value.trim();
  const couponDiscount = document.getElementById('coupon_discount').value.trim();
  const expireDate = document.getElementById('expire_date').value;
  let updateID = document.getElementById('updateID').value;

  document.getElementById('coupon_name-error').innerText = '';
  document.getElementById('coupon_discount-error').innerText = '';
  document.getElementById('expire_date-error').innerText = '';

  let isValid = true;

  if (!couponName) {
    document.getElementById('coupon_name-error').innerText = 'Coupon name is required!';
    isValid = false;
  }

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

  const formData = {
      coupon_name: couponName,
      coupon_discount: couponDiscount,
      expire_date: expireDate,
      id: updateID
  };

  const config = {
      headers: {
          'Content-Type': 'application/json'
      }
  };

  try {
    const res = await axios.post('/client/update/coupon', formData, config);
    if (res.status === 200 && res.data.status === 'success') {
      successToast(res.data.message || 'Request success');
      window.location.href = '/client/coupon/page';
      resetCreateForm();
    } else {
      errorToast(res.data.message || "Request failed");
    }
  } catch (error) {
    if (error.response) {
      if (error.response.status === 422) {
        let errorMessages = error.response.data.errors;
        for (let field in errorMessages) {
          if (errorMessages.hasOwnProperty(field)) {
            document.getElementById(`${field}-error`).innerText = errorMessages[field][0];
          }
        }
      } else if (error.response.status === 500) {
        errorToast(error.response.data.error || "An internal server error occurred.");
      } else {
        errorToast("Request failed!");
      }
    }
  }
}
</script>
