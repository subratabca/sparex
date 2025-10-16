<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Follower List (<span id="totalFollowers">0</span>)</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Time</th>
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
            let res = await axios.get("/client/followers-info");

            let tableList = $("#tableList");
            tableList.empty();

            document.getElementById('totalFollowers').textContent = res.data.totalFollowers;
            
            res.data.data.forEach(function (item, index) {
                let updatedAt = new Date(item.updated_at);
                let followDate = updatedAt.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
                
                let followTime = updatedAt.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });

                let customerName = item.customer
                    ? `${item.customer.firstName} ${item.customer.lastName || ''}`.trim()
                    : 'Unknown Customer';

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${customerName}</td>
                        <td>${followDate}</td>
                        <td>${followTime}</td>
                        <td>
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
