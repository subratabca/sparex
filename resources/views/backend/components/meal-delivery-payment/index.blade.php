<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Delivery Payment List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Order No</th>
                    <th>Meal Type</th>
                    <th>Date</th>
                    <th>Delivery Charge</th>
                    <th>Payment Status</th>
                    <th>Delivery Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList"></tbody>
        </table>
    </div>
</div>



<script>
document.addEventListener("DOMContentLoaded", function () {
    loadDeliveryList();
});

async function loadDeliveryList() {
    try {
        showLoader();

        const res = await axios.get("/admin/get/meal-delivery/payments");

        const tableList = $("#tableList");
        tableList.empty();

        res.data.data.forEach(item => {

            const mealTypes = item.formatted_meal_types || 'N/A';
            const deliveryCharge = `$${item.delivery_charge || '0.00'}`;

            const paymentBadge = `
                <span class="badge ${item.payment_status === 'paid' ? 'bg-success' : 'bg-warning'}">
                    ${item.payment_status}
                </span>`;

            const deliveryBadge = `
                <span class="badge ${item.delivery_status === 'delivered' ? 'bg-success' : 'bg-info'}">
                    ${item.delivery_status}
                </span>`;

            const viewButton = `
                <a href="/admin/meal-delivery/payment/details/${item.delivery_charge_ledger_id}"
                   class="btn btn-sm btn-info">
                    <i class="bx bx-show"></i> View
                </a>`;

            const paymentButton = item.payment_status === 'due'
                ? `<button 
                        class="btn btn-sm btn-success paymentBtn"
                        data-ledger-id="${item.delivery_charge_ledger_id}"
                        data-delivery-charge="${item.delivery_charge}">
                        <i class="bx bx-credit-card"></i> Payment
                   </button>`
                : '';

            const row = `
                <tr>
                    <td>${item.sl}</td>
                    <td>${item.order_number}</td>
                    <td>${mealTypes}</td>
                    <td>${item.meal_date}</td>
                    <td>${deliveryCharge}</td>
                    <td>${paymentBadge}</td>
                    <td>${deliveryBadge}</td>
                    <td class="d-flex gap-2">
                        ${viewButton}
                        ${paymentButton}
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
    if ($.fn.DataTable.isDataTable('#mealTable')) {
        $('#mealTable').DataTable().destroy();
    }

    $('#mealTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        autoWidth: false,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
        order: [[0, "desc"]],
        columnDefs: [{ targets: 7, orderable: false }]
    });
}

    function attachEventListeners() {
        $('.paymentBtn').on('click', function () {
            let ledgerId = $(this).data('ledger-id');
            let deliveryCharge   = $(this).data('delivery-charge');
            $("#ledgerID").val(ledgerId);
            $("#deiveryChargeID").val(deliveryCharge);
            $("#paymentModal").modal('show');
        });
    }
/*
function attachEventListeners() {
    document.querySelectorAll('.btn-payment').forEach(btn => {
        btn.addEventListener('click', function () {
            const ledgerId = this.dataset.ledgerId;
            const amount   = this.dataset.amount;

            // function lives in credit-modal.blade.php
            openPaymentModal(ledgerId, amount);
        });
    });
}*/

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
