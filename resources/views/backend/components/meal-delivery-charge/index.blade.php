<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Delivery Charges</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('create.meal.delivery.charge')}}" 
               type="button" 
               class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New
            </a>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealDeliveryChargeTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Meal Type</th>
                    <th>2 km</th>
                    <th>5 km</th>
                    <th>10 km</th>
                    <th>Above 10 km</th>
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
        let res = await axios.get("/admin/get/meal-delivery/charges");

        if (res.status === 200 && res.data.status === 'success') {

            let tableList = $("#tableList");
            tableList.empty();

            res.data.data.forEach(function (item, index) {

                let mealTypeName = item.meal_type?.name
                                ? item.meal_type.name.charAt(0).toUpperCase() + item.meal_type.name.slice(1)
                                : "-";

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${mealTypeName}</td>
                        <td>${item.inside_city_2km}</td>
                        <td>${item.inside_city_5km}</td>
                        <td>${item.inside_city_10km}</td>
                        <td>${item.inside_city_above_10km}</td>
                        <td>
                            <a href="/admin/edit/meal-delivery/charge/${item.id}" 
                               class="btn btn-sm btn-outline-success" 
                               title="Edit">
                                <span class="mdi mdi-pencil-outline"></span>
                            </a>

                            <button data-id="${item.id}" 
                                    class="btn deleteBtn btn-sm btn-outline-danger" 
                                    title="Delete">
                                <span class="mdi mdi-trash-can-outline"></span>
                            </button>
                        </td>
                    </tr>
                `;

                tableList.append(row);
            });

            initializeDataTable();
            attachEventListeners();

        } else {
            errorToast(res.data.message || "Failed to load delivery charges.");
        }

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}


function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#mealDeliveryChargeTable')) {
        $('#mealDeliveryChargeTable').DataTable().destroy();
    }

    $('#mealDeliveryChargeTable').DataTable({
        paging: true,
        ordering: true,
        searching: true,
        autoWidth: false,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
    });
}


function attachEventListeners() {
    $('.deleteBtn').on('click', function () {
        let id = $(this).data('id');
        $("#deleteID").val(id);
        $("#delete-modal").modal('show');
    });
}
</script>
