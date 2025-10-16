@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <h5 class="card-header pb-3 border-bottom mb-3">Product Details</h5>
            <input type="text" class="d-none" id="complainID"/>
            <input type="text" class="d-none" id="productID"/>
            <div class="card-body">
                <div class="info-container">
                    <ul class="list-group mb-3" id="product-details">
                        <!-- Product details will be dynamically inserted here -->
                    </ul>
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('complaints') }}" class="btn btn-primary me-3">Back to complaint list</a>
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
                            <th class="text-truncate">Complain & Reply Message</th>
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
@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {
    complainDetailsInfo();

    $(document).on('click', '.replyBtn', function () {
        let id = document.getElementById('complainID').value; 
        $("#complainID").val(id);
        $("#reply-modal").modal('show'); 
    });
});

async function complainDetailsInfo() {
        showLoader();
        try {
            let url = window.location.pathname;
            let segments = url.split('/');
            let id = segments[segments.length - 1];

            document.getElementById('complainID').value = id;
            document.getElementById('productID').value = id;

            let res = await axios.get("/user/get/complaint/details/" + id);
            if (res.status === 200 && res.data.status === 'success') {
            let data = res.data.data;
            //console.log('----------', data);

            const replyBtn = document.querySelector('.replyBtn');
            if (replyBtn) {
                if (data.status === 'under_review') {
                    replyBtn.classList.remove('d-none');
                } else {
                    replyBtn.classList.add('d-none');
                }
            }

            let productDetails = `
                <li class="list-group-item p-4">
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0">
                            <img src="${data.product.image ? `/upload/product/small/${data.product.image}` : '/upload/no_image.jpg'}" 
                                 alt="${data.product.name}" 
                                 class="w-px-100" />
                        </div>
                        <div class="flex-grow-1">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="me-3">
                                        <a href="javascript:void(0)" class="text-heading">${data.product.name}</a>
                                    </h6>
                                    ${data.product.category ? `<div class="mb-1"><small class="text-muted">Category: <strong>${data.product.category.name}</strong></small></div>` : ''}
                                    ${data.product.brand ? `<div class="mb-1"><small class="text-muted">Brand: <strong>${data.product.brand.name}</strong></small></div>` : ''}

                                    ${data.order_item.variant ? `
                                    <div class="mb-2">
                                        <span class="text-muted">Variant:</span>
                                        ${data.order_item.variant.color ? `<span class="me-2">Color: ${data.order_item.variant.color}</span>` : ''}
                                        ${data.order_item.variant.size ? `<span>Size: ${data.order_item.variant.size}</span>` : ''}
                                    </div>` : ''}

                                    <div class="d-flex flex-wrap mb-2">
                                        <span class="me-1">Sold by:</span>
                                        <a href="javascript:void(0)">${data.product.client.firstName} ${data.product.client.lastName}</a>
                                    </div>
                                  <div class="d-flex flex-wrap mb-2">
                                        <span class="me-1">Order No:</span>
                                        <a href="/user/order/details/${data.order.id}" target="_blank">${data.order.order_number}</a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-md-end">
                                        <div class="my-2 mt-md-4 mb-md-5">
                                            <span class="text-primary">$${data.order_item.unit_price}</span>
                                            <span class="text-body"> x ${data.order_item.quantity}</span>
                                        </div>
                                        <div class="h6">Total: $${data.order_item.total_price}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>`;
            
            document.getElementById('complaint-status').innerHTML = data.status;
            document.getElementById('product-details').innerHTML = productDetails;

            let tableList = $("#tableList");
            tableList.empty();

            document.getElementById('order-no').innerHTML = `Order NO: <a href="/user/order/details/${data.order.id}" target="_blank">${data.order.order_number}</a>`;

            const mainComplain = data.message;
            const cmpDate = data.cmp_date;
            const cmpTime = data.cmp_time;

            let mainRow = `<tr>
                  <td><strong>1</strong></td>
                  <td><strong>Customer</strong></td>
                  <td><strong class="message-html"></strong></td>
                  <td><strong>${cmpDate}</strong></td>
                  <td><strong>${cmpTime}</strong></td>
                 </tr>`;
            tableList.append(mainRow);
            $('.message-html').last().html(mainComplain);

            if (data.conversations && data.conversations.length > 0) {
                data.conversations.forEach((conversation, index) => {
                    let sender =
                        conversation.sender_role === 'client' ? 'Client' :
                        conversation.sender_role === 'customer' ? 'Customer' :
                        conversation.sender_role === 'admin' ? 'Admin' : 'Unknown';

                    let row = `<tr>
                      <td>${index + 1}</td>
                      <td>${sender}</td>
                      <td>${conversation.reply_message}</td>
                      <td>${conversation.created_at.split('T')[0]}</td>
                      <td>${conversation.created_at.split('T')[1].split('.')[0]}</td>
                     </tr>`;
                    tableList.append(row);
                });
            } else {
                let row = `<tr>
                      <td colspan="5" class="text-center">No complain conversations available.</td>
                     </tr>`;
                tableList.append(row);
            }

        } else {
            errorToast(res.data.message || "Failed to fetch complain details infoformation.");
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

