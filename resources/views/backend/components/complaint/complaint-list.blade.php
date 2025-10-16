<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Complaint List</h5></span>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="complaintTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Order No</th>
                    <th>Complaint Date</th>
                    <th>Complaint By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableList"></tbody>
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
        let res = await axios.get("/admin/get/complaints");

        if (res.status === 200 && res.data.status === "success") {
            let tableList = $("#tableList");
            tableList.empty();

            res.data.data.forEach(function (item, index) {
                let orderItem = item.order_item || item.orderItem;
                let order = orderItem?.order;
                let product = orderItem?.product;
                let variant = orderItem?.variant;
                let customer = item.customer;

                // Complaint date formatting
                let createdAt = new Date(item.created_at);
                let complainDate = createdAt.toLocaleDateString("en-GB", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                });

                let fullName = customer?.lastName
                    ? `${customer.firstName} ${customer.lastName}`
                    : customer?.firstName || "N/A";

                let imageSrc = product?.image
                    ? `/upload/product/small/${product.image}`
                    : `/upload/no_image.jpg`;

                // Badge for status
                let statusBadgeClass =
                    item.status === "pending"
                        ? "bg-danger"
                        : item.status === "under_review"
                        ? "bg-primary"
                        : item.status === "solved"
                        ? "bg-success"
                        : item.status === "further_investigation"
                        ? "bg-warning"
                        : "bg-secondary";

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td><img src="${imageSrc}" width="50" height="50" style="object-fit:cover;border-radius:4px;"></td>
                        <td>${product?.name || "N/A"} ${
                            variant
                                ? `<small class="text-muted">(${variant.color || ""} ${variant.size || ""})</small>`
                                : ""
                        }</td>
                        <td>
                            <a href="/admin/order/details/${order?.id || "#"}" target="_blank">
                                ${order?.order_number || "N/A"}
                            </a>
                        </td>
                        <td>${complainDate}</td>
                        <td>${fullName}</td>
                        <td><span class="badge ${statusBadgeClass}">${item.status}</span></td>
                        <td>
                            <a href="/admin/complaint/details/${item.id}" class="btn btn-sm btn-outline-primary">
                                <span class="mdi mdi-eye-circle"></span>
                            </a>
                            <button data-id="${item.id}" class="btn deleteBtn btn-sm btn-outline-danger">
                                <span class="mdi mdi-trash-can-outline"></span>
                            </button>
                        </td>
                    </tr>
                `;

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
    if ($.fn.DataTable.isDataTable("#complaintTable")) {
        $("#complaintTable").DataTable().destroy();
    }

    $("#complaintTable").DataTable({
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
    $(".deleteBtn").on("click", function () {
        let id = $(this).data("id");
        $("#deleteID").val(id);
        $("#delete-modal").modal("show");
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
