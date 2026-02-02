<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header header-elements bg-light">
                    <h5 class="fw-semibold mb-0">Delivery Notification Details</h5>
                    <div class="header-elements">
                        <a href="{{ route('delivery.notifications') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-arrow-back"></i> Back to Notifications
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" id="notificationId">
                    
                    <!-- Loading placeholder -->
                    <div id="loading-placeholder" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading notification details...</p>
                    </div>

                    <!-- Notification Details Container -->
                    <div id="notification-details-container" class="d-none">
                        <!-- NEW: Accept Order Section (Only shown when delivery_person_id is null) -->
                        <div id="accept-order-section" class="d-none">
                            <div class="row mb-4">
                                <div class="col-xl-12">
                                    <div class="card border border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <i class="bx bx-bell"></i> Delivery Request Available
                                            </h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                                <i class="bx bx-info-circle"></i>
                                                This delivery request is available for acceptance. You have until the deadline to accept it.
                                            </div>
                                            <div class="row justify-content-center">
                                                <div class="col-md-8">
                                                    <div class="card mb-4">
                                                        <div class="card-body">
                                                            <h6 class="card-title text-muted mb-3">Acceptance Deadline</h6>
                                                            <div class="d-flex justify-content-center align-items-center">
                                                                <i class="bx bx-time bx-lg text-warning me-3"></i>
                                                                <div>
                                                                    <h4 id="accept-deadline-display" class="mb-0 fw-bold"></h4>
                                                                    <small class="text-muted">You must accept before this time</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-center">
                                                <button type="button" class="btn btn-success btn-lg px-5" onclick="showAcceptConfirmation()">
                                                    <i class="bx bx-check-circle"></i> Accept Order
                                                </button>
                                            </div>
                                            <div class="mt-3 text-muted small">
                                                <i class="bx bx-info-circle"></i>
                                                Accepting this order will assign it to you for delivery.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Person Info Section (Shown when delivery_person_id is NOT null) -->
                        <div id="delivery-person-section" class="d-none">
                            <div class="row mb-4">
                                <div class="col-xl-12">
                                    <div class="card border border-success">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">
                                                <i class="bx bx-user-check"></i> Delivery Person Assigned
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-lg me-3">
                                                            <span class="avatar-initial rounded-circle bg-primary">
                                                                <i class="bx bx-user"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h5 id="delivery-person-name" class="mb-1"></h5>
                                                            <p class="text-muted mb-0">
                                                                <i class="bx bx-id-card"></i> Delivery Person ID: 
                                                                <span id="delivery-person-id" class="fw-semibold"></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                    <span class="badge bg-success fs-6">
                                                        <i class="bx bx-check-shield"></i> Assigned
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Summary Section -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Delivery Information</h6>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Order Tracking:</span>
                                            <span id="order-tracking" class="badge bg-info ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Order Number:</span>
                                            <span id="order-number" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Meal Type:</span>
                                            <span id="meal-type" class="badge bg-warning text-dark ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Delivery Date:</span>
                                            <span id="delivery-date" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Delivery Time:</span>
                                            <span id="delivery-time" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Delivery Charge:</span>
                                            <span id="delivery-charge" class="fw-bold text-success ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Distance:</span>
                                            <span id="distance-km" class="ms-2"></span> km
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Distance Category:</span>
                                            <span id="distance-category" class="ms-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Status Information</h6>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Delivery Status:</span>
                                            <span id="delivery-status" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Payment Status:</span>
                                            <span id="payment-status" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Notification Type:</span>
                                            <span id="notification-type" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Notification Time:</span>
                                            <span id="notification-time" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Accept Deadline:</span>
                                            <span id="accept-deadline" class="ms-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Restaurant and Customer Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Restaurant Details</h6>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Name:</span>
                                            <span id="client-name" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Mobile:</span>
                                            <span id="client-mobile" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Restaurant Address:</span>
                                            <div id="client-address" class="ms-2 text-muted"></div>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Location:</span>
                                            <span id="client-location" class="ms-2 text-muted"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Customer Details</h6>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Name:</span>
                                            <span id="customer-name" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Mobile:</span>
                                            <span id="customer-mobile" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Email:</span>
                                            <span id="customer-email" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Delivery Address:</span>
                                            <div id="delivery-address" class="ms-2 text-muted"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Items Section -->
                        <div class="row mb-4">
                            <div class="col-xl-12">
                                <div class="card border">
                                    <h5 class="card-header pb-3 border-bottom mb-3">Order Items</h5>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product Name</th>
                                                        <th>Quantity</th>
                                                        <th>Meal Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="order-items-body">
                                                    <!-- Items will be populated here -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            <span class="fw-semibold">Total Items:</span>
                                            <span id="total-items" class="badge bg-primary ms-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="row" id="action-buttons-section">
                            <div class="col-xl-12">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-3 text-muted">Delivery Actions</h6>
                                        <div id="action-buttons" class="d-flex justify-content-center gap-3">
                                            <!-- Action buttons will be populated here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="row mt-4">
                            <div class="col-xl-12">
                                <div class="card border">
                                    <h5 class="card-header pb-3 border-bottom mb-3">Additional Information</h5>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <span class="fw-semibold">Notification Title:</span>
                                            <span id="notification-title" class="ms-2"></span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-semibold">Notification Message:</span>
                                            <span id="notification-message" class="ms-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
    await loadNotificationDetails();
});

let notificationData = null;
let deliveryChargeLedgerId = null;
let currentDeliveryStatus = null;
let notificationId = null;

async function loadNotificationDetails() {
    showLoader();
    try {
        // Get notification ID from URL
        const segments = window.location.pathname.split('/');
        notificationId = segments[segments.length - 1];
        document.getElementById('notificationId').value = notificationId;
        
        // Fetch notification details
        const response = await axios.get(`/delivery/get/notification/details/${notificationId}`);
        
        if (response.status === 200 && response.data.status === 'success') {
            notificationData = response.data.data;
            console.log('Notification Data:', notificationData);
            
            // Show the details container
            document.getElementById('loading-placeholder').classList.add('d-none');
            document.getElementById('notification-details-container').classList.remove('d-none');
            
            // Store necessary data globally
            deliveryChargeLedgerId = notificationData.data?.delivery_charge_ledger_id;
            currentDeliveryStatus = notificationData.data?.delivery_status || 'pending';
            
            // Populate all fields
            populateNotificationDetails();
            setupActionButtons();
            
            // Show appropriate section based on delivery_person_id
            showDeliveryPersonSection();
        } else {
            errorToast(response.data.message || "Failed to fetch notification details.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// Function to show accept confirmation popup
function showAcceptConfirmation() {
    Swal.fire({
        title: 'Accept Delivery?',
        text: 'Are you sure you want to accept this delivery?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Accept It!',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            acceptDelivery();
        }
    });
}

// Show/Hide sections based on delivery_person_id
function showDeliveryPersonSection() {
    const acceptOrderSection = document.getElementById('accept-order-section');
    const deliveryPersonSection = document.getElementById('delivery-person-section');
    const acceptDeadlineDisplay = document.getElementById('accept-deadline-display');
    
    const deliveryPersonId = notificationData.data?.delivery_person_id;
    const acceptDeadline = notificationData.data?.accept_deadline;
    
    if (deliveryPersonId === null || deliveryPersonId === undefined) {
        // Show Accept Order section
        acceptOrderSection.classList.remove('d-none');
        deliveryPersonSection.classList.add('d-none');
        
        // Format and display accept deadline
        if (acceptDeadline) {
            const deadline = new Date(acceptDeadline);
            acceptDeadlineDisplay.textContent = deadline.toLocaleString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }
    } else {
        // Show Delivery Person Info section
        acceptOrderSection.classList.add('d-none');
        deliveryPersonSection.classList.remove('d-none');
        
        // Display delivery person info
        const deliveryPersonName = document.getElementById('delivery-person-name');
        const deliveryPersonIdSpan = document.getElementById('delivery-person-id');
        
        if (notificationData.data?.delivery_person?.name) {
            deliveryPersonName.textContent = notificationData.data.delivery_person.name;
        } else {
            deliveryPersonName.textContent = 'Delivery Person Assigned';
        }
        
        deliveryPersonIdSpan.textContent = deliveryPersonId;
    }
}

async function acceptDelivery() {
    try {
        showLoader();
        const res = await axios.post('/delivery/accept/meal/delivery', {
            delivery_charge_ledger_id: deliveryChargeLedgerId
        });
        
        if (res.status === 200 && res.data.status === 'success') {
            // Show success popup
            Swal.fire({
                title: 'Success!',
                text: res.data.message || 'Delivery accepted successfully',
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-success'
                },
                buttonsStyling: false
            }).then(() => {
                // Hide the accept button and show a temporary success message
                const acceptButton = document.querySelector('#accept-order-section .btn-success');
                if (acceptButton) {
                    acceptButton.disabled = true;
                    acceptButton.innerHTML = '<i class="bx bx-check"></i> Accepted';
                    acceptButton.classList.remove('btn-success');
                    acceptButton.classList.add('btn-secondary');
                    
                    // Show a success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'alert alert-success mt-3';
                    successMessage.innerHTML = '<i class="bx bx-check-circle"></i> Delivery accepted successfully! Refreshing page...';
                    
                    const cardBody = document.querySelector('#accept-order-section .card-body');
                    cardBody.appendChild(successMessage);
                    
                    // Reload the page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            });
        } else {
            // Show error popup
            Swal.fire({
                title: 'Error!',
                text: res.data.message || "Failed to accept delivery",
                icon: 'error',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });
        }
    } catch (error) {
        let errorMessage = 'An unexpected error occurred';
        
        if (error.response) {
            const status = error.response.status;
            const message = error.response.data.message || 'An unexpected error occurred';

            if (status === 400) {
                errorMessage = error.response.data.message || "Delivery is already accepted";
            } else if (status === 404) {
                errorMessage = error.response.data.message || "Delivery record not found";
            } else if (status === 500) {
                errorMessage = 'Server error: ' + message;
            } else {
                errorMessage = message;
            }
        } else {
            errorMessage = 'Error: ' + error.message;
        }
        
        // Show error popup
        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
    } finally {
        hideLoader();
    }
}

function populateNotificationDetails() {
    if (!notificationData) return;
    
    // Extract data from notification
    const data = notificationData.data || {};
    const deliveryDetails = data.delivery_details || {};
    const clientAddress = data.client_address || {};
    const deliveryAddress = data.delivery_address || {};
    const customerDetails = data.customer_details || {};
    
    // Basic Information
    document.getElementById('order-tracking').textContent = data.order_tracking || 'N/A';
    document.getElementById('order-number').textContent = data.order_number || 'N/A';
    document.getElementById('meal-type').textContent = deliveryDetails.meal_type || 'N/A';
    document.getElementById('delivery-date').textContent = deliveryDetails.delivery_date || 'N/A';
    
    // Format delivery time
    if (deliveryDetails.meal_time) {
        const timeParts = deliveryDetails.meal_time.split(':');
        if (timeParts.length >= 2) {
            const hours = parseInt(timeParts[0]);
            const minutes = timeParts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            document.getElementById('delivery-time').textContent = `${displayHours}:${minutes} ${ampm}`;
        } else {
            document.getElementById('delivery-time').textContent = deliveryDetails.meal_time;
        }
    }
    
    // Delivery charge
    document.getElementById('delivery-charge').textContent = deliveryDetails.delivery_charge ? 
        `$${parseFloat(deliveryDetails.delivery_charge).toFixed(2)}` : '$0.00';
    
    // Distance
    document.getElementById('distance-km').textContent = deliveryDetails.distance_km || '0.00';
    document.getElementById('distance-category').textContent = deliveryDetails.distance_category || 'N/A';
    
    // Status information
    updateStatusBadge('delivery-status', data.delivery_status || 'pending');
    updateStatusBadge('payment-status', data.payment_status || 'due');
    document.getElementById('notification-type').textContent = data.notification_type || 'N/A';
    
    // Format dates
    if (data.notification_time) {
        const notificationTime = new Date(data.notification_time);
        document.getElementById('notification-time').textContent = notificationTime.toLocaleString();
    }
    
    if (data.accept_deadline) {
        const deadline = new Date(data.accept_deadline);
        document.getElementById('accept-deadline').textContent = deadline.toLocaleString();
    }
    
    // Client/Restaurant Information
    document.getElementById('client-name').textContent = data.client_name || 'N/A';
    document.getElementById('client-mobile').textContent = data.client_mobile || 'N/A';
    
    // Client Address
    const clientAddressHtml = `
        ${clientAddress.address1 || ''}<br>
        ${clientAddress.address2 ? clientAddress.address2 + '<br>' : ''}
        ${clientAddress.city || ''}, ${clientAddress.county || ''}<br>
        ${clientAddress.country || ''} ${clientAddress.zip_code || ''}
    `;
    document.getElementById('client-address').innerHTML = clientAddressHtml;
    
    // Client Location
    if (clientAddress.latitude && clientAddress.longitude) {
        document.getElementById('client-location').textContent = 
            `Lat: ${clientAddress.latitude}, Lng: ${clientAddress.longitude}`;
    }
    
    // Customer Information
    document.getElementById('customer-name').textContent = customerDetails.name || 'N/A';
    document.getElementById('customer-mobile').textContent = customerDetails.mobile || 'N/A';
    document.getElementById('customer-email').textContent = customerDetails.email || 'N/A';
    
    // Delivery Address
    const deliveryAddressHtml = `
        ${deliveryAddress.name ? deliveryAddress.name + '<br>' : ''}
        ${deliveryAddress.address1 || ''}<br>
        ${deliveryAddress.address2 ? deliveryAddress.address2 + '<br>' : ''}
        ${deliveryAddress.city || ''}, ${deliveryAddress.county || ''}<br>
        ${deliveryAddress.country || ''} ${deliveryAddress.zip_code || ''}
        ${deliveryAddress.phone ? '<br>Phone: ' + deliveryAddress.phone : ''}
    `;
    document.getElementById('delivery-address').innerHTML = deliveryAddressHtml;
    
    // Order Items
    const items = data.items || [];
    const itemsBody = document.getElementById('order-items-body');
    itemsBody.innerHTML = '';
    
    items.forEach((item, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.product_name || 'N/A'}</td>
            <td>${item.quantity || 1}</td>
            <td>${item.meal_time ? formatTime(item.meal_time) : 'N/A'}</td>
        `;
        itemsBody.appendChild(row);
    });
    
    document.getElementById('total-items').textContent = items.length;
    
    // Additional Information
    document.getElementById('notification-title').textContent = notificationData.title || 'N/A';
    document.getElementById('notification-message').textContent = notificationData.message || 'N/A';
}

function updateStatusBadge(elementId, status) {
    const element = document.getElementById(elementId);
    const statusMap = {
        'pending': { class: 'badge bg-secondary', text: 'Pending' },
        'accept_order': { class: 'badge bg-info', text: 'Order Accepted' },
        'preparing': { class: 'badge bg-primary', text: 'Preparing' },
        'ready_for_pickup': { class: 'badge bg-warning text-dark', text: 'Ready for Pickup' },
        'picked_up': { class: 'badge bg-warning text-dark', text: 'Picked Up' },
        'on_the_way': { class: 'badge bg-primary text-white', text: 'On the Way' },
        'arrived': { class: 'badge bg-info text-white', text: 'Arrived' },
        'delivered': { class: 'badge bg-success', text: 'Delivered' },
        'cancelled': { class: 'badge bg-danger', text: 'Cancelled' },
        'due': { class: 'badge bg-warning text-dark', text: 'Due' },
        'paid': { class: 'badge bg-success', text: 'Paid' }
    };
    
    const statusInfo = statusMap[status] || { class: 'badge bg-secondary', text: status };
    element.innerHTML = `<span class="${statusInfo.class}">${statusInfo.text}</span>`;
}

function formatTime(timeString) {
    if (!timeString) return 'N/A';
    try {
        const timeParts = timeString.split(':');
        if (timeParts.length >= 2) {
            const hours = parseInt(timeParts[0]);
            const minutes = timeParts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return `${displayHours}:${minutes} ${ampm}`;
        }
        return timeString;
    } catch (e) {
        return timeString;
    }
}

function setupActionButtons() {
    const actionButtonsDiv = document.getElementById('action-buttons');
    const data = notificationData.data || {};
    const deliveryStatus = data.delivery_status || 'pending';
    const deliveryPersonId = data.delivery_person_id;
    
    let buttonsHTML = '';
    
    // Only show action buttons if delivery is already assigned to someone
    if (deliveryPersonId !== null && deliveryPersonId !== undefined) {
        if (['ready_for_pickup', 'picked_up', 'on_the_way', 'arrived'].includes(deliveryStatus)) {
            // Get the next status for the button label
            const nextStatus = getNextDeliveryStatus(deliveryStatus);
            const buttonLabel = getStatusButtonLabel(deliveryStatus);
            
            buttonsHTML = `
                <button type="button" class="btn btn-primary btn-lg" onclick="handleUpdateDeliveryStatus()">
                    <i class="bx bx-edit"></i> ${buttonLabel}
                </button>
            `;
        } else if (deliveryStatus === 'delivered') {
            buttonsHTML = `
                <button type="button" class="btn btn-success btn-lg" disabled>
                    <i class="bx bx-check-circle"></i> Delivered Successfully
                </button>
            `;
        } else if (deliveryStatus === 'cancelled') {
            buttonsHTML = `
                <button type="button" class="btn btn-danger btn-lg" disabled>
                    <i class="bx bx-x-circle"></i> Delivery Cancelled
                </button>
            `;
        } else if (deliveryStatus === 'accept_order') {
            buttonsHTML = `
                <button type="button" class="btn btn-info btn-lg" disabled>
                    <i class="bx bx-user-check"></i> Order Accepted - Awaiting Preparation
                </button>
            `;
        } else if (deliveryStatus === 'preparing') {
            buttonsHTML = `
                <button type="button" class="btn btn-primary btn-lg" disabled>
                    <i class="bx bx-time"></i> Order Being Prepared
                </button>
            `;
        } else {
            buttonsHTML = '<p class="text-muted">No actions available for current status.</p>';
        }
    } else {
        // If delivery is not assigned, don't show action buttons in this section
        buttonsHTML = '<p class="text-muted">Accept the order to see available actions.</p>';
    }
    
    actionButtonsDiv.innerHTML = buttonsHTML;
}

function getNextDeliveryStatus(currentStatus) {
    const statusFlow = {
        'ready_for_pickup': 'picked_up',
        'picked_up': 'on_the_way',
        'on_the_way': 'arrived',
        'arrived': 'delivered'
    };
    
    return statusFlow[currentStatus] || null;
}

function getStatusButtonLabel(currentStatus) {
    const buttonLabels = {
        'ready_for_pickup': 'Mark as Picked Up',
        'picked_up': 'Mark as On the Way',
        'on_the_way': 'Mark as Arrived',
        'arrived': 'Mark as Delivered'
    };
    
    return buttonLabels[currentStatus] || 'Update Status';
}

async function handleUpdateDeliveryStatus() {
    try {
        // First check the current delivery status using deliveryChargeLedgerId
        const checkResponse = await axios.post(`/delivery/check/delivery-status/${deliveryChargeLedgerId}`);
        
        if (checkResponse.data.status === 'success') {
            const currentStatus = checkResponse.data.data.delivery_status;
            const nextStatus = getNextDeliveryStatus(currentStatus);
            
            if (!nextStatus) {
                errorToast('No further status updates allowed.');
                return;
            }
            
            // Ask for confirmation
            const confirmMessage = `Are you sure you want to update delivery status to "${getStatusButtonLabel(currentStatus)}"?`;
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            showLoader();
            
            // Update the delivery status using deliveryChargeLedgerId
            const updateResponse = await axios.post(`/delivery/update/delivery-status/${deliveryChargeLedgerId}`, {
                delivery_status: nextStatus,
                notes: ''
            });
            
            if (updateResponse.data.status === 'success') {
                successToast(updateResponse.data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                errorToast(updateResponse.data.message || 'Failed to update delivery status.');
                hideLoader();
            }
        } else {
            errorToast(checkResponse.data.message || 'Failed to check delivery status.');
        }
    } catch (error) {
        handleError(error);
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
                message = serverMessage || 'Notification not found';
                break;
            case 422:
                message = serverMessage || 'Validation error';
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

<style>
.badge {
    font-size: 0.85em;
    padding: 0.35em 0.65em;
}
.card {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
}
.card-header {
    border-bottom: 1px solid #e0e0e0;
    background-color: #f8f9fa;
}
.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}
.avatar-initial {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}
.avatar-lg {
    width: 60px;
    height: 60px;
}
/* SweetAlert2 custom styles */
.swal2-popup {
    border-radius: 10px;
}
.swal2-title {
    font-size: 1.3rem !important;
}
.swal2-icon {
    font-size: 1.5rem !important;
}
.swal2-actions {
    margin-top: 1.5rem;
}
</style>