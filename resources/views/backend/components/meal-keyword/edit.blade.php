<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Update Meal Keyword</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="updateID">
            <div class="col-md-3">
              <div class="form-floating form-floating-outline">
                <select id="mealTypeSelect" name="meal_type_id" class="form-select w-100" aria-label="Select Meal Type">
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
        <button onclick="updateInfo()" class="btn btn-primary btn-lg"><i class="mdi mdi-check me-2"></i>Update</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async function () {
    await getMealKeywordInfo();
});

async function getMealKeywordInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split("/");
        let id = segments[segments.length - 1];

        let res = await axios.get("/admin/get/meal-keyword/details/" + id);
        if (res.status === 200 && res.data.status === "success") {
            const keywordData = res.data.data;

            document.getElementById("updateID").value = id;
            document.getElementById("name").value = keywordData["name"];

            const mealTypeResponse = await axios.get("/get/meal-types");
            const mealTypeSelect = document.getElementById("mealTypeSelect");
            mealTypeSelect.innerHTML = '<option value="" disabled>Select Meal Type</option>';

            mealTypeResponse.data.data.forEach((mealType) => {
                const option = document.createElement("option");
                option.value = mealType.id;
                option.textContent = mealType.name;
                if (parseInt(keywordData.meal_type_id) === parseInt(mealType.id)) {
                    option.selected = true;
                }
                mealTypeSelect.appendChild(option);
            });
        } else {
            errorToast(res.data.message || "Failed to fetch menu details.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

async function updateInfo() {
    document.querySelectorAll(".error-message").forEach(span => span.innerText = '');

    let updateID = document.getElementById('updateID').value;
    const name = document.getElementById('name').value.trim();
    const meal_type_id = document.getElementById('mealTypeSelect').value;

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
    formData.append('id', updateID);
    formData.append('name', name);
    formData.append('meal_type_id', meal_type_id);

    const config = { headers: { "content-type": "multipart/form-data" } };

    showLoader();
    try {
        const res = await axios.post("/admin/update/meal-keyword", formData, config);

        if (res.status === 200 && res.data.status === "success") {
            successToast(res.data.message || "Data updated successfully");
            resetCreateForm();
            window.location.href = '/admin/meal-keywords';
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
}

function handleError(error) {
  document.querySelectorAll(".error-text").forEach(el => el.innerText = '');

  let message = 'An unexpected error occurred';
  let showToast = true; 

  if (error.response) {
    const status = error.response.status;
    const serverMessage = error.response.data?.message;

    switch (status) {
      case 422:
        if (error.response.data.errors) {
          Object.entries(error.response.data.errors).forEach(([field, messages]) => {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
              errorElement.innerText = messages[0];
            }
          });
        }
        showToast = false;
        break;

      case 500:
        message = serverMessage || 'Server error. Please try again later.';
        break;

      default:
        message = serverMessage || message;
    }
  } else if (error.request) {
    message = 'No response received from the server.';
  } else {
    message = error.message || message;
  }

  if (showToast) {
    errorToast(message);
  }
}
</script>


