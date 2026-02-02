<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Delivery Person Details</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.delivery.persons') }}">Delivery Persons</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loading-spinner" class="text-center d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Main Content -->
    <div id="content-area">
        <div class="row">
            <!-- Left Column: Profile & Personal Info -->
            <div class="col-xl-8 col-lg-7 col-md-12 order-1 order-lg-0">
                <!-- Profile Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>Profile Information
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="javascript:void(0);" class="btn btn-sm btn-light" onclick="refreshDetails()" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                            <a href="" id="edit-link" class="btn btn-sm btn-light" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="position-relative d-inline-block">
                                    <img class="img-thumbnail rounded-circle border-primary border-3" 
                                         src="" 
                                         id="profile-image"
                                         height="150" 
                                         width="150" 
                                         alt="Profile Image"
                                         style="object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle">
                                        <i class="fas fa-check text-white"></i>
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <span class="badge bg-primary rounded-pill p-2">Delivery Person</span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h3 class="mb-1" id="full-name"></h3>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-envelope me-2"></i>
                                            <span id="email"></span>
                                            <span id="email-badge" class="ms-2"></span>
                                        </p>
                                        <p class="text-muted">
                                            <i class="fas fa-phone me-2"></i>
                                            <span id="phone"></span>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <div class="mb-2">
                                            <span class="badge bg-label-info fs-6" id="account-status"></span>
                                        </div>
                                        <button id="toggle-status-btn" class="btn btn-sm" onclick="toggleAccountStatus()">
                                            <i class="fas fa-power-off me-1"></i>
                                            <span id="toggle-btn-text"></span>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Stats -->
                                <div class="row mt-4">
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-primary">
                                                <span class="avatar-initial rounded"><i class="fas fa-box"></i></span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Total Orders</small>
                                                <h6 class="mb-0" id="total-orders">0</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-success">
                                                <span class="avatar-initial rounded"><i class="fas fa-check-circle"></i></span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Completed</small>
                                                <h6 class="mb-0" id="completed-orders">0</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-warning">
                                                <span class="avatar-initial rounded"><i class="fas fa-money-bill-wave"></i></span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Total Earnings</small>
                                                <h6 class="mb-0">$<span id="total-earnings">0.00</span></h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm flex-shrink-0 me-2 bg-label-danger">
                                                <span class="avatar-initial rounded"><i class="fas fa-clock"></i></span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Pending</small>
                                                <h6 class="mb-0">$<span id="pending-earnings">0.00</span></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Details Card -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card me-2"></i>Personal Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Full Name</label>
                                <div class="form-control-static fw-semibold" id="personal-name"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Email Address</label>
                                <div class="form-control-static" id="personal-email"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Mobile Number</label>
                                <div class="form-control-static" id="personal-phone"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Member Since</label>
                                <div class="form-control-static">
                                    <i class="fas fa-calendar me-2"></i>
                                    <span id="registration-date"></span>
                                    <small class="text-muted ms-2" id="registration-time"></small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Email Verification</label>
                                <div id="email-verification-status"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Account Status</label>
                                <div id="account-verification-status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Documents & Address -->
            <div class="col-xl-4 col-lg-5 col-md-12 order-0 order-lg-1">
                <!-- Documents Card -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-file-contract me-2"></i>Documents
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="alert alert-info py-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Click on images to view full size
                            </div>
                            
                            <div class="row g-3">
                                <!-- License Image -->
                                <div class="col-12">
                                    <label class="form-label d-block text-start">License Document</label>
                                    <div class="document-preview position-relative">
                                        <img class="img-fluid rounded border" 
                                             src="" 
                                             id="license-image"
                                             alt="License Document"
                                             style="cursor: pointer; height: 180px; object-fit: cover;"
                                             onclick="openImageModal('license-image')">
                                        <div class="mt-2">
                                            <a href="#" id="download-license" class="btn btn-sm btn-outline-primary w-100">
                                                <i class="fas fa-download me-2"></i>Download License
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- NID Image -->
                                <div class="col-12">
                                    <label class="form-label d-block text-start">NID Document</label>
                                    <div class="document-preview position-relative">
                                        <img class="img-fluid rounded border" 
                                             src="" 
                                             id="nid-image"
                                             alt="NID Document"
                                             style="cursor: pointer; height: 180px; object-fit: cover;"
                                             onclick="openImageModal('nid-image')">
                                        <div class="mt-2">
                                            <a href="#" id="download-nid" class="btn btn-sm btn-outline-primary w-100">
                                                <i class="fas fa-download me-2"></i>Download NID
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning py-2">
                            <small>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Documents must be verified before activating account
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Address & Vehicle Card -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>Location & Vehicle
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Address Details -->
                        <div class="mb-4">
                            <h6 class="mb-3">
                                <i class="fas fa-home me-2"></i>Address Details
                            </h6>
                            <div class="address-details">
                                <p class="mb-2" id="full-address"></p>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">Country</small>
                                        <div class="fw-semibold" id="country"></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">County</small>
                                        <div class="fw-semibold" id="county"></div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <small class="text-muted">City</small>
                                        <div class="fw-semibold" id="city"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicle Details -->
                        <div class="vehicle-details" id="vehicle-section">
                            <h6 class="mb-3">
                                <i class="fas fa-car me-2"></i>Vehicle Information
                            </h6>
                            <div id="vehicle-info">
                                <!-- Vehicle details will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Earnings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Recent Earnings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="earnings-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="earnings-body">
                                <!-- Earnings will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3" id="no-earnings" style="display: none;">
                        <div class="py-5">
                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No earnings found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modal-image" src="" class="img-fluid" alt="Document">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="modal-download" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let deliveryPersonId = null;
    let currentStatus = null;

function capitalizeWords(str) {
    if (!str) return '';
    return str
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

    document.addEventListener("DOMContentLoaded", function () {
        deliveryPersonId = getDeliveryPersonIdFromUrl();
        getDeliveryPersonDetails();
    });

    async function getDeliveryPersonDetails() {
        showLoader();
        try {
            let res = await axios.get("/admin/get/delivery-person/details/" + deliveryPersonId);
            
            if (res.status === 200 && res.data.status === 'success') {
                const data = res.data.data;
                currentStatus = data.status;
                console.log('-------',data);
                
                // Update edit link
                document.getElementById('edit-link').href = `/admin/delivery/edit/${data.id}`;
                
                // Profile Section
                const profileImage = data.image 
                    ? `/upload/delivery-profile/large/${data.image}`
                    : `/upload/no_image.jpg`;
                
                document.getElementById('profile-image').src = profileImage;
                
                const fullName = `${capitalizeWords(data.firstName || '')} ${capitalizeWords(data.lastName || '')}`.trim();
                document.getElementById('full-name').textContent = fullName;
                document.getElementById('personal-name').textContent = fullName;
                
                document.getElementById('email').textContent = data.email;
                document.getElementById('personal-email').textContent = data.email;
                
                // Phone with badge
                if (data.mobile) {
                    const phoneBadge = `<span class="badge bg-success">${data.mobile}</span>`;
                    document.getElementById('phone').innerHTML = phoneBadge;
                    document.getElementById('personal-phone').innerHTML = phoneBadge;
                } else {
                    document.getElementById('phone').innerHTML = '<span class="badge bg-secondary">Not provided</span>';
                    document.getElementById('personal-phone').innerHTML = '<span class="badge bg-secondary">Not provided</span>';
                }
                
                // Account Status
                const statusBadge = data.status == 1 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>';
                
                document.getElementById('account-status').innerHTML = statusBadge;
                document.getElementById('account-verification-status').innerHTML = statusBadge;
                
                // Update toggle button
                updateToggleButton(data.status);
                
                // Email Verification
                const emailBadge = data.is_email_verified == 1
                    ? '<span class="badge bg-success">Verified</span>'
                    : '<span class="badge bg-warning">Not Verified</span>';
                
                document.getElementById('email-badge').innerHTML = emailBadge;
                document.getElementById('email-verification-status').innerHTML = emailBadge;
                
                // Registration Date & Time
                const createdAt = new Date(data.created_at);
                document.getElementById('registration-date').textContent = createdAt.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                document.getElementById('registration-time').textContent = createdAt.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Statistics
                document.getElementById('total-orders').textContent = data.total_orders;
                document.getElementById('completed-orders').textContent = data.completed_orders;
                document.getElementById('total-earnings').textContent = data.total_earnings;
                document.getElementById('pending-earnings').textContent = data.pending_earnings;
                
                // Documents
                const licenseImage = data.license_image
                    ? `/upload/delivery-document/medium/${data.license_image}`
                    : `/upload/no_image.jpg`;
                
                const nidImage = data.nid_image
                    ? `/upload/delivery-document/medium/${data.nid_image}`
                    : `/upload/no_image.jpg`;
                
                document.getElementById('license-image').src = licenseImage;
                document.getElementById('nid-image').src = nidImage;
                
                // Download links
                if (data.license_image) {
                    document.getElementById('download-license').href = `/admin/download/license/${data.id}`;
                } else {
                    document.getElementById('download-license').classList.add('disabled');
                }
                
                if (data.nid_image) {
                    document.getElementById('download-nid').href = `/admin/download/nid/${data.id}`;
                } else {
                    document.getElementById('download-nid').classList.add('disabled');
                }
                
                // Address
                let address = data.address1 || 'Not provided';
                if (data.address2) address += `, ${data.address2}`;
                if (data.postal_code) address += `, ${data.postal_code}`;
                
                document.getElementById('full-address').textContent = address;
                document.getElementById('country').textContent = data.country?.name || 'Not provided';
                document.getElementById('county').textContent = data.county?.name || 'Not provided';
                document.getElementById('city').textContent = data.city?.name || 'Not provided';
                
                // Vehicle Information
                if (data.has_vehicle && data.vehicle) {
                    const vehicleHtml = `
                        <div class="vehicle-card mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-1">${data.vehicle.vehicle_brand || 'Unknown'} ${data.vehicle.vehicle_model || ''}</h5>
                                    <p class="mb-1"><small>${data.vehicle.vehicle_type_label}</small></p>
                                    <p class="mb-0"><small>${data.vehicle.registration_number}</small></p>
                                </div>
                                <span class="badge ${data.vehicle.is_active ? 'bg-success' : 'bg-danger'}">
                                    ${data.vehicle.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            <div class="text-end">
                                <a href="/admin/delivery/${data.id}/edit-vehicle" class="btn btn-sm btn-light">
                                    <i class="fas fa-edit me-1"></i>Edit Vehicle
                                </a>
                            </div>
                        </div>
                    `;
                    document.getElementById('vehicle-info').innerHTML = vehicleHtml;
                } else {
                    document.getElementById('vehicle-info').innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No vehicle information available
                        </div>
                    `;
                }
                
                // Recent Earnings
                if (data.recent_earnings && data.recent_earnings.length > 0) {
                    document.getElementById('no-earnings').style.display = 'none';
                    const earningsBody = document.getElementById('earnings-body');
                    earningsBody.innerHTML = '';
                    
                    data.recent_earnings.forEach(earning => {
                        const row = `
                            <tr>
                                <td>${earning.date}</td>
                                <td>$${earning.amount}</td>
                                <td>
                                    <span class="badge bg-${earning.status_badge}">
                                        ${earning.status.charAt(0).toUpperCase() + earning.status.slice(1)}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        earningsBody.innerHTML += row;
                    });
                } else {
                    document.getElementById('no-earnings').style.display = 'block';
                    document.getElementById('earnings-body').innerHTML = '';
                }
                
            } else {
                errorToast(res.data.message || "Failed to fetch delivery person details.");
            }
        } catch (error) {
            handleError(error);
        } finally {
            hideLoader();
        }
    }

    function updateToggleButton(status) {
        const btn = document.getElementById('toggle-status-btn');
        const btnText = document.getElementById('toggle-btn-text');
        
        if (status == 1) {
            // Currently active - show deactivate option
            btn.classList.remove('btn-success');
            btn.classList.add('btn-danger');
            btnText.textContent = 'Deactivate Account';
        } else {
            // Currently inactive - show activate option
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-success');
            btnText.textContent = 'Activate Account';
        }
    }

    async function toggleAccountStatus() {
        const newStatus = currentStatus == 1 ? 0 : 1;
        const action = newStatus == 1 ? 'activate' : 'deactivate';
        const actionText = newStatus == 1 ? 'activate' : 'deactivate';
        
        Swal.fire({
            title: `Are you sure?`,
            text: `You want to ${actionText} this delivery person's account?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: newStatus == 1 ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText} it!`,
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoader();
                try {
                    let res = await axios.post(`/admin/delivery-person/${deliveryPersonId}/toggle-status`, {
                        status: newStatus
                    });

                    if (res.data.status === 'success') {
                        successToast(res.data.message);
                        currentStatus = newStatus;
                        updateToggleButton(newStatus);
                        
                        // Update status badges
                        const statusBadge = newStatus == 1 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-danger">Inactive</span>';
                        
                        document.getElementById('account-status').innerHTML = statusBadge;
                        document.getElementById('account-verification-status').innerHTML = statusBadge;
                        
                    } else {
                        errorToast(res.data.message || 'Failed to update status.');
                    }
                } catch (error) {
                    handleError(error);
                } finally {
                    hideLoader();
                }
            }
        });
    }

    function openImageModal(imageType) {
        const imageElement = document.getElementById(imageType);
        const modalImage = document.getElementById('modal-image');
        const modalDownload = document.getElementById('modal-download');
        
        modalImage.src = imageElement.src;
        
        if (imageType === 'license-image' && !document.getElementById('download-license').classList.contains('disabled')) {
            modalDownload.href = document.getElementById('download-license').href;
            modalDownload.style.display = 'inline-block';
        } else if (imageType === 'nid-image' && !document.getElementById('download-nid').classList.contains('disabled')) {
            modalDownload.href = document.getElementById('download-nid').href;
            modalDownload.style.display = 'inline-block';
        } else {
            modalDownload.style.display = 'none';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
    }

    function refreshDetails() {
        getDeliveryPersonDetails();
        successToast('Refreshing details...');
    }

    function getDeliveryPersonIdFromUrl() {
        let url = window.location.pathname;
        let segments = url.split('/');
        return segments[segments.length - 1];
    }


    function handleError(error) {
        let message = "An unexpected error occurred.";

        if (error.response) {
            const { status, data } = error.response;
            switch (status) {
                case 500:
                    message = data?.message || "Internal server error. Please try again later.";
                    break;
                case 404:
                    message = data?.message || "Delivery person not found.";
                    break;
                default:
                    message = data?.message || "Something went wrong.";
            }
        } else if (error.request) {
            message = "No response from the server. Please check your internet connection.";
        } else {
            message = error.message;
        }

        errorToast(message);
    }
</script>

<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .card-header {
        border-bottom: 2px solid rgba(0,0,0,0.1);
    }
    
    .avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-initial {
        font-size: 16px;
    }
    
    .document-preview {
        transition: all 0.3s ease;
    }
    
    .document-preview:hover {
        transform: scale(1.02);
    }
    
    .bg-label-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .bg-label-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .bg-label-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .bg-label-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .bg-label-info { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
    
    .form-control-static {
        padding: 0.375rem 0.75rem;
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        min-height: 38px;
        display: flex;
        align-items: center;
    }
    
    .vehicle-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
    }
</style>