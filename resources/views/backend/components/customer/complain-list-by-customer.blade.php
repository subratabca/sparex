<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Complaint List By Customer</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Complaint By</th>
                    <th>Order No</th>
                    <th>Product Name</th>
                    <th>Product Owner</th>
                    <th>Date</th>
                    <th>Time</th>
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
            let res = await axios.get("/admin/get/complaint/list/by/customer/" + customer_id);
            if (res.status === 200 && res.data.status === 'success') {
                let tableList = $("#tableList");
                tableList.empty(); 

                res.data.data.forEach(function (item, index) {
                    let customerName = item['customer']['lastName'] 
                        ? `${item['customer']['firstName']} ${item['customer']['lastName']}` 
                        : item['customer']['firstName'];
                        
                    let clientName = `${item.product.client['firstName']}${item.product.client['lastName'] ? ' ' + item.product.client['lastName'] : ''}`;

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

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${customerName}</td>
                            <td>
                                <a href="/admin/order/details/${item['order']['id']}" target="_blank" title="Order details">
                                    ${item['order']['order_number']}
                                </a>
                            </td>
                            <td>${item['product']['name']}</td>
                            <td>${clientName}</td>
                            <td>${complainDate}</td>
                            <td>${complainTime}</td>
                            <td>
                                <span class="badge ${
                                    item['status'] === 'pending' ? 'bg-danger' :
                                    item['status'] === 'under_review' ? 'bg-primary' :
                                    item['status'] === 'solved' ? 'bg-success' :
                                    item['status'] === 'cancel' ? 'bg-info' :
                                    'bg-secondary'  
                                }">
                                    ${item['status']}
                                </span>
                            </td>
                            <td>
                                <a href="/admin/complaint/details/${item['id']}" class="btn btn-sm btn-outline-primary" target="_blank" title="Complaint details">
                                    <span class="mdi mdi-eye-circle"></span>
                                </a>
                            </td>
                         </tr>`;
                    tableList.append(row);
                });

                initializeDataTable();
            } else {
                errorToast(res.data.message || "Failed to fetch complaints.");
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
