<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Add Meal Keyword</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <div class="col-md-6">
              <div class="form-floating form-floating-outline mb-2">
                <input type="text" class="form-control" id="name" placeholder="Enter meal keyword" />
                <label for="name">Meal Keyword:<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="name-error"></span>
            </div>
            <div class="col-md-6">
              <label class="form-label d-block">Select Meal Types:<span class="text-danger">*</span></label>
              <div id="mealTypeCheckboxes" class="d-flex flex-wrap gap-3"></div>
              <span class="error-message text-danger d-block" id="meal_type_id-error"></span>
            </div>
          </div>
        </form>
        <button onclick="storeInfo()" class="btn btn-primary btn-lg mt-3"><i class="mdi mdi-check me-2"></i>Confirm</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async function () {
    await loadMealTypeCheckboxes();
});

async function loadMealTypeCheckboxes() {
    showLoader();
    try {
        const res = await axios.get("/get/meal-types");
        if (res.status === 200 && res.data.status === 'success') {
            const container = document.getElementById("mealTypeCheckboxes");
            container.innerHTML = '';
            res.data.data.forEach(mealType => {
                const label = mealType.name.charAt(0).toUpperCase() + mealType.name.slice(1);
                container.insertAdjacentHTML('beforeend', `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="meal_type_id[]" value="${mealType.id}" id="mt_${mealType.id}">
                        <label class="form-check-label" for="mt_${mealType.id}">${label}</label>
                    </div>`);
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

function getCheckedMealTypes() {
    return Array.from(document.querySelectorAll('input[name="meal_type_id[]"]:checked')).map(cb => cb.value);
}

async function storeInfo() {
    document.querySelectorAll(".error-message").forEach(span => span.innerText = '');

    const name = document.getElementById('name').value.trim();
    const mealTypeIds = getCheckedMealTypes();

    let isValid = true;
    if (!name) {
        document.getElementById('name-error').innerText = "Keyword name is required!";
        isValid = false;
    }
    if (mealTypeIds.length === 0) {
        document.getElementById('meal_type_id-error').innerText = "Please select at least one meal type!";
        isValid = false;
    }
    if (!isValid) return;

    const formData = new FormData();
    formData.append('name', name);
    mealTypeIds.forEach(id => formData.append('meal_type_id[]', id));

    showLoader();
    try {
        const res = await axios.post("/admin/store/meal-keyword", formData);
        if (res.status === 201 && res.data.status === "success") {
            successToast(res.data.message || "Data created successfully");
            window.location.href = '/admin/meal-keywords';
        } else {
            errorToast(res.data.message || "Request failed");
        }
    } catch (error) {
        if (error.response?.status === 422) {
            Object.entries(error.response.data.errors).forEach(([key, val]) => {
                const span = document.getElementById(`${key.split('.')[0]}-error`);
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
</script>
