<div id="result" style="display:none;">
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-2">Total Sales</p>
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
              <p class="text-heading mb-2">Total Order</p>
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
              <p class="text-heading mb-2">Delivered Orders</p>
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
              <p class="text-heading mb-2">Pending Orders</p>
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
              <p class="text-heading mb-2">Canceled Orders</p>
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
</div>

<div class="row" id='search'>
  <div class="col-md-4 mb-3">
    <div class="card">
      <div class="card-body">
        <h5>Search by Date</h5><hr>
        <div>
          <label for="defaultFormControlInput" class="form-label">Date: <span class="text-danger">*</span></label>
          <input type="date" class="form-control" id="single-date" name="single-date" placeholder="Select a date" aria-describedby="defaultFormControlHelp" />
          <span id="single-date-error" class="text-danger"></span>
        </div>
      </div>
      <div class="card-footer">
        <button class="btn btn-outline-primary" onclick="Save('single')">Search</button>
      </div>
    </div>
  </div>

  <div class="col-md-8 mb-3">
    <div class="card">
      <div class="card-body">
        <h5>Search by Date Range</h5><hr>
        <div class="row">
          <div class="col-md-6">
            <div>
              <label for="defaultFormControlInput" class="form-label">Start Date: <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="start-date" name="start-date" placeholder="Select start date" aria-describedby="defaultFormControlHelp" />
              <span id="start-date-error" class="text-danger"></span>
            </div>
          </div> 
          <div class="col-md-6">
            <div>
              <label for="defaultFormControlInput" class="form-label">End Date: <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="end-date" name="end-date" placeholder="Select end date" aria-describedby="defaultFormControlHelp" />
              <span id="end-date-error" class="text-danger"></span>
            </div>
          </div>      
        </div>
      </div>
      <div class="card-footer">
        <button class="btn btn-outline-primary" onclick="Save('range')">Search</button>
      </div>
    </div>
  </div>
</div> 

<script>

  async function Save(criteria) {
    let formData = new FormData();
    document.getElementById('single-date-error').innerText = '';
    document.getElementById('start-date-error').innerText = '';
    document.getElementById('end-date-error').innerText = '';

    if (criteria === 'single') {
      let singleDate = document.getElementById('single-date').value;
      if (!singleDate) {
        document.getElementById('single-date-error').innerText = 'Please select a date!';
        return;
      }
      formData.append('date', singleDate);

    } else if (criteria === 'range') {
      let startDate = document.getElementById('start-date').value;
      let endDate = document.getElementById('end-date').value;
      if (!startDate) {
        document.getElementById('start-date-error').innerText = 'Please select a start date!';
        return;
      }
      if (!endDate) {
        document.getElementById('end-date-error').innerText = 'Please select an end date!';
        return;
      }
      formData.append('start_date', startDate);
      formData.append('end_date', endDate);

    }

    const config = { headers: { 'content-type': 'multipart/form-data' } };

    showLoader();
    try {
      let res = await axios.post("/client/order/by/search", formData, config);
      if (res.status === 200) {
        document.getElementById('result').style.display = 'block';
        document.getElementById('search').style.display = 'none';

        document.getElementById('total-sales').innerText =
        res.data.total_sales > 0
        ? new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(res.data.total_sales)
        : res.data.total_sales;
        document.getElementById('total-orders').innerText = res.data.total_orders;
        document.getElementById('delivered-orders').innerText = res.data.total_delivered_orders;
        document.getElementById('pending-orders').innerText = res.data.total_pending_orders;
        document.getElementById('cancel-orders').innerText = res.data.total_canceled_orders;
        document.getElementById('partially-approved-orders').innerText = res.data.total_partially_approved_orders;
        document.getElementById('partially-delivered-orders').innerText = res.data.total_partially_delivered_orders;

        let tableList = $("#tableList");
        tableList.empty();

        res.data.data.forEach(function (item, index) {
          let customer = item.customer || {};
          let firstName = customer.firstName || '';
          let lastName = customer.lastName || '';

          let orderDate = item.created_at
              ? new Date(item.created_at).toLocaleDateString('en-GB', {
                  day: '2-digit', month: 'short', year: 'numeric'
                })
              : '-';

          let billedAmount = item.payable_amount !== null ? 
          new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(item.payable_amount) : '-';
          let paidAmount = item.paid_amount !== null ? 

          new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(item.payable_amount) : '-';
          
          let status = item.status || 'unknown';
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
                  <td>${billedAmount}</td>
                  <td>${paidAmount}</td>
                  <td><span class="badge ${badgeClass}">${status}</span></td>
                  <td>
                    <a href="/client/invoice/download/${item.id}" class="btn btn-sm btn-outline-primary" title="Invoice Download"><span class="mdi mdi-arrow-down-circle-outline"></span>
                    </a>
                    <a href="/client/order/details/${item.id}" class="btn btn-sm btn-outline-primary" title="Order Details"><span class="mdi mdi-eye-circle"></span>
                    </a>
                  </td>
          </tr>`;
          tableList.append(row);
        });
        initializeDataTable();
      }
    } catch (error) {
      handleError(error);
    } finally {
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
    let errorMessage = 'An unexpected error occurred.';
    if (error.response) {
      const status = error.response.status;
      errorMessage = error.response.data.message || errorMessage;
      if (status === 400) {
        document.getElementById('single-date-error').innerText = error.response.data.message || 'Please provide a valid date';
        document.getElementById('start-date-error').innerText = error.response.data.message || 'Please select a start date!';
        document.getElementById('end-date-error').innerText = error.response.data.message || 'Please select an end date!';
      } else if (status === 500) {
        errorMessage = error.response.data.message || 'An unexpected error occurred.';
      } else {
        errorToast(errorMessage);
      }
    }
  }
</script>

