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
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registration Date</th>
                    <th>Registration Time</th>
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
        let client_id = segments[segments.length - 1];

        showLoader();
        try {
            let res = await axios.get("/admin/get/customer/list/by/client/" + client_id);
            if (res.status === 200 && res.data.status === 'success') {
                let tableList = $("#tableList");
                tableList.empty(); 

                res.data.data.forEach(function (item, index) {
                    let createdAt = new Date(item['created_at']);
                    let registrationDate = createdAt.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });

                    let registrationTime = createdAt.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });

                    let fullName = item['lastName'] ? `${item['firstName']} ${item['lastName']}` : item['firstName'];
                    let row =`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item['image'] ? `<img src="/upload/customer-profile/small/${item['image']}" width="50" height="50">` : `<img src="/upload/no_image.jpg" width="50" height="50">`}
                            </td>
                            <td>${fullName}</td>
                            <td>${item['email']}</td>
                            <td>${item['mobile']}</td>
                            <td>${registrationDate}</td>
                            <td>${registrationTime}</td>
                        </tr>`;
                    tableList.append(row);
                });

                initializeDataTable();
            } else {
                errorToast(res.data.message || "Failed to fetch orders.");
            }
        } catch (error) {
            handleError(error);
        }finally{
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


