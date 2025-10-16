<section class="section-py bg-body first-section-pt">
    <div class="container">
        <div id="wizard-checkout" class="bs-stepper wizard-icons wizard-icons-example mt-2">
            <div class="bs-stepper-content border-top rounded-0">
                <div id="checkout-cart" class="content">
                    <div class="row">
                        <!-- Cart left -->
                        <div class="col-xl-8 mb-3 mb-xl-0" id="cart-items-container">
                            <!-- Cart items will be loaded here dynamically -->
                        </div>

                        <!-- Cart right -->
                        <div class="col-xl-4">
                            <div class="border rounded p-3 mb-3">
                                <!-- Coupon Section -->
                            <div id="coupon-section">
                                <h6>Apply Coupon</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-sm-8 col-xxl-8 col-xl-12">
                                        <input type="text" id="coupon-code" class="form-control" placeholder="Enter Coupon Code"
                                               aria-label="Enter Promo Code" />
                                        <span class="error-message text-danger" id="coupon_name-error"></span>
                                    </div>
                                    <div class="col-4 col-xxl-4 col-xl-12">
                                        <div class="d-grid">
                                            <button type="button" 
                                                    class="btn btn-outline-primary"
                                                    onclick="applyCoupon()">
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <hr class="mx-n3" />
                            </div>

                                <!-- Summary section -->
                                <div id="cart-summary">
                                    <!-- Summary content will be loaded dynamically -->
                                </div>
                                <div class="d-grid mt-5">
                                    <a href="{{ route('checkout') }}"><button class="btn btn-info btn-next">PROCCED TO CHECKOUT</button></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
let currentCartPage = 1;
let cartHasMore = false;
let cartTotalItems = 0;

document.addEventListener("DOMContentLoaded", async function () {
    try {
        showLoader();
        const response = await axios.get('/user/get-cart-product', {
            params: {
                page: currentCartPage,
                per_page: 3
            }
        });
        
        if (response.status === 200 && response.data.status === 'success') {
            const cartData = response.data.data;
            cartTotalItems = cartData.pagination.total_items;
            cartHasMore = cartData.pagination.has_more;
            renderCartItems(cartData.cart_items);
            updateCartSummary(cartData.summary);
            updateLoadMoreButton();
        }
    } catch (error) {
        console.error('Error loading cart:', error);
        errorToast('Failed to load cart items');
    } finally {
        hideLoader();
    }
});

function renderCartItems(cartItems) {
    const container = document.getElementById('cart-items-container');
    let html = '';
    let hasStockError = false;

    if (cartItems.length === 0) {
        html = `<div class="alert alert-info">Your cart is empty</div>`;
        container.innerHTML = html;
        return;
    }

    html += `<h5>My Shopping Bag (${cartTotalItems} Items)</h5>`;
    html += `<ul class="list-group mb-3" id="cart-items-list">`;

    cartItems.forEach(item => {
        const product = item.product;
        const variant = item.product_variant;
        const imagePath = product.image ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg';
        const price = parseFloat(item.price).toFixed(2);
        const originalPrice = parseFloat(product.price).toFixed(2);
        const clientName = product.client.lastName 
            ? `${product.client.firstName} ${product.client.lastName}`
            : product.client.firstName;

        const stock = variant ? variant.current_stock : product.current_stock;
        const quantityError = item.quantity > stock;
        if (quantityError) hasStockError = true;

        html += `
        <li class="list-group-item p-4">
            <div class="d-flex gap-3">
                <div class="flex-shrink-0">
                    <img src="${imagePath}" alt="${product.name}" class="w-px-100" />
                </div>
                <div class="flex-grow-1">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="me-3">
                                <a href="/product-details/${product.id}" class="text-heading">${product.name}</a>
                            </h6>
                            <div class="mb-1 d-flex flex-wrap">
                                <span class="me-1">Sold by:</span>
                                <span class="me-1">${clientName}</span>
                                ${item.price < product.price ? 
                                    `<span class="badge bg-label-success rounded-pill">On Sale</span>` : 
                                    `<span class="badge bg-label-primary rounded-pill">Standard Price</span>`}
                            </div>
                            ${variant ? `
                            <div class="mb-2">
                                <span class="text-muted">Variant:</span>
                                ${variant.color ? `<span class="me-2">Color: ${variant.color}</span>` : ''}
                                ${variant.size ? `<span>Size: ${variant.size}</span>` : ''}
                            </div>` : ''}
                            <div class="mb-2">
                                <span class="text-muted">Available Stock:</span>
                                <span>${stock}</span>
                            </div>
                            <input type="number" 
                                   class="form-control form-control-sm w-px-100 mt-4 cart-quantity-input ${quantityError ? 'is-invalid' : ''}" 
                                   value="${item.quantity}"
                                   min="1"
                                   data-cart-id="${item.id}"
                                   data-max-stock="${stock}"
                                   onchange="handleQuantityChange(${item.id}, this.value)">
                            <div class="invalid-feedback" id="quantity-error-${item.id}">
                                ${quantityError ? `Cannot order more than ${stock} items` : ''}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-md-end">
                                <button type="button" 
                                        class="btn-close btn-pinned" 
                                        aria-label="Close"
                                        onclick="removeCartItem(${item.id})"></button>
                                <div class="my-2 mt-md-4 mb-md-5">
                                    ${item.price < product.price ? 
                                        `<span class="text-primary">$${price}</span>
                                         <span class="text-body text-decoration-line-through">$${originalPrice}</span>` : 
                                        `<span class="text-primary">$${price}</span>`}
                                </div>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary mt-3"
                                        onclick="moveToWishlist(${item.product_id}, ${item.id})">
                                    Move to wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>`;
    });

    html += `</ul>`;

    // Add Load More button if needed
    if(cartHasMore) {
        html += `
        <div class="text-center mt-3" id="load-more-container">
            <button class="btn btn-primary" onclick="loadMoreCartItems()" id="load-more-btn">
                Load More
            </button>
        </div>`;
    }

    container.innerHTML = html;
    
    // Update order button state
    const orderButton = document.querySelector('.btn-next');
    orderButton.disabled = hasStockError;
}

//Newly added
async function loadMoreCartItems() {
    try {
        showLoader();
        currentCartPage++;
        const response = await axios.get('/user/get-cart-product', {
            params: {
                page: currentCartPage,
                per_page: 3
            }
        });
        
        if (response.status === 200 && response.data.status === 'success') {
            const cartData = response.data.data;
            cartHasMore = cartData.pagination.has_more;
            cartTotalItems = cartData.pagination.total_items;
            appendCartItems(cartData.cart_items);
            updateCartSummary(cartData.summary);
            updateLoadMoreButton();
        }
    } catch (error) {
        currentCartPage--;
        console.error('Error loading more items:', error);
        errorToast('Failed to load more items');
    } finally {
        hideLoader();
    }
}

//Newly added
function appendCartItems(newItems) {
    const container = document.getElementById('cart-items-list');
    let hasStockError = false;

    newItems.forEach(item => {
        const product = item.product;
        const variant = item.product_variant;
        const imagePath = product.image ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg';
        const price = parseFloat(item.price).toFixed(2);
        const originalPrice = parseFloat(product.price).toFixed(2);
        const clientName = product.client.lastName 
            ? `${product.client.firstName} ${product.client.lastName}`
            : product.client.firstName;

        const stock = variant ? variant.current_stock : product.current_stock;
        const quantityError = item.quantity > stock;
        if (quantityError) hasStockError = true;

        const itemHTML = `
        <li class="list-group-item p-4">
            <div class="d-flex gap-3">
                <div class="flex-shrink-0">
                    <img src="${imagePath}" alt="${product.name}" class="w-px-100" />
                </div>
                <div class="flex-grow-1">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="me-3">
                                <a href="/product-details/${product.id}" class="text-heading">${product.name}</a>
                            </h6>
                            <div class="mb-1 d-flex flex-wrap">
                                <span class="me-1">Sold by:</span>
                                <span class="me-1">${clientName}</span>
                                ${item.price < product.price ? 
                                    `<span class="badge bg-label-success rounded-pill">On Sale</span>` : 
                                    `<span class="badge bg-label-primary rounded-pill">Standard Price</span>`}
                            </div>
                            ${variant ? `
                            <div class="mb-2">
                                <span class="text-muted">Variant:</span>
                                ${variant.color ? `<span class="me-2">Color: ${variant.color}</span>` : ''}
                                ${variant.size ? `<span>Size: ${variant.size}</span>` : ''}
                            </div>` : ''}
                            <div class="mb-2">
                                <span class="text-muted">Available Stock:</span>
                                <span>${stock}</span>
                            </div>
                            <input type="number" 
                                   class="form-control form-control-sm w-px-100 mt-4 cart-quantity-input ${quantityError ? 'is-invalid' : ''}" 
                                   value="${item.quantity}"
                                   min="1"
                                   data-cart-id="${item.id}"
                                   data-max-stock="${stock}"
                                   onchange="handleQuantityChange(${item.id}, this.value)">
                            <div class="invalid-feedback" id="quantity-error-${item.id}">
                                ${quantityError ? `Cannot order more than ${stock} items` : ''}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-md-end">
                                <button type="button" 
                                        class="btn-close btn-pinned" 
                                        aria-label="Close"
                                        onclick="removeCartItem(${item.id})"></button>
                                <div class="my-2 mt-md-4 mb-md-5">
                                    ${item.price < product.price ? 
                                        `<span class="text-primary">$${price}</span>
                                         <span class="text-body text-decoration-line-through">$${originalPrice}</span>` : 
                                        `<span class="text-primary">$${price}</span>`}
                                </div>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary mt-3"
                                        onclick="moveToWishlist(${item.product_id}, ${item.id})">
                                    Move to wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>`;
        container.insertAdjacentHTML('beforeend', itemHTML);
    });

    // Update order button state
    const orderButton = document.querySelector('.btn-next');
    if (hasStockError) orderButton.disabled = true;
}

//Newly added
function updateLoadMoreButton() {
    const loadMoreContainer = document.getElementById('load-more-container');
    if (!loadMoreContainer) return;

    if (cartHasMore) {
        loadMoreContainer.style.display = 'block';
    } else {
        loadMoreContainer.style.display = 'none';
    }
}

async function handleQuantityChange(cartId, newQuantity) {
    const input = document.querySelector(`input[data-cart-id="${cartId}"]`);
    const errorElement = document.getElementById(`quantity-error-${cartId}`);
    const maxStock = parseInt(input.dataset.maxStock);
    const quantity = parseInt(newQuantity);

    // Clear previous errors
    input.classList.remove('is-invalid');
    errorElement.textContent = '';

    if (quantity > maxStock) {
        input.classList.add('is-invalid');
        errorElement.textContent = `Cannot order more than ${maxStock} items`;
        document.querySelector('.btn-next').disabled = true;
        return;
    }

    if (quantity < 1) {
        input.classList.add('is-invalid');
        errorElement.textContent = 'Quantity must be at least 1';
        document.querySelector('.btn-next').disabled = true;
        return;
    }

    // Proceed with valid update
    try {
        showLoader();
        const response = await axios.post('/user/cart/update', {
            cart_id: cartId,
            quantity: quantity
        });

        if (response.status === 200) {
            const cartResponse = await axios.get('/user/get-cart-product');
            renderCartItems(cartResponse.data.data.cart_items);
            updateCartSummary(cartResponse.data.data.summary);
            updateCartCount();
        }
    } catch (error) {
        errorToast('Failed to update cart item');
        console.error('Update error:', error);
    } finally {
        hideLoader();
    }
}

function updateCartSummary(summary) {
    const container = document.getElementById('cart-summary');
    const html = `
    <h6 class="mb-4">Price Details</h6>
    <dl class="row mb-0">
        <dt class="col-6 fw-normal text-heading">Bag Total</dt>
        <dd class="col-6 text-end">$${summary.subtotal.toFixed(2)}</dd>

        ${summary.coupon_discount ? `
        <dt class="col-6 fw-normal text-heading">Coupon Discount
            <button type="button" 
                class="btn-close ms-2 p-0" aria-label="Close" onclick="removeCoupon()" title="Remove coupon">
            </button>
        </dt>
        <dd class="col-6 text-end text-danger">
            -$${summary.coupon_discount.toFixed(2)}
        </dd>
        ` : ''}

        <dt class="col-6 fw-normal text-heading">Tax (${(summary.taxRate * 100).toFixed(0)}%)</dt>
        <dd class="col-6 text-end">$${summary.tax.toFixed(2)}</dd>

        <dt class="col-6 fw-normal text-heading">Order Total</dt>
        <dd class="col-6 text-end">$${summary.total.toFixed(2)}</dd>

        <dt class="col-6 fw-normal text-heading">Delivery Charges</dt>
        <dd class="col-6 text-end">
            <span class="badge bg-label-success rounded-pill">Free</span>
        </dd>
    </dl>
    <hr class="mx-n3 my-3" />
    <dl class="row mb-0 h6">
        <dt class="col-6 mb-0">Total Payable</dt>
        <dd class="col-6 text-end mb-0">$${summary.total.toFixed(2)}</dd>
    </dl>`;
    
    container.innerHTML = html;

    // Toggle coupon input visibility
    const couponSection = document.getElementById('coupon-section');
    if (summary.coupon_discount > 0) {
        couponSection.classList.add('d-none');
    } else {
        couponSection.classList.remove('d-none');
    }
}

async function removeCartItem(cartId) {
    try {
        showLoader();
        const response = await axios.post('/user/cart/remove', {
            cart_id: cartId
        });

        if (response.status === 200) {
            successToast('Item removed from cart');
            const cartResponse = await axios.get('/user/get-cart-product');
            renderCartItems(cartResponse.data.data.cart_items);
            updateCartSummary(cartResponse.data.data.summary);
            updateCartCount();
        }
    } catch (error) {
        errorToast('Failed to remove item');
        console.error('Remove cart error:', error);
    } finally {
        hideLoader();
    }
}

async function applyCoupon() {
    const errorSpan = document.getElementById('coupon_name-error');
    errorSpan.textContent = ''; // Clear previous errors
    const couponCode = document.getElementById('coupon-code').value.trim();

    if (!couponCode) {
        errorSpan.textContent = 'Please enter a coupon code';
        return;
    }

    try {
        showLoader();
        
        // Validate coupon status
        const validationResponse = await axios.get(`/user/validate-coupon/${couponCode}`);
        const couponData = validationResponse.data.data;
        
        // Client-side expiration check
        const currentDate = new Date();
        const expireDate = new Date(couponData.expire_date);
        
        if (expireDate < currentDate) {
            errorSpan.textContent = 'This coupon has expired';
            return;
        }

        // Apply the coupon
        const applyResponse = await axios.post('/user/cart/apply-coupon', {
            coupon_code: couponCode
        });

        if (applyResponse.status === 200) {
            successToast('Coupon applied successfully');
            const cartResponse = await axios.get('/user/get-cart-product');
            renderCartItems(cartResponse.data.data.cart_items);
            updateCartSummary(cartResponse.data.data.summary);
            errorSpan.textContent = ''; // Clear error on success
        }
    } catch (error) {
        const message = error.response?.data?.message || 'Failed to apply coupon';
        errorSpan.textContent = message;
    } finally {
        hideLoader();
    }
}

async function removeCoupon() {
    try {
        showLoader();
        await axios.post('/user/cart/remove-coupon');
        const response = await axios.get('/user/get-cart-product');
        
        if (response.status === 200 && response.data.status === 'success') {
            const cartData = response.data.data;
            renderCartItems(cartData.cart_items);
            updateCartSummary(cartData.summary);
            successToast('Coupon removed successfully');
        }
    } catch (error) {
        errorToast('Failed to remove coupon');
        console.error('Coupon removal error:', error);
    } finally {
        hideLoader();
    }
}

async function moveToWishlist(productId, cartId) {
    try {
        showLoader();
        
        // 1. Add to Wishlist
        const wishlistResponse = await axios.post('/user/store/wishlist-request', {
            id: productId
        });

        // 2. Remove from Cart only if wishlist add succeeded
        const cartResponse = await axios.post('/user/cart/remove', {
            cart_id: cartId
        });

        if (cartResponse.status === 200) {
            successToast('Item moved to wishlist');
            
            // Refresh cart data
            const cartData = await axios.get('/user/get-cart-product');
            renderCartItems(cartData.data.data.cart_items);
            updateCartSummary(cartData.data.data.summary);
            
            // Update counters
            await updateCartCount();
            await updateWishlistCount();
        }

    } catch (error) {
        if (error.response) {
            const status = error.response.status;
            const message = error.response.data?.message || 'Failed to move to wishlist';

            switch(status) {
                case 401:
                    window.location.href = '/user/login';
                    break;
                case 403:
                    errorToast(message || "Your wishlist limit is full (max 3 items)");
                    break;
                case 404:
                    errorToast(message || "Cart item not found");
                    break;
                case 409:
                    errorToast(message || "Item already exists in wishlist");
                    break;
                case 422:
                    errorToast(message || "Validation error");
                    break;
                case 500:
                    errorToast(message || "Server error - please try again later");
                    break;
                default:
                    errorToast("An unexpected error occurred");
            }
        } else if (error.request) {
            errorToast("Network error - check your internet connection");
        } else {
            errorToast("An unexpected error occurred");
        }
        console.error('Move to wishlist error:', error);
    } finally {
        hideLoader();
    }
}
</script>

