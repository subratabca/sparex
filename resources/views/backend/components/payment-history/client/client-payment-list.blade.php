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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    getList();
});

async function getList() {
    showLoader();
    try {
        let res = await axios.get("/admin/get/client-payments");
        if (res.status === 200 && res.data.status === 'success') {
            console.log('------',res);
            let tableList = $("#tableList");
            tableList.empty();

            if ($.fn.DataTable.isDataTable('#orderTable')) {
                $('#orderTable').DataTable().clear().destroy();
            }

            res.data.data.forEach(function (item, index) {
                let orderDate = item.order_date || '-';
                let invoiceNo = item.invoice_no || '-';
                let clientName = item.client_name || '-';
                let payableAmount = item.payable_amount || '£0.00';
                let paymentStatus = item.payment_status || 'Unknown';

                let badgeClass = '';
                switch (paymentStatus.toLowerCase()) {
                    case 'pending': badgeClass = 'bg-warning'; break;
                    case 'due': badgeClass = 'bg-danger'; break;
                    case 'paid': badgeClass = 'bg-success'; break;
                    case 'refunded': badgeClass = 'bg-info'; break;
                    case 'failed': badgeClass = 'bg-danger'; break;
                    default: badgeClass = 'bg-light text-dark';
                }

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${orderDate}</td>
                        <td>
                            <a href="/admin/order/details/${item.id}" class="text-primary text-decoration-underline" target="_blank">${invoiceNo}</a>
                        </td>
                        <td>${clientName}</td>
                        <td>${payableAmount}</td>
                        <td><span class="badge ${badgeClass}">${paymentStatus}</span></td>
                        <td class="text-center">
                            <a href="/admin/payment/details/client/${item.client_id}/order/${item.order_id}" 
                               class="btn btn-sm btn-outline-primary" 
                               title="Customer Payment Details">
                                <span class="mdi mdi-eye-circle"></span>
                            </a>
                            ${
                                paymentStatus.toLowerCase() === 'due'
                                    ? `<button class="btn btn-sm btn-outline-info ms-2" 
                                              onclick="confirmMarkAsPaid(${item.client_id},${item.order_id}, this)" 
                                              title="Mark as Paid">
                                            <span class="mdi mdi-check-circle-outline"></span> Mark as Paid
                                       </button>`
                                    : ''
                            }
                        </td>
                    </tr>
                `;
                tableList.append(row);
            });

            initializeDataTable();
        } else {
            errorToast(res.data.message || "Failed to fetch payment records.");
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

async function confirmMarkAsPaid(clientId, orderId, button) {
    const result = await Swal.fire({
        title: 'Mark Payment as Paid?',
        text: "Are you sure you want to mark this payment as paid?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, mark as paid',
        cancelButtonText: 'Cancel',
        width: '500px', 
        padding: '1.2rem', 
        customClass: {
            popup: 'swal-sm-font',          
            title: 'swal-title-sm',         
            content: 'swal-content-sm',     
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-outline-secondary ms-2'
        },
        buttonsStyling: false
    });

    if (!result.isConfirmed) return; 

    try {
        showLoader();
        let res = await axios.post(`/admin/payment/mark-as-paid/client/${clientId}/order/${orderId}`);

        if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message);

            // Dynamically update badge text & class
            let row = $(button).closest("tr");
            row.find(".badge").each(function() {
                let badge = $(this);
                if (badge.text().trim().toLowerCase() === 'due') {
                    badge.removeClass()
                         .addClass("badge bg-success")
                         .text("Paid");
                }
            });

            $(button).remove();
        } else {
            errorToast(res.data.message || 'Failed to update payment status.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function handleError(error) {
    let message = "An unexpected error occurred.";
    if (error.response) {
        const { status, data } = error.response;
        switch (status) {
            case 500: message = data?.error || "Internal server error."; break;
            case 404: message = data?.message || "Data not found."; break;
            default: message = data?.message || "Something went wrong.";
        }
    } else if (error.request) {
        message = "No response from the server. Check your internet.";
    } else {
        message = error.message;
    }
    errorToast(message);
}
</script>
