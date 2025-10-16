<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <div class="card-header"><h5>Create New Product</h5>
        @if(isset($client) && $client->status == 0)
        <p><span class="text-danger">To upload an product, you must submit the necessary documents.</span> 
          <a href="/client/document" style="color: green; text-decoration: none;">Upload Your Document Here</a>
        </p>
        @endif
      </div>
      <div class="card-header-elements ms-auto">
        <a href="{{ route('client.products') }}" type="button" class="btn btn-primary waves-effect waves-light">
          <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To Product List
        </a>
      </div>

      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
          <div class="row">
            <div class="col-md-3 p-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="hasVariants" name="has_variants" />
                <label class="form-check-label" for="defaultCheck3"><span class="text-info">Product has variants (color & size)</span></label>
              </div>
            </div>
            <div class="col-md-2 p-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="hasBrand" name="has_brand" />
                <label class="form-check-label" for="defaultCheck3"><span class="text-info">Product has brand</span></label>
              </div>
            </div>
            <div class="col-md-1 p-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="isFree" name="is_free" />
                <label class="form-check-label" for="defaultCheck3"><span class="text-info">Free</span></label>
              </div>
            </div>
            <div class="col-md-2 p-2" id="discountCheckboxContainer">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="hasDiscountPrice" name="has_discount_price" />
                <label class="form-check-label" for="defaultCheck3"><span class="text-info">Discount Price</span></label>
              </div>
            </div><hr class="mt-2">
          </div>
          <div class="row">
            <div class="col-md-2" id="categoryContainer">
              <div class="form-floating form-floating-outline">
                <select id="categorySelect" class="form-select w-100" aria-label="Select Category">
                </select>
                <label for="categorySelect">Select Category<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="category-error"></span>
            </div>
            <div class="col-md-2" id="brandContainer" style="display: none;">
              <div class="form-floating form-floating-outline">
                <select id="brandSelect" class="form-select w-100" aria-label="Select Brand">

                </select>
                <label for="brandSelect">Select Brand<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="brand-error"></span>
            </div>
            <div class="col-md-4" id="nameContainer">
              <div class="form-floating form-floating-outline mb-3">
                <input type="text" class="form-control" id="name" placeholder="Enter product name" />
                <label for="exampleFormControlInput1">Product Name<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="name-error"></span>
              </div>
            </div>
            <div class="col-md-2" id="weightContainer">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="weight" step="0.01" min=0 placeholder="Enter product weight" />
                <label for="exampleFormControlInput1">Product Weight (Kg)<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="weight-error"></span>
              </div>
            </div>
            <div class="col-md-2" id="priceContainer">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="price" min="0" step="0.01" placeholder="Enter product price" />
                <label for="price">Product Price<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="price-error"></span>
              </div>
            </div>
            <div class="col-md-2" id="discountContainer" style="display: none;">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="discountPrice" min="0" step="0.01" placeholder="Enter discount price" />
                <label for="price">Discount Price<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="discount_price-error"></span>
              </div>
            </div>
            <div class="col-md-2" id="qtyContainer">
              <div class="form-floating form-floating-outline mb-4">
                <input type="number" class="form-control" id="qty" min=0 placeholder="Enter product qty" />
                <label for="exampleFormControlInput1">Product Qty<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="qty-error"></span>
              </div>
            </div>
          </div>
          <div id="variantSection" style="display: none;">
            <div class="row variant-row">
              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-4">
                  <input type="text" class="form-control" name="color[]" placeholder="Enter product color" />
                  <label>Product Color<span class="text-danger">*</span></label>
                  <span class="error-message text-danger" id="color-error"></span>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-4">
                  <input type="text" class="form-control" name="size[]" placeholder="Enter product size" />
                  <label>Product Size<span class="text-danger">*</span></label>
                  <span class="error-message text-danger" id="size-error"></span>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-4">
                  <input type="number" class="form-control" name="qty2[]" min="1" placeholder="Enter product qty" />
                  <label>Product Qty<span class="text-danger">*</span></label>
                  <span class="error-message text-danger" id="qty2-error"></span>
                </div>
              </div>
              <div class="col-md-1">
                <button type="button" class="btn btn-primary add-variant">Add</button>
              </div>
              <div class="col-md-1">
                <button type="button" class="btn btn-primary remove-variant">Remove</button>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-5">
              <div class="form-floating form-floating-outline mb-4">
                <input
                type="text" class="form-control" id="address1" placeholder="Enter collection address" />
                <label for="exampleFormControlInput1">Collection Address1(Includes house number and street name.)<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="address1-error"></span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" id="address2" placeholder="Enter collection address" />
                <label for="exampleFormControlInput1">Collection Address2</label>
                <span class="error-message text-danger" id="address2-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="text" class="form-control" id="zip-code" placeholder="Enter zip code" />
                <label for="zip-code">Postcode(Example: EC1A 1BB)<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="zip_code-error"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-4">
              <div class="form-floating form-floating-outline">
                <select id="countrySelect" class="form-select w-100" aria-label="Select Country">
                </select>
                <label for="countrySelect">Select Country<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="country-error"></span>
            </div>
            <div class="col-md-4 mb-4">
              <div class="form-floating form-floating-outline">
                <select id="countySelect" class="form-select w-100" aria-label="Select County">
                </select>
                <label for="countySelect">Select County<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="county-error"></span>
            </div>
            <div class="col-md-4 mb-4">
              <div class="form-floating form-floating-outline">
                <select id="citySelect" class="form-select w-100" aria-label="Select City">
                </select>
                <label for="citySelect">Select City<span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="city-error"></span>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="date" class="form-control" id="expire_date" placeholder="Enter expire date" />
                <label for="exampleFormControlInput1">Expire Date<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="expire_date-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="date" class="form-control" id="collection_date" placeholder="Enter collection date" />
                <label for="exampleFormControlInput1">Collection Date<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="collection_date-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="time" class="form-control" id="start_collection_time" placeholder="Enter collection start time" />
                <label for="exampleFormControlInput1">Collection Time(From)<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="start_collection_time-error"></span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating form-floating-outline mb-4">
                <input type="time" class="form-control" id="end_collection_time" placeholder="Enter collection end time" />
                <label for="exampleFormControlInput1">Collection Time(To)<span class="text-danger">*</span></label>
                <span class="error-message text-danger" id="end_collection_time-error"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <div class="mb-3">
                    <label for="formFile" class="form-label">Upload Product Image<span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="image" onChange="mainImageUrl(this)"/>
                    <img src="{{asset('/upload/no_image.jpg')}}" id="mainImage" class="mt-1" style="width: 150px; height: 100px;">
                  </div>
                  <span class="error-message text-danger" id="image-error"></span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <div class="mb-3">
                    <label for="formFileMultiple" class="form-label">Multiple Image(maximum 2 images)<span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="multi_image" multiple onChange="multiImageUrl(this)"/>
                  </div>

                  <div id="multiImage" class="mt-1" style="display: flex; gap: 5px;">
                    <img src="{{asset('/upload/no_image.jpg')}}" id="defaultImage" style="width: 150px; height: 100px;">
                  </div>
                  <span class="error-message text-danger" id="multi_image-error"></span>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 my-4">
              <div class="card">
                <h5 class="card-header">Product Description<span class="text-danger">*</span></h5>
                <div class="card-body">
                  @include('client.components.editor')
                  <div id="snow-editor">

                  </div>
                  <span class="error-message text-danger" id="description-error"></span>
                </div>
              </div>
            </div>
          </div>         
          @if(isset($client) && $client->status == 1)
          <div class="row">
            <div class="col-md-12 p-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="accept_tnc" />
                <label class="form-check-label" for="defaultCheck3"><a href="/client/product/upload/terms-conditions/product_upload" target="_blank">Accept T&C For Product Upload</a><span class="text-danger">*</span></label>
              </div>
              <span class="error-message text-danger" id="accept_tnc-error"></span>
            </div>
          </div>
          @endif
          @if(isset($client) && $client->status == 1)
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="mdi mdi-check me-2"></i>Confirm
          </button>
          @endif
        </form>
      </div>
    </div>
  </div>
</div>

<script>
var quill;
document.addEventListener("DOMContentLoaded", async function() {
  let profileCityId = null;

  const categorySelect = document.querySelector('#categorySelect');
  const brandSelect = document.querySelector('#brandSelect');
  const countrySelect = document.querySelector('#countrySelect');
  const countySelect = document.querySelector('#countySelect');
  const citySelect = document.querySelector('#citySelect');

  categorySelect.innerHTML = '<option value="" disabled selected>Select Category</option>';
  brandSelect.innerHTML = '<option value="" disabled selected>Select Brand</option>';
  countrySelect.innerHTML = '<option value="" disabled selected>Select Country</option>';
  countySelect.innerHTML = '<option value="" disabled selected>Select County</option>';
  citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';

  try {
    const response = await axios.get('/categories');
    response.data.data.forEach(category => {
      const option = document.createElement('option');
      option.value = category.id;
      option.textContent = category.name;
      categorySelect.appendChild(option);
    });
  } catch (error) {
    if (error.response && error.response.status === 500) {
      errorToast(error.response.data.error);
    } else {
      errorToast("Failed to load categories. Please try again later.");
    }
  }

  try {
    const response = await axios.get('/brands');
    response.data.data.forEach(brand => {
      const option = document.createElement('option');
      option.value = brand.id;
      option.textContent = brand.name;
      brandSelect.appendChild(option);
    });
  } catch (error) {
    if (error.response && error.response.status === 500) {
      errorToast(error.response.data.error);
    } else {
      errorToast("Failed to load brands. Please try again later.");
    }
  }

  // 🔹 Load countries
  try {
    const response = await axios.get('/countries');
    response.data.data.forEach(country => {
      const option = document.createElement('option');
      option.value = country.id;
      option.textContent = country.name;
      countrySelect.appendChild(option);
    });
  } catch (error) {
    if (error.response && error.response.status === 500) {
      errorToast(error.response.data.error);
    } else {
      errorToast("Failed to load countries. Please try again later.");
    }
  }

  countrySelect.addEventListener('change', async function() {
    const selectedCountryId = this.value;
    await loadCounties(selectedCountryId);
  });

  async function loadCounties(countryId, preselectId = null) {
    countySelect.innerHTML = '<option value="" disabled selected>Select County</option>';
    citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';

    try {
      const response = await axios.get(`/counties/${countryId}`);
      response.data.data.forEach(county => {
        const option = document.createElement('option');
        option.value = county.id;
        option.textContent = county.name;
        countySelect.appendChild(option);
      });

      // ✅ Auto-select county if profile has it
      if (preselectId) {
        countySelect.value = preselectId;
        await loadCities(preselectId, profileCityId);
      }

    } catch (error) {
      if (error.response && error.response.status === 500) {
        errorToast(error.response.data.error);
      } else {
        errorToast("Failed to load counties. Please try again later.");
      }
    }
  }

  countySelect.addEventListener('change', async function() {
    const selectedCountyId = this.value;
    await loadCities(selectedCountyId);
  });

  async function loadCities(countyId, preselectId = null) {
    citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';

    try {
      const response = await axios.get(`/cities/${countyId}`);
      response.data.data.forEach(city => {
        const option = document.createElement('option');
        option.value = city.id;
        option.textContent = city.name;
        citySelect.appendChild(option);
      });

      // ✅ Auto-select city if profile has it
      if (preselectId) {
        citySelect.value = preselectId;
      }

    } catch (error) {
      if (error.response && error.response.status === 500) {
        errorToast(error.response.data.error);
      } else {
        errorToast("Failed to load cities. Please try again later.");
      }
    }
  }

  // 🔹 Load profile data and prefill form
  try {
    const profileRes = await axios.get('/client/profile/info');
    if (profileRes.data.status === 'success') {
      const user = profileRes.data.data;
      //console.log('-----',user);

      if (user.address1) document.getElementById('address1').value = user.address1;
      if (user.address2) document.getElementById('address2').value = user.address2;
      if (user.zip_code) document.getElementById('zip-code').value = user.zip_code;

      if (user.country_id) {
        countrySelect.value = user.country_id;
        profileCityId = user.city_id; // 🔹 store locally
        await loadCounties(user.country_id, user.county_id)
      }
    }
  } catch (error) {
    console.error("Failed to load profile info", error);
  }

  quill = new Quill('#snow-editor', {
    theme: 'snow',
    modules: {
      toolbar: '#toolbar'
    }
  });

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

function multiImageUrl(input) {
  $('#multiImage').empty();

  if (input.files) {
    Array.from(input.files).forEach(file => {
      let reader = new FileReader();
      reader.onload = function (e) {
        $('#multiImage').append(`
          <img src="${e.target.result}" class="mt-1" style="width: 150px; height: 100px; margin-right: 5px;">
        `);
      };
      reader.readAsDataURL(file);
    });
  }
}

function resetCreateForm() {
  document.getElementById('save-form').reset();
  $('#mainImage').attr('src', '');
  $('#multiImage').empty();
  quill.setContents([]);
  $('#accept_tnc').prop('checked', false);
}

async function Save() {
  let isValid = true;
  document.querySelectorAll(".error-message").forEach(span => span.innerText = '');

  let name = document.getElementById('name').value.trim();
  let weight = document.getElementById('weight').value.trim();
  let category_id = document.getElementById('categorySelect').value;
  let brand_id = document.getElementById('brandSelect').value;
  let price = document.getElementById('price').value.trim();
  let discount_price = document.getElementById('discountPrice').value.trim();
  let qty = document.getElementById('qty').value.trim();
  let address1 = document.getElementById('address1').value.trim();
  let address2 = document.getElementById('address2').value.trim();
  let country_id = document.getElementById('countrySelect').value;
  let county_id = document.getElementById('countySelect').value;
  let city_id = document.getElementById('citySelect').value;
  let zip_code = document.getElementById('zip-code').value.trim();
  let expire_date = document.getElementById('expire_date').value;
  let collection_date = document.getElementById('collection_date').value;
  let start_collection_time = document.getElementById('start_collection_time').value;
  let end_collection_time = document.getElementById('end_collection_time').value;
  let description = quill.root.innerHTML.trim();
  let image = document.getElementById('image').files[0];
  let multiImages = document.getElementById('multi_image').files;
  let accept_tnc = document.getElementById('accept_tnc').checked ? 1 : 0;
  let hasVariants = document.getElementById('hasVariants').checked ? 1 : 0;
  let hasBrand = document.getElementById('hasBrand').checked ? 1 : 0;
  let hasDiscountPrice = document.getElementById('hasDiscountPrice').checked ? 1 : 0;
  let isFree = document.getElementById('isFree').checked ? 1 : 0;

  if (!name) {
    document.getElementById('name-error').innerText = 'Product name is required!';
    isValid = false;
  }
  if (!weight) {
    document.getElementById('weight-error').innerText = 'Product weight is required!';
    isValid = false;
  }
  if (!category_id) {
    document.getElementById('category-error').innerText = 'Category is required!';
    isValid = false;
  }
  if (!address1) {
    document.getElementById('address1-error').innerText = 'Address1 is required!';
    isValid = false;
  }
  if (!country_id) {
    document.getElementById('country-error').innerText = 'Country is required!';
    isValid = false;
  }
  if (!county_id) {
    document.getElementById('county-error').innerText = 'County is required!';
    isValid = false;
  }
  if (!city_id) {
    document.getElementById('city-error').innerText = 'City is required!';
    isValid = false;
  }
  if (!zip_code) {
    document.getElementById('zip_code-error').innerText = 'Zip code is required!';
    isValid = false;
  }
  if (!expire_date) {
    document.getElementById('expire_date-error').innerText = 'Expire date is required!';
    isValid = false;
  }
  if (!collection_date) {
    document.getElementById('collection_date-error').innerText = 'Collection date is required!';
    isValid = false;
  }
  if (!start_collection_time) {
    document.getElementById('start_collection_time-error').innerText = 'Start collection time is required!';
    isValid = false;
  }
  if (!end_collection_time) {
    document.getElementById('end_collection_time-error').innerText = 'End collection time is required!';
    isValid = false;
  }
  if (!quill.getText().trim()) {
    document.getElementById('description-error').innerText = 'Description is required!';
    isValid = false;
  } else if (quill.getText().trim().length < 10) {
    document.getElementById('description-error').innerText = 'Description must be at least 10 characters!';
    isValid = false;
  }
  if (!image) {
    document.getElementById('image-error').innerText = 'Product image is required!';
    isValid = false;
  }
  if (!multiImages.length) {
    document.getElementById('multi_image-error').innerText = 'Multiple images are required!';
    isValid = false;
  }
  if (!accept_tnc) {
    document.getElementById('accept_tnc-error').innerText = 'You must accept the terms and conditions!';
    isValid = false;
  }

  if (hasBrand) {
    if (!brand_id) {
      document.getElementById('brand-error').innerText = 'Please select a brand!';
      isValid = false;
    }
  }

  if (!isFree) {
    let priceValue = parseFloat(price);
    if (isNaN(priceValue)) {
      document.getElementById('price-error').innerText = 'Price must be a valid number!';
      isValid = false;
    } else if (priceValue <= 0) {
      document.getElementById('price-error').innerText = 'Price must be greater than 0!';
      isValid = false;
    }
  }

  if (!isFree && hasDiscountPrice) {
    let discountPriceValue = parseFloat(discount_price);
    let priceValue = parseFloat(price);
    if (isNaN(discountPriceValue)) {
      document.getElementById('discount_price-error').innerText = 'Discount price must be a valid number!';
      isValid = false;
    } else if (discountPriceValue <= 0) {
      document.getElementById('discount_price-error').innerText = 'Discount price must be greater than 0!';
      isValid = false;
    } else if (discountPriceValue >= priceValue) {
      document.getElementById('discount_price-error').innerText = 'Discount price must be less than original price!';
      isValid = false;
    }
  }

  if (hasVariants) {
    let variantRows = document.querySelectorAll(".variant-row");
    variantRows.forEach((row, index) => {
      let colorInput = row.querySelector('input[name="color[]"]').value.trim();
      let sizeInput = row.querySelector('input[name="size[]"]').value.trim();
      let qtyInput = row.querySelector('input[name="qty2[]"]').value.trim();

      let colorError = row.querySelector('#color-error');
      let sizeError = row.querySelector('#size-error');
      let qtyError = row.querySelector('#qty2-error');

      if (!colorInput) {
        colorError.innerText = 'Color is required!';
        isValid = false;
      }
      if (!sizeInput) {
        sizeError.innerText = 'Size is required!';
        isValid = false;
      }
      if (!qtyInput || parseInt(qtyInput) <= 0) {
        qtyError.innerText = 'Quantity must be greater than 0!';
        isValid = false;
      }
    });
  } else {
    if (!qty || parseInt(qty) <= 0) {
      document.getElementById('qty-error').innerText = 'Quantity must be greater than 0!';
      isValid = false;
    }
  }

  if (!isValid) return;

  let formData = new FormData();
  formData.append('name', name);
  formData.append('weight', weight);
  formData.append('category_id', category_id);
  formData.append('address1', address1);
  formData.append('address2', address2);
  formData.append('country_id', country_id);
  formData.append('county_id', county_id);
  formData.append('city_id', city_id);
  formData.append('zip_code', zip_code);
  formData.append('expire_date', expire_date);
  formData.append('collection_date', collection_date);
  formData.append('start_collection_time', start_collection_time);
  formData.append('end_collection_time', end_collection_time);
  formData.append('description', description);
  formData.append('accept_tnc', accept_tnc);
  formData.append('has_variants', hasVariants);
  formData.append('has_brand', hasBrand);
  formData.append('has_discount_price', hasDiscountPrice);
  formData.append('is_free', isFree);
  formData.append('image', image);

  if (hasBrand && brand_id) {
    formData.append('brand_id', brand_id);
  }

  if (!isFree && price) {
    formData.append('price', price);
  }

  if (!isFree && hasDiscountPrice) {
    formData.append('discount_price', discount_price);
  }

  Array.from(multiImages).forEach((file, index) => {
    formData.append(`multi_images[${index}]`, file);
  });

  if (hasVariants) {
    let variantRows = document.querySelectorAll(".variant-row");
    variantRows.forEach((row, index) => {
      let colorInput = row.querySelector('input[name="color[]"]').value.trim();
      let sizeInput = row.querySelector('input[name="size[]"]').value.trim();
      let qtyInput = row.querySelector('input[name="qty2[]"]').value.trim();

      formData.append(`variants[${index}][color]`, colorInput);
      formData.append(`variants[${index}][size]`, sizeInput);
      formData.append(`variants[${index}][qty2]`, qtyInput);
    });
  } else {
    formData.append('current_stock', qty);
  }
  //console.log('---------------',formData);

  try {
    let res = await axios.post("/client/store/product", formData, {
      headers: { 'content-type': 'multipart/form-data' },
    });

    if (res.status === 201 && res.data.status === 'success') {
      successToast(res.data.message || 'Request successful');
      window.location.href = '/client/product-list';
      resetCreateForm();
    } else {
      errorToast(res.data.message || "Request failed");
    }
  } catch (error) {
      handleError(error);
  } finally {
      hideLoader(); 
  }
}

function handleError(error) {
  document.querySelectorAll(".error-text").forEach(el => el.innerText = '');

  let message = 'An unexpected error occurred';

  if (error.response) {
    const status = error.response.status;
    const serverMessage = error.response.data?.message;

    switch (status) {
      case 422:
        if (error.response.data.errors) {
          Object.entries(error.response.data.errors).forEach(([field, messages]) => {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
              errorElement.innerText = messages[0];
            }
          });
        }
        message = serverMessage || 'Validation failed';
        break;
      case 500:
        message = serverMessage || 'Server error. Please try again later.';
        break;
      default:
        message = serverMessage || message;
    }
  } else if (error.request) {
    message = 'No response received from the server.';
  } else {
    message = error.message || message;
  }

  errorToast(message);
}

document.getElementById("hasVariants").addEventListener("change", function() {
  const variantSection = document.getElementById("variantSection");
  const qtyContainer = document.getElementById("qtyContainer");
  if (this.checked) {
    variantSection.style.display = "block";
    qtyContainer.style.display = 'none';
  } else {
    variantSection.style.display = "none";
    qtyContainer.style.display = 'block';
  }
});

document.addEventListener("click", function (event) {
  if (event.target.classList.contains("add-variant")) {
    const originalRow = document.querySelector(".variant-row");
    const newRow = originalRow.cloneNode(true);
    
    // Clear input values and errors
    newRow.querySelectorAll("input").forEach(input => input.value = "");
    newRow.querySelectorAll(".error-message").forEach(span => span.textContent = "");
    
    // Change Add button to Remove button in cloned rows
    const addBtn = newRow.querySelector(".add-variant");
    const removeBtn = newRow.querySelector(".remove-variant");
    addBtn.style.display = "none";
    removeBtn.style.display = "inline-block";

    document.getElementById("variantSection").appendChild(newRow);
  }

  // Handle Remove button
  if (event.target.classList.contains("remove-variant")) {
    const allRows = document.querySelectorAll(".variant-row");
    // Prevent removing the last remaining row
    if (allRows.length > 1) {
      event.target.closest(".variant-row").remove();
    }
  }
});

document.getElementById("isFree").addEventListener("change", function() {
  const priceContainer = document.getElementById("priceContainer");
  const discountContainer = document.getElementById("discountContainer");
  const discountCheckboxContainer = document.getElementById("discountCheckboxContainer");
  if (this.checked) {
    priceContainer.style.display = "none";
    discountContainer.style.display = "none";
    discountCheckboxContainer.style.display = "none";
  } else {
    priceContainer.style.display = "block";
    discountContainer.style.display = "block";
    discountCheckboxContainer.style.display = "block";
  }
});

document.getElementById("hasDiscountPrice").addEventListener("change", function() {
  const discountContainer = document.getElementById("discountContainer");
  const freeCheckboxContainer = document.getElementById("isFree");
  if (this.checked) {
    discountContainer.style.display = "block";
  } else {
    discountContainer.style.display = "none";
  }
});

document.getElementById("hasBrand").addEventListener("change", function() {
  const brandContainer = document.getElementById("brandContainer");
  const categoryContainer = document.getElementById("categoryContainer");
  if (this.checked) {
    brandContainer.style.display = "block";

  } else {
    brandContainer.style.display = "none";

  }
});
</script>

