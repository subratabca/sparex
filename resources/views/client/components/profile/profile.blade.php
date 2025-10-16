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
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" disabled class="form-control" id="profile-email" autofocus />
                  <label for="firstName">Email</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="profile-firstName" autofocus />
                  <label for="firstName">First Name</label>
                  <span class="error-message text-danger" id="profile-firstName-error"></span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="profile-lastName" />
                  <label for="lastName">Last Name</label>
                  <span class="error-message text-danger" id="profile-lastName-error"></span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control"  id="profile-mobile" />
                  <label for="phoneNumber">Phone Number</label>
                  <span class="error-message text-danger" id="profile-mobile-error"></span>
                </div>
              </div>
            </div>
            <div class="mt-4">
              <button  onclick="onUpdate(event)" class="btn btn-primary me-2">Update</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>




<script type="text/javascript">
    function mainImgUrl(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                // Using pure JavaScript to set the image source
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
  async function onUpdate() {
    event.preventDefault();
    
    let firstName = document.getElementById('profile-firstName').value;
    let lastName = document.getElementById('profile-lastName').value;
    let mobile = document.getElementById('profile-mobile').value;
    let image = document.getElementById('upload').files[0];

    document.getElementById('profile-firstName-error').innerText = '';
    document.getElementById('profile-lastName-error').innerText = '';
    document.getElementById('profile-mobile-error').innerText = '';

    if(firstName.length===0){
      document.getElementById('profile-firstName-error').innerText = 'First name is required';
    }
    else if(lastName.length===0){
      document.getElementById('profile-lastName-error').innerText = 'Last name is required';
    }
    else if(mobile.length===0){
      document.getElementById('profile-mobile-error').innerText = 'Mobile is required';
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
        let res=await axios.post("/client/profile/update",formData,config)

        if(res.status===200 && res.data['status']==='success'){
          successToast(res.data['message']);
          window.location.href = '/client/update/profile';
        }else{
          errorToast(res.data['message'] || 'An unexpected error occurred');
        }
      }catch (error) {
        if (error.response) {
          const status = error.response.status;
          if (status === 404) {
            errorToast(error.response.data.message || 'User not found');
          } else if (status === 422) {
            const errors = error.response.data.errors || {};
            for (const key in errors) {
              if (errors.hasOwnProperty(key)) {
                const errorMessage = errors[key][0]; 
                document.getElementById(`${key}-error`).innerText = errorMessage; 
              }
            }
          } else {
            errorToast(error.response.data.message || 'An unexpected error occurred');
          }
        } else {
          errorToast('Network error: ' + error.message);
        }
      }
    }

  }

</script>