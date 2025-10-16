<section class="section-py bg-body first-section-pt">
  <div class="container">
    <div id="wizard-checkout" class="bs-stepper wizard-icons wizard-icons-example mt-2">
      <div class="bs-stepper-content border-top rounded-0">
        <form id="wizard-checkout-form" onSubmit="return false">
          <div id="checkout-confirmation" class="content">
            <div class="row mb-3">
              <div class="col-12 col-lg-8 mx-auto text-center mb-3">
                <h4 class="mt-2">Thank You! 😇</h4>
                <p>Your order <a href="javascript:void(0)" class="h6 mb-0" id="order-number"></a> has been placed!</p>
                <p class="email-info">
                  We sent an email to <a href="mailto:" class="h6 mb-0" id="customer-email"></a> 
                  with your order confirmation.
                </p>
                <p class="time-placed">
                  <span class="fw-medium"><i class="mdi mdi-clock-outline text-heading me-1"></i></span>
                  Time placed: <span id="order-time"></span>
                </p>
              </div>
              <div class="col-12">
                <ul class="list-group list-group-horizontal-md">
                  <li class="list-group-item flex-fill">
                    <h6 class="d-flex align-items-center gap-2">
                      <i class="mdi mdi-map-marker-outline"></i> Shipping
                    </h6>
                    <address class="text-body" id="shipping-address"></address>
                  </li>
                  <li class="list-group-item flex-fill">
                    <h6 class="d-flex align-items-center gap-2">
                      <i class="mdi mdi-credit-card-outline"></i> Billing Address
                    </h6>
                    <address class="text-body" id="billing-address"></address>
                  </li>
                  <li class="list-group-item flex-fill">
                    <h6 class="d-flex align-items-center gap-2"><i class="mdi mdi-ferry"></i> Shipping Method</h6>
                    <span class="fw-medium text-body">Preferred Method:</span>
                    <p class="text-body mb-0 mt-3">
                      Standard Delivery<br />(Normally 3-4 business days)
                    </p>
                  </li>
                </ul>
              </div>
            </div>

            <div class="row">
              <div class="col-xl-9 mb-3 mb-xl-0">
                <h4 class="mb-2">Order Summary (<span class="text-danger" id="order-item-count">0</span> Items)</h4>
                <hr>
                <ul class="list-group" id="order-items-container"></ul>
                <div id="order-load-more-container" class="text-center mt-3"></div>
              </div>
              
              <div class="col-xl-3">
                <div class="border rounded p-3">
                  <h6>Price Details</h6>
                  <dl class="row mb-0" id="price-details"></dl>
                  <hr class="mx-n3 mt-1">
                  <dl class="row mb-0">
                    <dt class="col-6 h6 mb-0">Total</dt>
                    <dd class="col-6 h6 text-end mb-0" id="total-amount"></dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", async function () {
    let currentOrderPage = 1;
    let orderHasMore = false;
    let orderTotalItems = 0;
    let orderId;

    async function loadOrderItems(page = 1, append = false) {
        try {
            showLoader();
            const response = await axios.get(`/user/get-order-info`, {
                params: {
                    order_id: orderId,
                    page: page,
                    per_page: 3
                }
            });

            if (response.data.status === 'success') {
                const order = response.data.data;
                orderHasMore = order.pagination.has_more;
                orderTotalItems = order.pagination.total_items;

                 document.getElementById('order-item-count').textContent = orderTotalItems;

                // Update order info on first load
                if (!append) {
                    updateOrderDetails(order);
                    updatePriceDetails(order);
                }

                renderOrderItems(order.order_items, append);
                updateLoadMoreButton();

                // Add scroll after 6 items
                if (currentOrderPage >= 2) {
                    document.getElementById('order-items-container').style.maxHeight = '600px';
                    document.getElementById('order-items-container').style.overflowY = 'auto';
                }
            }
        } catch (error) {
            console.error('Order loading failed:', error);
            errorToast('Failed to load order details');
        } finally {
            hideLoader();
        }
    }

    function updateOrderDetails(order) {
        document.getElementById('order-number').textContent = `#${order.order_number}`;
        document.getElementById('customer-email').textContent = order.customer.email;
        document.getElementById('customer-email').href = `mailto:${order.customer.email}`;
        document.getElementById('order-time').textContent = 
            `${new Date(order.created_at).toLocaleDateString()} ${new Date(order.created_at).toLocaleTimeString()}`;

        // Shipping Address
        const shippingAddress = order.shipping_address;
        document.getElementById('shipping-address').innerHTML = `
            ${shippingAddress.name}<br>
            ${shippingAddress.address1}<br>
            ${shippingAddress.address2 ? shippingAddress.address2 + '<br>' : ''}
            ${shippingAddress.city?.name}, ${shippingAddress.county?.name}<br>
            ${shippingAddress.zip_code}, ${shippingAddress.country?.name}<br>
            <span class="fw-medium">${shippingAddress.phone}</span>
        `;

        // Billing Address
        const customer = order.customer;
        document.getElementById('billing-address').innerHTML = `
            ${customer.firstName} ${customer.lastName || ''}<br>
            ${customer.address1}<br>
            ${customer.address2 ? customer.address2 + '<br>' : ''}
            ${customer.city?.name}, ${customer.county?.name}<br>
            ${customer.zip_code}, ${customer.country?.name}<br>
            <span class="fw-medium">${customer.mobile}</span>
        `;
    }

    function renderOrderItems(items, append = false) {
        const container = document.getElementById('order-items-container');
        let html = items.map(item => {
            const product = item.product;
            const variant = item.variant;
            const imagePath = product?.image ? `/upload/product/small/${product.image}` : '/upload/no_image.jpg';
            const clientName = product?.client?.lastName 
                ? `${product.client.firstName} ${product.client.lastName}` 
                : product?.client?.firstName ?? 'Unknown Seller';

            return `
                <li class="list-group-item">
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0">
                            <img src="${imagePath}" 
                                alt="${product?.name ?? 'Unknown Product'}" 
                                class="w-px-75" style="object-fit: cover">
                        </div>
                        <div class="flex-grow-1">
                            <div class="row">
                                <div class="col-md-8 pt-2">
                                    <a href="javascript:void(0)" class="text-heading mt-1">
                                        <h6>${product?.name ?? 'Unknown Product'}</h6>
                                    </a>
                                    ${variant ? `
                                    <div class="text-muted small">
                                        <strong>Variant:</strong> 
                                        ${variant.color ? `Color: ${variant.color}` : 'N/A'} 
                                        ${variant.size ? `Size: ${variant.size}` : 'N/A'}
                                    </div>` : ''}
                                    
                                    <div class="mb-1 d-flex flex-wrap">
                                        <span class="me-1">Sold by:</span>
                                        <a href="javascript:void(0)" class="me-1">${clientName}</a>
                                        <span class="badge ${product?.current_stock > 0 ? 
                                            'bg-label-success' : 'bg-label-danger'} rounded-pill">
                                            ${product?.current_stock > 0 ? 'In Stock' : 'Out of Stock'}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-md-end">
                                        <div class="my-2 my-lg-4">
                                            <span class="text-primary">$${parseFloat(item.unit_price).toFixed(2)}</span>
                                            <span class="text-body">x ${item.quantity}</span><br>
                                            <span class="text-body">Total: $${parseFloat(item.total_price).toFixed(2)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            `;
        }).join('');

        if (append) {
            container.insertAdjacentHTML('beforeend', html);
        } else {
            container.innerHTML = html;
        }
    }

    function updatePriceDetails(order) {
        const priceDetails = document.getElementById('price-details');
                   
        let deliveryCharges = parseFloat(order.delivery_fee) > 0
            ? formatCurrency(order.delivery_fee)
            : `<span class="badge bg-label-success rounded-pill">Free</span>`;

        priceDetails.innerHTML = `
            <dt class="col-6 fw-normal">Subtotal</dt>
            <dd class="col-6 text-end">$${parseFloat(order.subtotal).toFixed(2)}</dd>

            ${order.coupon_discount > 0 ? `
            <dt class="col-6 fw-normal">Coupon Discount</dt>
            <dd class="col-6 text-end text-danger">-$${parseFloat(order.coupon_discount).toFixed(2)}</dd>
            ` : ''}
            
            <dt class="col-6 fw-normal">Tax</dt>
            <dd class="col-6 text-end">$${parseFloat(order.tax).toFixed(2)}</dd>
            
            <dt class="col-6 fw-normal">Delivery Charge</dt>
            <dd class="col-6 text-end">
                <span class="badge bg-label-success rounded-pill">${deliveryCharges}</span>
            </dd>
        `;

        document.getElementById('total-amount').textContent = `$${parseFloat(order.payable_amount).toFixed(2)}`;
    }

    function updateLoadMoreButton() {
        const loadMoreContainer = document.getElementById('order-load-more-container');
        loadMoreContainer.innerHTML = orderHasMore ? `
            <button class="btn btn-primary" onclick="loadMoreItems()">
                Load More
            </button>
        ` : '';
    }

    window.loadMoreItems = async function() {
        currentOrderPage++;
        await loadOrderItems(currentOrderPage, true);
    };

    function formatCurrency(amount) {
        return '$' + parseFloat(amount || 0).toFixed(2);
    }

    // Initial load
    try {
        const url = window.location.pathname;
        const segments = url.split('/');
        orderId = segments[segments.length - 1];
        await loadOrderItems(1);
    } catch (error) {
        console.error('Initial load failed:', error);
        errorToast('Failed to load order details');
    }
});
</script>