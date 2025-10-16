<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Banned Customer Details Information</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('admin.banned.customers') }}" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To List
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="col-xl-12 mb-3 mb-xl-0" id="customer-details-container">
            <!-- Banned Customer will be loaded here dynamically -->
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    getBannedCustomerDetails();
});

async function getBannedCustomerDetails() {
    showLoader();
    try {
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];

        let res = await axios.get("/admin/get/banned/customer/details/" + id);
        if (res.status === 200 && res.data.status === 'success') {
            let bannedData = res.data.data;
            const container = document.getElementById('customer-details-container');
            let html = '';

            // Prepare customer and client data
            const customerImage = bannedData.customer.image ? 
                `/upload/customer-profile/small/${bannedData.customer.image}` : 
                '/upload/no_image.jpg';
            const customerName = `${bannedData.customer.firstName} ${bannedData.customer.lastName}`;
            const customerStatus = bannedData.customer.status === 1 ? 
                '<span class="badge bg-success">Active</span>' : 
                '<span class="badge bg-danger">Inactive</span>';
            
            const clientImage = bannedData.client.image ? 
                `/upload/client-profile/small/${bannedData.client.image}` : 
                '/upload/no_image.jpg';
            const clientName = `${bannedData.client.firstName} ${bannedData.client.lastName}`;

            html += `
            <div class="row">
                <div class="col-md-4">
                    <div class="card border">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <img src="${customerImage}" alt="${customerName}" class="w-px-150 mb-3 rounded" />
                                <h4 class="mb-2">${customerName}</h4>
                                <p class="text-muted">Customer</p>
                            </div>
                            <hr>
                            <h6 class="mb-3">Customer Details</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">${bannedData.customer.email}</dd>
                                    
                                <dt class="col-sm-5">Mobile:</dt>
                                <dd class="col-sm-7">${bannedData.customer.mobile || 'N/A'}</dd>
                                
                                <dt class="col-sm-5">Status:</dt>
                                <dd class="col-sm-7">${customerStatus}</dd>
                                
                                <dt class="col-sm-5">Banned On:</dt>
                                <dd class="col-sm-7">${new Date(bannedData.created_at).toLocaleString()}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card border">
                        <div class="card-body">
                            <div class="mb-4">
                                <h5 class="card-title">Banned Reason</h5>
                                <div class="p-3 bg-light rounded">${bannedData.message}</div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-4">
                                <h5 class="card-title">Customer Location</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Address:</strong></p>
                                        <p>${bannedData.customer.address1}</p>
                                        ${bannedData.customer.address2 ? `<p>${bannedData.customer.address2}</p>` : ''}
                                        <p>${bannedData.customer.zip_code}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Area:</strong></p>
                                        <p>${bannedData.customer.city?.name || 'N/A'}, 
                                           ${bannedData.customer.county?.name || 'N/A'}, 
                                           ${bannedData.customer.country?.name || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-0">
                                <h5 class="card-title">Banned By</h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="${clientImage}" alt="${clientName}" 
                                             class="rounded-circle me-3" width="60" height="60">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${clientName}</h6>
                                        <p class="mb-1">${bannedData.client.email}</p>
                                        <p class="mb-0">${bannedData.client.mobile || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

            container.innerHTML = html;
        } else {
            errorToast(res.data.message || "Failed to fetch banned customer details.");
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
                message = serverMessage || 'Banned customer not found';
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