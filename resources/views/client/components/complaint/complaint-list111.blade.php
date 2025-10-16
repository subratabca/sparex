<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Complaint List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="complaintTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Order No</th>
                    <th>Product Name</th>
                    <th>Complaint By</th>
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
        showLoader();
        try {
            let res = await axios.get("/client/get/complaints");
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


                    let fullName = item['customer']['lastName'] 
                        ? `${item['customer']['firstName']} ${item['customer']['lastName']}` 
                        : item['customer']['firstName'];

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <a href="/client/order/details/${item['order']['id']}" target="_blank">
                                    ${item['order']['order_number']}
                                </a>
                            </td>
                            <td>${item['product']['name']}</td>
                            <td>${fullName}</td>
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
                                <a href="/client/complaint/details/${item['id']}" class="btn btn-sm btn-outline-primary">
                                    <span class="mdi mdi-eye-circle"></span>
                                </a>

                                ${item['status'] === 'under_review' 
                                    ? `<button data-id="${item['id']}" class="btn replyBtn btn-sm btn-outline-danger">Reply</button>`
                                    : ''}
                            </td>
                         </tr>`;
                    tableList.append(row);
                });
                initializeDataTable();
                attachEventListeners();
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
        if ($.fn.DataTable.isDataTable('#complaintTable')) {
            $('#complaintTable').DataTable().destroy();
        }

        $('#complaintTable').DataTable({
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
        $('.replyBtn').on('click', function () {
            let id = $(this).data('id');
            $("#complainID").val(id);
            $("#reply-modal").modal('show');
        });
    }
    
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

