<div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <h4 class="card-header">Profile Details</h4>
        <!-- Account -->
        <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img
              src="{{ asset('frontend/assets/img/avatars/1.png') }}"
              alt="user-avatar"
              class="d-block w-px-120 h-px-120 rounded"
              id="mainImg" />
            <div class="button-wrapper">

              <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0">
                <span class="d-none d-sm-block">Upload new photo</span>
                <i class="mdi mdi-tray-arrow-up d-block d-sm-none"></i>
                <input type="file" id="upload" onChange="mainImgUrl(this)" 
                  class="account-file-input"
                  hidden
                  accept="image/png, image/jpeg" />
              </label>

              <div class="small">Allowed JPG, GIF or PNG. Max size of 800K</div>
            </div>
          </div>
        </div>


        <div class="card-body pt-2 mt-1">
          <form id="formAccountSettings">
            <div class="row mt-2 gy-4">
              <div class="col-md-4">
                <div class="form-floating form-floating-outline">
                  <input type="text" disabled class="form-control" id="email" autofocus />
                  <label for="firstName">Email</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="firstName" autofocus />
                  <label for="firstName">First Name</label>
                  <span class="error-message text-danger" id="firstName-error"></span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="lastName" />
                  <label for="lastName">Last Name</label>
                  <span class="error-message text-danger" id="lastName-error"></span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control"  id="mobile" />
                  <label for="phoneNumber">Phone Number</label>
                  <span class="error-message text-danger" id="mobile-error"></span>
                </div>
              </div>
              <div class="col-md-4">
                  <div class="form-floating form-floating-outline">
                      <input type="text" class="form-control" id="address1" placeholder="Enter house and street number" />
                      <label for="address1">Address1<span class="text-danger">*</span></label>
                      <span class="error-message text-danger" id="address1-error"></span>
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="form-floating form-floating-outline mb-4">
                      <input type="text" class="form-control" id="address2" placeholder="Enter address2" />
                      <label for="exampleFormControlInput1">Address2</label>
                      <span class="error-message text-danger" id="address2-error"></span>
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="form-floating form-floating-outline mb-4">
                      <input type="text" class="form-control" id="zip-code" placeholder="Enter zip code" />
                      <label for="zip-code">Postcode(Example: EC1A 1BB)<span class="text-danger">*</span></label>
                      <span class="error-message text-danger" id="zip_code-error"></span>
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="form-floating form-floating-outline">
                      <select id="countrySelect" class="form-select w-100" aria-label="Select Country"></select>
                      <label for="countrySelect">Select Country<span class="text-danger">*</span></label>
                  </div>
                  <span class="error-message text-danger" id="country-error"></span>
              </div>

              <div class="col-md-4">
                  <div class="form-floating form-floating-outline">
                      <select id="countySelect" class="form-select w-100" aria-label="Select County"></select>
                      <label for="countySelect">Select County<span class="text-danger">*</span></label>
                  </div>
                  <span class="error-message text-danger" id="county-error"></span>
              </div>

              <div class="col-md-4">
                  <div class="form-floating form-floating-outline">
                      <select id="citySelect" class="form-select w-100" aria-label="Select City"></select>
                      <label for="citySelect">Select City<span class="text-danger">*</span></label>
                  </div>
                  <span class="error-message text-danger" id="city-error"></span>
              </div>
            </div>
            <div class="mt-4">
              <button  onclick="onUpdate(event)" class="btn btn-primary me-2">Update</button>
            </div>
          </form>
        </div>
        <!-- /Account -->
      </div>
    </div>
</div>




<script type="text/javascript">
    function mainImgUrl(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                const mainImg = document.getElementById('mainImg');
                if (mainImg) {
                    mainImg.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<script>
  async function onUpdate(event) {
    event.preventDefault();
    
    let firstName = document.getElementById('firstName').value;
    let lastName = document.getElementById('lastName').value;
    let mobile = document.getElementById('mobile').value;
    let image = document.getElementById('upload').files[0];

    document.getElementById('firstName-error').innerText = '';
    document.getElementById('lastName-error').innerText = '';
    document.getElementById('mobile-error').innerText = '';

    if(firstName.length===0){
      document.getElementById('firstName-error').innerText = 'First name is required';
    }
    else if(lastName.length===0){
      document.getElementById('lastName-error').innerText = 'Last name is required';
    }
    else if(mobile.length===0){
      document.getElementById('mobile-error').innerText = 'Mobile is required';
    }
    else{
      let formData=new FormData();
      formData.append('firstName', firstName);
      formData.append('lastName', lastName);
      formData.append('mobile', mobile);
      if (image) {
        formData.append('image', image);
      }

      const config = {
        headers: {
          'content-type': 'multipart/form-data'
        }
      }

      try {
        let res=await axios.post("/admin/profile/update",formData,config)

        if(res.status===200 && res.data['status']==='success'){
          successToast(res.data['message']);
          window.location.href = '/admin/update/profile';
        }else{
          errorToast(res.data['message'] || 'An unexpected error occurred');
        }
      }catch (error) {
        // Map 422 validation errors to inline fields, otherwise use the global handler (config.js)
        if (error.response?.status === 422) {
          const errors = error.response.data.errors || {};
          Object.keys(errors).forEach(key => {
            const el = document.getElementById(`${key}-error`);
            if (el) el.innerText = errors[key][0];
            else errorToast(errors[key][0]);
          });
        } else {
          handleError(error);
        }
      }
    }

  }

</script>