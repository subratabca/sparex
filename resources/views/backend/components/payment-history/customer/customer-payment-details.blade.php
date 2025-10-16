<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Customer Payment Details Information</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('admin.customer.payments') }}" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To Customer Payment List
            </a>
        </div>
    </div>
    <div class="card-body">
        <input type="text" class="d-none" id="paymentDetailsID">
        <div class="col-xl-12 mb-xl-0" id="payment-details-container">
            <!-- Payment details will load dynamically -->
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    getCustomerPaymentDetailsInfo();
});

async function getCustomerPaymentDetailsInfo() {
    try {
        showLoader();
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];
        document.getElementById('paymentDetailsID').value = id;

        let res = await axios.get(`/admin/get/payment/details/customer/info/${id}`);
        if (res.status === 200 && res.data.status === 'success') {
            const data = res.data.data;
            //console.log('------',data);
            const container = document.getElementById('payment-details-container');
            let html = '';

            // Base info
            const product = data?.order_item?.product || {};
            const client = data?.client || {};
            const order = data?.order || {};
            const customer = order?.customer || {};
            const variant = data?.order_item?.variant || {};

            const imagePath = product.image ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg';
            const clientName = `${client.firstName ?? ''} ${client.lastName ?? ''}`.trim();
            const productName = product.name ?? '-';
            const variantInfo = variant
                ? `${variant.color ? 'Color: ' + variant.color : ''} ${variant.size ? ', Size: ' + variant.size : ''}`
                : '';

            html += `<ul class="list-group mb-3" id="payment-details-list">
                <li class="list-group-item p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <img src="${imagePath}" alt="${productName}" class="w-px-150 mb-3 rounded" />
                                    <h4 class="mb-2">${productName}</h4>
                                    ${variantInfo ? `<p class="text-muted">${variantInfo}</p>` : ''}
                                    <hr>
                                    <dl class="row mb-0 text-start">
                                        <dt class="col-sm-6">Seller:</dt>
                                        <dd class="col-sm-6">${clientName || '-'}</dd>
                                        <dt class="col-sm-6">Customer:</dt>
                                        <dd class="col-sm-6">${(customer.firstName ?? '-') + ' ' + (customer.lastName ?? '')}</dd>
                                        <dt class="col-sm-6">Order No:</dt>
                                        <dd class="col-sm-6">${order.order_number ?? '-'}</dd>
                                        <dt class="col-sm-6">Payment Type:</dt>
                                        <dd class="col-sm-6">${
                                            order.payment_type 
                                                ? order.payment_type.charAt(0).toUpperCase() + order.payment_type.slice(1) 
                                                : '-'
                                        }</dd>

                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <h5 class="mb-3">Payment & Refund Details</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Product Price:</dt>
                                <dd class="col-sm-8">£${parseFloat(data.order_item?.unit_price ?? 0).toFixed(2)}</dd>

                                <dt class="col-sm-4">Quantity:</dt>
                                <dd class="col-sm-8">${data.quantity ?? '-'}</dd>

                                <dt class="col-sm-4">Subtotal:</dt>
                                <dd class="col-sm-8">£${parseFloat(data.subtotal ?? 0).toFixed(2)}</dd>

                                <dt class="col-sm-4">Coupon Discount:</dt>
                                <dd class="col-sm-8">£${parseFloat(data.coupon_discount ?? 0).toFixed(2)}</dd>

                                <dt class="col-sm-4">Tax:</dt>
                                <dd class="col-sm-8">£${parseFloat(data.tax ?? 0).toFixed(2)}</dd>

                                <dt class="col-sm-4">Delivery Fee:</dt>
                                <dd class="col-sm-8">£${parseFloat(data.delivery_fee ?? 0).toFixed(2)}</dd>

                                <dt class="col-sm-4">Total Refund:</dt>
                                <dd class="col-sm-8 text-danger fw-bold">£${parseFloat(data.total_refund ?? 0).toFixed(2)}</dd>

                                <dt class="col-sm-4">Payment Status:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge ${getPaymentBadgeClass(data.payment_status)}">${data.payment_status ?? 'Unknown'}</span>
                                </dd>

                                <dt class="col-sm-4">Action Type:</dt>
                                <dd class="col-sm-8">${data.action_type ?? '-'}</dd>

                                <dt class="col-sm-4">Action Reason:</dt>
                                <dd class="col-sm-8">${data.action_reason ?? '-'}</dd>

                                <dt class="col-sm-4">Action By:</dt>
                                <dd class="col-sm-8">${data.action_by?.firstName ?? '-'} ${data.action_by?.lastName ?? ''}</dd>
                            </dl>

                            ${
                                data.payment_status === 'due'
                                    ? `<div class="text-end mt-3">
                                        <button type="button" class="btn btn-success" onclick="markAsPaid(${data.id})">
                                            <i class="mdi mdi-check-circle-outline me-1"></i> Mark as Paid
                                        </button>
                                    </div>`
                                    : ''
                            }
                        </div>
                    </div>
                </li>
            </ul>`;

            container.innerHTML = html;
        } else {
            errorToast("Failed to fetch payment details information.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

async function markAsPaid(id) {
    // Show nice Material-style confirmation
    const result = await Swal.fire({
        title: 'Mark Payment as Paid?',
        text: "Are you sure you want to mark this payment as paid?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, mark as paid',
        cancelButtonText: 'Cancel',
        width: '500px', // set width to a smaller size
        padding: '1.2rem', // adjust padding
        customClass: {
            popup: 'swal-sm-font',          
            title: 'swal-title-sm',         
            content: 'swal-content-sm',     
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-outline-secondary ms-2'
        },
        buttonsStyling: false
    });

    if (!result.isConfirmed) return; 

    try {
        showLoader();
        let res = await axios.post(`/admin/customer-payment/mark-as-paid/${id}`);

        if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message);
            getCustomerPaymentDetailsInfo(); // Refresh the details
        } else {
            errorToast(res.data.message || 'Failed to update payment status.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}


async function markAsPaid11(id) {
    if (!confirm('Are you sure you want to mark this payment as paid?')) return;
    
    try {
        showLoader();
        let res = await axios.post(`/admin/customer-payment/mark-as-paid/${id}`);

        if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message);
            getCustomerPaymentDetailsInfo(); // Refresh the details
        } else {
            errorToast(res.data.message || 'Failed to update payment status.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}


function getPaymentBadgeClass(status) {
    switch (status) {
        case 'paid': return 'bg-success';
        case 'pending': return 'bg-warning';
        case 'refunded': return 'bg-info';
        case 'failed': return 'bg-danger';
        default: return 'bg-light text-dark';
    }
}

function handleError(error) {
    let message = 'An unexpected error occurred';
    if (error.response) {
        const status = error.response.status;
        const serverMessage = error.response.data?.message;
        switch (status) {
            case 404:
                message = serverMessage || 'Data not found';
                break;
            case 500:
                message = serverMessage || 'Server error';
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
