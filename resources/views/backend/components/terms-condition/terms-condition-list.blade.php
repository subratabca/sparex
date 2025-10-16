<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>T&C List</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="/admin/create/terms-conditions" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New T&C
            </a>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>T&C For</th>
                    <th>Description</th>
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
            let res = await axios.get("/admin/terms-conditions/list/info");

            let tableList = $("#tableList");
            tableList.empty(); 

            res.data.data.forEach(function (item, index) {
                let limitedDescription = item['description'].substring(0, 50);
                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item['name']}</td>
                        <td>${limitedDescription}...</td>

                        <td>
                            <a  href="/admin/terms-conditions/details/${item['id']}" class="btn btn-sm btn-outline-primary"><span class="mdi mdi-eye-circle"></span>
                            </a>

                            <a href="/admin/edit/terms-conditions/${item['id']}" class="btn btn-sm btn-outline-success"><span class="mdi mdi-pencil-outline"></span>
                            </a>

                            <button data-id="${item['id']}" class="btn deleteBtn btn-sm btn-outline-danger">
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
        if ($.fn.DataTable.isDataTable('#foodTable')) {
            $('#foodTable').DataTable().destroy();
        }

        $('#foodTable').DataTable({
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
