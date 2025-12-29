<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Orders</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealOrderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Customer Name</th>
                    <th>Order No</th>
                    <th>Meal Date</th>
                    <th>Meal Type</th>
                    <th>Billed Amount</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList"></tbody>
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
        let res = await axios.get("/admin/get/meal-orders");
        //console.log('API Response:', res.data);
        
        if (res.status === 200 && res.data.status === 'success') {
            let tableList = $("#tableList");
            tableList.empty();

            if (res.data.data.length === 0) {
                tableList.append(`
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="mdi mdi-cart-off me-2" style="font-size: 24px;"></i>
                                <p class="mb-0">No meal orders found</p>
                            </div>
                        </td>
                    </tr>
                `);
                hideLoader();
                initializeDataTable();
                return;
            }

            // Group data by order_id to calculate rowspans
            const orderGroups = {};
            res.data.data.forEach(item => {
                if (!orderGroups[item.order_id]) {
                    orderGroups[item.order_id] = [];
                }
                orderGroups[item.order_id].push(item);
            });

            // Render rows with merged cells
            let rowIndex = 0;
            let previousOrderId = null;
            
            Object.values(orderGroups).forEach(orderItems => {
                // Sort items by meal_date descending within the order
                orderItems.sort((a, b) => new Date(b.meal_date) - new Date(a.meal_date));
                
                const rowspan = orderItems.length;
                const orderId = orderItems[0].order_id;
                
                orderItems.forEach((item, itemIndex) => {
                    // Format meal date
                    const formattedDate = new Date(item.meal_date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });

                    // Format meal types
                    let formattedMealTypes = '-';
                    if (item.meal_types && item.meal_types.length > 0) {
                        formattedMealTypes = item.meal_types
                            .map(t => t.charAt(0).toUpperCase() + t.slice(1))
                            .join(', ');
                    }

                    let row = `
                        <tr data-order-id="${orderId}" data-row-index="${itemIndex}">
                            <td class="text-center">${item.sl}</td>`;
                    
                    // Customer Name (merged - only show in first row)
                    if (itemIndex === 0) {
                        row += `
                            <td rowspan="${rowspan}" class="customer-name-cell">
                                <div class="customer-info">
                                    <div class="fw-semibold">${item.customer_name}</div>
                                    <small class="text-muted">${item.customer_email}</small>
                                </div>
                            </td>`;
                    }
                    
                    // Order No (merged - only show in first row)
                    if (itemIndex === 0) {
                        row += `
                            <td rowspan="${rowspan}" class="text-center order-number-cell">
                                <a href="/admin/meal-order/details/${item.order_id}" 
                                   class="text-primary fw-semibold order-link"
                                   title="View Order Details">
                                    ${item.order_number}
                                </a>
                            </td>`;
                    }
                    
                    // Regular columns (not merged)
                    row += `
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-calendar-clock me-2 text-primary"></i>
                                    <div>
                                        <div>${formattedDate}</div>
                                        <small class="text-muted">${item.total_items} item(s)</small>
                                    </div>
                                </div>
                            </td>
                            <td>${formattedMealTypes}</td>
                            <td class="text-end">
                                <span class="fw-bold">$${item.billed_amount}</span>
                            </td>
                            <td class="text-center">${item.payment_status_badge}</td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="/admin/meal-order/details/${item.order_id}?date=${item.meal_date}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="View Order Details">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </a>
                                    <a href="/admin/meal-order/${item.order_id}/date/${item.meal_date}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="View By Date">
                                        <i class="mdi mdi-calendar-text"></i>
                                    </a>
                                    <button data-id="${item.order_id}" 
                                            data-date="${item.meal_date}"
                                            class="btn deleteBtn btn-sm btn-outline-danger" 
                                            title="Delete">
                                        <i class="mdi mdi-trash-can-outline"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    
                    tableList.append(row);
                    rowIndex++;
                });
            });

            initializeDataTable();
            attachEventListeners();
        } else {
            errorToast(res.data.message || "Failed to fetch orders.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#mealOrderTable')) {
        $('#mealOrderTable').DataTable().destroy();
    }

    $('#mealOrderTable').DataTable({
        "paging": true,
        "serverSide": false,
        "autoWidth": false,
        "ordering": true,
        "searching": true,
        "lengthMenu": [10, 25, 50, 100],
        "pageLength": 10,
        "order": [[0, 'asc']], // Sort by SL number
        "columnDefs": [
            {
                "targets": 0, // SL No column
                "orderable": false,
                "className": "text-center",
                "width": "5%"
            },
            {
                "targets": 1, // Customer Name (merged)
                "orderable": true,
                "width": "15%"
            },
            {
                "targets": 2, // Order No (merged)
                "orderable": true,
                "className": "text-center",
                "width": "10%"
            },
            {
                "targets": 3, // Meal Date
                "orderable": true,
                "type": "date",
                "width": "12%"
            },
            {
                "targets": 4, // Meal Type
                "orderable": false,
                "width": "15%"
            },
            {
                "targets": 5, // Billed Amount
                "orderable": true,
                "className": "text-end",
                "width": "10%"
            },
            {
                "targets": 6, // Payment Status
                "orderable": true,
                "className": "text-center",
                "width": "10%"
            },
            {
                "targets": 7, // Action
                "orderable": false,
                "className": "text-center",
                "width": "13%"
            }
        ],
        "language": {
            "emptyTable": "No meal orders available",
            "search": "_INPUT_",
            "searchPlaceholder": "Search orders...",
            "lengthMenu": "_MENU_ orders per page",
            "paginate": {
                "previous": "←",
                "next": "→"
            }
        },
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
               '<"row"<"col-sm-12"tr>>' +
               '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        "initComplete": function(settings, json) {
            // Add custom styles after initialization
            $('.dataTables_filter input').addClass('form-control form-control-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
            
            // Add border styling for merged rows
            addMergedRowStyles();
        },
        "drawCallback": function(settings) {
            // Re-apply styling after DataTable redraws
            setTimeout(() => {
                addMergedRowStyles();
                attachEventListeners();
            }, 100);
        }
    });
}

function addMergedRowStyles() {
    // Add styling for merged rows
    $('td[rowspan]').each(function() {
        const $td = $(this);
        const rowspan = parseInt($td.attr('rowspan'));
        
        if (rowspan > 1) {
            $td.addClass('merged-cell');
            
            // Find all rows in this merged group
            const $row = $td.closest('tr');
            const orderId = $row.data('order-id');
            const rowIndex = $row.data('row-index');
            
            // Add border classes to all rows in the merged group
            $(`tr[data-order-id="${orderId}"]`).each(function(index) {
                const $thisRow = $(this);
                if (index === 0) {
                    $thisRow.addClass('first-merged-row');
                } else if (index === rowspan - 1) {
                    $thisRow.addClass('last-merged-row');
                } else {
                    $thisRow.addClass('middle-merged-row');
                }
            });
        }
    });
}

function attachEventListeners() {
    // Remove existing event listeners to prevent duplicates
    $(document).off('click', '.deleteBtn');
    
    // Use event delegation for delete buttons
    $(document).on('click', '.deleteBtn', function () {
        let id = $(this).data('id');
        let date = $(this).data('date');
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You want to delete this meal order for ${date}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteOrder(id, date);
            }
        });
    });
}

async function deleteOrder(orderId, date) {
    showLoader();
    try {
        const res = await axios.delete(`/admin/meal-order/${orderId}`, {
            data: { meal_date: date }
        });
        
        if (res.data.status === 'success') {
            successToast(res.data.message || 'Order deleted successfully');
            getList(); // Refresh the list
        } else {
            errorToast(res.data.message || 'Failed to delete order');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
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
                message = data?.message || "Order not found.";
                break;
            default:
                message = data?.message || "Something went wrong.";
        }
    } else if (error.request) {
        message = "No response from server. Please check your connection.";
    } else {
        message = error.message;
    }
    errorToast(message);
}
</script>

<style>
.customer-info {
    line-height: 1.2;
}

.order-link {
    text-decoration: none;
    transition: all 0.2s;
}

.order-link:hover {
    text-decoration: underline;
    color: #0a58ca !important;
}

/* Merged cell styling */
.merged-cell {
    background-color: #f8f9fa !important;
    vertical-align: middle !important;
    position: relative;
    border-right: 2px solid #dee2e6 !important;
}

.customer-name-cell {
    border-left: 2px solid #dee2e6 !important;
}

.order-number-cell {
    background-color: #f8f9fa !important;
}

/* Row border styling for merged groups */
.first-merged-row td:not(.merged-cell) {
    border-top: 2px solid #007bff !important;
}

.last-merged-row td:not(.merged-cell) {
    border-bottom: 2px solid #007bff !important;
}

.middle-merged-row td:not(.merged-cell) {
    border-top: 1px solid #dee2e6 !important;
}

/* Hover effect */
#mealOrderTable tbody tr:hover {
    background-color: #f8f9fa;
}

#mealOrderTable tbody tr:hover .merged-cell {
    background-color: #e9ecef !important;
}

/* Button group styling */
.btn-group .btn {
    padding: 0.25rem 0.5rem;
    margin: 0 2px;
}

/* Badge styling */
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

/* DataTable pagination styling */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.375rem 0.75rem;
    margin-left: 2px;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #0d6efd;
    color: white !important;
    border-color: #0d6efd;
}

/* Ensure proper vertical alignment in merged cells */
#mealOrderTable td {
    vertical-align: middle;
}
</style>