<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <h5 class="card-header pb-3 border-bottom mb-3">Product Details</h5>
            <input type="text" class="d-none" id="complaintID"/>
            <div class="card-body">
                <div class="info-container">
                    <ul class="list-group mb-3" id="product-details"></ul>
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('admin.complaints') }}" class="btn btn-primary me-3">Back to complaint list</a>
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
                    <h5 class="card-header d-flex justify-content-between align-items-center">
                      <span>Complaint Conversation Details</span>
                      <small class="text-muted">Current Status: <span class="badge bg-success text-capitalize" id="complaint-status"></span></small>
                      <small class="text-muted" id="order-no"></small>
                    </h5>
                    <hr class="p-0 m-0">

                    <div class="table-responsive p-2 pt-2">
                      <table class="table table-hover">
                        <thead class="table-light">
                          <tr>
                            <th>SL</th>
                            <th>By</th>
                            <th>Complaint & Reply Message</th>
                            <th>Date</th>
                            <th>Time</th>
                          </tr>
                        </thead>
                        <tbody id="tableList"></tbody>
                      </table>
                    </div>
                </div>
                <div class="text-center">
                    <button type="button" onclick="complaintForwarded()" id="complaintForwarded" class="btn btn-primary d-none me-3">Complaint forwarded to client</button>
                    <button type="button" onclick="complaintSolvedModal()" id="complaintSolved" class="btn btn-info d-none me-3">Complaint solved or further investigation</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    complaintDetailsInfo();

    $(document).on('click', '.complaintSolvedModal', function () {
        let id = document.getElementById('complainID').value; 
        $("#complainID").val(id);
        $("#reply-modal").modal('show'); 
    });
});

async function complaintDetailsInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let id = url.split("/").pop();
        document.getElementById("complaintID").value = id;

        let res = await axios.get(`/admin/get/complaint/details/${id}`);

        if (res.status === 200 && res.data.status === "success") {
            let data = res.data.data;
            let orderItem = data.order_item || data.orderItem;
            let order = orderItem?.order;
            let product = orderItem?.product;
            let variant = orderItem?.variant;
            let customer = data.customer;

            let complaintStatus = data.status;
            toggleActionButtons(complaintStatus);

            // 🧩 Product details
            let productDetails = `
                <li class="list-group-item p-4">
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0">
                            <img src="${product?.image ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg'}" 
                                 alt="${product?.name || ''}" 
                                 class="w-px-100 rounded" />
                        </div>
                        <div class="flex-grow-1">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6><a href="javascript:void(0)" class="text-heading">${product?.name || 'N/A'}</a></h6>
                                    ${product?.category ? `<div><small class="text-muted">Category: <strong>${product.category.name}</strong></small></div>` : ''}
                                    ${product?.brand ? `<div><small class="text-muted">Brand: <strong>${product.brand.name}</strong></small></div>` : ''}

                                    ${variant ? `
                                    <div class="mb-2">
                                        ${variant.color ? `<span class="me-2">Color: ${variant.color}</span>` : ''}
                                        ${variant.size ? `<span>Size: ${variant.size}</span>` : ''}
                                    </div>` : ''}

                                    ${product?.client ? `
                                    <div class="mb-2">
                                        <span class="me-1">Sold by:</span>
                                        <a href="javascript:void(0)">${product.client.firstName} ${product.client.lastName}</a>
                                    </div>` : ''}

                                    ${order ? `
                                    <div>
                                        <span class="me-1">Order No:</span>
                                        <a href="/admin/order/details/${order.id}" target="_blank">${order.order_number}</a>
                                    </div>` : ''}
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="my-2 mt-md-4 mb-md-5">
                                        <span class="text-primary">$${orderItem?.unit_price || 0}</span>
                                        <span class="text-body"> x ${orderItem?.quantity || 1}</span>
                                    </div>
                                    <div class="h6">Total: $${orderItem?.total_price || 0}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            `;
            document.getElementById("product-details").innerHTML = productDetails;

            // 🧩 Complaint and conversation details
            document.getElementById("complaint-status").innerHTML = complaintStatus;
            document.getElementById("order-no").innerHTML = order
                ? `Order NO: <a href="/admin/order/details/${order.id}" target="_blank">${order.order_number}</a>`
                : "";

            let tableList = $("#tableList");
            tableList.empty();

            // Main complaint
            let mainRow = `
                <tr>
                    <td>1</td>
                    <td>Customer</td>
                    <td>${data.message}</td>
                    <td>${data.cmp_date || ''}</td>
                    <td>${data.cmp_time || ''}</td>
                </tr>`;
            tableList.append(mainRow);

            // Conversations
            if (data.conversations && data.conversations.length > 0) {
                data.conversations.forEach((conversation, index) => {
                    let sender =
                        conversation.sender_role === "client"
                            ? "Client"
                            : conversation.sender_role === "customer"
                            ? "Customer"
                            : conversation.sender_role === "admin"
                            ? "Admin"
                            : "Unknown";

                    let createdAt = new Date(conversation.created_at);
                    let date = createdAt.toLocaleDateString("en-GB");
                    let time = createdAt.toLocaleTimeString("en-US", {
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: true,
                    });

                    let row = `
                        <tr>
                            <td>${index + 2}</td>
                            <td>${sender}</td>
                            <td>${conversation.reply_message}</td>
                            <td>${date}</td>
                            <td>${time}</td>
                        </tr>`;
                    tableList.append(row);
                });
            } else {
                tableList.append(`
                    <tr>
                        <td colspan="5" class="text-center">No complaint conversations available.</td>
                    </tr>
                `);
            }
        } else {
            errorToast(res.data.message || "Failed to fetch complaint details.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function toggleActionButtons(status) {
    const forwardedBtn = document.getElementById("complaintForwarded");
    const solvedBtn = document.getElementById("complaintSolved");

    forwardedBtn.classList.add("d-none");
    solvedBtn.classList.add("d-none");

    if (status === "pending") forwardedBtn.classList.remove("d-none");
    if (status === "under_review") solvedBtn.classList.remove("d-none");
}

async function complaintForwarded() {
    showLoader();
    try {
        let id = document.getElementById("complaintID").value;
        const res = await axios.get(`/admin/complaint/forwarded/${id}`);
        if (res.status === 200) {
            successToast(res.data.message || "Complaint forwarded successfully.");
            window.location.href = "/admin/complaints";
        } else {
            errorToast("Request failed");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function complaintSolvedModal() {
    const modal = new bootstrap.Modal(document.getElementById("reply-modal"));
    modal.show();
}

function handleError(error) {
    let message = "An unexpected error occurred.";
    if (error.response) {
        const { status, data } = error.response;
        if (status === 400) message = data?.message || "Complaint not in pending status.";
        else if (status === 404) message = data?.message || "Complaint not found.";
        else if (status === 500) message = data?.message || "Server error.";
        else message = data?.message || message;
    } else if (error.request) {
        message = "No server response. Check your connection.";
    } else {
        message = error.message;
    }
    errorToast(message);
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
