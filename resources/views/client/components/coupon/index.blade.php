<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Coupon  List</h5></span>
        <div id="addNewButtonContainer" class="card-header-elements ms-auto">
            <a href="/client/create/coupon" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New
            </a>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="couponTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Provided By</th>
                    <th>Coupon Name</th>
                    <th>Coupon Discount</th>
                    <th>Expire Date</th>
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
            let res = await axios.get("/client/coupon/list");
            let tableList = $("#tableList");
            tableList.empty(); 

            res.data.data.forEach(function (item, index) {
                const client = item.client;
                const fullName = client.lastName ? `${client.firstName} ${client.lastName}` : client.firstName;

                let expireDate = new Date(item.expire_date);
                let currentDate = new Date();

                // Set time to midnight for accurate comparison
                currentDate.setHours(0, 0, 0, 0);
                expireDate.setHours(0, 0, 0, 0);

                // Check if the coupon is still valid
                let statusBadge = expireDate >= currentDate
                    ? `<span class="badge bg-success">Valid</span>`
                    : `<span class="badge bg-danger">Invalid</span>`;

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${fullName}</td>
                        <td>${item.coupon_name}</td>
                        <td>${item.coupon_discount}%</td>
                        <td>${item.expire_date}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <a href="/client/edit/coupon/${item['id']}" class="btn btn-sm btn-outline-success"><span class="mdi mdi-pencil-outline"></span></a>

                            <button data-id="${item['id']}" class="btn deleteBtn btn-sm btn-outline-danger"><span class="mdi mdi-trash-can-outline"></span></button>
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

