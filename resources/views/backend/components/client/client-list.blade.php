<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Client List Information</h5></span>
    </div>
    <div class="card-datatable table-responsive pt-0">
        <table id="foodTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Images</th>
                    <th>Clients</th>
                    <th>products</th>
                    <th>Orders</th>
                    <th>Complaints</th>
                    <th>Customers</th>
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
            let res = await axios.get("/admin/get/clients");
            if (res.status === 200 && res.data.status === 'success') {
                let tableList = $("#tableList");
                tableList.empty(); 

                res.data.data.forEach(function (item, index) {
                    let fullName = item['lastName'] ? `${item['firstName']} ${item['lastName']}` : item['firstName'];
                    let row = `
                        <tr>
                            <td>${index + 1}</td>

                            <td>${item['image'] ? `<img src="/upload/client-profile/small/${item['image']}" width="50" height="50">` : `<img src="/upload/no_image.jpg" width="50" height="50">`}
                            </td>

                            <td>
                                <a href="/admin/client/details/${item['id']}" target="_blank" title="Client details">${fullName}
                                </a>
                            </td>
                            <td>
                              ${item['total_products'] > 0 
                                ? `<a href="/admin/product/list/by/client/${item['id']}" class="badge bg-success" target="_blank" title="Product list by client">${item['total_products']}</a>` 
                                : item['total_products']}
                            </td>


                            <td>
                               ${item['total_orders'] > 0 
                               ? `<a href="/admin/order/list/by/client/${item['id']}" class="badge bg-success" target="_blank" title="Order list by client">${item['total_orders']}</a>` 
                               : item['total_orders']}
                            </td>

                            <td>
                                ${item['total_complaints'] > 0 
                                ? `<a href="/admin/complaint/list/by/client/${item['id']}" class="badge bg-success" target="_blank" title="Complaint list by client">${item['total_complaints']}</a>` : item['total_complaints']}
                            </td>

                            <td>
                               ${item['total_customers'] > 0 
                               ? `<a href="/admin/customer/list/by/client/${item['id']}" class="badge bg-success" target="_blank" title="Customer list by client">${item['total_customers']}</a>` 
                               : item['total_customers']}
                            </td>


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
                                <button data-id="${item['id']}" class="btn deleteBtn btn-sm btn-outline-danger" title="Delete client"><span class="mdi mdi-trash-can-outline"></span></button>
                            </td>
                        </tr>`;
                    tableList.append(row);
                });

                $('.toggleStatusBtn').on('click', function() {
                    let clientId = $(this).data('id');
                    let newStatus = $(this).data('status');
                    toggleClientStatus(clientId, newStatus);
                });

                initializeDataTable();
                attachEventListeners();
            } else {
                errorToast(res.data.message || "Failed to fetch client info.");
            }
        } catch (error) {
            handleError(error);
        } finally {
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
                case 422:
                    if (data?.errors) {
                        // Combine all validation errors into a single message
                        message = Object.values(data.errors).flat().join(' ');
                    } else {
                        message = data?.message || "Validation failed.";
                    }
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


