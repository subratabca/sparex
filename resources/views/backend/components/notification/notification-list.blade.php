<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Notification List Information</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class=" table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Notification Type</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList">
                
            </tbody>
        </table>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {
    getList();
});

async function getList() {
    showLoader();
    try {
        let res = await axios.get("/admin/notification/list/info");

        if (res.data.status !== 'success') {
            errorToast(res.data.message || 'Failed to load notifications');
            hideLoader();
            return;
        }

        let tableList = $("#tableList");
        tableList.empty(); 

        const unreadNotifications = res.data.unreadNotifications || [];
        const readNotifications = res.data.readNotifications || [];
        
        // Combine all notifications
        const notifications = [
            ...unreadNotifications.map(item => ({ ...item, status: 'unread' })),
            ...readNotifications.map(item => ({ ...item, status: 'read' }))
        ];

        // Sort by created_at (most recent first)
        notifications.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        function getNotificationLink(notification) {
            if (notification && notification.data) {
                const data = notification.data;
                
                if (data.order_id) {
                    return `/admin/order/details/${data.order_id}?notification_id=${notification.id}`;
                } else if (data.meal_order_id) {
                    return `/admin/meal-order/details/${data.meal_order_id}?notification_id=${notification.id}`;
                } else if (data.complaint_id) {
                    return `/admin/complaint/details/${data.complaint_id}?notification_id=${notification.id}`;
                } else if (data.product_id) {
                    return `/admin/product/details/${data.product_id}?notification_id=${notification.id}`;
                } else if (data.client_id) {
                    return `/admin/client/details/${data.client_id}?notification_id=${notification.id}`;
                } else if (data.customer_id) {
                    return `/admin/customer/details/${data.customer_id}?notification_id=${notification.id}`;
                } else if (data.customer_complain_id) {
                    return `/admin/customer-complain/details/${data.customer_complain_id}?notification_id=${notification.id}`;
                }
            }
            return '#'; 
        }

        if (notifications.length === 0) {
            tableList.append('<tr><td colspan="6" class="text-center py-4">No notifications found</td></tr>');
        } else {
            notifications.forEach(function (item, index) {
                const link = getNotificationLink(item);
                let date = new Date(item.created_at);
                
                let notificationText = item.data?.data || 'Notification';
                let badgeClass = item.status === 'unread' ? 'badge bg-danger' : 'badge bg-success';
                let badgeText = item.status.charAt(0).toUpperCase() + item.status.slice(1);

                let row = `<tr>
                            <td class="text-truncate">${index + 1}</td>
                            <td class="text-truncate">
                                <a href="${link}" class="text-decoration-none">
                                    ${notificationText}
                                </a>
                            </td>
                            <td class="text-truncate">${date.toLocaleDateString()}</td>
                            <td class="text-truncate">${date.toLocaleTimeString()}</td>
                            <td class="text-truncate"><span class="${badgeClass}">${badgeText}</span></td>
                            <td class="text-truncate">
                               <button class="btn btn-danger btn-sm" onclick="deleteNotification('${item.id}')">Delete</button>
                            </td>
                         </tr>`;
                tableList.append(row);
            });
        }
        
        initializeDataTable();

    } catch (error) {
        console.error('Error loading notifications:', error);
        handleError(error);
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
        "order": [[2, 'desc']] // Order by date column (3rd column)
    });
}

async function deleteNotification(notificationId) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }
    
    try {
        let res = await axios.delete(`/admin/delete/notification/${notificationId}`);
        if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message || 'Notification deleted successfully');
            await getList(); 
        } else {
            errorToast(res.data.message || "Failed to delete notification");
        }
    } catch (error) {
        console.error('Delete notification error:', error);
        if (error.response) {
            if (error.response.status === 404) {
                errorToast(error.response.data.message || 'Notification not found');
            } else if (error.response.status === 500) {
                errorToast('Server error occurred');
            } else {
                errorToast(error.response.data.message || 'Failed to delete notification');
            }
        } else {
            errorToast('Network error occurred');
        }
    }
}

function handleError(error) {
    if (error.response) {
        if (error.response.status === 400) {
            errorToast(error.response.data.message || "Unauthorized! Need to login.");
        } else if (error.response.status === 401) {
            errorToast("Session expired. Please login again.");
        } else if (error.response.status === 404) {
            errorToast("User not found.");
        } else if (error.response.status === 500) {
            errorToast("Server error. Please try again later.");
        } else {
            errorToast("Request failed!");
        }
    } else if (error.request) {
        errorToast("No response from server. Please check your connection.");
    } else {
        errorToast("Error: " + error.message);
    }
}
</script>

</script>

