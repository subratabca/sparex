<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Order List Information</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="orderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Product Owner</th>
                    <th>Customer</th>
                    <th>Order No</th>
                    <th>Invoice No</th>
                    <th>Bill Amount</th>
                    <th>Order Date</th>
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
        let url = window.location.pathname;
        let segments = url.split('/');
        let client_id = segments[segments.length - 1];

        showLoader();
        try {
            let res = await axios.get("/admin/get/order/list/by/client/" + client_id);
            if (res.status === 200 && res.data.status === 'success') {
                let tableList = $("#tableList");
                tableList.empty();

                res.data.data.forEach(function (item, index) {
                    let clientName = `${item.client['firstName']}${item.client['lastName'] ? ' ' + item.client['lastName'] : ''}`;
                    let customer = item.order.customer || {};
                    let firstName = customer.firstName || '';
                    let lastName = customer.lastName || '';
                    let orderNo = item.order.order_number || '-';
                    let invoiceNo = item.order.invoice_no || '-';
                    let orderDate = item.order.order_date || '-';
                    let amount = item.payable_amount !== null ? `£${parseFloat(item.payable_amount).toFixed(2)}` : '-';
                    let status = item.order.status || 'unknown';
                    let badgeClass = status === 'pending' ? 'bg-danger' : 'bg-success';

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${clientName}</td>
                            <td>${firstName} ${lastName}</td>
                            <td>
                               <a href="/admin/order/details/${item.order_id}" target="_blank" title="Order details">${orderNo}
                               </a>
                            </td>
                            <td>${invoiceNo}</td>
                            <td>${amount}</td>
                            <td>${orderDate}</td>
                            <td>
                                <span class="badge ${badgeClass}">${status}</span>
                            </td>
                            <td>
                                <a href="/admin/order/details/${item.order_id}" class="btn btn-sm btn-outline-primary" target="_blank" title="Order details"><span class="mdi mdi-eye-circle"></span>
                                </a>
                            </td>
                        </tr>
                    `;

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

