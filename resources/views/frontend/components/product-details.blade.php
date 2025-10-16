<section class="section-py bg-body first-section-pt">
  <div class="container"> 
    <form id="save-form"> 
      <div class="card g-3 mt-5">
        <div class="card-body row g-3">
          <hr>
          <div class="col-lg-7">
            <div class="card academy-content shadow-none border">
              <div class="p-2">
                <div class="cursor-pointer">
                  <div class="row">
                    <div class="col-12">
                      <div id="gallery-wrapper">
                        <!-- Main gallery -->
                        <div class="gallery-main">
                          <img id="mainImage" src="/upload/no_image.jpg" alt="Main Image" style="width: 100%; height: auto;" />
                        </div>
                        <div class="gallery-thumbs mt-3" id="galleryThumbImages" style="display: flex; gap: 10px;">

                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <button onclick="shareToFacebook(event)" class="btn btn-primary mt-3 btn-next">Share with Facebook</button>
            <!-- <button onclick="shareFacebookURL(event)" class="btn btn-success mt-3 btn-next">Share Facebook URL</button> -->
           <!--  <button onclick="shareInstagramURL(event)" class="btn btn-success mt-3 btn-next">Share Instagram URL</button> -->
            <button onclick="openEmailModal(event)" class="btn btn-primary mt-3 btn-next">Share With Email</button>
          </div>
          <div class="col-lg-5">
            <div class="border rounded p-3 mb-3">
              <div class="bg-lighter rounded p-3">
                <input type="hidden" id="hasVariants" name="has_variants" value="0">

                <h6>Product Name: <span id="product-name" class="text-success"></span></h6><hr class="my-3" />
                <h6>Category Name: <span id="category-name" class="text-success"></span></h6><hr class="my-3" />
                <h6>Product Weight: <span id="product-weight" class="text-success"></span></h6><hr class="my-3" />
                <h6 class="d-inline">Price:</h6><span id="product-price" class="ms-2"></span><hr class="my-3" />
                <h6>Provided By: <span id="client-name" class="text-success"></span></h6><hr class="my-3" />
                <h6>Description:</h6> <span id="product-description"></span><hr class="my-3" />
                <h6>Available Stock: <span id="current-stock" class="text-success"></span></h6><hr class="my-3" />
                

                <div id="food-item-fields" style="display: none;">
                  <h6>Expire Date: <span id="expire-date"></span></h6><hr class="my-4" />
                  <h6>Collection Location: <span id="address"></span></h6><hr class="my-4" />
                  <h6>Collection Date: <span id="collection-date"></span></h6><hr class="my-4" />
                  <h6>Collection Time: <span id="end-collection-time"></span></h6><hr class="my-4" />
                </div>


              <!-- Variant Selection (Initially Hidden) -->
              <div id="variant-section" style="display: none;">
                <div class="row">
                    <div class="col-md-6" id="colorContainer">
                        <div class="form-floating form-floating-outline">
                            <select id="colorSelect" class="form-select w-100" aria-label="Select Color">
                            </select>
                            <label for="colorSelect">Select Color<span class="text-danger">*</span></label>
                        </div>
                        <span class="error-message text-danger" id="color-error"></span>
                    </div>

                    <div class="col-md-6" id="sizeContainer">
                        <div class="form-floating form-floating-outline">
                            <select id="sizeSelect" class="form-select w-100" aria-label="Select Size">
                            </select>
                            <label for="sizeSelect">Select Size<span class="text-danger">*</span></label>
                        </div>
                        <span class="error-message text-danger" id="size-error"></span>
                    </div>
                </div><hr class="my-4" />
              </div>
                <h6>Select Qty: 
                  <input type="number" id="qty" class="form-control" value="1" min="1">
                  <span class="error-message text-danger" id="qty-error"></span>
                </h6><hr class="my-4" />
              </div>
            </div>

<div class="d-grid">
    <button type="button" id="add-to-cart-btn" onclick="addToCart()" class="btn btn-primary mt-2">
        <span class="mdi mdi-cart-outline"></span> ADD TO CART
    </button>
    <div id="status-message" class="my-2"></div>
    <!-- Wrapped conditional elements -->
    <div id="post-cart-options" style="display: none;">
        <div class="text-center my-2">OR</div>
        
        <a href="/" class="btn btn-outline-primary mt-2 w-100">
            <span class="mdi mdi-plus-circle-outline"></span> ADD MORE ITEMS
        </a>

        <div class="text-center my-2">OR</div>
        
        <a href="http://127.0.0.1:8000/user/my-cart" class="btn btn-outline-success mt-2 w-100">
            <span class="mdi mdi-cart-arrow-right"></span> CONTINUE SHOPPING
        </a>
    </div>
</div>
          </div>
        </div>
      </div>
    </form>
  </div>


<script>
let url = window.location.pathname;
let segments = url.split('/');
let id = segments[segments.length - 1];

function openEmailModal(event) {
  event.preventDefault();  
  document.getElementById('itemID').value = id;
  let emailModal = new bootstrap.Modal(document.getElementById('email-modal'));
  emailModal.show();
}

async function getProductDetails() {
  try {
    showLoader();
    if (isTokenValid()) {
      await getProfile();
    }
    let res = await axios.get("/get/product/details/" + id);
    if (res.status === 200 && res.data.status === 'success') {
      let data = res.data.data;
      //console.log('--------------',data);
      let mainImageElement = document.getElementById('mainImage');
      let mainImage = '{{ asset("/upload/product/large/") }}/' + data.image;
      mainImageElement.src = mainImage;

      let galleryThumbs = document.getElementById('galleryThumbImages');
      let thumbHTML = '';

      thumbHTML += `<img src="${mainImage}" alt="Thumb Image" class="thumb-img" style="cursor: pointer; width: 120px; height: auto;" onclick="updateMainImage('${mainImage}')">`;

      if (data.product_images && data.product_images.length > 0) {
        data.product_images.forEach(productImage => {
          let thumbImage = '{{ asset("/upload/product/multiple/") }}/' + productImage.image;
          thumbHTML += `<img src="${thumbImage}" alt="Thumb Image" class="thumb-img" style="cursor: pointer; width: 120px; height: auto;" onclick="updateMainImage('${thumbImage}')">`;
        });
      }

      galleryThumbs.innerHTML = thumbHTML;

      let firstName = data['client']['firstName'];
      let lastName = data['client']['lastName'];
      let fullName = lastName ? `${firstName} ${lastName}` : firstName;

      const priceElement = document.getElementById('product-price');
      if (data.is_free === 1) {
          priceElement.innerHTML = '<span class="text-success fw-semibold">Free</span>';
      } else {
          let priceHTML = `<span class="text-primary">£${parseFloat(data.price).toFixed(2)}</span>`;
          if (data.has_discount_price === 1) {
              priceHTML = `
                  <span class="text-muted text-decoration-line-through me-2">£${parseFloat(data.price).toFixed(2)}</span>
                  <span class="text-danger fw-bold">£${parseFloat(data.discount_price).toFixed(2)}</span>
              `;
          }
          priceElement.innerHTML = priceHTML;
      }

      if (data['category']['name'] === 'Food') {
        document.getElementById('food-item-fields').style.display = 'block';
      }

      if (data['category']['name'] !== 'Food' && data.variants && data.variants.length > 0) {
          document.getElementById('variant-section').style.display = 'block';
          populateVariants(data.variants);
      } 

      document.getElementById('hasVariants').value = data['has_variants'];
      document.getElementById('product-name').innerText = data['name'];
      document.getElementById('category-name').innerText = data['category']['name'];
      document.getElementById('product-weight').innerText = data['weight'] + ' Kg';
      document.getElementById('current-stock').innerHTML = data['current_stock'];
      document.getElementById('product-description').innerHTML = data['description'];
      document.getElementById('expire-date').innerText = data['expire_date'];
      document.getElementById('address').innerText = data['address1'];
      document.getElementById('collection-date').innerText = data['collection_date'];
      document.getElementById('end-collection-time').innerText = data['end_collection_time'];
      document.getElementById('client-name').innerText = fullName;

    } else {
        errorToast(res.data.message || "Failed to fetch product details infoformation.");
    }

  } catch (error) {
      handleError(error);
  } finally {
      hideLoader();
  }
}

function populateVariants(variants) {
  const colorSelect = document.getElementById('colorSelect');
  const sizeSelect = document.getElementById('sizeSelect');
  let colorSizeMap = {};
  let variantMap = {}; 
  let productTotalStock = document.getElementById('current-stock').innerHTML; 

  variants.forEach(variant => {
    if (variant.current_stock <= 0) return; 

    if (!colorSizeMap[variant.color]) {
      colorSizeMap[variant.color] = [];
    }
    colorSizeMap[variant.color].push({
      id: variant.id,
      size: variant.size,
      qty: variant.current_stock
    });

    variantMap[variant.id] = variant.current_stock;
  });


  colorSelect.innerHTML = '<option value="">Select Color</option>';
  Object.keys(colorSizeMap).forEach(color => {
    colorSelect.add(new Option(color, color));
  });

  colorSelect.addEventListener('change', function() {
    const selectedColor = this.value;
    sizeSelect.innerHTML = '<option value="">Select Size</option>';
    document.getElementById('current-stock').innerHTML = productTotalStock; 
    
    if (selectedColor && colorSizeMap[selectedColor]) {
      colorSizeMap[selectedColor].forEach(sizeInfo => {
        if (sizeInfo.qty > 0) {
          const option = new Option(`${sizeInfo.size}`, sizeInfo.id);
          sizeSelect.add(option);
        }
      });
    }
    
    checkAddToCartVisibility();
    validateQty();
  });


  sizeSelect.addEventListener('change', function() {
    const variantId = this.value;
    if (variantId && variantMap[variantId]) {
        document.getElementById('current-stock').textContent = variantMap[variantId];
    } else {
        document.getElementById('current-stock').textContent = productTotalStock;
    }
    validateQty();
});

  document.getElementById('qty').addEventListener('input', validateQty);
}

function validateQty() {
    const qtyInput = document.getElementById('qty');
    const qtyError = document.getElementById('qty-error');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    
    const availableStock = parseInt(document.getElementById('current-stock').textContent) || 0;
    const enteredQty = parseInt(qtyInput.value) || 0;

    if (enteredQty < 1) {
        qtyError.textContent = 'Quantity must be at least 1';
        addToCartBtn.disabled = true;
    } else if (enteredQty > availableStock) {
        qtyError.textContent = `Quantity must be less than or equal to ${availableStock}`;
        addToCartBtn.disabled = true;
    } else {
        qtyError.textContent = '';
        addToCartBtn.disabled = false;
    }
}

document.getElementById('qty').addEventListener('input', validateQty);
document.getElementById('sizeSelect').addEventListener('change', validateQty);
document.getElementById('colorSelect').addEventListener('change', validateQty);

async function addToCart() {
    const statusMessage = document.getElementById('status-message');
    statusMessage.innerHTML = ''; // Clear previous messages

    // Check authentication first
    if (!isTokenValid()) {
        statusMessage.innerHTML = `
            <div class="text-danger d-flex justify-content-center">
                Please login to add items to cart
            </div>
        `;
        return;
    }

    showLoader();
    try {
        // Get fresh profile data
        const profileRes = await axios.get("/user/profile/info");
        if (profileRes.status === 200 && profileRes.data.status === 'success') {
            const customerData = profileRes.data.data;
            const customerStatus = customerData.status;

            // Check document submission status
            if (customerStatus === 0) {
                statusMessage.innerHTML = `
                  <div class="text-danger d-flex justify-content-center"><span class="text-danger me-2">To order an item, you must submit the necessary documents. <a href="/user/document" style="color: green; text-decoration: none;">Upload Your Document Here</a></span></div>
                `;
                hideLoader();
                return;
            }

            // Validate quantity inputs
            validateQty();
            if (document.getElementById('add-to-cart-btn').disabled) {
                hideLoader();
                return;
            }

            // Prepare cart data
            const qty = document.getElementById('qty').value;
            const hasVariants = document.getElementById('hasVariants').value;
            let variantId = null;

            if (hasVariants === '1') {
                variantId = document.getElementById('sizeSelect').value;
                if (!variantId) {
                    errorToast("Please select a variant.");
                    return;
                }
            }

            const formData = new FormData();
            formData.append('product_id', id);
            formData.append('quantity', qty);
            if (variantId) {
                formData.append('variant_id', variantId);
            }

            const res = await axios.post('/user/cart/add', formData);
            if (res.status === 200 && res.data.status === 'success') {
                successToast(res.data.message || 'Item added to cart');
                updateCartCount();
                document.getElementById('post-cart-options').style.display = 'block';
            } else {
                errorToast(res.data.message || 'Failed to add to cart');
            }
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function checkAddToCartVisibility() {
  const colorSelected = document.getElementById('colorSelect').value !== '';
  const sizeSelected = document.getElementById('sizeSelect').value !== '';
  document.getElementById('add-to-cart-btn').style.display = 
    (colorSelected && sizeSelected) ? 'block' : 'none';
}

function updateMainImage(imageSrc) {
  let mainImageElement = document.getElementById('mainImage');
  mainImageElement.src = imageSrc;
}

async function getProfile() {
  showLoader();
  try {
    let res = await axios.get("/user/profile/info");

    if (res.status === 200 && res.data.status === 'success') {
      let customerData = res.data.data;
      const customerStatus = customerData.status;
      
      // Get reference to post-cart options
      const postCartOptions = document.getElementById('post-cart-options');
      
      if (customerStatus === 0) {
        let cardBody = document.querySelector('.card-body.row.g-3');
        //let dGridContainer = document.querySelector('.d-grid');
        let requestButton = document.querySelector('.item-request-btn');
        const addToCartButton = document.getElementById('add-to-cart-btn');

        // Hide post-cart options
        if (postCartOptions) {
          postCartOptions.style.display = 'none';
        }

        if (cardBody) {
          let message = `
          <h5 class="text-danger d-flex justify-content-center mt-5">
          <span class="text-danger">
          To order an item, you must submit the necessary documents.
          </span> 
          <a href="/user/document" style="color: green; text-decoration: none;">
          Upload Your Document Here
          </a>
          </h5>
          `;
          cardBody.insertAdjacentHTML('beforebegin', message);
        }

        // if (dGridContainer) {
        //   let message = `
        //   <p class="text-danger d-flex justify-content-center">
        //   <span class="text-danger">
        //   To order an item, you must submit the necessary documents.

        //   <a href="/user/document" style="color: green; text-decoration: none;">
        //   Upload Your Document Here
        //   </a>
        //   </p>
        //   `;
        //   dGridContainer.insertAdjacentHTML('beforebegin', message);
        // }
      } else {
        // Show post-cart options if status is valid
        if (postCartOptions) {
          postCartOptions.style.display = 'block';
        }
      }

    } else {
      errorToast(res.data.message || 'Failed to fetch profile infoformation.');
    }
  } catch (error) {
      handleError(error);
  } finally {
      hideLoader();
  }
}

async function shareToFacebook(event) {
  event.preventDefault();
  try {
    let res = await axios.post(`/user/facebook/share/${id}`);

    if (res.status === 200) {
      successToast(res.data.message || 'Shared successfully to Facebook.');
    } else {
      errorToast(res.data.message || "Failed to share to Facebook.");
    }
  } catch (error) {
    if (error.response) {
      if (error.response.status === 401) {
        window.location.href = '/user/login';
      } else if (error.response.status === 500) {
        errorToast(error.response.data.error || "An internal server error occurred while sharing to Facebook.");
      } else if (error.response.status === 400) {
        errorToast(error.response.data.message || "Bad request.");
      } else {
        errorToast("Failed to share to Facebook!");
      }
    } else {
      errorToast("Request failed!");
    }
    console.error(error);
  }
}

async function shareFacebookURL(event) {
  event.preventDefault();
  try {
    const response = await axios.post(`/user/facebook/url/share/${id}`);

    if (response.status === 200) {
      const shareUrl = response.data.facebook_share_url;
      window.open(shareUrl, '_blank', 'width=600,height=400');
      successToast(response.data.message || 'Sharing to Facebook...');
    } else {
      errorToast(response.data.message || 'Failed to generate share URL.');
    }
  } catch (error) {
    if (error.response) {
      if (error.response.status === 401) {
        window.location.href = '/user/login';
      } else if (error.response.status === 500) {
        errorToast(error.response.data.error || "An internal server error occurred while sharing to Facebook.");
      } else if (error.response.status === 400) {
        errorToast(error.response.data.message || "Bad request.");
      } else {
        errorToast("Failed to share to Facebook!");
      }
    } else {
      errorToast("Request failed!");
    }
    console.error(error);
  }
}

function handleError(error) {
    let message = 'An unexpected error occurred';

    if (error.response) {
        const status = error.response.status;
        const serverMessage = error.response.data?.message;

        switch (status) {
            case 400:
                message = serverMessage || 'Unauthorized! Please login.';
                break;
            case 403:
                message = serverMessage || 'You cannot order until the client removes the ban';
                break;
            case 404:
                message = serverMessage || 'Data not found';
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
</script>

<style>
  .gallery-main {
    width: 80%;
    height: 400px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: #f0f0f0;
  }

  .gallery-main img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
  }

  .gallery-thumbs {
    display: flex;
    gap: 10px;
    justify-content: center;
    align-items: center;
  }

  .gallery-thumbs img {
    width: 100px; 
    min-height: 150px; 
    object-fit: cover;
    cursor: pointer;
    border: 1px solid #ddd;
  }
</style>

