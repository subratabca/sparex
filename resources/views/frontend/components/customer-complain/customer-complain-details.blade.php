@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <h5 class="card-header pb-3 border-bottom mb-3">Customer Details</h5>
            <div class="card-body">
                <div class="info-container">
                    <ul class="list-unstyled mb-4">
                        <input type="text" class="d-none" id="complainID" />
                        <li class="mb-3">
                            <span class="fw-medium text-heading me-2">Customer Image:</span>
                            <span>
                                <img id="customer-image" alt="Customer Image" width="100" height="100" class="rounded-circle" />
                            </span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-medium text-heading me-2">Customer Name:</span>
                            <span id="customer-name"></span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-medium text-heading me-2">Customer Phone:</span>
                            <span id="customer-phone"></span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-medium text-heading me-2">Complaint Status:</span>
                            <span class="badge rounded-pill" id="complain-status"></span>
                        </li>
                    </ul>
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('customer.complains') }}" class="btn btn-primary me-3">Back to complaint list</a>
                    </div>
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
                    <h5 class="card-header">Customer Complain Conversation Details</h5>
                    <div class="table-responsive p-4 pt-0">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-truncate">SL</th>
                                    <th class="text-truncate">By</th>
                                    <th class="text-truncate">Complain & Reply Message</th>
                                    <th class="text-truncate">Date</th>
                                    <th class="text-truncate">Time</th>
                                </tr>
                            </thead>
                            <tbody id="tableList">
                                <!-- Table rows will be dynamically appended here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-center">
                    <button class="btn replyBtn btn-sm btn-outline-danger" disabled>Appeal</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
    document.addEventListener("DOMContentLoaded", function () {
        CustomerComplainDetailsInfo();

        $(document).on('click', '.replyBtn', function () {
            const id = document.getElementById('complainID').value;
            $("#complainID").val(id);
            $("#reply-modal").modal('show'); 
        });
    });

    async function CustomerComplainDetailsInfo() {
        showLoader();
        try {
            const url = window.location.pathname;
            const segments = url.split('/');
            const id = segments[segments.length - 1];

            document.getElementById('complainID').value = id;

            const res = await axios.get("/user/customer-complain-details-info/" + id);
            const data = res.data.data;

            // Set customer details
            const customer = data.customer;
            const customerImage = document.getElementById("customer-image");
            const customerName = document.getElementById("customer-name");
            const customerPhone = document.getElementById("customer-phone");
            const complainStatus = document.getElementById("complain-status");

            customerImage.src = customer.image
                ? `/upload/user-profile/small/${customer.image}`
                : `/upload/no_image.jpg`;

            customerName.innerText = customer.firstName || "N/A";
            customerPhone.innerText = customer.mobile || "N/A";

            const statusBadgeClass = {
                "pending": "bg-danger",
                "under-review": "bg-info",
                "solved": "bg-success"
            }[data.status] || "bg-primary";

            complainStatus.innerText = data.status.replace("-", " ").toUpperCase();
            complainStatus.className = `badge rounded-pill ${statusBadgeClass}`;

            const status = data.status;
            const replyBtn = document.querySelector('.replyBtn');
            replyBtn.disabled = (status !== 'under-review');

            const tableList = $("#tableList");
            tableList.empty();

            // Append main complaint details
            const mainRow = `<tr>
                <td><strong>1</strong></td>
                <td><strong>${data.sender_role}</strong></td>
                <td>${data.message}</td>
                <td>${data.cmp_date}</td>
                <td>${data.cmp_time}</td>
            </tr>`;
            tableList.append(mainRow);

            // Append conversation details if available
            if (data.customer_complain_conversations.length > 0) {
                data.customer_complain_conversations.forEach((conversation, index) => {
                    const senderRoleMap = {
                        client: 'client',
                        customer: 'customer',
                        admin: 'admin'
                    };
                    const sender = senderRoleMap[conversation.sender_role] || 'Unknown';
                    const createdAt = conversation.created_at.split('T');
                    const date = createdAt[0];
                    const time = createdAt[1].split('.')[0];

                    const row = `<tr>
                        <td>${index + 2}</td>
                        <td>${sender}</td>
                        <td>${conversation.reply_message}</td>
                        <td>${date}</td>
                        <td>${time}</td>
                    </tr>`;
                    tableList.append(row);
                });
            } else {
                const noDataRow = `<tr>
                    <td colspan="5" class="text-center">No complain conversations available.</td>
                </tr>`;
                tableList.append(noDataRow);
            }
        } catch (error) {
            if (error.response) {
                const errorMessage = error.response.data.message || "Request failed!";
                errorToast(errorMessage);
            } else {
                errorToast("Request failed! Please check your internet connection or try again later.");
            }
        } finally {
            hideLoader();
        }
    }
</script>

<style type="text/css">
    .card-header {
        color: orange;
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }

    .replyBtn[disabled] {
        cursor: not-allowed;
        opacity: 0.65;
    }
</style>
