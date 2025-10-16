<div class="row mb-5">
  <div class="col-md-12 col-lg-12 mb-3">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Contact Message Details</h5><hr>
        <p class="card-text"><strong>Name:</strong> <span id="name"></span></p>
        <p class="card-text"><strong>Email:</strong> <span id="email"></span></p>
        <p class="card-text"><strong>Phone:</strong> <span id="phone"></span></p>
        <p class="card-text"><strong>Message:</strong> <span id="message"></span></p>
        <a href="{{ route('admin.contact.message') }}" class="btn btn-primary waves-effect waves-light mt-4">
            <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To Contact Message List
        </a>
      </div>
    </div>
  </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        ContactDetailsInfo();
    });

    async function ContactDetailsInfo() {
        showLoader();
        try {
            let url = window.location.pathname;
            let segments = url.split('/');
            let id = segments[segments.length - 1];

            let res = await axios.get("/admin/contact-message/info/" + id);
            let data = res.data.data;

            document.getElementById('name').innerText = data.name;
            document.getElementById('email').innerText = data.email;
            document.getElementById('phone').innerText = data.phone;
            document.getElementById('message').innerHTML = data.message;
        } catch (error) {
            if (error.response) {
                if (error.response.status === 500) {
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