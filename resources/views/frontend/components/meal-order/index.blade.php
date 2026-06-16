@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Orders</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('meal.order')}}" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New Meal Plan
            </a>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealOrderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Order No</th>
                    <th>Meal Date</th>
                    <th>Meal Type</th>
                    <th>Calories</th>
                    <th>Suggested Meal Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList"></tbody>
        </table>
    </div>
</div>
@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {
    getList(); 
});

let allMealTypes = [];

async function getAllMealTypes() {
    showLoader();
    try {
        const res = await axios.get("/get/meal-types");

        if (res.status === 200 && res.data.status === "success" && Array.isArray(res.data.data)) {
            allMealTypes = res.data.data.map(item => item.name);
        } else {
            errorToast(res.data.message || "Failed to load meal types.");
            allMealTypes = []; 
        }
    } catch (error) {
        handleError(error);
        allMealTypes = []; 
    } finally {
        hideLoader();
    }
}

async function getList() {
    showLoader();
    try {
        await getAllMealTypes();

        let res = await axios.get("/get/meal-order");
        //console.log('API Response:', res.data); 
        
        if (res.status === 200 && res.data.status === 'success') {
            let tableList = $("#tableList");
            tableList.empty();

            const today = new Date().toISOString().split("T")[0];
            let slNo = 1;

            // Group data by order_number and sort dates within each order
            const groupedData = {};
            res.data.data.forEach(function (item) {
                if (!groupedData[item.order_number]) {
                    groupedData[item.order_number] = [];
                }
                groupedData[item.order_number].push(item);
            });

            // Sort order numbers and process each group
            const sortedOrderNumbers = Object.keys(groupedData).sort((a, b) => b - a);
            
            sortedOrderNumbers.forEach(function (orderNumber) {
                const orderItems = groupedData[orderNumber];
                
                // Sort items by meal_date in descending order
                orderItems.sort((a, b) => new Date(b.meal_date) - new Date(a.meal_date));
                
                let isFirstRow = true;
                let rowCount = orderItems.length;

                orderItems.forEach(function (item, itemIndex) {
                    // Format meal date
                    const formattedDate = new Date(item.meal_date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });

                    // Format meal types
                    let formattedMealTypes = item.meal_types 
                        ? item.meal_types
                            .split(",")
                            .map(t => t.trim())
                            .map(t => t.charAt(0).toUpperCase() + t.slice(1))
                            .join(", ")
                        : '-';

                    // Build suggested meal types
                    let suggestedHTML = '';
                    const isFutureDate = item.meal_date > today;
                    
                    if (isFutureDate && item.remaining_meal_types && item.remaining_meal_types.length > 0) {
                        // Create buttons for each remaining meal type
                        item.remaining_meal_types.forEach(type => {
                            if (type && type.trim() !== '') {
                                suggestedHTML += `<button class="btn btn-sm btn-outline-success suggest-btn me-1 mb-1" 
                                                    data-id="${item.order_id}" 
                                                    data-type="${type}" 
                                                    data-date="${item.meal_date}">
                                                    ${type.charAt(0).toUpperCase() + type.slice(1)}
                                                  </button>`;
                            }
                        });
                        
                        // If no valid buttons were created, show message
                        if (suggestedHTML === '') {
                            suggestedHTML = `<span class="text-muted">All meal types added</span>`;
                        }
                    } else if (item.remaining_meal_types && item.remaining_meal_types.length === 0) {
                        suggestedHTML = `<span class="text-muted">All meal types added</span>`;
                    } else {
                        suggestedHTML = `<span class="text-muted">N/A</span>`;
                    }

                    let row = `
                        <tr class="order-group-row" data-order-number="${orderNumber}" data-row-index="${itemIndex}">
                            <td>${isFirstRow ? slNo : ''}</td>
                            <td class="order-number-cell">
                                ${isFirstRow ? 
                                    `<a href="/meal-order/details/${item.order_id}" class="order-number-link" title="View Order Details">
                                        ${orderNumber}
                                    </a>` 
                                    : ''
                                }
                            </td>
                            <td>
                                <a href="/meal-order/${item.order_id}/date/${item.meal_date}" class="date-link" title="View Order Details By Date">
                                    ${formattedDate}
                                </a>
                            </td>
                            <td>${formattedMealTypes}</td>
                            <td>${item.calories ?? 0} kcal</td>
                            <td>${suggestedHTML}</td> 
                            <td>
                                <a href="/meal-order/details/${item.order_id}" class="btn btn-sm btn-outline-primary" title="Order Details">
                                    <span class="mdi mdi-eye-circle"></span>
                                </a>
                                <button data-id="${item.order_id}" class="btn deleteBtn btn-sm btn-outline-danger" title="Delete">
                                    <span class="mdi mdi-trash-can-outline"></span>
                                </button>
                            </td>
                        </tr>`;
                    tableList.append(row);
                    isFirstRow = false;
                });
                
                slNo++; // Increment SL No only after processing all items for this order
            });

            // Apply visual merging after all rows are added
            applyVisualRowMerge();
            
            // Initialize DataTable
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

function applyVisualRowMerge() {
    // Group rows by order number
    const orderGroups = {};
    $('.order-group-row').each(function() {
        const orderNumber = $(this).data('order-number');
        if (!orderGroups[orderNumber]) {
            orderGroups[orderNumber] = [];
        }
        orderGroups[orderNumber].push(this);
    });

    // Apply visual merging for each order group
    Object.values(orderGroups).forEach(rows => {
        if (rows.length > 1) {
            const firstRow = $(rows[0]);
            const orderNumberCell = firstRow.find('.order-number-cell');
            const slNoCell = firstRow.find('td:first');
            
            // Calculate total height for visual merge
            let totalHeight = 0;
            rows.forEach(row => {
                totalHeight += $(row).outerHeight();
            });
            
            // Add visual merge class
            orderNumberCell.addClass('visually-merged-cell');
            slNoCell.addClass('visually-merged-cell');
            
            // Add grouping classes to all rows in the group
            rows.forEach((row, index) => {
                const $row = $(row);
                $row.addClass('merged-row-group');
                if (index === 0) {
                    $row.addClass('first-merged-row');
                } else if (index === rows.length - 1) {
                    $row.addClass('last-merged-row');
                } else {
                    $row.addClass('middle-merged-row');
                }
            });
        }
    });
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
        "order": [[2, 'desc']], // Default sort by Meal Date descending
        "columnDefs": [
            {
                "targets": 0, // SL No column
                "orderable": false,
                "className": "text-center"
            },
            {
                "targets": 1, // Order No column
                "orderable": true,
                "className": "text-center"
            },
            {
                "targets": 2, // Meal Date column
                "orderable": true,
                "type": "date"
            },
            {
                "targets": 4, // Calories column
                "orderable": true,
                "className": "text-center"
            },
            {
                "targets": 5, // Suggested Meal Type column
                "orderable": false
            },
            {
                "targets": 6, // Action column
                "orderable": false,
                "className": "text-center"
            }
        ],
        "drawCallback": function(settings) {
            // Re-apply visual merging after DataTable redraws
            setTimeout(() => {
                applyVisualRowMerge();
                attachEventListeners();
            }, 100);
        },
        "initComplete": function(settings, json) {
            // Apply visual merging after table initialization
            setTimeout(() => {
                applyVisualRowMerge();
            }, 100);
        }
    });
}

function attachEventListeners() {
    // Remove existing event listeners to prevent duplicates
    $(document).off('click', '.deleteBtn');
    $(document).off('click', '.suggest-btn');

    // Use event delegation for dynamically created buttons
    $(document).on('click', '.deleteBtn', function () {
        let id = $(this).data('id');
        $("#deleteID").val(id);
        $("#delete-modal").modal('show');
    });

    $(document).on('click', '.suggest-btn', function () {
        let type = $(this).data('type');
        let date = $(this).data('date'); 
        let orderId = $(this).data('id');
        
        console.log('Suggest button clicked:', { type, date, orderId });
        
        window.location.href = "{{ route('meal.order') }}" 
                               + "?meal_type=" + encodeURIComponent(type) 
                               + "&meal_date=" + encodeURIComponent(date)
                               + "&order_id=" + encodeURIComponent(orderId);
    });
}

function handleError(error) {
    let message = "An unexpected error occurred.";
    if (error.response) {
        const { status, data } = error.response;
        switch (status) {
            case 500:
                message = data?.error || "Internal server error. Please try again later.";
                break;
            case 404:
                message = data?.message || "Data not found.";
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
.visually-merged-cell {
    position: relative;
    background-color: #f8f9fa;
    font-weight: 600;
}

.order-number-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
    padding: 8px 12px;
    display: block;
}

.order-number-link:hover {
    color: #0056b3;
    text-decoration: underline;
}

.merged-row-group td {
    border-bottom: 1px solid #dee2e6;
}

.first-merged-row td {
    border-top: 2px solid #007bff !important;
}

.middle-merged-row td.order-number-cell,
.middle-merged-row td:first-child {
    border-top: none !important;
    background-color: #f8f9fa;
}

.last-merged-row {
    border-bottom: 2px solid #007bff !important;
}

.last-merged-row td.order-number-cell,
.last-merged-row td:first-child {
    border-bottom: 2px solid #007bff !important;
}

/* Hide the empty cells in middle rows */
.middle-merged-row td.order-number-cell:empty,
.middle-merged-row td:first-child:empty {
    background-color: #f8f9fa;
}

/* Add vertical alignment for better appearance */
#mealOrderTable td {
    vertical-align: middle;
}

/* Optional: Add some padding for better spacing */
.visually-merged-cell {
    padding: 12px 8px !important;
}

/* Ensure the link takes full cell space */
.order-number-cell {
    position: relative;
}
</style>



