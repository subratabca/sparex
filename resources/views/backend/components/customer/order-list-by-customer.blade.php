<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Order List Information</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="orderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Customer</th>
                    <th>Product Owner</th>
                    <th>Order No</th>
                    <th>Invoice No</th>
                    <th>Bill Amount</th>
                    <th>Order Date</th>
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
    let url = window.location.pathname;
    let segments = url.split('/');
    let customer_id = segments[segments.length - 1];

    showLoader();
    try {
        let res = await axios.get("/admin/get/order/list/by/customer/" + customer_id);
        if (res.status === 200 && res.data.status === 'success') {
            let data = res.data.data;
            let tableList = $("#tableList");
            tableList.empty();

            if (data.length === 0) {
                tableList.append('<tr><td colspan="9" class="text-center">No Data Found</td></tr>');
            } else {
                data.forEach(function (item, index) {
                    let customerName = `${item.customer?.firstName || ''} ${item.customer?.lastName || ''}`;
                    let productOwner = item.order_items?.[0]?.client 
                        ? `${item.order_items[0].client.firstName} ${item.order_items[0].client.lastName}` 
                        : 'N/A';

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <a href="/admin/customer/details/${item['customer']['id']}" target="_blank" title="Customer details">${customerName}
                                </a>
                            </td>
                            <td>
                                <a href="/admin/client/details/${item.order_items[0].client.id}" target="_blank" title="Client details">${productOwner}
                                </a>
                            </td>
                            <td>
                                <a href="/admin/order/details/${item.id}" target="_blank" title="Order details">
                                    ${item.order_number}
                                </a>    
                            </td>
                            <td>${item.invoice_no}</td>
                            <td>$${item.payable_amount}</td>
                            <td>${item.order_date}</td>
                            <td>
                                <span class="badge ${item.status === 'pending' ? 'bg-danger' : 'bg-success'}">${item.status}</span>
                            </td>
                            <td>
                                <a href="/admin/order/details/${item.id}" class="btn btn-sm btn-outline-primary" target="_blank" title="Order details">
                                    <span class="mdi mdi-eye-circle"></span>
                                </a>
                            </td>
                        </tr>`;
                    tableList.append(row);
                });
            }

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


