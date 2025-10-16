<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Order List Information</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="orderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Delivery Type</th>
                    <th>Payment Type</th>
                    <th>Billed Amount</th>
                    <th>Status</th>
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
            let res = await axios.get("/admin/get/orders");
            if (res.status === 200 && res.data.status === 'success') {
                
                let tableList = $("#tableList");
                tableList.empty();

                res.data.data.forEach(function (item, index) {
                    let customer = item.customer || {};
                    let firstName = customer.firstName || '';
                    let lastName = customer.lastName || '';
                    
                    let deliveryType = item.delivery_type ? item.delivery_type.charAt(0).toUpperCase() + item.delivery_type.slice(1) : '-';

                    let paymentType = item.payment_type ? item.payment_type.charAt(0).toUpperCase() + item.payment_type.slice(1) : '-';


                    let orderDate = item.created_at
                        ? new Date(item.created_at).toLocaleDateString('en-GB', {
                            day: '2-digit', month: 'short', year: 'numeric'
                          })
                        : '-';

                    let amount = item.payable_amount !== null ? `£${parseFloat(item.payable_amount).toFixed(2)}` : '-';

                    let status = item.status || 'unknown';
                    // ✅ Handle all possible status colors
                    let badgeClass = '';
                    switch (status) {
                        case 'pending':
                            badgeClass = 'bg-warning';
                            break;
                        case 'approved':
                            badgeClass = 'bg-primary';
                            break;
                        case 'delivered':
                            badgeClass = 'bg-success';
                            break;
                        case 'canceled':
                            badgeClass = 'bg-danger';
                            break;
                        case 'partially_approved':
                            badgeClass = 'bg-info';
                            break;
                        case 'partially_delivered':
                            badgeClass = 'bg-secondary';
                            break;
                        default:
                            badgeClass = 'bg-light text-dark';
                    }

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${firstName} ${lastName}</td>
                            <td>${orderDate}</td>
                            <td>${deliveryType}</td>
                            <td>${paymentType}</td>
                            <td>${amount}</td>
                            <td>
                                <span class="badge ${badgeClass}">${status}</span>
                            </td>
                            <td>
                                <a href="/admin/invoice/download/${item.id}" class="btn btn-sm btn-outline-primary" title="Invoice Download">
                                   <span class="mdi mdi-arrow-down-circle-outline"></span>
                                </a>

                                <a href="/admin/order/details/${item.id}" class="btn btn-sm btn-outline-primary" title="Order Details">
                                   <span class="mdi mdi-eye-circle"></span>
                                </a>
                                <button data-id="${item.id}" class="btn deleteBtn btn-sm btn-outline-danger" title="Order Delete">
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

