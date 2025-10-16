<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Update Brand information</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="updateID">
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
                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="formFile" class="form-label">Old Image<span class="text-danger">*</span></label><br>
                        <img src="{{ asset('/upload/no_image.jpg')}}" id="oldImg" class="mt-1" style="width: 150px; height: 100px;">
                      </div>
                    </div>

                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="formFile" class="form-label">Upload New Image<span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="imgUpdate" onChange="updateImgUrl(this)"/>
                        <img src="{{asset('/upload/no_image.jpg')}}" id="updateImg" class="mt-1" style="width: 150px; height: 100px;">
                      </div>
                      <span class="error-message text-danger" id="image-error"></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
        <button onclick="updateBrandInfo()" class="btn btn-primary btn-lg">
          <i class="mdi mdi-check me-2"></i>Update
        </button>
      </div>
    </div>
  </div>

</div>


<script type="text/javascript">
  function updateImgUrl(input){
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e){
        $('#updateImg').attr('src',e.target.result).width(150).height(100);
      };
      reader.readAsDataURL(input.files[0]);
    }
  } 
</script>

<script>
var quill;
document.addEventListener("DOMContentLoaded", function() {
    BrandInfo();

    const form = document.getElementById('save-form');
    form.addEventListener('onclick', async function(event) {
        event.preventDefault();
        await updateBrandInfo();
    });
});

async function BrandInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];

        let res = await axios.get("/client/show/brand/info/" + id);
        document.getElementById('updateID').value = id;
        document.getElementById('name').value = res.data.data['name'];

        const imageElement = document.getElementById('oldImg');
        if (res.data.data['image']) {
            imageElement.src = `/upload/brand/${res.data.data['image']}`; 
        } else {
            imageElement.src = '/upload/no_image.jpg'; 
        }


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
  document.getElementById('save-form').reset(); // Resets all input fields
  document.getElementById('mainImage').src = "{{asset('/upload/no_image.jpg')}}"; 
  document.getElementById('name-error').innerText = '';
  document.getElementById('image-error').innerText = '';
}


async function updateBrandInfo() {
  const name = document.getElementById('name').value;
  const image = document.getElementById('imgUpdate').files[0];
  let updateID = document.getElementById('updateID').value;

  document.getElementById('name-error').innerText = '';
  document.getElementById('image-error').innerText = '';


  if (name.length === 0) {
    errorToast("name required !");
  }   
  else {
    let formData = new FormData();
        formData.append('name', name);
        if (image) {
            formData.append('image', image);
        }

        formData.append('id', updateID);

        const config = {
            headers: {
                'content-type': 'multipart/form-data'
            }
        };

        try {
          const res = await axios.post('/client/update/brand', formData, config);
          if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message || 'Request success');
            window.location.href = '/client/brand/page';
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


