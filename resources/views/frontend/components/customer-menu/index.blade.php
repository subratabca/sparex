@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Menu List</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('create.customer.menu')}}" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New
            </a>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealTypeTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Menu Type</th>
                    <th>Menu Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList">
                
            </tbody>
        </table>
    </div>
</div>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function () {
        getList(); 
    });

    async function getList() {
        showLoader();
        try {
            let res = await axios.get("/user/get/customer-menus");
            if (res.status === 200 && res.data.status === 'success') {
                //console.log('--------',res);
                let tableList = $("#tableList");
                tableList.empty();

                res.data.data.forEach(function (item, index) {
                    let mealTypeName = item.meal_type.name ? item.meal_type.name : '-';
                    let menuName = item.name ? item.name.charAt(0).toUpperCase() + item.name.slice(1) : '-';

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${mealTypeName}</td>
                            <td>${menuName}</td>
                            <td>
                                <a href="/user/edit/customer-menu/${item.id}" class="btn btn-sm btn-outline-success" title="Edit Menu">
                                    <span class="mdi mdi-pencil-outline"></span>
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
        if ($.fn.DataTable.isDataTable('#mealTypeTable')) {
            $('#mealTypeTable').DataTable().destroy();
        }

        $('#mealTypeTable').DataTable({
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
