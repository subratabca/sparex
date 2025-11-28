<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Add Meal Delivery Charge</h5>

      <div class="card-body demo-vertical-spacing demo-only-element">

        <form id="save-form">
          <div class="row">

            <!-- Client Dropdown -->
            <div class="col-md-4">
              <div class="form-floating form-floating-outline">
                <select id="client_id" name="client_id" class="form-select w-100">
                  <option value="" disabled selected>Select Client</option>
                </select>
                <label for="client_id">Select Client<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="client_id-error"></span>
            </div>

            <!-- Meal Type Dropdown -->
            <div class="col-md-4">
              <div class="form-floating form-floating-outline">
                <select id="meal_type_id" name="meal_type_id" class="form-select w-100">
                  <option value="" disabled selected>Select Meal Type</option>
                </select>
                <label for="meal_type_id">Select Meal Type<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="meal_type_id-error"></span>
            </div>

          </div>


          <div class="row mt-4">

            <!-- Inside 2 KM -->
            <div class="col-md-3">
              <div class="form-floating form-floating-outline">
                  <input type="number" step="0.01" class="form-control" id="inside_city_2km" placeholder="2 KM Charge">
                  <label for="inside_city_2km">Inside City (0-2 KM)</label>
              </div>
              <span class="error-message text-danger" id="inside_city_2km-error"></span>
            </div>

            <!-- Inside 5 KM -->
            <div class="col-md-3">
              <div class="form-floating form-floating-outline">
                  <input type="number" step="0.01" class="form-control" id="inside_city_5km" placeholder="5 KM Charge">
                  <label for="inside_city_5km">Inside City (2-5 KM)</label>
              </div>
              <span class="error-message text-danger" id="inside_city_5km-error"></span>
            </div>

            <!-- Inside 10 KM -->
            <div class="col-md-3">
              <div class="form-floating form-floating-outline">
                  <input type="number" step="0.01" class="form-control" id="inside_city_10km" placeholder="10 KM Charge">
                  <label for="inside_city_10km">Inside City (5-10 KM)</label>
              </div>
              <span class="error-message text-danger" id="inside_city_10km-error"></span>
            </div>

            <!-- Above 10 KM -->
            <div class="col-md-3">
              <div class="form-floating form-floating-outline">
                  <input type="number" step="0.01" class="form-control" id="inside_city_above_10km" placeholder="Above 10 KM Charge">
                  <label for="inside_city_above_10km">Above 10 KM</label>
              </div>
              <span class="error-message text-danger" id="inside_city_above_10km-error"></span>
            </div>

          </div>

        </form>

        <button onclick="storeInfo()" class="btn btn-primary btn-lg mt-3">
          <i class="mdi mdi-check me-2"></i>Confirm
        </button>

      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", async function () {
    await loadClients();
    await loadMealTypeDropdown();
});

function toTitleCase(str) {
    return str.replace(/\w\S*/g, (txt) => {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}


async function loadClients() {
    showLoader();
    try {
        const res = await axios.get("/clients");
        const select = document.getElementById("client_id");

        res.data.data.forEach(client => {
            let option = document.createElement("option");
            option.value = client.id;

            let fullName = `${client.firstName} ${client.lastName}`;
            option.text = toTitleCase(fullName); 

            select.appendChild(option);
        });

    } catch (error) {
        errorToast("Failed to load clients");
    } finally {
        hideLoader();
    }
}

async function loadMealTypeDropdown() {
    showLoader();
    try {
        const res = await axios.get("/get/meal-types");
        const mealTypeSelect = document.getElementById("meal_type_id");

        if (res.status === 200) {
            res.data.data.forEach(type => {
                const option = document.createElement("option");
                option.value = type.id;

                option.text = toTitleCase(type.name); 
                mealTypeSelect.appendChild(option);
            });
        }
    } catch (error) {
        errorToast("Failed to load meal types");
    } finally {
        hideLoader();
    }
}



async function storeInfo() {

    document.querySelectorAll(".error-message").forEach(span => span.innerText = "");

    let client_id = document.getElementById("client_id").value;
    let meal_type_id = document.getElementById("meal_type_id").value;
    let inside_city_2km = document.getElementById("inside_city_2km").value;
    let inside_city_5km = document.getElementById("inside_city_5km").value;
    let inside_city_10km = document.getElementById("inside_city_10km").value;
    let inside_city_above_10km = document.getElementById("inside_city_above_10km").value;

    let isValid = true;

    if (!client_id) {
        document.getElementById("client_id-error").innerText = "Client is required!";
        isValid = false;
    }
    if (!meal_type_id) {
        document.getElementById("meal_type_id-error").innerText = "Meal type is required!";
        isValid = false;
    }

    if (!isValid) return;

    const formData = new FormData();
    formData.append("client_id", client_id);
    formData.append("meal_type_id", meal_type_id);
    formData.append("inside_city_2km", inside_city_2km);
    formData.append("inside_city_5km", inside_city_5km);
    formData.append("inside_city_10km", inside_city_10km);
    formData.append("inside_city_above_10km", inside_city_above_10km);

    showLoader();

    try {
        const res = await axios.post("/admin/store/meal-delivery/charge", formData);

        if (res.status === 201) {
            successToast("Delivery charge created successfully");
            window.location.href = "/admin/meal-delivery/charge";
        }
    }
    catch (error) {
        handleError(error);
    }
    finally {
        hideLoader();
    }
}

function handleError(error) {
    if (error.response?.status === 422) {
        Object.entries(error.response.data.errors).forEach(([key, val]) => {
            let span = document.getElementById(`${key}-error`);
            if (span) span.innerText = val[0];
        });
        return;
    }

    errorToast(error.response?.data?.message ?? "Unexpected error occurred");
}
</script>
