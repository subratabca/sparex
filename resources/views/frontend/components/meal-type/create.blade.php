@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Add Meal Type</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <div class="col-md-6">
              <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" id="name" placeholder="Enter meal type" />
                <label for="exampleFormControlInput1">Meal Type:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="name-error"></span>
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

@endsection

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
  document.getElementById('name-error').innerText = '';
}


  async function Save() {
    const name = document.getElementById('name').value;
    document.getElementById('name-error').innerText = '';

    if (name.length === 0) {
      errorToast("Meal type required !");
    } else {
      let formData = new FormData();
          formData.append('name', name);
          const config = {
              headers: {
                  'content-type': 'multipart/form-data'
              }
          };

      try {
        const res = await axios.post('/user/store/meal-type', formData, config);
        if (res.status === 201) {
          successToast(res.data.message || 'Request success');
          window.location.href = '/user/meal-types';
          resetCreateForm();
        } else {
          errorToast(res.data.message || "Request failed");
        }
      } catch (error) {
          if (error.response && error.response.status === 422) {
            let errorMessages = error.response.data.errors;
            for (let field in errorMessages) {
              if (errorMessages.hasOwnProperty(field)) {
                document.getElementById(`${field}-error`).innerText = errorMessages[field][0];
              }
            }
          } else if (error.response && error.response.status === 500) {
            errorToast(error.response.data.error);
          } else {
            errorToast("Request failed!");
          }
      }
    }
  }

</script>