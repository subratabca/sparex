<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Order List Information</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="orderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Order Date</th>
                    <th>Invoice No</th>
                    <th>Client Name</th>
                    <th>Payable Amount</th>
                    <th>Payment Status</th>
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
        const res = await axios.get('/admin/get/meal/payments');

        if (res.status === 200 && res.data.status === 'success') {
            const tableList = $('#tableList');
            tableList.empty();

            if ($.fn.DataTable.isDataTable('#orderTable')) {
                $('#orderTable').DataTable().clear().destroy();
            }

            res.data.data.forEach(function (item, index) {
                const paymentStatus = item.payment_status || 'unknown';

                let badgeClass = 'bg-secondary';
                switch (paymentStatus.toLowerCase()) {
                    case 'due':      badgeClass = 'bg-danger';          break;
                    case 'paid':     badgeClass = 'bg-success';         break;
                    case 'pending':  badgeClass = 'bg-warning';         break;
                    case 'refunded': badgeClass = 'bg-info';            break;
                    case 'failed':   badgeClass = 'bg-danger';          break;
                    default:         badgeClass = 'bg-light text-dark'; break;
                }

                const viewButton = `
                    <a href="/admin/meal/payment/details/client/${item.client_id}/order/${item.order_id}"
                       class="btn btn-sm btn-info" title="View Details">
                        <span class="mdi mdi-eye-circle"></span>
                    </a>`;

                const paymentButton = paymentStatus.toLowerCase() === 'due'
                    ? `<button
                            class="btn btn-sm btn-success paymentBtn"
                            data-client-meal-order-id="${item.id}"
                            data-payable-amount="${item.payable_amount}"
                            title="Process Payment">
                            <i class="mdi mdi-credit-card-outline"></i> Payment
                       </button>`
                    : '';

                tableList.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.order_date || '-'}</td>
                        <td>
                            <a href="/admin/meal-order/details/${item.order_id}"
                               class="text-primary text-decoration-underline"
                               target="_blank">
                               ${item.invoice_no || '-'}
                            </a>
                        </td>
                        <td>${item.client_name || '-'}</td>
                        <td>${parseFloat(item.payable_amount || 0).toFixed(2)}</td>
                        <td>
                            <span class="badge ${badgeClass}">
                                ${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            ${viewButton}
                            ${paymentButton}
                        </td>
                    </tr>
                `);
            });

            initializeDataTable();
            attachEventListeners();

        } else {
            errorToast(res.data.message || 'Failed to fetch payment records.');
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
        order:      [[1, 'desc']],
    });
}

function attachEventListeners() {
    // .off() prevents duplicate listeners on each reload
    $('.paymentBtn').off('click').on('click', function () {
        const clientMealOrderId = $(this).data('client-meal-order-id');
        const payableAmount     = $(this).data('payable-amount');
        $('#clientMealOrderID').val(clientMealOrderId);
        $('#payableAmount').val(payableAmount);
        $('#paymentModal').modal('show');
    });
}
</script>