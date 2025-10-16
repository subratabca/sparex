<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Delivery Charge List</h5></span>
        <div id="addNewButtonContainer" class="card-header-elements ms-auto">
            <a href="/client/create/delivery-charge" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New
            </a>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="couponTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>in_city_2kg</th>
                    <th>in_city_5kg</th>
                    <th>in_city_10kg</th>
                    <th>in_city_above_10kg</th>
                    <th>out_city_2kg</th>
                    <th>out_city_5kg</th>
                    <th>out_city_10kg</th>
                    <th>out_city_above_10kg</th>
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
        let res = await axios.get("/client/delivery-charge/list");
        let tableList = $("#tableList");
        tableList.empty(); 

        const addButtonContainer = $("#addNewButtonContainer");

        // Show Add New button only if no delivery charge exists
        if (res.data.data.length === 0) {
            addButtonContainer.show();
        } else {
            addButtonContainer.hide();
        }

        res.data.data.forEach(function (item, index) {
            let row = `
                <tr>
                    <td>${item.inside_city_2kg}</td>
                    <td>${item.inside_city_5kg}</td>
                    <td>${item.inside_city_10kg}</td>
                    <td>${item.inside_city_above_10kg}</td>
                    <td>${item.outside_city_2kg}</td>
                    <td>${item.outside_city_5kg}</td>
                    <td>${item.outside_city_10kg}</td>
                    <td>${item.outside_city_above_10kg}</td>
                    <td>
                        <a href="/client/edit/delivery-charge/${item.id}" class="btn btn-sm btn-outline-success">
                            <span class="mdi mdi-pencil-outline"></span>
                        </a>
                        <button data-id="${item.id}" class="btn deleteBtn btn-sm btn-outline-danger">
                            <span class="mdi mdi-trash-can-outline"></span>
                        </button>
                    </td>
                </tr>`;
            tableList.append(row);
        });

        initializeDataTable();
        attachEventListeners();

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#couponTable')) {
        $('#couponTable').DataTable().destroy();
    }

    $('#couponTable').DataTable({
        order: [[0, 'asc']],
        paging: true,
        serverSide: false,
        responsive: true,
        autoWidth: true,
        searching: true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
        columnDefs: [
            { targets: 0, orderable: true, searchable: false },
            { targets: -1, orderable: false, searchable: false }
        ]
    });
}

function attachEventListeners() {
    $(document).on('click', '.deleteBtn', function () {
        let id = $(this).data('id');
        $("#deleteID").val(id);
        const deleteModal = new bootstrap.Modal(document.getElementById('delete-modal'));
        deleteModal.show();
    });
}


// function attachEventListeners() {
//     $('.deleteBtn').on('click', function () {
//         let id = $(this).data('id');
//         $("#deleteID").val(id);
//         $("#delete-modal").modal('show');
//     });
// }

function handleError(error) {
    if (error.response) {
        if (error.response.status === 500) {
            errorToast(error.response.data.error || "An internal server error occurred.");
        } else {
            errorToast("Request failed!");
        }
    } else {
        errorToast("Request failed!");
    }
}
</script>
