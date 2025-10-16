<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Create Hero information</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <div class="col-md-12">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="title" 
                placeholder="Enter title" />
                <label for="exampleFormControlInput1">Title:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="title-error"></span>
              </div>
            </div>

            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <div class="mb-3">
                    <label for="formFile" class="form-label">Upload Image<span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="image" onChange="mainImageUrl(this)"/>
                    <img src="{{asset('/upload/no_image.jpg')}}" id="mainImage" class="mt-1" style="width: 150px; height: 100px;">
                  </div>
                  <span class="error-message text-danger" id="image-error"></span>
                </div>
              </div>
            </div>

            <div class="col-md-12 my-4">
              <div class="card">
                <h5 class="card-header">Description:<span class="text-danger">*</span></h5>
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
        <button onclick="Save()" class="btn btn-primary btn-lg">
          <i class="mdi mdi-check me-2"></i>Confirm
        </button>
      </div>
    </div>
  </div>

</div>



<script>
  var quill;
  document.addEventListener("DOMContentLoaded", function() {
      quill = new Quill('#snow-editor', {
          theme: 'snow',
          modules: {
              toolbar: '#toolbar'
          }
      });

      const form = document.getElementById('save-form');
      form.addEventListener('submit', async function(event) {
          event.preventDefault();
          await Save();
      });
  });

  function mainImageUrl(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#mainImage').attr('src', e.target.result).width(150).height(100);
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

function resetCreateForm() {
  document.getElementById('save-form').reset(); 
  document.getElementById('mainImage').src = "{{asset('/upload/no_image.jpg')}}"; 
  quill.setText('');
  document.getElementById('title-error').innerText = '';
  document.getElementById('description-error').innerText = '';
  document.getElementById('image-error').innerText = '';
}


  async function Save() {
    const title = document.getElementById('title').value;
    const description = quill.getText().trim();  
    const image = document.getElementById('image').files[0];

    document.getElementById('title-error').innerText = '';
    document.getElementById('description-error').innerText = '';
    document.getElementById('image-error').innerText = '';


    if (title.length === 0) {
      errorToast("Title required !");
    } 
    else if (description.length === 0) {
      errorToast("Description required !");
    }
    else if (!image) {
      errorToast("Image required !");
    }   
    else {
      let formData = new FormData();
          formData.append('title', title);
          formData.append('description', description);
          formData.append('image', image);

          const config = {
              headers: {
                  'content-type': 'multipart/form-data'
              }
          };

      try {
        const res = await axios.post('/admin/store/hero', formData, config);
        if (res.status === 201) {
          successToast(res.data.message || 'Request success');
          window.location.href = '/admin/hero/page';
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


