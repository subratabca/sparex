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

        if (window.location.href === "http://127.0.0.1:8000/admin/update/profile") {
          document.getElementById('email').value = data['email'];
          document.getElementById('firstName').value = data['firstName'];
          document.getElementById('lastName').value = data['lastName'];
          document.getElementById('mobile').value = data['mobile'];

          document.getElementById('address1').value = data['address1'];
          document.getElementById('address2').value = data['address2'];
          document.getElementById('zip-code').value = data['zip_code'];

          document.getElementById('mainImg').src = data['image'] ? "/upload/admin-profile/small/" + data['image'] : "/upload/no_image.jpg";

          const countriesResponse = await axios.get('/countries');
          const countrySelect = document.getElementById('countrySelect');
          countrySelect.innerHTML = '<option value="">Select Country</option>';
          countriesResponse.data.data.forEach(country => {
              const option = document.createElement('option');
              option.value = country.id;
              option.textContent = country.name;
              if (data.country_id === country.id) {
                  option.selected = true;
              }
              countrySelect.appendChild(option);
          });


          await loadCounties(data.country_id || '', data.county_id || '');
          countrySelect.addEventListener('change', async function () {
              await loadCounties(this.value);
          });


          await loadCities(data.county_id || '', data.city_id || '');
          const countySelect = document.getElementById('countySelect');
          countySelect.addEventListener('change', async function () {
              await loadCities(this.value);
          });
        }
      
      }
      else{
        errorToast(res.data['message'] || 'An unexpected error occurred');
      }
    }catch (error) {
      if (error.response) {
        const status = error.response.status;
        if (status === 404) {
          errorToast(error.response.data.message || 'User not found'); 
        } else if (status === 500) {
          errorToast(error.response.data.message || 'An error occurred on the server');
        } else {
          errorToast(error.response.data.message || 'An unexpected error occurred');
        }
      } else {
        errorToast('Network error: ' + error.message);
      }
    }
  }


  async function loadCounties(countryId, selectedCountyId = '') {
  const countySelect = document.getElementById('countySelect');
  countySelect.innerHTML = '<option value="">Select County</option>';
  if (countryId) {
    const countiesResponse = await axios.get(`/counties/${countryId}`);
    countiesResponse.data.data.forEach(county => {
      const option = document.createElement('option');
      option.value = county.id;
      option.textContent = county.name;
      if (selectedCountyId === county.id) {
        option.selected = true;
      }
      countySelect.appendChild(option);
    });
  }
}

async function loadCities(countyId, selectedCityId = '') {
  const citySelect = document.getElementById('citySelect');
  citySelect.innerHTML = '<option value="">Select City</option>';
  if (countyId) {
    const citiesResponse = await axios.get(`/cities/${countyId}`);
    citiesResponse.data.data.forEach(city => {
      const option = document.createElement('option');
      option.value = city.id;
      option.textContent = city.name;
      if (selectedCityId === city.id) {
        option.selected = true;
      }
      citySelect.appendChild(option);
    });
  }
}
</script>

