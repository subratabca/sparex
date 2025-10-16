<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Product Stock Details</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('current.stock') }}" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-arrow-left me-1"></span>Back To Product Stock
            </a>
        </div>
    </div>
    <div class="card-body">
        <input type="text" class="d-none" id="updateShowID">
        <div class="col-xl-12 mb-xl-0" id="product-details-container">
            <!-- Product details will be loaded here dynamically -->
        </div>
    </div>
    <div class="card-footer mx-auto">
        <button type="button" onclick="productPublish()" id="status-update-btn" class="btn btn-info pd-x-20 d-none">Publish</button>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    productDetailsInfo();
});

async function productDetailsInfo() {
    try {
        showLoader();
        let url = window.location.pathname;
        let segments = url.split('/');
        let id = segments[segments.length - 1];
        document.getElementById('updateShowID').value = id;

        let res = await axios.get("/admin/get/product/stock/info/" + id);
        if (res.status === 200 && res.data.status === 'success') {
            let product = res.data.data;

            const publishBtn = document.getElementById('status-update-btn');
            if (publishBtn) {
                if (product.status === 'pending') {
                    publishBtn.classList.remove('d-none');
                } else {
                    publishBtn.classList.add('d-none');
                }
            }

            const container = document.getElementById('product-details-container');
            let html = '';

            html += `<ul class="list-group mb-3" id="cart-items-list">`;
            
            const imagePath = product.image ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg';
            const clientName = `${product.client.firstName} ${product.client.lastName}`;
            const price = parseFloat(product.price).toFixed(2);
            const hasDiscount = product.has_discount_price === 1;
            const discountPrice = parseFloat(product.discount_price).toFixed(2);
            const currentStock = product.current_stock;

            html += `
            <li class="list-group-item p-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <img src="${imagePath}" alt="${product.name}" class="w-px-150 mb-3 rounded" />
                                    <h4 class="mb-2">${product.name}</h4>
                                    <div class="fs-3 ${hasDiscount ? 'text-danger' : 'text-primary'}">
                                        £${hasDiscount ? discountPrice : price}
                                        ${hasDiscount ? `<small class="text-muted text-decoration-line-through ms-2 fs-6">£${price}</small>` : ''}
                                    </div>
                                </div>
                                <hr>
                                <h6 class="mb-3">Quick Info</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-6">Category:</dt>
                                    <dd class="col-sm-6">${product.category.name}</dd>
                                        
                                    ${product.has_brand ? `
                                        <dt class="col-sm-6">Brand:</dt>
                                        <dd class="col-sm-6">${product.brand.name}</dd>
                                    ` : ''}
                                    
                                    <dt class="col-sm-6">Total Stock:</dt>
                                    <dd class="col-sm-6">${currentStock}</dd>
                                    
                                    <dt class="col-sm-6">Seller:</dt>
                                    <dd class="col-sm-6">${clientName}</dd>
                                    
                                    <dt class="col-sm-6">Contact:</dt>
                                    <dd class="col-sm-6">${product.client.mobile}</dd>

                                    <dt class="col-sm-6"><a href="/admin/terms-conditions/product_upload" target="_blank">Upload T&C:</a></dt>
                                    <dd class="col-sm-6">
                                        <span class="badge ${product.accept_tnc === 1 ? 'bg-primary' : 'bg-danger'}">
                                            ${product.accept_tnc === 1 ? 'Accepted' : 'Not Accepted'}
                                        </span>
                                    </dd>
                                    <dt class="col-sm-6">Publish Status:</dt>
                                    <dd class="col-sm-6">
                                        <span class="badge ${product.status != 'pending' ? 'bg-success' : 'bg-danger'}">
                                            ${product.status != 'pending' ? 'Published' : 'Not Published'}
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <h5>Description:</h5>
                            ${product.description}
                        </div><hr>
                        ${product.category.name === 'Food' ? `
                        <div class="mb-3">
                            <h5>Collection Information:</h5>
                            <p class="mb-1">Date: ${new Date(product.collection_date).toLocaleDateString()}</p>
                            <p class="mb-1">Time: ${product.start_collection_time} - ${product.end_collection_time}</p>
                            <p class="mb-1">Expires: ${new Date(product.expire_date).toLocaleDateString()}</p>
                        </div><hr>
                        ` : ''}
                        <div class="mb-3">
                            <h5>Location:</h5>
                            <p class="mb-1">${product.address1} ${product.address2}</p>
                            <p class="mb-1">${product.city.name}, ${product.county.name}, ${product.country.name}</p>
                            <p class="mb-1">Postcode: ${product.zip_code}</p>
                        </div><hr>
                        ${product.has_variants ? `
                        <div class="mb-3">
                            <h5>Variants:</h5>
                            ${product.variants.map(variant => `
                                <div class="border p-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            ${variant.color ? `<span class="me-2">Color: ${variant.color}</span>` : ''}
                                            ${variant.size ? `<span>Size: ${variant.size}</span>` : ''}
                                        </div>
                                        <span class="text-muted">Stock: ${variant.current_stock}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        ` : ''}
                    </div>
                </div>
            </li>`;

            html += `</ul>`;
            container.innerHTML = html;
        } else {
            errorToast(res.data.message || "Failed to fetch product details infoformation.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

async function productPublish() { 
    try {
        showLoader();
        let id = document.getElementById('updateShowID').value;

        let res = await axios.post("/admin/update/product/status", { id: id }); 
        if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message || "Product published successfully");
            window.location.href = '/admin/product-list';
        } else {
            errorToast("Request failed!");
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
                message = serverMessage || 'Data not found';
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