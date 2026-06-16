@extends('frontend.components.dashboard.dashboard-master')
@section('dashboard-content')

<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Credit Info</h5></span>
        <div class="card-header-elements ms-auto">
            <button type="button" class="btn btn-success waves-effect waves-light"
                    data-bs-toggle="modal" data-bs-target="#creditLimitModal">
                <span class="tf-icon mdi mdi-cash-plus me-1"></span>Add Credit Limit
            </button>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealOrderTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody id="tableList"></tbody>
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
        let res = await axios.get("/get/credit/info");

        if (res.status === 200 && res.data.status === "success") {
            let tableList = $("#tableList");
            tableList.empty();

            res.data.data.forEach(function (item, index) {

                let row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.type ? item.type.toUpperCase() : '-'}</td>
                        <td>$${item.amount ?? 0}</td>
                        <td>$${item.balance_after ?? 0}</td>
                    </tr>
                `;
                tableList.append(row);
            });

            initializeDataTable();
        } else {
            errorToast("Failed to load credit info.");
        }

    } catch (err) {
        handleError(err);
    } finally {
        hideLoader();
    }
}

function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#mealOrderTable')) {
        $('#mealOrderTable').DataTable().destroy();
    }

    $('#mealOrderTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10
    });
}

function handleError(error) {
    let message = "Something went wrong.";
    if (error.response) {
        message = error.response.data.message || message;
    }
    errorToast(message);
}
</script>

