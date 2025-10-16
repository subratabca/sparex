<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <h5 class="card-header pb-3 border-bottom mb-3">Product Details</h5>
            <input type="text" class="d-none" id="complaintID"/>
            <div class="card-body">
                <div class="info-container">
                    <ul class="list-group mb-3" id="product-details">
                        <!-- Product details will be dynamically inserted here -->
                    </ul>
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('client.complains') }}" class="btn btn-primary me-3">Back to complaint list</a>
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
                      <span>Complain Conversation Details</span>
                      <small class="text-muted">Current Status: <span class="badge bg-success text-capitalize" id="complaint-status"></span></small>
                      <small class="text-muted" id="order-no"></small>
                    </h5><hr class="p-0 m-0">

                    <div class="table-responsive p-2 pt-2">
                      <table class="table table-hover">
                        <thead class="table-light">
                          <tr>
                            <th class="text-truncate">SL</th>
                            <th class="text-truncate">By</th>
                            <th class="text-truncate">Complaint & Reply Message</th>
                            <th class="text-truncate">Date</th>
                            <th class="text-truncate">Time</th>
                          </tr>
                        </thead>
                        <tbody id="tableList">
                        </tbody>
                      </table>
                    </div>
                </div>
                <div class="text-center">
                    <button class="btn replyBtn btn-sm btn-outline-danger d-none">Reply</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    complaintDetailsInfo();

    $(document).on('click', '.replyBtn', function () {
        let id = document.getElementById('complaintID').value; 
        $("#complaintID").val(id);
        $("#reply-modal").modal('show'); 
    });
});

async function complaintDetailsInfo() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];
        document.getElementById('complaintID').value = id;

        let res = await axios.get("/client/get/complaint/details/" + id);
        if (res.status === 200 && res.data.status === 'success') {
            let data = res.data.data;
            //console.log('-----------',data);

            // Show/hide reply button
            const replyBtn = document.querySelector('.replyBtn');
            if (replyBtn) {
                data.status === 'under_review'
                    ? replyBtn.classList.remove('d-none')
                    : replyBtn.classList.add('d-none');
            }

            // Product details
            let product = data.order_item?.product || {};
            let variant = data.order_item?.variant || {};
            let client = product.client || {};
            let order = data.order_item?.order || {};

            let productDetails = `
                <li class="list-group-item p-4">
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0">
                            <img src="${product.image ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg'}" 
                                 alt="${product.name}" class="w-px-100" />
                        </div>
                        <div class="flex-grow-1">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="me-3">${product.name || 'N/A'}</h6>
                                    ${product.category ? `<div class="mb-1"><small>Category: <strong>${product.category.name}</strong></small></div>` : ''}
                                    ${product.brand ? `<div class="mb-1"><small>Brand: <strong>${product.brand.name}</strong></small></div>` : ''}
                                    ${variant ? `<div class="mb-2">
                                        ${variant.color ? `<span class="me-2">Color: ${variant.color}</span>` : ''}
                                        ${variant.size ? `<span>Size: ${variant.size}</span>` : ''}
                                    </div>` : ''}
                                    <div class="mb-2">Sold by: ${client.firstName || ''} ${client.lastName || ''}</div>
                                    <div class="mb-2">Order No: <a href="/client/order/details/${order.id}" target="_blank">${order.order_number || 'N/A'}</a></div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="my-2">
                                        <span class="text-primary">$${data.order_item?.unit_price || 0}</span>
                                        <span>x ${data.order_item?.quantity || 0}</span>
                                    </div>
                                    <div>Total: $${data.order_item?.total_price || 0}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>`;
            document.getElementById('product-details').innerHTML = productDetails;

            // Complaint status & order no
            document.getElementById('complaint-status').innerHTML = data.status;
            document.getElementById('order-no').innerHTML = `Order No: <a href="/client/order/details/${order.id}" target="_blank">${order.order_number || 'N/A'}</a>`;

            // Complaint conversations
            let tableList = $("#tableList");
            tableList.empty();

            // Main complaint
            let mainRow = `<tr>
                <td>1</td>
                <td>Customer</td>
                <td class="message-html"></td>
                <td>${data.cmp_date || ''}</td>
                <td>${data.cmp_time || ''}</td>
            </tr>`;
            tableList.append(mainRow);
            $('.message-html').last().html(data.message || '');

            // Conversations
            if (data.conversations && data.conversations.length) {
                data.conversations.forEach((conv, index) => {
                    let sender = conv.sender_role.charAt(0).toUpperCase() + conv.sender_role.slice(1);
                    let row = `<tr>
                        <td>${index + 2}</td>
                        <td>${sender}</td>
                        <td>${conv.reply_message}</td>
                        <td>${conv.created_at.split('T')[0]}</td>
                        <td>${conv.created_at.split('T')[1].split('.')[0]}</td>
                    </tr>`;
                    tableList.append(row);
                });
            } else {
                tableList.append('<tr><td colspan="5" class="text-center">No complain conversations available.</td></tr>');
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



function handleError(error) {
    let message = 'An unexpected error occurred';

    if (error.response) {
        const status = error.response.status;
        const serverMessage = error.response.data?.message;

        switch (status) {
            case 404:
                message = serverMessage || 'There is no complain found';
                break;
            case 500:
                message = serverMessage || 'Server error. Please try again later.';
                break;
            default:
                message = serverMessage || message;
        }
    } else if (error.request) {
        message = 'No response received from the server.';
    } else {
        message = error.message || message;
    }

    errorToast(message);
}
</script>

<style type="text/css">
    .card-header {
        color: orange;
    }

    .custom-option-body img {
        width: 100%;  
        max-height: 200px; 
        object-fit: cover; 
    }
</style>
