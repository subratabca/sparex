@extends('frontend.components.dashboard.dashboard-master')

@section('dashboard-content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <h5 class="card-header pb-3 border-bottom mb-3">Reply your complaint</h5>
      <div class="card-body">
        <form id="complain-form">
          @include('frontend.components.editor')
          <div id="snow-editor"></div>
          <div id="message-error" class="text-danger mt-2"></div>
          <div class="mt-3">
            <button type="submit" class="btn btn-primary">Send Reply</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection


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

    const form = document.getElementById('complain-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        await Save();
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
                    await axios.post('/user/delete-editor-image', { image_url: uploadedImageUrl });

                    if (uploadedImageIndex !== null) {
                        quill.deleteText(uploadedImageIndex, 1); 
                    }
                }

                const res = await axios.post('/user/upload-editor-image', formData, {
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

async function Save() {
    let url = window.location.pathname;
    let segments = url.split('/');
    let complaint_id = segments[segments.length - 1];

    document.getElementById('message-error').textContent = '';
    let htmlMessage = quill.root.innerHTML.trim();
    let textMessage = quill.getText().trim();

    if (textMessage === '') {
        document.getElementById('message-error').textContent = 'Message is required';
    } 
    else if (textMessage.length < 10) {
        document.getElementById('message-error').textContent = 'Message must be at least 10 characters long';
    }
    else{
        let formData = new FormData();
        formData.append('complaint_id', complaint_id);
        formData.append('reply_message', htmlMessage);

        const config = {
            headers: {
                'content-type': 'multipart/form-data',
            },
        };
        showLoader();
        try {
            let res = await axios.post("/user/store/complaint/reply", formData, config);

            if (res.status === 201 && res.data.status === "success") {
                successToast(res.data.message || 'Request success');
                uploadedImageUrl = null;
                uploadedImageIndex = null;
                quill.root.innerHTML = ''; 
                window.location.href = '/user/complaints';
            } else {
                errorToast("Request failed with status: " + res.status);
            }

        } catch (error) {
            handleError(error);
        } finally {
            hideLoader();
        }
    }
}

function handleError(error) {
    let message = 'An unexpected error occurred';

    if (error.response) {
        const status = error.response.status;
        const serverMessage = error.response.data?.message || message;

        switch (status) {
            case 403:
                message = serverMessage;
                break;

            case 422:
                const errors = error.response.data.errors;
                if (errors) {
                    for (const key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            const errorField = document.getElementById(`${key}-error`);
                            if (errorField) {
                                errorField.innerText = errors[key][0];
                            }
                        }
                    }
                    return; // Skip toast, since field errors are shown
                } else {
                    message = serverMessage;
                }
                break;

            case 404:
                message = serverMessage || 'Not Found';
                break;

            case 500:
                message = 'Server error: ' + serverMessage;
                break;

            default:
                message = serverMessage;
        }
    } else if (error.request) {
        message = 'No response received from the server.';
    } else {
        message = error.message || message;
    }

    errorToast(message);
}

window.addEventListener('beforeunload', async (event) => {
    if (uploadedImageUrl) {
        try {
            await axios.post('/user/delete-editor-image', { image_url: uploadedImageUrl });
        } catch (error) {
            console.error('Failed to delete image on unload:', error);
        }
    }
});
</script>

