<div class="row mb-5">
  <div class="col-md-12 col-lg-12 mb-3">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title" id="name"></h5><hr>
        <p class="card-text" id="description"></p>
      </div>
    </div>
  </div>
</div>



<script>
    document.addEventListener("DOMContentLoaded", function () {
        ClientProductUploadTCInfo();
    });

    async function ClientProductUploadTCInfo() {
        showLoader();
        try {
            let url = window.location.pathname;
            let segments = url.split('/');
            let name = decodeURIComponent(segments[segments.length - 1]);

            let res = await axios.get("/client/product/upload/terms-conditions/info/" + name);
            let data = res.data.data;

            document.getElementById('name').innerHTML = data.name + ' <span>Terms & Condition</span>';
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
        } finally {
            hideLoader();
        }
    }
</script>
