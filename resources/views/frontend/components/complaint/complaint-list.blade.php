@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Product complaint list</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="complaintTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Order No</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList">
                
            </tbody>
        </table>
    </div>
</div>
@endsection
<script>
document.addEventListener("DOMContentLoaded", function () {
    getList(); 
});

async function getList() {
    showLoader();
    try {
        let res = await axios.get("/user/get/complaints");
        if (res.status === 200 && res.data.status === 'success') {
            console.log('------',res);
            let tableList = $("#tableList");
            tableList.empty();

            res.data.data.forEach(function (item, index) {
                let orderItem = item.order_item || item.orderItem;
                let product = orderItem?.product;
                let client = product?.client;
                let order = orderItem?.order;

                let clientName = client?.lastName
                    ? `${client.firstName} ${client.lastName}`
                    : (client?.firstName || 'N/A');

                let imageSrc = product?.image
                    ? `/upload/product/small/${product.image}`
                    : `/upload/no_image.jpg`;

                // ✅ Define badge color per complaint status
                let badgeClass = '';
                switch (item.status) {
                    case 'pending':
                        badgeClass = 'bg-danger';
                        break;
                    case 'under_review':
                        badgeClass = 'bg-warning text-dark';
                        break;
                    case 'solved':
                        badgeClass = 'bg-success';
                        break;
                    case 'further_investigation':
                        badgeClass = 'bg-info text-dark';
                        break;
                    default:
                        badgeClass = 'bg-secondary';
                        break;
                }

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td><img src="${imageSrc}" width="50" height="50" style="object-fit:cover;border-radius:4px;"></td>
                        <td>${product?.name || 'N/A'}</td>
                        <td>
                            <a href="/user/order/details/${order?.id || '#'}" target="_blank">
                                ${order?.order_number || 'N/A'}
                            </a>
                        </td>
                        <td>${item.cmp_date || ''}</td>
                        <td>
                            <span class="badge ${badgeClass}" style="font-size:0.85rem;">
                                ${item.status?.replace('_', ' ') || 'unknown'}
                            </span>
                        </td>
                        <td>
                            <a href="/user/complaint/details/${item.id}" class="btn btn-sm btn-info">Details</a>
                            ${(item.status === 'under_review') 
                                ? `<a href="/user/complaint/reply/${item.id}" class="btn btn-sm btn-danger">Reply</a>` 
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
        paging: true,
        serverSide: false,
        autoWidth: false,
        ordering: true,
        searching: true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
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



