<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="me-1">
                        <p class="text-heading mb-2">Today's Total Sale</p>
                        <div class="d-flex align-items-center">
                            <h4 class="mb-2 me-1 display-6" id="total-sales">0</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-primary rounded">
                            <div class="mdi mdi-account-outline mdi-24px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="me-1">
                        <p class="text-heading mb-2">Today's Total Order</p>
                        <div class="d-flex align-items-center">
                            <h4 class="mb-2 me-1 display-6" id="total-orders">0</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-primary rounded">
                            <div class="mdi mdi-account-outline mdi-24px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="me-1">
                        <p class="text-heading mb-2">Delivered Order</p>
                        <div class="d-flex align-items-center">
                            <h4 class="mb-2 me-1 display-6" id="delivered-orders">0</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-danger rounded">
                            <div class="mdi mdi-account-plus-outline mdi-24px scaleX-n1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="me-1">
                        <p class="text-heading mb-2">Pending Order</p>
                        <div class="d-flex align-items-center">
                            <h4 class="mb-2 me-1 display-6" id="pending-orders">0</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-success rounded">
                            <div class="mdi mdi-account-check-outline mdi-24px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="me-1">
                        <p class="text-heading mb-2">Canceled Order</p>
                        <div class="d-flex align-items-center">
                            <h4 class="mb-2 me-1 display-6" id="cancel-orders">0</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-warning rounded">
                            <div class="mdi mdi-account-search mdi-24px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="me-1">
                        <p class="text-heading mb-2">Partially Approved Order</p>
                        <div class="d-flex align-items-center">
                            <h4 class="mb-2 me-1 display-6" id="partially-approved-orders">0</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-warning rounded">
                            <div class="mdi mdi-account-search mdi-24px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="me-1">
                        <p class="text-heading mb-2">Partially Delivered Order</p>
                        <div class="d-flex align-items-center">
                            <h4 class="mb-2 me-1 display-6" id="partially-delivered-orders">0</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <div class="avatar-initial bg-label-warning rounded">
                            <div class="mdi mdi-account-search mdi-24px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Order List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="productTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Billed Amount</th>
                    <th>Paid Amount</th>
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
            let res = await axios.get("/client/todays/order/information");
            if (res.status === 200) {
                document.getElementById('total-sales').innerText =  res.data.total_sales > 0 ? `£${res.data.total_sales}` : res.data.total_sales;
                document.getElementById('total-orders').innerText = res.data.total_orders;
                document.getElementById('delivered-orders').innerText = res.data.total_delivered_orders;
                document.getElementById('pending-orders').innerText = res.data.total_pending_orders;
                document.getElementById('cancel-orders').innerText = res.data.total_canceled_orders;
                document.getElementById('partially-approved-orders').innerText = res.data.total_partially_approved_orders;
                document.getElementById('partially-delivered-orders').innerText = res.data.total_partially_delivered_orders;


                let tableList = $("#tableList");
                tableList.empty(); 

                res.data.data.forEach(function (item, index) {
                    // Access customer through order relationship
                    let customer = item.order && item.order.customer ? item.order.customer : {};
                    let firstName = customer.firstName || '';
                    let lastName = customer.lastName || '';
                    let fullName = (firstName + ' ' + lastName).trim() || '-';

                    // ✅ Show only date (no time)
                    // Format order date (only date)
                    let orderDate = item.created_at
                        ? new Date(item.created_at).toLocaleDateString('en-GB', {
                            day: '2-digit', month: 'short', year: 'numeric'
                          })
                        : '-';

                    // Amounts
                    let billedAmount = item.payable_amount !== null ? `£${parseFloat(item.payable_amount).toFixed(2)}` : '-';
                    let paidAmount = item.paid_amount !== null ? `£${parseFloat(item.paid_amount).toFixed(2)}` : '-';

                    // Status colors
                    let status = item.status || 'unknown';
                    let badgeClass = '';
                    switch (status) {
                        case 'pending':
                            badgeClass = 'bg-danger';
                            break;
                        case 'approved':
                            badgeClass = 'bg-info';
                            break;
                        case 'delivered':
                            badgeClass = 'bg-success';
                            break;
                        case 'canceled':
                            badgeClass = 'bg-secondary';
                            break;
                        case 'partially_approved':
                            badgeClass = 'bg-warning';
                            break;
                        case 'partially_delivered':
                            badgeClass = 'bg-primary';
                            break;
                        default:
                            badgeClass = 'bg-dark';
                    }

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${fullName}</td>
                            <td>${orderDate}</td>
                            <td>${billedAmount}</td>
                            <td>${paidAmount}</td>
                            <td><span class="badge ${badgeClass}">${status.replace('_', ' ')}</span></td>
                            <td>
                              <a href="/client/invoice/download/${item.order_id}" class="btn btn-sm btn-outline-primary" title="Invoice Download"><span class="mdi mdi-arrow-down-circle-outline"></span>
                              </a>

                              <a href="/client/order/details/${item.order_id}" class="btn btn-sm btn-outline-primary" title="Order Details"><span class="mdi mdi-eye-circle"></span>
                              </a>
                            </td>
                        </tr>
                    `;

                  tableList.append(row);
                });
                initializeDataTable(); 
            }
        } catch (error) {
            handleError(error);
        }finally {
            hideLoader();
        }
    }

    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('#productTable')) {
            $('#productTable').DataTable().destroy();
        }

        $('#productTable').DataTable({
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