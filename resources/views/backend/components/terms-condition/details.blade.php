<div class="row mb-5">
  <div class="col-md-12 col-lg-12 mb-3">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title" id="name"></h5><hr>
        <p class="card-text" id="description"></p>
        <a href="{{ route('terms.conditions') }}" class="btn btn-primary waves-effect waves-light mt-4">
            <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To T&C List
        </a>
      </div>
    </div>
  </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        TCDetailsInfo();
    });

    async function TCDetailsInfo() {
        showLoader();
        try {
            let url = window.location.pathname;
            let segments = url.split('/');
            let id = segments[segments.length - 1];

            let res = await axios.get("/admin/terms-conditions/info/" + id);
            let data = res.data.data;

            document.getElementById('name').innerText = data.name;
            document.getElementById('description').innerHTML = data.description;
        } catch (error) {
            if (error.response) {
                if (error.response.status === 404) {
                    errorToast(error.response.data.message || "Data not found.");
                } else if (error.response.status === 500) {
                    errorToast(error.response.data.error || "An internal server error occurred."); 
                } else {
                    errorToast("Request failed!");
                }
            } else {
                errorToast("Request failed! Please check your internet connection or try again later.");
            }
        } finally{
          hideLoader();
        }
    }

</script>