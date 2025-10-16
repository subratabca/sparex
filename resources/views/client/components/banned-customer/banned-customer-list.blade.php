<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Banned Customer List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="customerTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Time</th>
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
            let res = await axios.get("/client/get/banned/customers");
            if (res.status === 200 && res.data.status === 'success') {
                let tableList = $("#tableList");
                tableList.empty(); 

                res.data.data.forEach(function (item, index) {

                    let createdAt = new Date(item['created_at']);
                    let complainDate = createdAt.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                    let complainTime = createdAt.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });

                   let customerName = item.customer.lastName ? `${item.customer.firstName} ${item.customer.lastName}` : item.customer.firstName;

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <a href="/client/customer/details/${item.customer.id}" target="_blank" title="Customer details">
                                   ${customerName}
                                </a>
                            </td>
                            <td>${complainDate}</td>
                            <td>${complainTime}</td>
                            <td>
                                <a  href="/client/banned/customer/details/${item.id}" class="btn btn-sm btn-outline-primary" title="Banned customer details"><span class="mdi mdi-eye-circle"></span>
                                </a>

                                <button data-id="${item['id']}" class="btn deleteBtn btn-sm btn-outline-danger" title="Delete Banned customer">
                                    <span class="mdi mdi-trash-can-outline"></span>
                                </button>
                            </td>
                        </tr>`;
                    tableList.append(row);
                });

                initializeDataTable();
                attachEventListeners();
            } else {
                errorToast(response.data.message || "Failed to fetch banned customer info.");
            }
        } catch (error) {
            handleError(error);
        } finally {
            hideLoader();
        }
    }

    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('#customerTable')) {
            $('#customerTable').DataTable().destroy();
        }

        $('#customerTable').DataTable({
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

