<div class="row">

  <div class="col-md-12">
    <div class="card mb-4">
        <div class="card-header header-elements">
            <span class="me-2"><h5>Update multiple product images</h5></span>
            <div class="card-header-elements ms-auto">
                <a href="{{ route('client.products') }}" type="button" class="btn btn-primary waves-effect waves-light">
                    <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To Product List
                </a>
            </div>
        </div>
      <div class="card-body demo-vertical-spacing demo-only-element">
        <form id="save-form">
            <div id="multi-images-container"></div>
        </form>
      </div>
    </div>
  </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {
    multiImgInfo();
});

async function multiImgInfo() {
    showLoader();
    try {
      let url = window.location.pathname;
      let segments = url.split('/');
      let id = segments[segments.length - 1];

      let res = await axios.get("/client/product/info/" + id);
      if (res.status === 200 && res.data.status === 'success') {
        if (res.data && res.data.data.product_images) {
          renderMultiImages(res.data.data.product_images);
        } else {
          errorToast(res.data.message || "Request failed");
        }
      } else {
          errorToast(res.data.message || "Failed to fetch multi image infoformation.");
      }

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function updateMultiImgUrl(input, index) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById(`updateMultiImg${index}`).setAttribute('src', e.target.result);
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function renderMultiImages(multiImages) {
  const container = document.getElementById('multi-images-container');
  container.innerHTML = ''; // Clear the container before rendering

  multiImages.forEach((image, index) => {
    const imgIndex = index + 1;
    const imgElement = `
      <div class="row mg-b-25">
        <div class="col-lg-12">
          <div class="row">
            <!-- Old image display -->
            <div class="col-lg-4">
              <div class="form-group">
                <label class="form-control-label">Old image:</label><br>
                <img style="width: 150px; height: 100px;" id="oldMultiImg${imgIndex}" src="{{asset('/upload/product/multiple/${image.image}')}}"/>
              </div>
            </div>

            <!-- New image upload input -->
            <div class="col-lg-8">
              <div class="row">
                <div class="col-lg-6">
                  <label class="form-control-label">Upload new image:</label>
                  <input type="file" id="imgUpdate${imgIndex}" class="form-control" onChange="updateMultiImgUrl(this, ${imgIndex})">
                  <span class="error-message text-danger" id="update_image-error${imgIndex}"></span>
                </div>
                <div class="col-lg-6">
                  <img src="{{ asset('/upload/no_image.jpg') }}" id="updateMultiImg${imgIndex}" class="mt-1" width="150" height="100">
                </div>
              </div>
            </div>

            <!-- Update button -->
            <div class="col-lg-4 mt-4">


        <button type="button" onclick="updateMultiImg(${image.id}, ${imgIndex})" class="btn btn-primary btn-lg">
          <i class="mdi mdi-check me-2"></i>Update
        </button>
            </div>
          </div>
        </div>
      </div><hr>
    `;

    container.insertAdjacentHTML('beforeend', imgElement);
  });
}

async function updateMultiImg(imageId, imgIndex) {
  let formData = new FormData();
  formData.append('id', imageId);
  const imgUpdateInput = document.getElementById(`imgUpdate${imgIndex}`);
  if (imgUpdateInput.files && imgUpdateInput.files[0]) {
    formData.append('image', imgUpdateInput.files[0]);
  } else {
    errorToast("Please select an image to update");
    return;
  }

  showLoader();
  const config = {
    headers: {
      'content-type': 'multipart/form-data'
    }
  };

  try {
    let res = await axios.post("/client/update-multi-image", formData, config);
    if (res.status === 200 && res.data.status === 'success') {
      successToast(res.data.message || 'Update Success');
      window.location.href = '/client/product-list';
      document.getElementById('save-form').reset();
    } else {
      errorToast(res.data.message || "Request failed");
    }
  } catch (error) {
      handleError(error);
  } finally {
      hideLoader();
  }
}

function handleError(error, imgIndex = null) {
    let message = 'An unexpected error occurred';

    if (error.response) {
        const status = error.response.status;
        const serverMessage = error.response.data?.message;

        switch (status) {
            case 404:
                message = serverMessage || 'Data not found.';
                break;
            case 422:
                if (imgIndex !== null && error.response.data.errors) {
                    const errorMessages = error.response.data.errors;
                    for (let field in errorMessages) {
                        if (errorMessages.hasOwnProperty(field)) {
                            const errorEl = document.getElementById(`update_${field}-error${imgIndex}`);
                            if (errorEl) {
                                errorEl.innerText = errorMessages[field][0];
                            }
                        }
                    }
                    return; // Already handled specific errors
                } else {
                    message = serverMessage || 'Validation error.';
                }
                break;
            case 500:
                message = error.response.data?.error || serverMessage || 'Server error. Please try again later.';
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


