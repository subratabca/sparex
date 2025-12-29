<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h4 class="card-header bg-primary text-white">Vehicle Information</h4>
            <div id="success-message" class="alert alert-success d-none"></div>
            <div id="error-message" class="alert alert-danger d-none"></div>
            <hr>
            <div class="card-body">
                <input type="hidden" id="deliveryId" value="{{ $deliveryId }}">
                <!-- Delivery Person Info -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-user me-2"></i>Delivery Person Information
                            </h6>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <small class="text-muted">Name:</small>
                                    <div class="fw-semibold" id="delivery-name">Loading...</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Email:</small>
                                    <div class="fw-semibold" id="delivery-email">Loading...</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Mobile:</small>
                                    <div class="fw-semibold" id="delivery-mobile">Loading...</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Status:</small>
                                    <div id="delivery-status">Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="vehicleForm">
                    <div class="row gy-4">
                        <!-- Vehicle Type -->
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="vehicle_type" required>
                                    <option value="">Select Vehicle Type</option>
                                    <option value="bike">Bike</option>
                                    <option value="scooter">Scooter</option>
                                    <option value="car">Car</option>
                                    <option value="bicycle">Bicycle</option>
                                    <option value="walking">Walking</option>
                                </select>
                                <label for="vehicle_type">Vehicle Type <span class="text-danger">*</span></label>
                            </div>
                            <span class="error-message text-danger" id="vehicle_type-error"></span>
                        </div>

                        <!-- Registration Number -->
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="registration_number" 
                                       placeholder="Enter registration number" maxlength="50" />
                                <label for="registration_number">Registration Number</label>
                            </div>
                            <span class="error-message text-danger" id="registration_number-error"></span>
                        </div>

                        <!-- Vehicle Brand -->
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="vehicle_brand" 
                                       placeholder="Enter vehicle brand" maxlength="50" />
                                <label for="vehicle_brand">Vehicle Brand</label>
                            </div>
                            <span class="error-message text-danger" id="vehicle_brand-error"></span>
                        </div>

                        <!-- Vehicle Model -->
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="vehicle_model" 
                                       placeholder="Enter vehicle model" maxlength="50" />
                                <label for="vehicle_model">Vehicle Model</label>
                            </div>
                            <span class="error-message text-danger" id="vehicle_model-error"></span>
                        </div>

                        <!-- Vehicle Color -->
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="vehicle_color" 
                                       placeholder="Enter vehicle color" maxlength="30" />
                                <label for="vehicle_color">Vehicle Color</label>
                            </div>
                            <span class="error-message text-danger" id="vehicle_color-error"></span>
                        </div>

                        <!-- Vehicle Image -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Vehicle Image</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" id="vehicle_image" 
                                           accept="image/png, image/jpeg, image/jpg">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="document.getElementById('vehicle_image').click()">
                                        <i class="fas fa-upload"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Allowed: JPG, JPEG, PNG. Max size: 2MB</small>
                                <span class="error-message text-danger" id="vehicle_image-error"></span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="is_active" checked>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Vehicle Active
                                </label>
                            </div>
                            <small class="text-muted">Toggle to activate/deactivate vehicle</small>
                        </div>

                        <!-- Preview Section -->
                        <div class="col-12 mt-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-car me-2"></i>Vehicle Preview
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3 text-center">
                                            <img src="/upload/no_image.jpg" alt="Vehicle Preview" 
                                                 class="img-fluid rounded border" id="vehicle_image_preview"
                                                 style="max-height: 150px;">
                                        </div>
                                        <div class="col-md-9">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <small class="text-muted">Vehicle Type:</small>
                                                    <div class="fw-semibold" id="preview_vehicle_type">-</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Registration:</small>
                                                    <div class="fw-semibold" id="preview_registration">-</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Brand/Model:</small>
                                                    <div class="fw-semibold" id="preview_brand_model">-</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Color:</small>
                                                    <div class="fw-semibold" id="preview_color">-</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Status:</small>
                                                    <div id="preview_status">
                                                        <span class="badge bg-success">Active</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="goBack()">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                                <div>
                                    <button type="button" class="btn btn-outline-danger me-2" 
                                            onclick="resetForm()">
                                        <i class="fas fa-redo me-2"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Vehicle Info
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Image Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vehicle Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modal_vehicle_image" src="" class="img-fluid" alt="Vehicle Image">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadImage()">
                    <i class="fas fa-download me-2"></i>Download
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let deliveryPersonId = null;
    let existingVehicle = null;
    let currentImageUrl = '/upload/no_image.jpg';

    document.addEventListener("DOMContentLoaded", function () {
        deliveryPersonId = document.getElementById('deliveryId');;
        loadDeliveryPersonInfo();
        loadVehicleInfo();
        
        // Add event listeners for real-time preview
        document.getElementById('vehicle_type').addEventListener('change', updatePreview);
        document.getElementById('registration_number').addEventListener('input', updatePreview);
        document.getElementById('vehicle_brand').addEventListener('input', updatePreview);
        document.getElementById('vehicle_model').addEventListener('input', updatePreview);
        document.getElementById('vehicle_color').addEventListener('input', updatePreview);
        document.getElementById('is_active').addEventListener('change', updatePreview);
        document.getElementById('vehicle_image').addEventListener('change', handleImageUpload);
        
        // Form submission
        document.getElementById('vehicleForm').addEventListener('submit', saveVehicleInfo);
    });

    function capitalizeName(name) {
        if (!name) return '';
        return name
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    async function loadDeliveryPersonInfo() {
        showLoader();
        try {
            const response = await axios.get('/delivery/get/profile/info');
            
            if (response.status === 200 && response.data.status === 'success') {
                const data = response.data.data;

                // Capitalize first and last names
                const firstName = capitalizeName(data.firstName || '');
                const lastName = capitalizeName(data.lastName || '');
                const fullName = `${firstName} ${lastName}`.trim();

                // Update delivery person info
                document.getElementById('delivery-name').textContent = fullName || 'Not provided';
                document.getElementById('delivery-email').textContent = data.email;
                document.getElementById('delivery-mobile').textContent = data.mobile || 'Not provided';
                
                const statusBadge = data.status == 1 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>';
                document.getElementById('delivery-status').innerHTML = statusBadge;
            } else {
                handleError({ response: response });
            }
        } catch (error) {
            handleError(error);
        } finally {
            hideLoader();
        }
    }

    async function loadVehicleInfo() {
        showLoader();
        try {
            const response = await axios.get(`/delivery/get/vehicle/info`);
            
            if (response.status === 200 && response.data.status === 'success') {
                existingVehicle = response.data.data;
                populateVehicleForm(existingVehicle);
                updatePreview();
            } else {
                // No existing vehicle, show empty form
                resetForm();
                handleError({ response: response });
            }
        } catch (error) {
            if (error.response && error.response.status === 404) {
                // No vehicle found, this is normal for new delivery persons
                resetForm();
            } else {
                handleError(error);
            }
        } finally {
            hideLoader();
        }
    }

    function populateVehicleForm(vehicle) {
        // Set form values
        document.getElementById('vehicle_type').value = vehicle.vehicle_type || '';
        document.getElementById('registration_number').value = vehicle.registration_number || '';
        document.getElementById('vehicle_brand').value = vehicle.vehicle_brand || '';
        document.getElementById('vehicle_model').value = vehicle.vehicle_model || '';
        document.getElementById('vehicle_color').value = vehicle.vehicle_color || '';
        document.getElementById('is_active').checked = vehicle.is_active !== false;
        
        // Set image preview if exists
        if (vehicle.image) {
            currentImageUrl = `/upload/vehicle/medium/${vehicle.image}`;
            document.getElementById('vehicle_image_preview').src = currentImageUrl;
        }
    }

    function handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            errorToast('File size must be less than 2MB');
            event.target.value = '';
            return;
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            errorToast('Only JPG, JPEG, and PNG files are allowed');
            event.target.value = '';
            return;
        }

        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            currentImageUrl = e.target.result;
            document.getElementById('vehicle_image_preview').src = currentImageUrl;
            updatePreview();
        };
        reader.readAsDataURL(file);
    }

    function updatePreview() {
        // Get form values
        const vehicleType = document.getElementById('vehicle_type').value;
        const registration = document.getElementById('registration_number').value;
        const brand = document.getElementById('vehicle_brand').value;
        const model = document.getElementById('vehicle_model').value;
        const color = document.getElementById('vehicle_color').value;
        const isActive = document.getElementById('is_active').checked;

        // Update preview
        document.getElementById('preview_vehicle_type').textContent = vehicleType 
            ? document.getElementById('vehicle_type').options[document.getElementById('vehicle_type').selectedIndex].text 
            : '-';
        
        document.getElementById('preview_registration').textContent = registration || '-';
        
        const brandModel = [];
        if (brand) brandModel.push(brand);
        if (model) brandModel.push(model);
        document.getElementById('preview_brand_model').textContent = brandModel.length > 0 
            ? brandModel.join(' ') 
            : '-';
        
        document.getElementById('preview_color').textContent = color || '-';
        
        const statusBadge = isActive 
            ? '<span class="badge bg-success">Active</span>' 
            : '<span class="badge bg-danger">Inactive</span>';
        document.getElementById('preview_status').innerHTML = statusBadge;
    }

    async function saveVehicleInfo(event) {
        event.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }

        showLoader();
        
        try {
            const formData = new FormData();
            
            // Add vehicle data
            formData.append('vehicle_type', document.getElementById('vehicle_type').value);
            formData.append('registration_number', document.getElementById('registration_number').value);
            formData.append('vehicle_brand', document.getElementById('vehicle_brand').value);
            formData.append('vehicle_model', document.getElementById('vehicle_model').value);
            formData.append('vehicle_color', document.getElementById('vehicle_color').value);
            formData.append('is_active', document.getElementById('is_active').checked ? '1' : '0');
            
            // Add image if selected
            const imageFile = document.getElementById('vehicle_image').files[0];
            if (imageFile) {
                formData.append('vehicle_image', imageFile);
            }
            
            // Determine endpoint (update or create)
            let endpoint;
            if (existingVehicle) {
                endpoint = `/delivery/update/vehicle/info`;
            } else {
                endpoint = `/delivery/store/vehicle/info`;
            }
            
            const response = await axios.post(endpoint, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            if (response.status === 200 && response.data.status === 'success') {
                successToast(response.data.message);
                existingVehicle = response.data.data;
                
                // Update image preview if new image was uploaded
                if (response.data.data.image) {
                    currentImageUrl = `/upload/vehicle/medium/${response.data.data.image}`;
                    document.getElementById('vehicle_image_preview').src = currentImageUrl;
                }
            } else {
                handleError({ response: response });
            }
        } catch (error) {
            if (error.response && error.response.status === 422) {
                // Validation errors
                const errors = error.response.data.errors || {};
                for (const field in errors) {
                    const errorElement = document.getElementById(`${field}-error`);
                    if (errorElement) {
                        errorElement.textContent = errors[field][0];
                    }
                }
                errorToast('Please fix the validation errors');
            } else {
                handleError(error);
            }
        } finally {
            hideLoader();
        }
    }

    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        
        // Validate required fields
        const vehicleType = document.getElementById('vehicle_type').value;
        if (!vehicleType) {
            document.getElementById('vehicle_type-error').textContent = 'Vehicle type is required';
            isValid = false;
        }
        
        // Validate registration number if vehicle type is not "walking"
        if (vehicleType && vehicleType !== 'walking') {
            const registration = document.getElementById('registration_number').value;
            if (!registration) {
                document.getElementById('registration_number-error').textContent = 'Registration number is required for vehicles';
                isValid = false;
            }
        }
        
        return isValid;
    }

    function handleError(error) {
        let message = "An unexpected error occurred.";

        if (error.response) {
            const { status, data } = error.response;
            switch (status) {
                case 500:
                    message = data?.message || data?.error || "Internal server error. Please try again later.";
                    break;
                case 404:
                    message = data?.message || "Data not found.";
                    break;
                case 422:
                    message = data?.message || "Validation failed.";
                    break;
                case 400:
                    message = data?.message || "Bad request.";
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

    function resetForm() {
        document.getElementById('vehicleForm').reset();
        document.getElementById('vehicle_image_preview').src = '/upload/no_image.jpg';
        currentImageUrl = '/upload/no_image.jpg';
        document.getElementById('vehicle_image').value = '';
        existingVehicle = null;
        updatePreview();
        clearErrors();
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    }

    function goBack() {
        window.history.back();
    }

    function openImageModal() {
        document.getElementById('modal_vehicle_image').src = currentImageUrl;
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
    }

    function downloadImage() {
        if (currentImageUrl.startsWith('data:')) {
            // For base64 images
            const link = document.createElement('a');
            link.href = currentImageUrl;
            link.download = 'vehicle_image.jpg';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else if (currentImageUrl !== '/upload/no_image.jpg') {
            // For server images
            window.open(currentImageUrl, '_blank');
        }
    }

    function showLoader() {
        document.body.style.cursor = 'wait';
    }

    function hideLoader() {
        document.body.style.cursor = 'default';
    }

    // Add click event to preview image
    document.getElementById('vehicle_image_preview').addEventListener('click', openImageModal);
</script>
<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        border-bottom: 2px solid rgba(0,0,0,0.1);
    }
    
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    
    .error-message {
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    #vehicle_image_preview {
        cursor: pointer;
        transition: transform 0.3s;
    }
    
    #vehicle_image_preview:hover {
        transform: scale(1.05);
    }
    
    .border-info {
        border: 2px solid #0dcaf0 !important;
    }
</style>