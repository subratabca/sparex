@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Update Meal Type</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="updateID">
            <div class="col-md-6">
              <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" id="name" placeholder="Enter meal type" />
                <label for="exampleFormControlInput1">Meal Type:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="name-error"></span>
              </div>
            </div>
          </div>
        </form>
        <button onclick="updateMealType()" class="btn btn-primary btn-lg">
          <i class="mdi mdi-check me-2"></i>Update
        </button>
      </div>
    </div>
  </div>

</div>

@endsection

<script>
var quill;
document.addEventListener("DOMContentLoaded", function() {
    mealTypeInfo();

    const form = document.getElementById('save-form');
    form.addEventListener('onclick', async function(event) {
        event.preventDefault();
        await updateMealType();
    });
});

async function mealTypeInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];

        let res = await axios.get("/user/get/meal-type/details/" + id);
        document.getElementById('updateID').value = id;
        document.getElementById('name').value = res.data.data['name'];
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
  document.getElementById('name-error').innerText = '';
}


async function updateMealType() {
  const name = document.getElementById('name').value;
  let updateID = document.getElementById('updateID').value;

  document.getElementById('name-error').innerText = '';

  if (name.length === 0) {
    errorToast("name required !");
  } else {
    let formData = new FormData();
        formData.append('name', name);
        formData.append('id', updateID);

        const config = {
            headers: {
                'content-type': 'multipart/form-data'
            }
        };

        try {
          const res = await axios.post('/user/update/meal-type', formData, config);
          if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message || 'Request success');
            window.location.href = '/user/meal-types';
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
}

</script>