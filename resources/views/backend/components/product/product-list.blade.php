<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Product List</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="/admin/create/product" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New Product
            </a>
        </div>
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
            let res = await axios.get("/admin/index");
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
                                <a  href="/admin/product/details/${item['id']}" class="btn btn-sm btn-outline-primary" title="Product Details"><span class="mdi mdi-eye-circle"></span>
                                </a>

                                <a href="/admin/edit/product/${item['id']}" class="btn btn-sm btn-outline-success" title="Edit Product"><span class="mdi mdi-pencil-outline"></span>
                                </a>

                                <a href="/admin/edit/product/multi-image/${item['id']}" class="btn btn-sm btn-outline-info" title="Edit Multi Image"><span class="mdi mdi-image-edit-outline"></span>
                                </a>

                                <button data-id="${item['id']}" class="btn deleteBtn btn-sm btn-outline-danger" title="Delete Product"><span class="mdi mdi-trash-can-outline"></span></button>

                            </td>
                        </tr>`;
                        tableList.append(row);
                    });
                
                    initializeDataTable();
                    attachEventListeners();
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


