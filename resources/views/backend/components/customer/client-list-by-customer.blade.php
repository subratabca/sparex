<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Client List Information</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Image</th>
                    <th>Client Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Uploaded Products</th>
                    <th>Customer Name</th>
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
            let res = await axios.get("/admin/get/client/list/by/customer/" + customer_id);
            if (res.status === 200 && res.data.status === 'success') {
                let tableList = $("#tableList");
                tableList.empty(); 

                res.data.data.forEach(function (item, index) {

                    let clientName = item['lastName'] ? `${item['firstName']} ${item['lastName']}` : item['firstName'];

                    let customerName = item['customer_lastName'] ? `${item['customer_firstName']} ${item['customer_lastName']}` : item['customer_firstName'];

                    let productCount = item['products_count'] || 0;
                    let row =`<tr>
                                <td>${index + 1}</td>
                                <td>${item['image'] ? `<img src="/upload/client-profile/small/${item['image']}" width="50" height="50">` : `<img src="/upload/no_image.jpg" width="50" height="50">`}
                                </td>
                                <td>
                                    <a href="/admin/client/details/${item['id']}" target="_blank" title="Client details">${clientName}
                                    </a>
                                </td>
                                <td>${item['email']}</td>
                                <td>${item['mobile']}</td>
                                <td>${productCount}</td>
                                <td>${customerName}</td>
                                <td>
                                    <span class="badge ${item['status'] === 1 ? 'bg-success' : 'bg-danger'}">
                                        ${item['status'] === 1 ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td>
                                    ${item['status'] === 1 ? `
                                        <button data-id="${item['id']}" data-status="0" title="Inactive client account" class="toggleStatusBtn btn btn-sm btn-outline-danger">
                                            <span class="mdi mdi-thumb-down"></span>
                                        </button>
                                    ` : `
                                        <button data-id="${item['id']}" data-status="1" title="Active client account" class="toggleStatusBtn btn btn-sm btn-outline-success">
                                            <span class="mdi mdi-thumb-up"></span>
                                        </button>
                                    `}
                                </td>
                             </tr>`;
                    tableList.append(row);;
                });

                $('.toggleStatusBtn').on('click', function() {
                    let clientId = $(this).data('id');
                    let newStatus = $(this).data('status');
                    toggleClientStatus(clientId, newStatus);
                });

                initializeDataTable();
            } else {
                errorToast(res.data.message || "Failed to fetch client list.");
            }
        } catch (error) {
            handleError(error);
        }finally{
            hideLoader();
        }
    }

    async function toggleClientStatus(clientId, newStatus) {
        showLoader();
        try {
            let res = await axios.post(`/admin/update/client/account/${clientId}`, {
                status: newStatus
            }, {
                headers: {
                    'Cache-Control': 'no-cache'
                }
            });

            successToast(res.data.message);
            window.location.href = '/admin/clients';

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


