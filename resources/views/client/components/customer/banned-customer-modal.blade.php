<div class="modal fade" id="banned-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel3">Ban Reason<span class="text-danger">*</span></h4>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="bannedCustomerID">
            <div class="col-md-12 my-4">
              <div class="card">
                <div class="card-body">
                   @include('client.components.editor2')
                  <div id="snow-editor2"></div>
                  <span class="error-message text-danger" id="message-error1"></span>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Close
        </button>
        <button type="button" class="btn btn-primary" id="banned-btn">Banned Customer</button>
      </div>
    </div>
  </div>
</div>

<script>
  var quill2;
  let uploadedBannedImageUrl = null; 
  let uploadedBannedImageIndex = null;
  
  document.addEventListener("DOMContentLoaded", function() {
    quill2 = new Quill('#snow-editor2', {
      theme: 'snow',
      modules: {
        toolbar: {
          container: '#toolbar2',
          handlers: {
            //image: imageHandler
            image: () => imageHandler2(quill2)  // Pass quill2 instance
          }
        }
      }
    });

    document.getElementById('banned-btn').addEventListener('click', function(event) {
      event.preventDefault();
      bannedCustomer();
    });

    $('#banned-modal').on('hidden.bs.modal', async function () {
      quill2.setContents([]); 

      if (uploadedBannedImageUrl) {
        try {
          await axios.post('/client/delete-banned-editor-image', { image_url: uploadedBannedImageUrl });
          uploadedBannedImageUrl = null;  
          uploadedBannedImageIndex = null;
        } catch (error) {
          console.error('Failed to delete image on modal close:', error);
        }
      }
    });

  });


  function imageHandler2(editor) {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async () => {
      const file = input.files[0];
      if (file) {
        const formData = new FormData();
        formData.append('image2', file);

        try {
          if (uploadedBannedImageUrl) {
            await axios.post('/client/delete-banned-editor-image', { image_url: uploadedBannedImageUrl });

            if (uploadedBannedImageIndex !== null) {
              quill2.deleteText(uploadedBannedImageIndex, 1); 
            }
          }

          const res = await axios.post('/client/upload-banned-editor-image', formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          });

          const imageUrl = res.data.image_url;
          uploadedBannedImageUrl = imageUrl; 

          const range = quill2.getSelection();
          if (range) {
            uploadedBannedImageIndex = range.index; 
            quill2.insertEmbed(range.index, 'image', imageUrl);
          } else {
            quill2.focus();
            uploadedBannedImageIndex = quill2.getSelection().index; 
            quill2.insertEmbed(uploadedBannedImageIndex, 'image', imageUrl);
          }

        } catch (error) {
          console.error('Image upload failed:', error);
          errorToast('Failed to upload image');
        }
      }
    };
  }


  async function bannedCustomer() {
    let htmlMessage = quill2.root.innerHTML.trim();
    let textMessage = quill2.getText().trim();
    let bannedCustomerID = document.getElementById('bannedCustomerID').value;

    document.getElementById('message-error1').innerText = '';

    if (textMessage === '') {
      document.getElementById('message-error1').innerText = 'Message is required';
    }         
    else if (textMessage.length < 10) {
      document.getElementById('message-error1').innerText = 'Message must be at least 10 characters long!';
    }else {
      let formData = new FormData();
      formData.append('message', htmlMessage);
      formData.append('customer_id', bannedCustomerID);

      const config = {
        headers: {
          'content-type': 'multipart/form-data'
        }
      };

      showLoader();

      try {
        let res = await axios.post("/client/store/ban/customer/info", formData, config);
        if (res.status === 201) {
          successToast(res.data.message || 'Customer banned successfully');
          uploadedBannedImageUrl = null;
          uploadedBannedImageIndex = null;
          window.location.href = '/client/customers';
          document.getElementById('save-form').reset();
          $('#banned-modal').modal('hide');
        } else {
          errorToast(res.data.message || "Request failed");
        }
      } catch (error) {
        handleError(error);
      } finally {
        hideLoader();
      }
    }
  }

  function handleError(error) {
    let message = 'Request failed!';
    let status = null;

    if (error.response) {
        status = error.response.status;
        const responseData = error.response.data;

        switch (status) {
            case 422:
                let errors = responseData.errors;
                for (let key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        document.getElementById('message-error1').innerText = errors[key][0];
                        return;
                    }
                }
                break;
            case 403:
                message = responseData.message;
                break;
            case 500:
                message = responseData.error;
                break;
            case 404:
                message = responseData.message || 'Complain not found!';
                break;
            default:
                message = responseData.message || message;
        }
    } else if (error.request) {
        message = 'No response received from the server.';
    } else {
        message = error.message || message;
    }

    if (status !== 422) {
        errorToast(message);
    }
  }

  window.addEventListener('beforeunload', async (event) => {
    if (uploadedBannedImageUrl) {
      try {
        await axios.post('/client/delete-banned-editor-image', { image_url: uploadedBannedImageUrl });
      } catch (error) {
        console.error('Failed to delete image on unload:', error);
      }
    }
  });
</script>

