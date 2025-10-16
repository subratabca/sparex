<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Update site setting</h5>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="updateID">
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="name"
                placeholder="Enter company name" />
                <label for="exampleFormControlInput1">Company Name:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="name-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="email"
                class="form-control"
                id="email"
                placeholder="Enter email address" />
                <label for="exampleFormControlInput1">Email:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="email-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="phone1"
                placeholder="Enter phone number" />
                <label for="exampleFormControlInput1">Phone1:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="phone1-error"></span>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="phone2"
                placeholder="Enter phone number" />
                <label for="exampleFormControlInput1">Phone2:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="phone2-error"></span>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="website_name"
                placeholder="Enter website name" />
                <label for="exampleFormControlInput1">Website Name:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="website_name-error"></span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="slogan"
                placeholder="Enter slogan" />
                <label for="exampleFormControlInput1">Slogan:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="slogan-error"></span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="address"
                placeholder="Enter address" />
                <label for="exampleFormControlInput1">Address:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="address-error"></span>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="zip_code"
                placeholder="Enter zip code" />
                <label for="exampleFormControlInput1">Zip Code<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="zip_code-error"></span>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="country"
                placeholder="Enter country" />
                <label for="exampleFormControlInput1">Country:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="country-error"></span>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="city"
                placeholder="Enter city" />
                <label for="exampleFormControlInput1">City:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="city-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="facebook"
                placeholder="Enter facebook link" />
                <label for="exampleFormControlInput1">Facebook Link:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="facebook-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="linkedin"
                placeholder="Enter linkedin link" />
                <label for="exampleFormControlInput1">Linkedin Link:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="linkedin-error"></span>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text"
                class="form-control"
                id="youtube"
                placeholder="Enter food collection address" />
                <label for="exampleFormControlInput1">Youtube Link:<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="youtube-error"></span>
              </div>
            </div>

            <div class="col-md-12">
              <div class="card">
                <div class="card-body">

                  <div class="row">
                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="formFile" class="form-label">Old Logo<span class="text-danger">*</span></label><br>
                        <img src="{{ asset('/upload/no_image.jpg')}}" id="oldImg" class="mt-1" style="width: 150px; height: 100px;">
                      </div>
                    </div>

                    <div class="col-lg-4">
                      <div class="mb-3">
                        <label for="formFile" class="form-label">Upload New Logo<span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="imgUpdate" onChange="updateImgUrl(this)"/>
                        <img src="{{asset('/upload/no_image.jpg')}}" id="updateImg" class="mt-1" style="width: 150px; height: 100px;">
                      </div>
                      <span class="error-message text-danger" id="image-error"></span>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <div class="col-md-6 my-4">
              <div class="card">
                <h5 class="card-header">Company Description:<span class="text-danger">*</span></h5>
                <div class="card-body">
                   @include('backend.components.editor')
                  <div id="snow-editor">

                  </div>
                  <span class="error-message text-danger" id="description-error"></span>
                </div>
              </div>
            </div>

            <div class="col-md-6 my-4">
              <div class="card">
                <h5 class="card-header">Refund Policy Description:<span class="text-danger">*</span></h5>
                <div class="card-body">
                   @include('backend.components.editor1')
                  <div id="snow-editor1">

                  </div>
                  <span class="error-message text-danger" id="refund-error"></span>
                </div>
              </div>
            </div>

            <div class="col-md-6 my-4">
              <div class="card">
                <h5 class="card-header">Terms & Condition Description:<span class="text-danger">*</span></h5>
                <div class="card-body">
                   @include('backend.components.editor2')
                  <div id="snow-editor2">

                  </div>
                  <span class="error-message text-danger" id="terms-error"></span>
                </div>
              </div>
            </div>

            <div class="col-md-6 my-4">
              <div class="card">
                <h5 class="card-header">Privacy Policy Description:<span class="text-danger">*</span></h5>
                <div class="card-body">
                   @include('backend.components.editor3')
                  <div id="snow-editor3">

                  </div>
                  <span class="error-message text-danger" id="privacy-error"></span>
                </div>
              </div>
            </div>

          </div>
        </form>
        <button onclick="updateSiteSetting()" class="btn btn-primary btn-lg">
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
var quill, quill1, quill2, quill3;
document.addEventListener("DOMContentLoaded", function() {
    SiteSettingInfo();

    quill = new Quill('#snow-editor', {
        theme: 'snow',
        modules: {
            toolbar: '#toolbar'
        }
    });

    quill1 = new Quill('#snow-editor1', {
        theme: 'snow',
        modules: {
            toolbar: '#toolbar1'
        }
    });
    
    quill2 = new Quill('#snow-editor2', {
        theme: 'snow',
        modules: {
            toolbar: '#toolbar2'
        }
    });

    quill3 = new Quill('#snow-editor3', {
        theme: 'snow',
        modules: {
            toolbar: '#toolbar3'
        }
    });

    const form = document.getElementById('save-form');
    form.addEventListener('onclick', async function(event) {
        event.preventDefault();
        await updateSiteSetting();
    });
});

async function SiteSettingInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];

        let res = await axios.get("/admin/site-setting/info/" + id);
        document.getElementById('updateID').value = id;
        document.getElementById('name').value = res.data.data['name'];
        document.getElementById('email').value = res.data.data['email'];
        document.getElementById('phone1').value = res.data.data['phone1'];
        document.getElementById('phone2').value = res.data.data['phone2'];
        document.getElementById('website_name').value = res.data.data['website_name'];
        document.getElementById('slogan').value = res.data.data['slogan'];
        document.getElementById('address').value = res.data.data['address'];
        document.getElementById('country').value = res.data.data['country'];
        document.getElementById('city').value = res.data.data['city'];
        document.getElementById('zip_code').value = res.data.data['zip_code'];
        document.getElementById('facebook').value = res.data.data['facebook'];
        document.getElementById('linkedin').value = res.data.data['linkedin'];
        document.getElementById('youtube').value = res.data.data['youtube'];

        const imageElement = document.getElementById('oldImg');
        if (res.data.data['logo']) {
            imageElement.src = `/upload/site-setting/${res.data.data['logo']}`; 
        } else {
            imageElement.src = '/upload/no_image.jpg'; 
        }

        quill.root.innerHTML = res.data.data['description'];
        quill1.root.innerHTML = res.data.data['refund'];
        quill2.root.innerHTML = res.data.data['terms'];
        quill3.root.innerHTML = res.data.data['privacy'];

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

  quill.setText('');
  quill1.setText('');
  quill2.setText('');
  quill3.setText('');
  
  document.getElementById('name-error').innerText = '';
  document.getElementById('email-error').innerText = '';
  document.getElementById('phone1-error').innerText = '';
  document.getElementById('phone2-error').innerText = '';
  document.getElementById('website_name-error').innerText = '';
  document.getElementById('slogan-error').innerText = '';
  document.getElementById('address-error').innerText = '';
  document.getElementById('country-error').innerText = '';
  document.getElementById('city-error').innerText = '';
  document.getElementById('zip_code-error').innerText = '';
  document.getElementById('facebook-error').innerText = '';
  document.getElementById('linkedin-error').innerText = '';
  document.getElementById('youtube-error').innerText = '';
  document.getElementById('description-error').innerHTML = '';
  document.getElementById('refund-error').innerHTML = '';
  document.getElementById('terms-error').innerHTML = '';
  document.getElementById('privacy-error').innerHTML = '';
  document.getElementById('image-error').innerText = '';
}


async function updateSiteSetting() {
  const name = document.getElementById('name').value;
  const email = document.getElementById('email').value;
  const phone1 = document.getElementById('phone1').value;
  const phone2 = document.getElementById('phone2').value;
  const website_name = document.getElementById('website_name').value;
  const slogan = document.getElementById('slogan').value;
  const address = document.getElementById('address').value;
  const country = document.getElementById('country').value;
  const city = document.getElementById('city').value;
  const zip_code = document.getElementById('zip_code').value;
  const facebook = document.getElementById('facebook').value;
  const linkedin = document.getElementById('linkedin').value;
  const youtube = document.getElementById('youtube').value;
  
  let description = quill.root.innerHTML.trim();
  let refund = quill1.root.innerHTML.trim();
  const terms = quill2.root.innerHTML.trim();
  const privacy = quill3.root.innerHTML.trim();

  const image = document.getElementById('imgUpdate').files[0];
  let updateID = document.getElementById('updateID').value;

  document.getElementById('name-error').innerText = '';
  document.getElementById('email-error').innerText = '';
  document.getElementById('phone1-error').innerText = '';
  document.getElementById('phone2-error').innerText = '';
  document.getElementById('website_name-error').innerText = '';
  document.getElementById('slogan-error').innerText = '';
  document.getElementById('address-error').innerText = '';
  document.getElementById('country-error').innerText = '';
  document.getElementById('city-error').innerText = '';
  document.getElementById('zip_code-error').innerText = '';
  document.getElementById('facebook-error').innerText = '';
  document.getElementById('linkedin-error').innerText = '';
  document.getElementById('youtube-error').innerText = '';
  document.getElementById('description-error').innerHTML = '';
  document.getElementById('refund-error').innerHTML = '';
  document.getElementById('terms-error').innerHTML = '';
  document.getElementById('privacy-error').innerHTML = '';
  document.getElementById('image-error').innerText = '';


  if (name.length === 0) {
    errorToast("Company name required !");
  } 
  else if (website_name.length === 0) {
    errorToast("Website name required !");
  }
  else if (slogan.length === 0) {
    errorToast("Slogan required !");
  }
  else if (email.length === 0) {
    errorToast("Email required !");
  } 
  else if (phone1.length === 0) {
    errorToast("Phone number required !");
  }
  else if (phone2.length === 0) {
    errorToast("Phone number2 required !");
  }
  else if (address.length === 0) {
    errorToast("Address required !");
  }
  else if (country.length === 0) {
    errorToast("Country required !");
  }
  else if (city.length === 0) {
    errorToast("City required !");
  }
  else if (zip_code.length === 0) {  
    errorToast("Zip code required !");
  }
  else if (facebook.length === 0) {
    errorToast("Facebook url required !");
  }
  else if (linkedin.length === 0) {
    errorToast("Linkedin url required !");
  }
  else if (youtube.length === 0) {
    errorToast("Youtube url required !");
  }
  else if (quill.getText().trim().length === 0) {
      errorToast("Company description22 required!");
  }
  else if (quill.getText().trim().length < 10) {
      errorToast("Company description must be at least 10 characters long!");
  }
  else if (quill1.getText().trim().length === 0) {
    errorToast("Refund description required !");
  }
  else if (quill1.getText().trim().length < 10) {
      errorToast("Refund description must be at least 10 characters long!");
  }
  else if (quill2.getText().trim().length === 0) {  
    errorToast("T&C description required !");
  }
  else if (quill2.getText().trim().length < 10) {
      errorToast("T&C description must be at least 10 characters long!");
  }
  else if (quill3.getText().trim().length.length === 0) {  
    errorToast("Privacy description required !");
  }
  else if (quill3.getText().trim().length.length < 10) {
      errorToast("Privacy description must be at least 10 characters long!");
  }   
  else {
    let formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('phone1', phone1);
        formData.append('phone2', phone2);
        formData.append('website_name', website_name);
          formData.append('slogan', slogan);
        formData.append('address', address);
        formData.append('country', country);
        formData.append('city', city);
        formData.append('zip_code', zip_code);
        formData.append('facebook', facebook);
        formData.append('linkedin', linkedin);
        formData.append('youtube', youtube);
        formData.append('description', description);
        formData.append('refund', refund);
        formData.append('terms', terms);
        formData.append('privacy', privacy);
        if (image) {
            formData.append('logo', image);
        }

        formData.append('id', updateID);

        const config = {
            headers: {
                'content-type': 'multipart/form-data'
            }
        };

        try {
          const res = await axios.post('/admin/update/site-setting', formData, config);
          if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message || 'Request success');
            window.location.href = '/admin/setting-page';
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


