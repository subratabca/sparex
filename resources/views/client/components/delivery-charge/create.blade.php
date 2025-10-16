<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Add New Delivery Charge</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <!-- Inside City Charges -->
          <h6>Inside City Charges</h6>
          <div class="row">
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="inside_city_2kg" min="0" step="0.01" placeholder="2kg" />
                <label for="inside_city_2kg">2kg<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="inside_city_2kg-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="inside_city_5kg" min="0" step="0.01" placeholder="5kg" />
                <label for="inside_city_5kg">5kg<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="inside_city_5kg-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="inside_city_10kg" min="0" step="0.01" placeholder="10kg" />
                <label for="inside_city_10kg">10kg<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="inside_city_10kg-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="inside_city_above_10kg" min="0" step="0.01" placeholder="Above 10kg" />
                <label for="inside_city_above_10kg">Above 10kg<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="inside_city_above_10kg-error"></span>
              </div>
            </div>
          </div>

          <!-- Outside City Charges -->
          <h6>Outside City Charges</h6>
          <div class="row">
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="outside_city_2kg" min="0" step="0.01" placeholder="2kg" />
                <label for="outside_city_2kg">2kg<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="outside_city_2kg-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="outside_city_5kg" min="0" step="0.01" placeholder="5kg" />
                <label for="outside_city_5kg">5kg<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="outside_city_5kg-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="outside_city_10kg" min="0" step="0.01" placeholder="10kg" />
                <label for="outside_city_10kg">10kg<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="outside_city_10kg-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="outside_city_above_10kg" min="0" step="0.01" placeholder="Above 10kg" />
                <label for="outside_city_above_10kg">Above 10kg<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="outside_city_above_10kg-error"></span>
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
  const errors = document.querySelectorAll('.error-message');
  errors.forEach(el => el.innerText = '');
}

async function Save() {
  const fields = [
    'inside_city_2kg','inside_city_5kg','inside_city_10kg','inside_city_above_10kg',
    'outside_city_2kg','outside_city_5kg','outside_city_10kg','outside_city_above_10kg'
  ];

  let isValid = true;
  const formData = new FormData();

  // Validate input fields
  fields.forEach(field => {
    const value = document.getElementById(field).value.trim();
    document.getElementById(`${field}-error`).innerText = '';

    if (!value || isNaN(parseFloat(value)) || parseFloat(value) < 0) {
      document.getElementById(`${field}-error`).innerText = 'Enter a valid number!';
      isValid = false;
    } else {
      formData.append(field, value);
    }
  });

  if (!isValid) return;

  try {
    const res = await axios.post('/client/store/delivery-charge', formData, {
      headers: { 'content-type': 'multipart/form-data' }
    });

    if (res.status === 201) {
      successToast(res.data.message || 'Delivery charge created successfully!');
      resetCreateForm();
      window.location.href = '/client/delivery-charge/page';
    } else {
      errorToast(res.data.message || "Failed to create delivery charge");
    }
  } catch (error) {
    if (error.response) {
      if (error.response.status === 422) {
        // Validation errors
        const errors = error.response.data.errors;
        Object.keys(errors).forEach(field => {
          const errorElement = document.getElementById(`${field}-error`);
          if (errorElement) errorElement.innerText = errors[field][0];
        });
      } else if (error.response.status === 400) {
        // Already exists error
        errorToast(error.response.data.message || "Cannot create delivery charge. Already exists.");
      } else {
        errorToast(error.response.data.message || "An error occurred");
      }
    } else {
      errorToast("Network error. Please try again.");
    }
  }
}

</script>
