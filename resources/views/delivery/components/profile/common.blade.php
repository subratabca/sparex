<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="user-profile-header-banner">
        <img src="{{ asset('backend/assets/img/pages/profile-banner.png') }}" alt="Banner image" class="rounded-top" />
      </div>
      <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
          <img
            src="{{ asset('backend/assets/img/avatars/1.png') }}"
            alt="user image" id="common-image" 
            class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
        </div>
        <div class="flex-grow-1 mt-3 mt-sm-5">
          <div
            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
            <div class="user-profile-info">
              <h4 id="common-firstName">Delivery Name</h4>
              <ul
                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                <li class="list-inline-item">
                  <i class="mdi mdi-phone me-1 mdi-20px"></i>
                  <span class="fw-medium" id="common-mobile">Phone</span>
                </li>
                <li class="list-inline-item">
                  <i class="mdi mdi-email me-1 mdi-20px"></i
                  ><span class="fw-medium" id="common-email">Email</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
function capitalizeWords(str) {
    if (!str) return '';
    return str
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

  async function getProfile(){
    try {
      let res=await axios.get("/rider/get/profile/info")
      if(res.status === 200 && res.data['status']==='success'){
        let data = res.data['data'];

        document.getElementById('common-image').src = data['image'] ? "/upload/delivery-profile/small/" + data['image'] : "/upload/no_image.jpg";

        const fullName = `${capitalizeWords(data.firstName || '')} ${capitalizeWords(data.lastName || '')}`.trim();
        document.getElementById('common-firstName').innerText = fullName;
        document.getElementById('common-mobile').innerText = data['mobile'];
        document.getElementById('common-email').innerText = data['email'];

        document.getElementById('profile-email').value = data['email'];
        document.getElementById('profile-firstName').value = data['firstName'];
        document.getElementById('profile-lastName').value = data['lastName'];
        document.getElementById('profile-mobile').value = data['mobile'];

      
      }
      else{
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
</script>

