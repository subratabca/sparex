<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <h5 class="card-header pb-3 border-bottom mb-3">Customer Details</h5>
            <div class="card-body">
                <div class="info-container">
                    <ul class="list-unstyled mb-4">
                        <input type="text" class="d-none" id="complainID" />
                        <li class="mb-3">
                            <span class="fw-medium text-heading me-2">Image:</span>
                            <img id="image" src="/upload/no_image.jpg" alt="Customer Image" style="width: 100px; height: 100px; object-fit: cover;" />
                        </li>
                        <li class="mb-3">
                            <span class="fw-medium text-heading me-2">Name:</span>
                            <span id="name"></span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-medium text-heading me-2">Email:</span>
                            <span id="email"></span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-medium text-heading me-2">Phone:</span>
                            <span id="phone">N/A</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4 border-2 border-primary">
            <div class="card-body">
                <div class="card mb-4">
                    <h5 class="card-header">Complaint Details</h5>
                    <div class="table-responsive p-4 pt-0">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-truncate">SL</th>
                                    <th class="text-truncate">By</th>
                                    <th class="text-truncate">Complaint Message</th>
                                    <th class="text-truncate">Date</th>
                                    <th class="text-truncate">Time</th>
                                    <th class="text-truncate">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tableList"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        CustomerComplainDetailsInfo();
    });

    async function CustomerComplainDetailsInfo() {
        showLoader();
        try {
            let url = window.location.pathname;
            let segments = url.split('/');
            let id = segments[segments.length - 1];

            let res = await axios.get("/admin/customer-complain/details/info/" + id);
            let data = res.data.data;
            let status = data.status;
            const customer = data.customer;
            const client = data.client;

            document.getElementById('image').src = customer.image ? `/upload/user-profile/large/${customer.image}` : '/upload/no_image.jpg';
            document.getElementById('name').innerText = `${customer.firstName} ${customer.lastName || ''}`;
            document.getElementById('email').innerText = customer.email;
            document.getElementById('phone').innerText = customer.mobile || 'N/A';

            let tableList = document.getElementById("tableList");
            tableList.innerHTML = '';

            let complain = data; 
            let row = `
                <tr>
                    <td>1</td>
                    <td>${complain.sender_role.charAt(0).toUpperCase() + complain.sender_role.slice(1)}</td>
                    <td>${complain.message}</td>
                    <td>${complain.created_at.split('T')[0]}</td>
                    <td>${complain.created_at.split('T')[1].split('.')[0]}</td>
                    <td><span class="badge ${getStatusBadgeClass(complain.status)}">${complain.status}</span></td>
                </tr>
            `;
            tableList.insertAdjacentHTML('beforeend', row);

            // Loop through customer_complain_conversations and add rows to the table
            if (data.customer_complain_conversations && data.customer_complain_conversations.length > 0) {
                data.customer_complain_conversations.forEach((conversation, index) => {

                    let senderName;
                    if (conversation.sender_role === "customer") {
                        senderName = `${customer.firstName} ${customer.lastName || ''}`;
                    } else if (conversation.sender_role === "client") {
                        senderName = `${client.firstName} ${client.lastName || ''}`;
                    } else if (conversation.sender_role === "admin") {
                        senderName = "Admin"; 
                    } else {
                        senderName = "Unknown Sender";
                    }

                    let conversationRow = `
                        <tr>
                            <td>${index + 2}</td> 
                            <td>${senderName}</td>
                            <td>${conversation.sender_role.charAt(0).toUpperCase() + conversation.sender_role.slice(1)}</td>
                            <td>${conversation.reply_message}</td>
                            <td>${conversation.created_at.split('T')[0]}</td>
                            <td>${conversation.created_at.split('T')[1].split('.')[0]}</td>
                        </tr>
                    `;
                    tableList.insertAdjacentHTML('beforeend', conversationRow);
                });
            }
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

    function complainSolved() {
        const replyModal = new bootstrap.Modal(document.getElementById('reply-modal'));
        replyModal.show();
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'solved': return 'bg-success';
            case 'pending': return 'bg-danger';
            case 'under-review': return 'bg-info';
            default: return 'bg-secondary';
        }
    }
</script>

<style>
    .card-header {
        color: orange;
    }

    .custom-option-body img {
        width: 100%;  
        max-height: 200px; 
        object-fit: cover; 
    }
</style>
