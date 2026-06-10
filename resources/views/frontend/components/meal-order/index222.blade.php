@extends('frontend.components.dashboard.dashboard-master')

@section('dashboard-content')

<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Orders</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('meal.plan') }}"
               class="btn btn-primary waves-effect waves-light">
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

@push('styles')
<style>
.visually-merged-cell {
    position:         relative;
    background-color: #f8f9fa;
    font-weight:      600;
    padding:          12px 8px !important;
}
.order-number-link {
    color:           #007bff;
    text-decoration: none;
    font-weight:     600;
    padding:         8px 12px;
    display:         block;
}
.order-number-link:hover {
    color:           #0056b3;
    text-decoration: underline;
}
.date-link {
    color:           #0d6efd;
    text-decoration: none;
    font-weight:     500;
}
.date-link:hover {
    color:           #0a58ca;
    text-decoration: underline;
}
.merged-row-group td       { border-bottom: 1px solid #dee2e6; }
.first-merged-row td       { border-top:    2px solid #007bff !important; }
.middle-merged-row td.order-number-cell,
.middle-merged-row td:first-child {
    border-top:       none !important;
    background-color: #f8f9fa;
}
.last-merged-row td.order-number-cell,
.last-merged-row td:first-child {
    border-bottom: 2px solid #007bff !important;
}
#mealOrderTable td        { vertical-align: middle; }
.order-number-cell        { position: relative; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    getList();
});

let allMealTypes = [];

async function getAllMealTypes() {
    try {
        const res = await axios.get('/get/meal-types');
        if (res.status === 200 && res.data.status === 'success' && Array.isArray(res.data.data)) {
            allMealTypes = res.data.data.map(item => item.name);
        } else {
            errorToast(res.data.message || 'Failed to load meal types.');
            allMealTypes = [];
        }
    } catch (error) {
        handleError(error); // ← global config.js
        allMealTypes = [];
    }
}

async function getList() {
    try {
        showLoader();
        await getAllMealTypes();

        const res = await axios.get('/user/get/meal-order');

        if (res.status === 200 && res.data.status === 'success') {
            const tableList = $('#tableList');
            tableList.empty();

            const today = new Date().toISOString().split('T')[0];
            let slNo    = 1;

            // Group by order_number
            const groupedData = {};
            res.data.data.forEach(item => {
                (groupedData[item.order_number] ||= []).push(item);
            });

            // Sort order numbers descending
            Object.keys(groupedData)
                .sort((a, b) => b - a)
                .forEach(orderNumber => {
                    const orderItems = groupedData[orderNumber];

                    // Sort items by meal_date descending
                    orderItems.sort((a, b) => new Date(b.meal_date) - new Date(a.meal_date));

                    let isFirstRow = true;

                    orderItems.forEach((item, itemIndex) => {
                        const formattedDate = new Date(item.meal_date + 'T00:00:00')
                            .toLocaleDateString('en-GB', {
                                year:  'numeric',
                                month: 'short',
                                day:   'numeric'
                            });

                        const formattedMealTypes = item.meal_types
                            ? item.meal_types.split(',')
                                .map(t => t.trim())
                                .map(t => t.charAt(0).toUpperCase() + t.slice(1))
                                .join(', ')
                            : '-';

                        // Suggested meal types — uses controller's is_future_date
                        let suggestedHTML = '';
                        if (item.is_future_date && item.remaining_meal_types?.length > 0) {
                            item.remaining_meal_types.forEach(type => {
                                if (type && type.trim() !== '') {
                                    suggestedHTML += `
                                        <button class="btn btn-sm btn-outline-success suggest-btn me-1 mb-1"
                                                data-id="${item.order_id}"
                                                data-type="${type}"
                                                data-date="${item.meal_date}">
                                            ${type.charAt(0).toUpperCase() + type.slice(1)}
                                        </button>`;
                                }
                            });
                            if (suggestedHTML === '') {
                                suggestedHTML = `<span class="text-muted">All meal types added</span>`;
                            }
                        } else if (item.remaining_meal_types?.length === 0) {
                            suggestedHTML = `<span class="text-muted">All meal types added</span>`;
                        } else {
                            suggestedHTML = `<span class="text-muted">N/A</span>`;
                        }

                        tableList.append(`
                            <tr class="order-group-row"
                                data-order-number="${orderNumber}"
                                data-row-index="${itemIndex}">
                                <td>${isFirstRow ? slNo : ''}</td>
                                <td class="order-number-cell">
                                    ${isFirstRow
                                        ? `<a href="/user/meal-order/details/${item.order_id}"
                                               class="order-number-link"
                                               title="View Order Details">${orderNumber}</a>`
                                        : ''}
                                </td>
                                <td>
                                    <a href="/user/meal-order/${item.order_id}/date/${item.meal_date}"
                                       class="date-link"
                                       title="View Order Details By Date">${formattedDate}</a>
                                </td>
                                <td>${formattedMealTypes}</td>
                                <td>${item.calories ?? 0} kcal</td>
                                <td>${suggestedHTML}</td>
                                <td>
                                    <a href="/user/meal-order/details/${item.order_id}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Order Details">
                                        <span class="mdi mdi-eye-circle"></span>
                                    </a>
                                    <button data-id="${item.order_id}"
                                            class="btn deleteBtn btn-sm btn-outline-danger"
                                            title="Delete">
                                        <span class="mdi mdi-trash-can-outline"></span>
                                    </button>
                                </td>
                            </tr>`);

                        isFirstRow = false;
                    });

                    slNo++;
                });

            applyVisualRowMerge();
            initializeDataTable();
            attachEventListeners();

        } else {
            errorToast(res.data.message || 'Failed to fetch orders.');
        }
    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
    }
}

function applyVisualRowMerge() {
    const orderGroups = {};
    $('.order-group-row').each(function () {
        const orderNumber = $(this).data('order-number');
        (orderGroups[orderNumber] ||= []).push(this);
    });

    Object.values(orderGroups).forEach(rows => {
        if (rows.length > 1) {
            const firstRow        = $(rows[0]);
            const orderNumberCell = firstRow.find('.order-number-cell');
            const slNoCell        = firstRow.find('td:first');

            orderNumberCell.addClass('visually-merged-cell');
            slNoCell.addClass('visually-merged-cell');

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
        paging:     true,
        serverSide: false,
        autoWidth:  false,
        ordering:   true,
        searching:  true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
        order:      [[2, 'desc']],
        columnDefs: [
            { targets: 0, orderable: false, className: 'text-center' },
            { targets: 1, orderable: true,  className: 'text-center' },
            { targets: 2, orderable: true,  type: 'date' },
            { targets: 4, orderable: true,  className: 'text-center' },
            { targets: 5, orderable: false },
            { targets: 6, orderable: false, className: 'text-center' },
        ],
        drawCallback: function () {
            setTimeout(() => {
                applyVisualRowMerge();
                attachEventListeners();
            }, 100);
        },
        initComplete: function () {
            setTimeout(() => applyVisualRowMerge(), 100);
        }
    });
}

function attachEventListeners() {
    $(document).off('click', '.deleteBtn').on('click', '.deleteBtn', function () {
        $('#deleteID').val($(this).data('id'));
        $('#delete-modal').modal('show');
    });

    $(document).off('click', '.suggest-btn').on('click', '.suggest-btn', function () {
        const type    = $(this).data('type');
        const date    = $(this).data('date');
        const orderId = $(this).data('id');

        window.location.href = "{{ route('meal.plan') }}"
            + "?meal_type=" + encodeURIComponent(type)
            + "&meal_date=" + encodeURIComponent(date)
            + "&order_id="  + encodeURIComponent(orderId);
    });
}
</script>
@endpush