@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h4 class="card-header">Update your documents</h4>
            <div id="success-message" class="alert alert-success d-none"></div>
            <hr>
            <div class="card-body">
                <form id="formAccountSettings">
                    <div class="row gy-4">
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="firstName" autofocus />
                                <label for="firstName">First Name</label>
                                <span class="error-message text-danger" id="firstName-error"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="lastName" />
                                <label for="lastName">Last Name</label>
                                <span class="error-message text-danger" id="lastName-error"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control"  id="mobile" />
                                <label for="mobile">Phone Number</label>
                                <span class="error-message text-danger" id="mobile-error"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" disabled id="email" />
                                <label for="email">Email</label>
                                <span class="error-message text-danger" id="email-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2 gy-4">
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
                    </div>

                    <div class="row">
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
                    <hr>
                    <p class="mb-4"><span class="text-danger">You must upload any of 2 documens</span> (Passport,NID,Bank Statement,Driving license,Utility bill)</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start align-items-sm-center gap-4">
                                <img src="{{ asset('/upload/no_image.jpg') }}" alt="user-avatar" class="d-block w-px-120 h-px-120 rounded" id="docImg1" />
                                <div class="button-wrapper">
                                    <label for="image1" class="btn btn-primary me-2 mb-3" tabindex="0">
                                        <span class="d-none d-sm-block">Upload Document Image1</span>
                                        <i class="mdi mdi-tray-arrow-up d-block d-sm-none"></i>
                                        <input type="file" id="image1" onChange="mainImgUrl1(this)" class="account-file-input upload" hidden accept="image/png, image/jpeg" />
                                    </label>
                                    <div class="small">Allowed JPG, JPEG, PNG or PDF. Max size of 800K</div>
                                </div>
                            </div>
                            <span class="error-message text-danger" id="image1-error"></span>
                        </div> 

                        <div class="col-md-6">
                            <div class="d-flex align-items-start align-items-sm-center gap-4">
                                <img src="{{ asset('/upload/no_image.jpg') }}" alt="user-avatar" class="d-block w-px-120 h-px-120 rounded" id="docImg2" />
                                <div class="button-wrapper">
                                    <label for="image2" class="btn btn-primary me-2 mb-3" tabindex="0">
                                        <span class="d-none d-sm-block">Upload Document Image2</span>
                                        <i class="mdi mdi-tray-arrow-up d-block d-sm-none"></i>
                                        <input type="file" id="image2" onChange="mainImgUrl2(this)" class="account-file-input" hidden accept="image/png, image/jpeg" />
                                    </label>
                                    <div class="small">Allowed JPG, JPEG, PNG or PDF. Max size of 800K</div>
                                </div>
                            </div>
                            <span class="error-message text-danger" id="image2-error"></span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button  onclick="onDocumentUpdate(event)" class="btn btn-primary me-2">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


<script type="text/javascript">
    function mainImgUrl1(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                const docImg1 = document.getElementById('docImg1');
                if (docImg1) {
                    docImg1.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }



    function mainImgUrl2(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                const docImg2 = document.getElementById('docImg2');
                if (docImg2) {
                    docImg2.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<script>
document.addEventListener("DOMContentLoaded", async function() {
    await DocDetailsInfo();
})


async function DocDetailsInfo() {
  showLoader();
  try {
      const response = await axios.get('/user/profile/info');

      if (response.status === 200) {
          const userData = response.data.data;

        document.getElementById('firstName').value = userData.firstName;
        document.getElementById('lastName').value = userData.lastName;
        document.getElementById('mobile').value = userData.mobile;
        document.getElementById('email').value = userData.email;
        document.getElementById('address1').value = userData.address1;
        document.getElementById('address2').value = userData.address2;
        document.getElementById('zip-code').value = userData.zip_code;

        document.getElementById('docImg1').src = userData['doc_image1'] ? "/upload/customer-document/small/" + userData['doc_image1'] : "/upload/no_image.jpg";

        document.getElementById('docImg2').src = userData['doc_image2'] ? "/upload/customer-document/small/" + userData['doc_image2'] : "/upload/no_image.jpg";

 
        const countriesResponse = await axios.get('/countries');
        const countrySelect = document.getElementById('countrySelect');
        countrySelect.innerHTML = '<option value="">Select Country</option>';
        countriesResponse.data.data.forEach(country => {
            const option = document.createElement('option');
            option.value = country.id;
            option.textContent = country.name;
            if (userData.country_id === country.id) {
                option.selected = true;
            }
            countrySelect.appendChild(option);
        });


        await loadCounties(userData.country_id || '', userData.county_id || '');
        countrySelect.addEventListener('change', async function () {
            await loadCounties(this.value);
        });


        await loadCities(userData.county_id || '', userData.city_id || '');
        const countySelect = document.getElementById('countySelect');
        countySelect.addEventListener('change', async function () {
            await loadCities(this.value);
        });
    }
  } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
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

async function onDocumentUpdate(event) {
    event.preventDefault();

    let firstName = document.getElementById('firstName').value;
    let lastName = document.getElementById('lastName').value;
    let mobile = document.getElementById('mobile').value;

    let address1 = document.getElementById('address1').value;
    let address2 = document.getElementById('address2').value;
    let zip_code = document.getElementById('zip-code').value;
    let country_id = document.getElementById('countrySelect').value;
    let county_id = document.getElementById('countySelect').value;
    let city_id = document.getElementById('citySelect').value;
    

    let doc_image1 = document.getElementById('image1').files[0];
    let doc_image2 = document.getElementById('image2').files[0];

    document.getElementById('firstName-error').innerText = '';
    document.getElementById('lastName-error').innerText = '';
    document.getElementById('mobile-error').innerText = '';

    document.getElementById('address1-error').innerText = '';
    document.getElementById('address2-error').innerText = '';
    document.getElementById('country-error').innerText = '';
    document.getElementById('county-error').innerText = '';
    document.getElementById('city-error').innerText = '';
    document.getElementById('zip_code-error').innerText = '';

    document.getElementById('image1-error').innerText = '';
    document.getElementById('image2-error').innerText = '';


    if (firstName.length === 0) {
        document.getElementById('firstName-error').innerText = 'First name is required';
    }
    else if (lastName.length === 0) {
        document.getElementById('lastName-error').innerText = 'Last name is required';
    }
    else if (mobile.length === 0) {
        document.getElementById('mobile-error').innerText = 'Mobile is required';
    }
    else if (address1.length === 0) {
        document.getElementById('address1-error').innerText = 'Address1 is required';
    }
    else if (country_id.length === 0) {
        document.getElementById('country-error').innerText = 'Country is required';
    }
    else if (county_id.length === 0) {
        document.getElementById('county-error').innerText = 'County is required';
    }
    else if (city_id.length === 0) {
        document.getElementById('city-error').innerText = 'City is required';
    }
    else if (zip_code.length === 0) {
        document.getElementById('zip_code-error').innerText = 'Zip code is required';
    }
    else if (!doc_image1) {
        document.getElementById('image1-error').innerText = 'Document image1 is required!.';
    }
    else if (!doc_image2) {
        document.getElementById('image2-error').innerText = 'Document image2 is required!.';
    }
    else{
        let formData = new FormData();
        formData.append('firstName', firstName);
        formData.append('lastName', lastName);
        formData.append('mobile', mobile);
        formData.append('address1', address1);
        formData.append('address2', address2);
        formData.append('country_id', country_id);
        formData.append('county_id', county_id);
        formData.append('city_id', city_id);
        formData.append('zip_code', zip_code);
        formData.append('doc_image1', doc_image1);
        formData.append('doc_image2', doc_image2);

        const config = {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        };

        try {
            let res = await axios.post("/user/store/document/info", formData, config);

            if (res.status === 201 && res.data.status === 'success') {
                const successMessageDiv = document.getElementById('success-message');
                successMessageDiv.innerText = res.data.message;
                successMessageDiv.classList.remove('d-none');

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                errorToast(res.data.message || 'An unexpected error occurred');
            }
        } catch (error) {
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

function handleError(error) {
    if (error.response) {
        const status = error.response.status;
        const message = error.response.data.message || 'An unexpected error occurred';

        if (status === 400) {
            errorToast(message || 'Bad Request');
        } else if (status === 404) {
            errorToast(message || 'Customer not found');
        } else if (status === 500) {
            errorToast(message || 'Server Error');
        } else {
            errorToast(message);
        }
    }
}
</script>

