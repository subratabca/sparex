<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Order List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="orderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Order Date</th>
                    <th>Invoice No</th>
                    <th>Customer Name</th>
                    <th>Subtotal</th>
                    <th>Tax</th>
                    <th>Service Fee</th>
                    <th>Client Fee</th>
                    <th>Rider Fee</th>
                    <th>Delivery Fee</th>
                    <th>Profit</th>
                    <th>Payable Amount</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList"></tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    getList();
});

async function getList() {
    try {
        showLoader();
        const res = await axios.get('/admin/get/meal-orders');

        if (res.status === 200 && res.data.status === 'success') {
            const tableList = $('#tableList');
            tableList.empty();

            if ($.fn.DataTable.isDataTable('#orderTable')) {
                $('#orderTable').DataTable().clear().destroy();
            }

            res.data.data.forEach(function (item, index) {

                // Payment status badge
                let paymentBadgeClass = 'bg-secondary';
                switch ((item.payment_status || '').toLowerCase()) {
                    case 'paid':     paymentBadgeClass = 'bg-success'; break;
                    case 'due':      paymentBadgeClass = 'bg-danger';  break;
                    case 'pending':  paymentBadgeClass = 'bg-warning'; break;
                    case 'refunded': paymentBadgeClass = 'bg-info';    break;
                    case 'failed':   paymentBadgeClass = 'bg-dark';    break;
                }

                // Order status badge
                let orderBadgeClass = 'bg-secondary';
                switch ((item.status || '').toLowerCase()) {
                    case 'pending':   orderBadgeClass = 'bg-warning'; break;
                    case 'completed': orderBadgeClass = 'bg-success'; break;
                    case 'cancelled': orderBadgeClass = 'bg-danger';  break;
                }

                const paymentStatus = item.payment_status || 'Unknown';
                const orderStatus   = item.status         || 'Unknown';

                tableList.append(`
                    <tr>
                        <td>${item.sl}</td>
                        <td>${item.order_date || '-'}</td>
                        <td>
                            <a href="/admin/meal-order/details/${item.order_id}"
                               class="text-primary text-decoration-underline"
                               target="_blank">
                               ${item.invoice_no || '-'}
                            </a>
                        </td>
                        <td>${item.customer_name || '-'}</td>
                        <td>£${item.subtotal}</td>
                        <td>£${item.tax}</td>
                        <td>
                            <span class="text-success fw-semibold">£${item.service_fee}</span>
                        </td>
                        <td>
                            <span class="text-success fw-semibold">£${item.client_platform_fee}</span>
                        </td>
                        <td>
                            <span class="text-success fw-semibold">£${item.rider_platform_fee}</span>
                        </td>
                        <td>£${item.delivery_fee}</td>
                        <td>
                            <span class="badge bg-success fs-6">£${item.profit}</span>
                        </td>
                        <td>
                            <strong>£${item.payable_amount}</strong>
                        </td>
                        <td>
                            <span class="badge ${paymentBadgeClass}">
                                ${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${orderBadgeClass}">
                                ${orderStatus.charAt(0).toUpperCase() + orderStatus.slice(1)}
                            </span>
                        </td>
                        <td>
                            <a href="/admin/meal-order/details/${item.order_id}"
                               class="btn btn-sm btn-info"
                               title="View Details">
                                <span class="mdi mdi-eye-circle"></span>
                            </a>
                        </td>
                    </tr>
                `);
            });

            initializeDataTable();

        } else {
            errorToast(res.data.message || 'Failed to fetch meal orders.');
        }
    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
    }
}

function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#orderTable')) {
        $('#orderTable').DataTable().destroy();
    }
    $('#orderTable').DataTable({
        paging:     true,
        serverSide: false,
        autoWidth:  false,
        ordering:   true,
        searching:  true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
        order:      [[1, 'desc']], // Sort by order date descending
        columnDefs: [{ targets: [14], orderable: false }]
    });
}
</script>