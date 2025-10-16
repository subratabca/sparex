<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Update T&C</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="updateID">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="largeSelect" class="form-label">T&C For<span class="text-danger">*</span></label>
                <select id="name" class="form-select form-select-lg">
                  <option value="" disabled selected>Select T&C for</option>
                  <option value="Food Upload">Food Upload</option>
                  <option value="Request Approve">Request Approve</option>
                  <option value="Food Deliver">Food Deliver</option>
                  <option value="Customer Registration">Customer Registration</option>
                  <option value="Client Registration">Client Registration</option>
                </select>
                <span class="error-message text-danger" id="name-error"></span> 
              </div>
            </div>

            <div class="col-md-12 my-4">
              <div class="card">
                <h6 class="card-header">T&C Description<span class="text-danger">*</span></h6>
                <div class="card-body">
                   @include('backend.components.editor')
                  <div id="snow-editor">

                  </div>
                  <span class="error-message text-danger" id="description-error"></span>
                </div>
              </div>
            </div>
          </div>
        </form>
        <button onclick="updateTC()" class="btn btn-primary btn-lg">
          <i class="mdi mdi-check me-2"></i>Update
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  var quill;
  document.addEventListener("DOMContentLoaded", function() {
      TCDetailsInfo();
      quill = new Quill('#snow-editor', {
          theme: 'snow',
          modules: {
              toolbar: '#toolbar'
          }
      });

      const form = document.getElementById('save-form');
      form.addEventListener('onclick', async function(event) {
          event.preventDefault();
          await Save();
      });
  });

async function TCDetailsInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];
        let res = await axios.get("/admin/terms-conditions/info/" + id);
        document.getElementById('updateID').value = id;
        document.getElementById('name').value = res.data.data['name'];

        quill.root.innerHTML = res.data.data['description'];

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
  document.getElementById('description-error').innerHTML = ''; 
  quill.setContents([]);
}



async function updateTC() {
    let name = document.getElementById('name').value;
    let description = quill.root.innerHTML.trim(); 
    let updateID = document.getElementById('updateID').value;

    document.getElementById('name-error').innerText = '';
    document.getElementById('description-error').innerHTML = '';

    if (name.length === 0) {
      errorToast("Item name required !");
    } 
    else if (quill.getText().trim().length === 0) {  
        errorToast("Item description required !");
    }
    else if (quill.getText().trim().length < 10) {
        errorToast("Item description must be at least 10 characters long!");
    }
    else{
        let formData = new FormData();
            formData.append('name', name);
            formData.append('description', quill.root.innerHTML);
            formData.append('id', updateID);

        const config = {
            headers: {
                'content-type': 'multipart/form-data',
            },
        };

        try {
            showLoader();
            let res = await axios.post("/admin/update/terms-conditions", formData, config);
            if (res.status === 200) {
                successToast(res.data.message || 'Request success');
                window.location.href = '/admin/terms-conditions/list';
                resetCreateForm();
            } else {
                errorToast(res.data.message || "Request failed");
            }
        } catch (error) {
            if (error.response && error.response.status === 422) {
                let errorMessages = error.response.data.errors;
                for (let field in errorMessages) {
                    let fieldErrorElement = document.getElementById(`${field}-error`);
                    if (fieldErrorElement) {
                        fieldErrorElement.innerText = errorMessages[field][0]; 
                    }
                }
            } else if (error.response && error.response.status === 500) {
                errorToast(error.response.data.error);
            } else {
                errorToast("Request failed!");
            }
        } finally {
            hideLoader();
        }
    }
}


</script>

