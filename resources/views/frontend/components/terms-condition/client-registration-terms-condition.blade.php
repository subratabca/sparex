<section class="section-py bg-body first-section-pt">
  <div class="container">
    <div class="card g-3 mt-5">
    <div class="card-body row g-3">
        <h5 class="card-title" id="name"></h5><hr>
        <p class="card-text" id="description"></p>
    </div>
    </div>
  </div>
</section>

<script>
    async function ClientRegistrationTCInfo() {
        showLoader();
        try {
            let url = window.location.pathname;
            let segments = url.split('/');
            let name = decodeURIComponent(segments[segments.length - 1]);

            let res = await axios.get("/client/registration/terms-conditions/info/" + name);
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








