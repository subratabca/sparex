@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Orders</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('favourite.meals')}}" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New Meal Plan
            </a>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealOrderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
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

        let res = await axios.get("/user/get/meal-order");
        if (res.status === 200 && res.data.status === 'success') {
            let tableList = $("#tableList");
            tableList.empty();

            const today = new Date().toISOString().split("T")[0];

            res.data.data.forEach(function (item, index) {
                const existingMealTypes = item.meal_types ? item.meal_types.split(",").map(t => t.trim()) : [];
                const remainingMealTypes = allMealTypes.filter(t => !existingMealTypes.includes(t));

                let formattedMealTypes = "";

                if (item.meal_types) {
                    formattedMealTypes = item.meal_types
                        .split(",")
                        .map(t => t.trim())
                        .map(t => t.charAt(0).toUpperCase() + t.slice(1))
                        .join(", ");
                }

                // Build suggested meal buttons
                let suggestedHTML = '';
                if (item.meal_date > today) {
                    if (remainingMealTypes.length > 0) {
                        remainingMealTypes.forEach(type => {
                            suggestedHTML += `<button class="btn btn-sm btn-outline-success suggest-btn me-1" 
                                                data-id="${item.id}" 
                                                data-type="${type}" 
                                                data-date="${item.meal_date}">
                                                ${type}
                                              </button>`;
                        });
                    } else {
                        suggestedHTML = `<span class="text-muted">All added</span>`;
                    }
                } else {
                    suggestedHTML = `<span class="text-muted">N/A</span>`;
                }

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.meal_date}</td>
                        <td>${formattedMealTypes}</td>
                        <td>${item.calories ?? 0} kcal</td>
                        <td>${suggestedHTML}</td> 
                        <td>
                            <a  href="/user/meal-order/details/${item['id']}" class="btn btn-sm btn-outline-primary" title="Order Details"><span class="mdi mdi-eye-circle"></span>
                            </a>
                            <button data-id="${item.id}" class="btn deleteBtn btn-sm btn-outline-danger" title="Delete">
                                <span class="mdi mdi-trash-can-outline"></span>
                            </button>
                        </td>
                    </tr>`;
                tableList.append(row);
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
    });
}

function attachEventListeners() {
    $('.deleteBtn').on('click', function () {
        let id = $(this).data('id');
        $("#deleteID").val(id);
        $("#delete-modal").modal('show');
    });

    $('.suggest-btn').on('click', function () {
        let type = $(this).data('type');
        let date = $(this).data('date'); 
        window.location.href = "{{ route('favourite.meals') }}" 
                               + "?meal_type=" + encodeURIComponent(type) 
                               + "&meal_date=" + encodeURIComponent(date);
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
