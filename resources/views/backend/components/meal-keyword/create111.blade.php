<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Add Meal Keyword</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <div class="col-md-4">
              <div class="form-floating form-floating-outline">
                <select id="meal_type_id" name="meal_type_id" class="form-select w-100" aria-label="Select Meal Type">
                  <option value="" disabled selected>Select Meal Type</option>
                </select>
                <label for="mealTypeSelect">Select Meal Type<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="meal_type_id-error"></span>
            </div>
            <div class="col-md-4">
              <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" id="name" placeholder="Enter meal type" />
                <label for="exampleFormControlInput1">Meal Keyword:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="name-error"></span>
              </div>
            </div>
          </div>
        </form>
        <button onclick="storeInfo()" class="btn btn-primary btn-lg"><i class="mdi mdi-check me-2"></i>Confirm</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async function () {
    await loadMealTypeDropdown();
});

async function loadMealTypeDropdown() {
    showLoader();
    try {
        const res = await axios.get("/get/meal-types");
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

async function storeInfo() {
    document.querySelectorAll(".error-message").forEach(span => span.innerText = '');

    const name = document.getElementById('name').value.trim();
    const meal_type_id = document.getElementById('meal_type_id').value;

    let isValid = true;

    if (!name) {
        document.getElementById('name-error').innerText = "Keyword name is required!";
        isValid = false;
    }

    if (!meal_type_id) {
        document.getElementById('meal_type_id-error').innerText = "Need to select meal type!";
        isValid = false;
    }

    if (!isValid) return;

    const formData = new FormData();
    formData.append('name', name);
    formData.append('meal_type_id', meal_type_id);

    const config = { headers: { "content-type": "multipart/form-data" } };

    showLoader();
    try {
        const res = await axios.post("/admin/store/meal-keyword", formData, config);

        if (res.status === 201 && res.data.status === "success") {
            successToast(res.data.message || "Data created successfully");
            resetCreateForm();
            window.location.href = '/admin/meal-keywords';
        } else {
            errorToast(res.data.message || "Request failed");
        }
    } catch (error) {
        if (error.response?.status === 422) {
            Object.entries(error.response.data.errors).forEach(([key, val]) => {
                const span = document.getElementById(`${key}-error`);
                if (span) span.innerText = val[0];
                else errorToast(val[0]);
            });
        } else {
            handleError(error);
        }
    } finally {
        hideLoader();
    }
}

function resetCreateForm() {
    const form = document.getElementById('save-form');
    if (form) form.reset();
}

</script>
