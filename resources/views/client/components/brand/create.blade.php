<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Add New Brand</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <div class="col-md-6">
              <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" id="name" placeholder="Enter name" />
                <label for="exampleFormControlInput1">Brand Name:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="name-error"></span>
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
  document.getElementById('name-error').innerText = '';
  document.getElementById('image-error').innerText = '';
}


  async function Save() {
    const name = document.getElementById('name').value;
    const image = document.getElementById('image').files[0];

    document.getElementById('name-error').innerText = '';
    document.getElementById('image-error').innerText = '';


    if (name.length === 0) {
      errorToast("Category name required !");
    } 
    else if (!image) {
      errorToast("Image required !");
    }   
    else {
      let formData = new FormData();
          formData.append('name', name);
          formData.append('image', image);

          const config = {
              headers: {
                  'content-type': 'multipart/form-data'
              }
          };

      try {
        const res = await axios.post('/client/store/brand', formData, config);
        if (res.status === 201) {
          successToast(res.data.message || 'Request success');
          window.location.href = '/client/brand/page';
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


