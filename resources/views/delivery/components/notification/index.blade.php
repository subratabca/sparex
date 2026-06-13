<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Delivery Notifications</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Title</th>
                    <th>Meal Type</th>
                    <th>Delivery Date</th>
                    <th>Time</th>
                    <th>Charge</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList">
                
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    getList();
});

// e.g. "2026-06-05T18:00:00.000000Z" -> "05-June-2026"
function formatDateOnly(value) {
    if (!value) return 'N/A';
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    return `${String(d.getUTCDate()).padStart(2, '0')}-${months[d.getUTCMonth()]}-${d.getUTCFullYear()}`;
}

async function getList() {
    showLoader();
    try {
        let res = await axios.get("/delivery/get/notification/list");
        console.log('Notification list response:', res);
        let tableList = $("#tableList");
        tableList.empty(); 

        // Combine unread and read notifications with status
        const notifications = [
            ...res.data.unreadNotifications.map(item => ({ ...item, status: 'unread' })),
            ...res.data.readNotifications.map(item => ({ ...item, status: 'read' }))
        ];

        function getNotificationDetailsLink(notification) {
            if (notification.data && notification.data.data) {
                return `/delivery/view/notification/${notification.id}`;
            }
            return '#'; 
        }
        
        // Process notifications
        for (let i = 0; i < notifications.length; i++) {
            const item = notifications[i];
            const detailsLink = getNotificationDetailsLink(item);
            
            // Extract data from notification object
            const notificationData = item.data || {};
            const nestedData = notificationData.data || {};
            const deliveryDetails = nestedData.delivery_details || {};
            
            // Get the values with fallbacks
            const notificationTitle = notificationData.title || 'No Title';
            const mealType = deliveryDetails.meal_type || 'N/A';
            const deliveryDate = formatDateOnly(deliveryDetails.delivery_date);
            
            // Extract and format meal_time
            let deliveryTime = 'N/A';
            if (deliveryDetails.meal_time) {
                try {
                    const timeParts = deliveryDetails.meal_time.split(':');
                    if (timeParts.length >= 2) {
                        const hours = parseInt(timeParts[0]);
                        const minutes = timeParts[1];
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        const displayHours = hours % 12 || 12;
                        deliveryTime = `${displayHours}:${minutes} ${ampm}`;
                    }
                } catch (e) {
                    console.error('Error formatting delivery time:', e);
                }
            }
            
            // Format delivery charge
            let deliveryCharge = 'N/A';
            if (deliveryDetails.delivery_charge) {
                const charge = parseFloat(deliveryDetails.delivery_charge);
                deliveryCharge = `$${charge.toFixed(2)}`;
            }

            let badgeClass = item.status === 'unread' ? 'badge bg-danger' : 'badge bg-success';
            let badgeText = item.status.charAt(0).toUpperCase() + item.status.slice(1);

            // Get delivery ledger ID from notification data
            const deliveryChargeLedgerId = nestedData.delivery_charge_ledger_id;

            // Create View button
            const viewButton = `<a href="${detailsLink}" class="btn btn-sm btn-info me-1" title="View Details">
                                   <i class="bx bx-show"></i> View
                                </a>`;

            // Create Delete button (always shown)
            const deleteButton = `<button class="btn btn-sm btn-danger me-1" onclick="showDeleteConfirmation('${item.id}')" title="Delete">
                                     <i class="bx bx-trash"></i> Delete
                                  </button>`;

            // Start with empty action button
            let acceptButton = '';
            
            // Check if delivery_charge_ledger_id exists
            if (deliveryChargeLedgerId) {
                try {
                    // Call API to check if delivery is already accepted
                    console.log('Checking availability for notification:', item.id);
                    const checkResponse = await axios.post('/delivery/check/meal/delivery/availability', {
                        notification_id: item.id
                    });
                    
                    console.log('Availability check response:', checkResponse.data);
                    
                    // If API returns data with accepted status
                    if (checkResponse.data && checkResponse.data.status === 'success') {
                        const data = checkResponse.data.data;
                        const isAvailable = data.is_available;
                        
                        if (isAvailable) {
                            // Delivery is available - show "Accept" button
                            acceptButton = `<button class="btn btn-sm btn-success me-1" onclick="showAcceptConfirmation('${item.id}', '${deliveryChargeLedgerId}')" title="Accept Delivery">
                                               <i class="bx bx-check"></i> Accept
                                            </button>`;
                        }
                        // If not available, don't show any accept button (as per your requirement)
                    } else {
                        console.error('Check availability failed:', checkResponse.data);
                    }
                } catch (error) {
                    console.error('Error checking delivery availability:', error);
                    console.error('Error details:', error.response?.data);
                }
            } else {
                console.warn('No delivery_charge_ledger_id found for notification:', item.id);
            }

            // Build action buttons container
            let actionButtonsHTML = `<div class="d-flex gap-1">${viewButton}`;
            
            // Add accept button only if it exists
            if (acceptButton) {
                actionButtonsHTML += acceptButton;
            }
            
            // Always add delete button
            actionButtonsHTML += `${deleteButton}</div>`;

            let row = `<tr id="row-${item.id}">
                        <td class="text-truncate">${i + 1}</td>
                        <td class="text-truncate">
                            <a href="${detailsLink}" class="text-primary fw-semibold">
                                ${notificationTitle}
                            </a>
                        </td>
                        <td class="text-truncate">
                            <span class="badge bg-warning text-dark">${mealType}</span>
                        </td>
                        <td class="text-truncate">${deliveryDate}</td>
                        <td class="text-truncate">
                            <span class="badge bg-secondary">${deliveryTime}</span>
                        </td>
                        <td class="text-truncate fw-bold">${deliveryCharge}</td>
                        <td class="text-truncate">
                            <span class="${badgeClass}">${badgeText}</span>
                        </td>
                        <td class="text-truncate">
                           ${actionButtonsHTML}
                        </td>
                     </tr>`;
            tableList.append(row);
        }

        initializeDataTable();

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

// Function to show accept confirmation popup
function showAcceptConfirmation(notificationId, deliveryChargeLedgerId) {
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
            acceptDelivery(notificationId, deliveryChargeLedgerId);
        }
    });
}

// Function to show delete confirmation popup
function showDeleteConfirmation(notificationId) {
    Swal.fire({
        title: 'Delete Notification?',
        text: 'Are you sure you want to delete this notification?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Delete It!',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            deleteNotification(notificationId);
        }
    });
}

async function acceptDelivery(notificationId, deliveryChargeLedgerId) {
    try {
        showLoader();
        let res = await axios.post('/delivery/accept/meal/delivery', {
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
                // Find the row by notification ID
                const row = document.getElementById(`row-${notificationId}`);
                if (row) {
                    // Find the accept button in the row
                    const acceptButton = row.querySelector('button.btn-success');
                    if (acceptButton) {
                        // Remove the accept button completely (don't show disabled "Accepted" button)
                        acceptButton.remove();
                        
                        // Show a temporary success message on the row
                        const successBadge = document.createElement('span');
                        successBadge.className = 'badge bg-success me-1';
                        successBadge.innerHTML = '<i class="bx bx-check"></i> Accepted';
                        successBadge.style.fontSize = '0.8rem';
                        successBadge.style.padding = '0.25rem 0.5rem';
                        
                        // Insert the badge before the delete button
                        const actionCell = row.querySelector('td:last-child .d-flex');
                        const deleteButton = actionCell.querySelector('button.btn-danger');
                        actionCell.insertBefore(successBadge, deleteButton);
                        
                        // Remove the success badge after 3 seconds
                        setTimeout(() => {
                            successBadge.remove();
                        }, 3000);
                    }
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

async function deleteNotification(notificationId) {
    try {
        showLoader();
        let res = await axios.delete(`/delivery/delete/notification/${notificationId}`);
        
        if (res.status === 200 && res.data.status === 'success') {
            // Show success popup
            Swal.fire({
                title: 'Success!',
                text: res.data.message || 'Notification deleted successfully',
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-success'
                },
                buttonsStyling: false
            }).then(() => {
                // Remove the row from the table
                const row = document.getElementById(`row-${notificationId}`);
                if (row) {
                    row.remove();
                    // Reinitialize DataTable
                    initializeDataTable();
                }
            });
        } else {
            // Show error popup
            Swal.fire({
                title: 'Error!',
                text: res.data.message || "Failed to delete notification",
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

            if (status === 404) {
                errorMessage = message || "Notification not found";
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

function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#foodTable')) {
        $('#foodTable').DataTable().destroy();
    }

    $('#foodTable').DataTable({
        "paging": true,
        "serverSide": false, 
        "autoWidth": false,
        "ordering": true,
        "searching": true, 
        "lengthMenu": [10, 25, 50, 100], 
        "pageLength": 10,
        "order": [[0, "desc"]],
        "columnDefs": [
            { "targets": [0], "orderable": true },
            { "targets": [1], "orderable": true },
            { "targets": [2], "orderable": true },
            { "targets": [3], "orderable": true },
            { "targets": [4], "orderable": true },
            { "targets": [5], "orderable": true },
            { "targets": [6], "orderable": true },
            { "targets": [7], "orderable": false },
        ],
        "language": {
            "emptyTable": "No notifications found",
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
}

function handleError(error) {
    let message = 'An unexpected error occurred';
    
    if (error.response) {
        const status = error.response.status;
        const serverMessage = error.response.data?.message;
        
        switch (status) {
            case 400:
                message = serverMessage || "Unauthorized! Need to login.";
                break;
            case 401:
                message = serverMessage || "Unauthorized access.";
                break;
            case 404:
                message = serverMessage || "Resource not found.";
                break;
            case 500:
                message = serverMessage || "An internal server error occurred.";
                break;
            default:
                message = serverMessage || "Request failed!";
        }
    } else {
        message = "Network error. Please check your connection.";
    }
    
    // Show error popup
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK',
        customClass: {
            confirmButton: 'btn btn-danger'
        },
        buttonsStyling: false
    });
}
</script>

<style>
/* Optional custom styles for SweetAlert2 */
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