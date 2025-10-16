<div class="modal fade" id="reply-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel3">Appeal against your complaint</h4>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="complainID">
            <div class="col-md-12 my-4">
              <div class="card">
                <div class="card-body">
                   @include('frontend.components.editor')
                  <div id="snow-editor"></div>
                  <span class="error-message text-danger" id="reply_message-error"></span>
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
        <button type="button" class="btn btn-primary" id="send-reply-btn">Send Appeal</button>
      </div>
    </div>
  </div>
</div>

<script>
  var quill;
  let uploadedImageUrl = null; 
  let uploadedImageIndex = null; 

  document.addEventListener("DOMContentLoaded", function() {
    quill = new Quill('#snow-editor', {
      theme: 'snow',
      modules: {
        toolbar: {
          container: '#toolbar',
          handlers: {
            image: imageHandler 
          }
        }
      }
    });

    document.getElementById('send-reply-btn').addEventListener('click', function(event) {
      event.preventDefault();
      sendReply();
    });

    $('#reply-modal').on('hidden.bs.modal', async function () {
      quill.setContents([]); 

      if (uploadedImageUrl) {
        try {
          await axios.post('/user/delete-customer-complain-editor-image', { image_url: uploadedImageUrl });
          uploadedImageUrl = null;  
          uploadedImageIndex = null;
        } catch (error) {
          console.error('Failed to delete image on modal close:', error);
        }
      }
    });

});




  function imageHandler() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async () => {
      const file = input.files[0];
      if (file) {
        const formData = new FormData();
        formData.append('image', file);

        try {
          if (uploadedImageUrl) {
            await axios.post('/user/delete-customer-complain-editor-image', { image_url: uploadedImageUrl });

            if (uploadedImageIndex !== null) {
              quill.deleteText(uploadedImageIndex, 1); 
            }
          }

          const res = await axios.post('/user/upload-customer-complain-editor-image', formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          });

          const imageUrl = res.data.image_url;
          uploadedImageUrl = imageUrl; 

          const range = quill.getSelection();
          if (range) {
            uploadedImageIndex = range.index; 
            quill.insertEmbed(range.index, 'image', imageUrl);
          } else {
            quill.focus();
            uploadedImageIndex = quill.getSelection().index; 
            quill.insertEmbed(uploadedImageIndex, 'image', imageUrl);
          }

        } catch (error) {
          console.error('Image upload failed:', error);
          errorToast('Failed to upload image');
        }
      }
    };
  }


  async function sendReply() {
    let complainID = document.getElementById('complainID').value;

    document.getElementById('reply_message-error').textContent = '';

    let htmlMessage = quill.root.innerHTML.trim();
    let textMessage = quill.getText().trim();

    if (textMessage === '') {
      document.getElementById('reply_message-error').textContent = 'Message is required';
    } 
    else if (textMessage.length < 10) {
      document.getElementById('reply_message-error').textContent = 'Message must be at least 10 characters long';
    }
    else {
      let formData = new FormData();
      formData.append('reply_message', htmlMessage);
      formData.append('complain_id', complainID);

      const config = {
        headers: {
          'content-type': 'multipart/form-data'
        }
      };

      showLoader();

      try {
        let res = await axios.post("/user/store-customer-complain-appeal-info", formData, config);
        if (res.status === 201) {
          successToast(res.data.message || 'Complain feedback given successfully');
          uploadedImageUrl = null;
          uploadedImageIndex = null;
          window.location.href = '/user/customer-complain-list';
          document.getElementById('save-form').reset();
          $('#reply-modal').modal('hide');
        } else {
          errorToast(res.data.message || "Request failed");
        }
      } catch (error) {
        if (error.response && error.response.status === 422) {
          let errors = error.response.data.errors;
          for (let key in errors) {
            if (errors.hasOwnProperty(key)) {
              document.getElementById('reply_message-error').innerText = errors[key][0];
            }
          }
        } 
        else if (error.response && error.response.status === 403) {
          errorToast(error.response.data.message);
        } 
        else if (error.response && error.response.status === 500) {
          errorToast(error.response.data.error);
        } 
        else if (error.response && error.response.status === 404) {
          errorToast(error.response.data.message || "Complain not found!");  
        } 
        else {
          errorToast("Request failed!");
        }
      } finally {
        hideLoader();
      }
    }
  }


  window.addEventListener('beforeunload', async (event) => {
    if (uploadedImageUrl) {
      try {
        await axios.post('/user/delete-customer-complain-editor-image', { image_url: uploadedImageUrl });
      } catch (error) {
        console.error('Failed to delete image on unload:', error);
      }
    }
  });
</script>
