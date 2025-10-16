<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Customer List Information</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Orders</th>
                    <th>Product Complaints Given</th>
                    <th>Complaints Received</th>
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
        let res = await axios.get("/client/get/customers"); 
        if (res.status === 200 && res.data.status === 'success') {
            let data = res.data.data;

            let tableList = $("#tableList");
            tableList.empty(); 

            data.forEach(function (item, index) {
                let fullName = item.lastName ? `${item.firstName} ${item.lastName}` : item.firstName;

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                           ${item.image ? `<img src="/upload/customer-profile/small/${item.image}" width="50" height="50">` : `<img src="/upload/no_image.jpg" width="50" height="50">`}
                        </td>

                        <td><a href="/client/customer/details/${item.id}" target="_blank" title="Customer details">${fullName}</a></td>

                        <td>${item.total_orders > 0 
                            ? `<a href="/client/order/list/by/customer/${item.id}" class="badge bg-success" 
                            target="_blank" title="Order list by customer">${item.total_orders}</a>` 
                            : item.total_orders}
                        </td>

                        <td>${item.total_product_complaints > 0 
                            ? `<a href="/client/complaint/list/by/customer/${item.id}" class="badge bg-success" 
                            target="_blank" title="Product complaints by customer">${item.total_product_complaints}</a>` 
                            : item.total_product_complaints}
                        </td>

                        <td>${item.total_received_complaints > 0 
                            ? `<a href="/client/customer-complain/list/by/customer/${item.id}" class="badge bg-success" target="_blank" title="Complaints received by customer">${item.total_received_complaints}</a>` 
                            : item.total_received_complaints}
                        </td>

                        <td>
                            <span class="badge ${item.status === 1 ? 'bg-success' : 'bg-danger'}">
                                ${item.status === 1 ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        
                        <td>
                            <button data-id="${item.id}" class="btn deleteBtn btn-sm btn-outline-danger" title="Delete customer">
                                <span class="mdi mdi-trash-can-outline"></span>
                            </button>

                            <button data-id="${item['id']}" class="btn complaintBtn btn-sm btn-outline-info">Complaint</button>

                            <button data-id="${item['id']}" class="btn bannedBtn btn-sm btn-outline-danger" ${item['is_banned'] ? 'disabled' : ''}>Banned</button>
                        </td>
                    </tr>`;
                
                tableList.append(row);
            });

            initializeDataTable();
            attachEventListeners();
        } else {
            errorToast(response.data.message || "Failed to fetch client info.");
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}


    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('#foodTable')) {
            $('#foodTable').DataTable().destroy();
        }

        $('#foodTable').DataTable({
            "paging": true,
            "serverSide": false,
            "autoWidth": false,
            "ordering": true,
            "searching": true,
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": 10,
        });
    }

    function attachEventListeners() {
        $('.deleteBtn').on('click', function () {
            let id = $(this).data('id');
            $("#deleteID").val(id);
            $("#delete-modal").modal('show');
        });

        $('.complaintBtn').on('click', function () {
            let id = $(this).data('id');
            $("#customerID").val(id);
            $("#complain-modal").modal('show');
        });

        $('.bannedBtn').on('click', function () {
            let id = $(this).data('id');
            $("#bannedCustomerID").val(id);
            $("#banned-modal").modal('show');
        });
    }

  function handleError(error) {
      let message = "An unexpected error occurred.";

      if (error.response) {
          const { status, data } = error.response;
          switch (status) {
              case 500:
                  message = data?.message || "Internal server error. Please try again later.";
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
