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
            alt="user image" id="commonImg" 
            class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
        </div>
        <div class="flex-grow-1 mt-3 mt-sm-5">
          <div
            class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
            <div class="user-profile-info">
              <h4 id="user-firstName">John Doe</h4>
              <ul
                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                <li class="list-inline-item">
                  <i class="mdi mdi-phone me-1 mdi-20px"></i>
                  <span class="fw-medium" id="user-mobile">Phone</span>
                </li>
                <li class="list-inline-item">
                  <i class="mdi mdi-email me-1 mdi-20px"></i
                  ><span class="fw-medium" id="user-email">Email</span>
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
  async function getProfile(){
    try {
      let res=await axios.get("/admin/profile/info")
      if(res.status === 200 && res.data['status']==='success'){
        let data = res.data['data'];

        document.getElementById('commonImg').src = data['image'] ? "/upload/admin-profile/small/" + data['image'] : "/upload/no_image.jpg";

        document.getElementById('user-firstName').innerText = data['firstName'];
        document.getElementById('user-mobile').innerText = data['mobile'];
        document.getElementById('user-email').innerText = data['email'];

        if (window.location.pathname === '/admin/update/profile') {
          document.getElementById('email').value = data['email'];
          document.getElementById('firstName').value = data['firstName'];
          document.getElementById('lastName').value = data['lastName'];
          document.getElementById('mobile').value = data['mobile'];

          document.getElementById('address1').value = data['address1'];
          document.getElementById('address2').value = data['address2'];
          document.getElementById('zip-code').value = data['zip_code'];

          document.getElementById('mainImg').src = data['image'] ? "/upload/admin-profile/small/" + data['image'] : "/upload/no_image.jpg";

          // Country → County → City using the global loaders in config.js (with pre-selection)
          await loadCountries(data.country_id || '', 'countrySelect');
          await loadCounties(data.country_id || '', data.county_id || '', 'countySelect');
          await loadCities(data.county_id || '', data.city_id || '', 'citySelect');

          document.getElementById('countrySelect').addEventListener('change', async function () {
              await loadCounties(this.value, '', 'countySelect');
              document.getElementById('citySelect').innerHTML = '<option value="">Select City</option>';
          });
          document.getElementById('countySelect').addEventListener('change', async function () {
              await loadCities(this.value, '', 'citySelect');
          });
        }
      
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


  // loadCountries / loadCounties / loadCities are provided globally by config.js
</script>

