<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Update hero information</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="updateID">
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
        <button onclick="updateHeroInfo()" class="btn btn-primary btn-lg">
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
    HeroInfo();

    quill = new Quill('#snow-editor', {
        theme: 'snow',
        modules: {
            toolbar: '#toolbar'
        }
    });

    const form = document.getElementById('save-form');
    form.addEventListener('onclick', async function(event) {
        event.preventDefault();
        await updateHeroInfo();
    });
});

async function HeroInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];

        let res = await axios.get("/admin/show/hero/info/" + id);
        document.getElementById('updateID').value = id;
        document.getElementById('title').value = res.data.data['title'];

        const imageElement = document.getElementById('oldImg');
        if (res.data.data['image']) {
            imageElement.src = `/upload/hero/${res.data.data['image']}`; 
        } else {
            imageElement.src = '/upload/no_image.jpg'; 
        }

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
  document.getElementById('mainImage').src = "{{asset('/upload/no_image.jpg')}}"; 

  quill.setText('');
  
  document.getElementById('title-error').innerText = '';
  document.getElementById('description-error').innerText = '';
  document.getElementById('image-error').innerText = '';
}


async function updateHeroInfo() {
  const title = document.getElementById('title').value;
  const description = quill.getText().trim();  
  const image = document.getElementById('imgUpdate').files[0];
  let updateID = document.getElementById('updateID').value;

  document.getElementById('title-error').innerText = '';
  document.getElementById('description-error').innerText = '';
  document.getElementById('image-error').innerText = '';


  if (title.length === 0) {
    errorToast("Title required !");
  } 
  else if (description.length === 0) {
    errorToast("Company description required !");
  }   
  else {
    let formData = new FormData();
        formData.append('title', title);
        formData.append('description', description);
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
          const res = await axios.post('/admin/update/hero', formData, config);
          if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message || 'Request success');
            window.location.href = '/admin/hero/page';
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


