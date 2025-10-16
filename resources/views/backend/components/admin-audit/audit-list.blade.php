<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Contact message list</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Activity Type</th>
                    <th>Message</th>
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
            let res = await axios.get("/admin/audit/list/info");

            let tableList = $("#tableList");
            tableList.empty(); 

            res.data.data.forEach(function (item, index) {
                let limitedMessage = item['message'].substring(0, 50);
                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item['user']['firstName']}</td>
                        <td>${item['user']['role']}</td>
                        <td>${item['user']['email']}</td>
                        <td>${item['activity_type']}</td>
                        <td>${limitedMessage}...</td>

                        <td>
                            <a  href="/admin/audit/details/${item['id']}" class="btn btn-sm btn-outline-primary"><span class="mdi mdi-eye-circle"></span>
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
