<div class="row mb-5">
    <div class="col-md-12 col-lg-12 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Audit Details</h5><hr>
                <p class="card-text"><strong>Name:</strong> <span id="name"></span></p>
                <p class="card-text"><strong>Email:</strong> <span id="email"></span></p>
                <p class="card-text"><strong>Phone:</strong> <span id="phone"></span></p>
                <p class="card-text"><strong>IP Address:</strong> <span id="ip-address"></span></p>
                <p class="card-text"><strong>Date:</strong> <span id="date"></span></p>
                <p class="card-text"><strong>Time:</strong> <span id="time"></span></p>
                <p class="card-text"><strong>Activity Type:</strong> <span id="activity-type"></span></p>
                <p class="card-text"><strong>Message:</strong> <span id="message"></span></p>
                <a href="{{ route('audits') }}" class="btn btn-primary waves-effect waves-light mt-4">
                    <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To Audit List
                </a>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        AuditDetailsInfo();
    });

    async function AuditDetailsInfo() {
        showLoader();
        try {
            let url = window.location.pathname;
            let segments = url.split('/');
            let id = segments[segments.length - 1];

            let res = await axios.get("/admin/audit/info/" + id);
            let data = res.data.data;

            let firstName = data['user']['firstName'];
            let lastName = data['user']['lastName'];
            let fullName = lastName ? `${firstName} ${lastName}` : firstName;

            let email = data['user']['email'] ? data['user']['email'] : 'N/A';
            let phone = data['user']['phone'] ? data['user']['phone'] : 'N/A';

            let createdAt = new Date(data.created_at);
            let formattedDate = createdAt.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            let formattedTime = createdAt.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            document.getElementById('name').innerText = fullName;
            document.getElementById('email').innerText = email;
            document.getElementById('phone').innerText = phone;
            document.getElementById('ip-address').innerText = data.ip_address;
            document.getElementById('date').innerText = formattedDate; 
            document.getElementById('time').innerText = formattedTime;
            document.getElementById('activity-type').innerText = data.activity_type;
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