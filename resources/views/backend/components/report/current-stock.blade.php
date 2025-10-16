<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Product List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="productTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Image</th>
                    <th>Client Name</th>
                    <th>Product Name</th>
                    <th>Type</th>
                    <th>Has Variant</th>
                    <th>Current Stock</th>
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
            let res = await axios.get("/admin/current/stock/information");
            if (res.status === 200 && res.data.status === 'success') {
                let tableList = $("#tableList");
                tableList.empty(); 

                res.data.data.forEach(function (item, index) {
                    let isFree = item['is_free'] ? 'Free' : 'Paid';
                    let hasVariants = item['has_variants'] ? 'View Variants' : 'No';
                    let fullName = `${item.client['firstName']}${item.client['lastName'] ? ' ' + item.client['lastName'] : ''}`;

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                ${item['image'] ? `<img src="/upload/product/small/${item['image']}" width="50" height="50">` : `<img src="/upload/no_image.jpg" width="50" height="50">`}
                            </td>
                            <td>${fullName}</td>
                            <td>${item['name']}</td>
                            <td>${isFree}</td>
                            <td>${hasVariants}</td>
                            <td>${item['current_stock']}</td>
                            <td>
                            <span class="badge ${
                                item['status'] === 'pending' ? 'bg-danger' :
                                item['status'] === 'published' ? 'bg-primary' :
                                'bg-success'
                                }">
                                ${item['status']}
                            </span>
                            </td>
                            <td>
                                <a  href="/admin/product/stock-details/${item['id']}" class="btn btn-sm btn-outline-primary"><span class="mdi mdi-eye-circle"></span>
                                </a>

                            </td>
                        </tr>`;
                        tableList.append(row);
                    });
                
                    initializeDataTable();
            } else {
                errorToast(res.data.message || "Failed to fetch products.");
            }
        } catch (error) {
            handleError(error);
        }finally{
            hideLoader();
        }
    }

    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('#productTable')) {
            $('#productTable').DataTable().destroy();
        }

        $('#productTable').DataTable({
            "paging": true,
            "serverSide": false, 
            "autoWidth": false,
            "ordering": true,
            "searching": true, 
            "lengthMenu": [10, 25, 50, 100], 
            "pageLength": 10, 
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


