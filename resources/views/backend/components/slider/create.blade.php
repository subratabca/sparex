<div id="create-modal" class="modal fade">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content tx-size-sm">
      <div class="modal-header pd-x-20">
        <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Create New</h6>
        <button type="button" id="modal-close" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pd-20">
        <form id="save-form">
          <div class="row mg-b-25">
            <div class="col-lg-12">
              <div class="form-group">
                <label class="form-control-label">Title: <span class="tx-danger">*</span></label>
                <input type="text" class="form-control" id="title" placeholder="Enter Title">
              </div>
            </div>

            <div class="col-lg-12">
              <div class="form-group">
                <label class="form-control-label">Description: <span class="tx-danger">*</span></label>
                <textarea class="form-control summernote" id="description" placeholder="Enter Description"></textarea>
              </div>
            </div>

            <div class="col-lg-4">
              <label class="form-control-label">Upload Slider Image:</label><br>
              <label class="custom-file">
                <input type="file" class="custom-file-input" id="image" onChange="mainImageUrl(this)">
                <span class="custom-file-control"></span>
              </label>
              <div></div>
              <img src="{{asset('/upload/no_image.jpg')}}" id="mainImage" class="mt-1" style="width: 120px; height: 100px;">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button onclick="Save()" id="save-btn" class="btn btn-info pd-x-20">Save</button>
        <button class="btn btn-secondary pd-x-20" data-dismiss="modal">Close</button>
        <a href="{{ route('sliders') }}" class="btn btn-success">Back</a>
      </div>
    </div>
  </div>
</div>


<script>
  function mainImageUrl(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#mainImage').attr('src', e.target.result).width(200).height(150);
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  function resetCreateForm() {
    document.getElementById('save-form').reset();
    $('#mainImage').attr('src', '');
    $('.summernote').summernote('code', '');
  }

  $('#create-modal').on('show.bs.modal', function (e) {
    resetCreateForm();
  });

  async function Save() {
    let title = document.getElementById('title').value;
    let description = $('.summernote').summernote('code'); 
    let image = document.getElementById('image').files[0];

    if (!image) {
      errorToast("Image Required !");
    } else {
      document.getElementById('modal-close').click();
      let formData = new FormData();
      formData.append('title', title);
      formData.append('description', description);
      formData.append('image', image);

      const config = {
        headers: {
          'content-type': 'multipart/form-data',
        },
      };

      try {
        let res = await axios.post("/admin/create-slider", formData, config);
        if (res.status === 201) {
          successToast(res.data.message || 'Request success');
          resetCreateForm();
          $('#create-modal').modal('hide');
          await getList(); 
        } else {
          errorToast(res.data.message || "Request failed");
        }
      } catch (error) {
        if (error.response) {
          if (error.response.status === 422) {
            if (error.response.data.message) {
              errorToast(error.response.data.message);
            }
            if (error.response.data.errors) {
              let errorMessages = error.response.data.errors;
              for (let field in errorMessages) {
                if (errorMessages.hasOwnProperty(field)) {
                  errorMessages[field].forEach(msg => errorToast(msg));
                }
              }
            }
          } else if (error.response.status === 500) {
            errorToast(error.response.data.error);
          } else {
            errorToast("Request failed!");
          }
        } else {
          errorToast("Request failed!");
        }
        console.error(error);
      }
    }
  }

</script>

