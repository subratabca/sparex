@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header"><h5>Create New Menu</h5></div>
      <div class="card-header-elements ms-auto">
        <a href="{{ route('customer.menus') }}" type="button" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To Menu List
        </a>
      </div>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <div class="col-md-3">
              <div class="form-floating form-floating-outline">
                <select id="meal_type_id" name="meal_type_id" class="form-select w-100" aria-label="Select Meal Type">
                  <option value="" disabled selected>Select Meal Type</option>
                </select>
                <label for="mealTypeSelect">Select Meal Type<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="meal_type_id-error"></span>
            </div>

            <div class="col-md-4">
              <div class="form-floating form-floating-outline mb-3">
                <input type="text" class="form-control" id="name" placeholder="Enter menu name" />
                <label for="exampleFormControlInput1">Menu Name<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="name-error"></span>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 my-4">
              <div class="card">
                <h5 class="card-header">Menu Description<span class="text-danger">*</span></h5>
                <div class="card-body">
                  @include('frontend.components.editor')
                  <div id="snow-editor"></div>
                  <span class="error-message text-danger" id="description-error"></span>
                </div>
              </div>
            </div>
          </div> 

          <button type="submit" class="btn btn-primary btn-lg"><i class="mdi mdi-check me-2"></i>Confirm</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

<script>
window.handleError = function (error) {
    //console.log("Error response:", error.response?.data);
    if (error.response && error.response.status === 422) {
        const errorMessages = error.response.data.errors || {};

        document.querySelectorAll(".error-message").forEach(span => (span.innerText = ""));

        for (let field in errorMessages) {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
                errorElement.innerText = errorMessages[field][0];
            }
        }
    } else if (error.response && error.response.status === 500) {
        errorToast(error.response.data.error);
    } else {
        errorToast("Request failed!");
    }
};

let quill;

document.addEventListener("DOMContentLoaded", async function () {
    await loadMealTypeDropdown();

    quill = new Quill("#snow-editor", {
        theme: "snow",
        modules: {
            toolbar: "#toolbar",
        },
    });

    // Submit form
    document.getElementById("save-form").addEventListener("submit", async function (event) {
        event.preventDefault();
        await createMenu();
    });
});


async function loadMealTypeDropdown() {
    showLoader();
    try {
        const res = await axios.get("/user/get/meal-types");
        if (res.status === 200 && res.data.status === 'success') {
            const mealTypeSelect = document.getElementById("meal_type_id");
            res.data.data.forEach(mealType => {
                const option = document.createElement("option");
                option.value = mealType.id;
                option.text = mealType.name;
                mealTypeSelect.appendChild(option);
            });
        } else {
            errorToast(res.data.message || "Failed to fetch meal types");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

/**
 * Create a new menu
 */
async function createMenu() {
    // Clear previous errors
    document.querySelectorAll(".error-message").forEach(span => span.innerText = '');

    const name = document.getElementById('name').value.trim();
    const meal_type_id = document.getElementById('meal_type_id').value;
    const description = quill.root.innerHTML.trim();

    let isValid = true;

    // Minimal frontend validation
    if (!name) {
        document.getElementById('name-error').innerText = "Menu name is required!";
        isValid = false;
    }

    if (!meal_type_id) {
        document.getElementById('meal_type_id-error').innerText = "Need to select meal type!";
        isValid = false;
    }

    if (!quill.getText().trim()) {
        document.getElementById('description-error').innerText = "Description is required!";
        isValid = false;
    }

    if (!isValid) return;

    const formData = new FormData();
    formData.append('name', name);
    formData.append('meal_type_id', meal_type_id);
    formData.append('description', description);

    const config = { headers: { "content-type": "multipart/form-data" } };

    showLoader();
    try {
        const res = await axios.post("/user/store/customer-menu",formData, config);

        // Success
        if (res.status === 201 && res.data.status === "success") {
            successToast(res.data.message || "Menu created successfully");
            window.location.href = '/user/customer-menus';
            resetCreateForm();
        } else {
            errorToast(res.data.message || "Request failed");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}


function resetCreateForm() {
    const form = document.getElementById('save-form');
    if (form) form.reset();
    if (quill) quill.setContents([]);
}
</script>
