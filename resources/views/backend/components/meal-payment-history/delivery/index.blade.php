<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Delivery Payment List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Delivery Date</th>
                    <th>Invoice No</th>
                    <th>Rider Name</th>
                    <th>Delivery Charge</th>
                    <th>Rider Platform</th>
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
    loadDeliveryList();
});

async function loadDeliveryList() {
    try {
        showLoader();

        const res = await axios.get('/admin/get/meal-delivery/payments');

        if (res.status === 200 && res.data.status === 'success') {
            const tableList = $('#tableList');
            tableList.empty();

            if ($.fn.DataTable.isDataTable('#mealTable')) {
                $('#mealTable').DataTable().clear().destroy();
            }

            res.data.data.forEach(item => {

                // Payment status badge
                let paymentBadgeClass = 'bg-secondary';
                switch (item.payment_status) {
                    case 'paid':      paymentBadgeClass = 'bg-success'; break;
                    case 'due':       paymentBadgeClass = 'bg-danger';  break;
                    case 'cancelled': paymentBadgeClass = 'bg-dark';    break;
                }
                const paymentBadge = `
                    <span class="badge ${paymentBadgeClass}">
                        ${toTitleCase(item.payment_status || 'Unknown')}
                    </span>`;

                // View button
                const viewButton = `
                    <a href="/admin/meal-delivery/payment/details/${item.delivery_charge_ledger_id}"
                       class="btn btn-sm btn-info">
                        <i class="mdi mdi-eye-circle"></i> View
                    </a>`;

                // Payment button — show only when delivered AND payment due
                const paymentButton = (item.is_delivered && item.payment_status === 'due')
                    ? `<button
                            class="btn btn-sm btn-success paymentBtn"
                            data-ledger-id="${item.delivery_charge_ledger_id}"
                            data-payable-amount="${item.payable_amount}">
                            <i class="mdi mdi-credit-card-outline"></i> Payment
                       </button>`
                    : '';

                const row = `
                    <tr>
                        <td>${item.sl}</td>
                        <td>${item.delivery_date || '-'}</td>
                        <td>${item.invoice_no || '-'}</td>
                        <td>${item.delivery_person_name || '-'}</td>
                        <td>£${item.delivery_charge}</td>
                        <td>
                            <span class="text-success fw-semibold">
                                £${item.rider_platform_fee}
                            </span>
                        </td>
                        <td><strong>£${item.payable_amount}</strong></td>
                        <td>${paymentBadge}</td>
                        <td class="d-flex gap-2">
                            ${viewButton}
                            ${paymentButton}
                        </td>
                    </tr>`;

                tableList.append(row);
            });

            initializeDataTable();
            attachEventListeners();

        } else {
            errorToast(res.data.message || 'Failed to fetch delivery payments.');
        }

    } catch (error) {
        handleError(error); // ← global config.js
    } finally {
        hideLoader();
    }
}

function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#mealTable')) {
        $('#mealTable').DataTable().destroy();
    }
    $('#mealTable').DataTable({
        paging:     true,
        searching:  true,
        ordering:   true,
        autoWidth:  false,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
        order:      [[0, 'desc']],
        columnDefs: [{ targets: 8, orderable: false }]  // Action column
    });
}

function attachEventListeners() {
    $('.paymentBtn').off('click').on('click', function () {
        const ledgerId      = $(this).data('ledger-id');
        const payableAmount = $(this).data('payable-amount');
        $('#ledgerID').val(ledgerId);
        $('#deiveryChargeID').val(payableAmount); // ← now uses payable_amount (net after rider fee)
        $('#paymentModal').modal('show');
    });
}

function toTitleCase(str) {
    if (!str) return '';
    return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}
</script>