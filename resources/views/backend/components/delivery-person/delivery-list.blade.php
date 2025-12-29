<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Delivery List</h5></span>
    </div>
    <div class="card-datatable table-responsive pt-0">
        <table id="deliveryTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Images</th>
                    <th>Name</th>
                    <th>E-mail</th>
                    <th>Mobile</th>
                    <th>Vehicle Type</th>
                    <th>Account Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList"></tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        getList(); 
    });

async function getList() {
    showLoader();
    try {
        let res = await axios.get("/admin/get/delivery-person/list");
        
        if (res.status === 200 && res.data.status === 'success') {
            let tableList = $("#tableList");
            tableList.empty(); 

            if (res.data.data.length === 0) {
                tableList.append(`
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="mdi mdi-account-off me-2" style="font-size: 24px;"></i>
                                <p class="mb-0">No delivery persons found</p>
                            </div>
                        </td>
                    </tr>
                `);
                initializeDataTable();
                hideLoader();
                return;
            }

            res.data.data.forEach(function (item, index) {
                // Capitalize first letter of each word in name
                const capitalizeName = (name) => {
                    if (!name) return '';
                    return name
                        .toLowerCase()
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                };
                
                // Capitalize first and last names separately
                const firstName = capitalizeName(item.firstName);
                const lastName = capitalizeName(item.lastName);
                const fullName = `${firstName} ${lastName}`.trim();
                
                // Also capitalize city name if exists
                const cityName = item.city_name ? capitalizeName(item.city_name) : '';
                
                // Profile image
                let imageUrl = item.image ? "/upload/delivery-profile/small/" + item.image : "/upload/no_image.jpg";
                
                // Vehicle info modal trigger
                let vehicleInfo = 'No vehicle';
                if (item.has_vehicle) {
                    vehicleInfo = `
                        <a href="javascript:void(0);" 
                           class="text-primary fw-semibold" 
                           onclick="showVehicleDetails(${item.id}, '${fullName}')"
                           title="Click to view vehicle details">
                            ${item.vehicle_type_label}
                            <i class="mdi mdi-information-outline ms-1"></i>
                        </a>
                    `;
                }
                
                // Account status badge
                let accountStatus = `
                    <span class="badge bg-${item.status_badge}" id="status-badge-${item.id}">
                        ${item.status_text}
                    </span>
                `;
                                    
                // Toggle button based on status
                let toggleButton = item.status == 1 ? `
                    <button onclick="toggleAccountStatus(${item.id}, 0)"
                            title="Deactivate account" 
                            class="btn btn-sm btn-outline-danger toggle-status-btn"
                            data-id="${item.id}">
                        <span class="mdi mdi-thumb-down"></span>
                    </button>
                ` : `
                    <button onclick="toggleAccountStatus(${item.id}, 1)"
                            title="Activate account" 
                            class="btn btn-sm btn-outline-success toggle-status-btn"
                            data-id="${item.id}">
                        <span class="mdi mdi-thumb-up"></span>
                    </button>
                `;

                let row = `
                    <tr id="delivery-row-${item.id}">
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">
                            <img src="${imageUrl}" 
                                 alt="${fullName}" 
                                 class="rounded-circle" 
                                 width="50" 
                                 height="50"
                                 style="object-fit: cover; border: 2px solid #dee2e6;">
                        </td>
                        <td>
                            <div class="fw-semibold">${fullName}</div>
                            ${cityName ? `<small class="text-muted">${cityName}</small>` : ''}
                        </td>
                        <td>
                            <div>${item.email}</div>
                            <small class="text-muted ${item.is_email_verified == 1 ? 'text-success' : 'text-warning'}">
                                <i class="mdi mdi-${item.is_email_verified == 1 ? 'email-check' : 'email-alert'} me-1"></i>
                                ${item.email_status_text}
                            </small>
                        </td>
                        <td>${item.mobile || '-'}</td>
                        <td class="text-center">${vehicleInfo}</td>
                        <td class="text-center" id="status-cell-${item.id}">${accountStatus}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="/admin/delivery-person/details/${item.id}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="View Details">
                                    <span class="mdi mdi-eye-outline"></span>
                                </a>
                                ${toggleButton}
                                <button data-id="${item['id']}" class="btn deleteBtn btn-sm btn-outline-danger" title="Delete Delivery Person"><span class="mdi mdi-trash-can-outline"></span>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                tableList.append(row);
            });

            initializeDataTable();
            attachEventListeners();
        } else {
            errorToast(res.data.message || "Failed to fetch delivery persons.");
        }
    } catch (error) {
        console.error('Error loading delivery list:', error);
        handleError(error);
    } finally {
        hideLoader();
    }
}

    async function showVehicleDetails(deliveryId, deliveryName) {
        showLoader();
        try {
            let res = await axios.get(`/admin/delivery/${deliveryId}/vehicle-details`);
            
            if (res.data.status === 'success') {
                const vehicle = res.data.data;
                
                let modalContent = `
                    <div class="modal-header">
                        <h5 class="modal-title">Vehicle Details - ${deliveryName}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Vehicle Type</th>
                                        <td>${vehicle.vehicle_type_label || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Registration Number</th>
                                        <td>${vehicle.registration_number || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle Brand</th>
                                        <td>${vehicle.vehicle_brand || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle Model</th>
                                        <td>${vehicle.vehicle_model || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle Color</th>
                                        <td>${vehicle.vehicle_color || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge ${vehicle.is_active ? 'bg-success' : 'bg-danger'}">
                                                ${vehicle.is_active ? 'Active' : 'Inactive'}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="/admin/delivery/${deliveryId}/edit-vehicle" class="btn btn-primary">Edit Vehicle</a>
                    </div>
                `;
                
                // Create or update modal
                let modal = $('#vehicleDetailsModal');
                if (modal.length === 0) {
                    $('body').append(`
                        <div class="modal fade" id="vehicleDetailsModal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    ${modalContent}
                                </div>
                            </div>
                        </div>
                    `);
                } else {
                    modal.find('.modal-content').html(modalContent);
                }
                
                // Show modal
                $('#vehicleDetailsModal').modal('show');
            } else {
                errorToast(res.data.message || 'Failed to load vehicle details.');
            }
        } catch (error) {
            console.error('Error loading vehicle details:', error);
            errorToast('Failed to load vehicle details.');
        } finally {
            hideLoader();
        }
    }

    async function toggleAccountStatus(deliveryId, newStatus) {
        const action = newStatus == 1 ? 'activate' : 'deactivate';
        
        Swal.fire({
            title: `Are you sure?`,
            text: `You want to ${action} this delivery person's account?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: newStatus == 1 ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                showLoader();
                try {
                    let res = await axios.post(`/admin/delivery-person/${deliveryId}/toggle-status`, {
                        status: newStatus
                    });

                    if (res.data.status === 'success') {
                        successToast(res.data.message);
                        
                        // Update the status badge
                        const statusBadge = $(`#status-badge-${deliveryId}`);
                        const newStatusText = newStatus == 1 ? 'Active' : 'Inactive';
                        const newStatusClass = newStatus == 1 ? 'bg-success' : 'bg-danger';
                        
                        statusBadge.removeClass('bg-success bg-danger').addClass(newStatusClass);
                        statusBadge.text(newStatusText);
                        
                        // Find and update the toggle button using the data-id attribute
                        const toggleButton = $(`.toggle-status-btn[data-id="${deliveryId}"]`);
                        
                        if (newStatus == 1) {
                            // Update to deactivate button
                            toggleButton.removeClass('btn-outline-success').addClass('btn-outline-danger');
                            toggleButton.attr('title', 'Deactivate account');
                            toggleButton.attr('onclick', `toggleAccountStatus(${deliveryId}, 0)`);
                            toggleButton.find('.mdi').removeClass('mdi-thumb-up').addClass('mdi-thumb-down');
                        } else {
                            // Update to activate button
                            toggleButton.removeClass('btn-outline-danger').addClass('btn-outline-success');
                            toggleButton.attr('title', 'Activate account');
                            toggleButton.attr('onclick', `toggleAccountStatus(${deliveryId}, 1)`);
                            toggleButton.find('.mdi').removeClass('mdi-thumb-down').addClass('mdi-thumb-up');
                        }
                        
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

    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('#deliveryTable')) {
            $('#deliveryTable').DataTable().destroy();
        }

        $('#deliveryTable').DataTable({
            "paging": true,
            "serverSide": false, 
            "autoWidth": false,
            "ordering": true,
            "searching": true, 
            "lengthMenu": [10, 25, 50, 100], 
            "pageLength": 10,
            "order": [[0, 'asc']],
            "columnDefs": [
                { "targets": 0, "className": "text-center", "width": "5%" },
                { "targets": 1, "className": "text-center", "width": "8%" },
                { "targets": 2, "width": "15%" },
                { "targets": 3, "width": "18%" },
                { "targets": 4, "width": "12%" },
                { "targets": 5, "className": "text-center", "width": "12%" },
                { "targets": 6, "className": "text-center", "width": "10%" },
                { "targets": 7, "className": "text-center", "width": "20%" }
            ],
            "language": {
                "emptyTable": "No delivery persons available",
                "search": "_INPUT_",
                "searchPlaceholder": "Search delivery persons...",
                "lengthMenu": "_MENU_ per page"
            }
        });
    }

    function attachEventListeners() {
        $('.deleteBtn').on('click', function () {
            let id = $(this).data('id');
            $("#deleteID").val(id);
            $("#delete-modal").modal('show');
        });
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
                    message = data?.message || "Data not found.";
                    break;
                case 422:
                    if (data?.errors) {
                        message = Object.values(data.errors).flat().join(' ');
                    } else {
                        message = data?.message || "Validation failed.";
                    }
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
    #deliveryTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    #deliveryTable img {
        transition: transform 0.2s;
    }
    
    #deliveryTable img:hover {
        transform: scale(1.1);
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 2px;
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.35em 0.65em;
        font-weight: 600;
    }
    
    #deliveryTable td {
        vertical-align: middle;
    }
    
    /* Test badge to verify Bootstrap CSS is loaded */
    .test-badge {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 9999;
        display: none; /* Set to block to test */
    }
</style>

