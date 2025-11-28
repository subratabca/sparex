<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meals Order List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="orderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Customer Name</th>
                    <th>Meal Date</th>
                    <th>Meal Type</th>
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
            if (res.status === 200 && res.data.status === 'success') {
                
                let tableList = $("#tableList");
                tableList.empty();

                let data = res.data.data;
                //console.log('meal order------:',data);

                    data.forEach(function (item, index) {
                    let formattedMealTypes = "";

                    if (item.meal_types) {
                        formattedMealTypes = item.meal_types
                            .split(",")
                            .map(t => t.trim())
                            .map(t => t.charAt(0).toUpperCase() + t.slice(1))
                            .join(", ");
                    }
                    
                    let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.customer_name}</td>
                                <td>${item.meal_date}</td>
                                <td>${formattedMealTypes}</td>
                                <td>
                                    <a  href="/admin/meal-order/details/${item['id']}" class="btn btn-sm btn-outline-primary" title="Order Details"><span class="mdi mdi-eye-circle"></span>
                                    </a>
                                </td>
                        </tr>`;
                    tableList.append(row);
                });

                initializeDataTable();
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
        if ($.fn.DataTable.isDataTable('#orderTable')) {
            $('#orderTable').DataTable().destroy();
        }

        $('#orderTable').DataTable({
            paging: true,
            serverSide: false,
            autoWidth: false,
            ordering: true,
            searching: true,
            lengthMenu: [10, 25, 50, 100],
            pageLength: 10
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

