<div class="card">
    <div class="card-header header-elements">
        <span class="me-2"><h5>Meal Keywords</h5></span>
        <div class="card-header-elements ms-auto">
            <a href="{{ route('create.meal.keyword')}}" type="button" class="btn btn-primary waves-effect waves-light">
                <span class="tf-icon mdi mdi-plus me-1"></span>Add New
            </a>
        </div>
    </div>

    <div class="card-datatable table-responsive pt-0">
        <table id="mealKeywordTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Meal Keyword</th>
                    <th>Meal Types</th>
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
            let res = await axios.get("/admin/get/meal-keywords");
            if (res.status === 200 && res.data.status === 'success') {
                let tableList = $("#tableList");
                tableList.empty();

                res.data.data.forEach(function (item, index) {
                    let keywordName = item.name ? item.name.charAt(0).toUpperCase() + item.name.slice(1) : '-';

                    let badges = (item.meal_types && item.meal_types.length)
                        ? item.meal_types.map(mt => {
                            let n = mt.name.charAt(0).toUpperCase() + mt.name.slice(1);
                            return `<span class="badge bg-label-primary me-1">${n}</span>`;
                          }).join('')
                        : '<span class="text-muted">-</span>';

                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${keywordName}</td>
                            <td>${badges}</td>
                            <td>
                                <a href="/admin/edit/meal-keyword/${item['id']}" class="btn btn-sm btn-outline-success" title="Edit"><span class="mdi mdi-pencil-outline"></span></a>
                                <button data-id="${item['id']}" class="btn deleteBtn btn-sm btn-outline-danger" title="Delete"><span class="mdi mdi-trash-can-outline"></span></button>
                            </td>
                        </tr>`;
                    tableList.append(row);
                });

                initializeDataTable();
                attachEventListeners();
            } else {
                errorToast(res.data.message || "Failed to fetch meal keywords.");
            }
        } catch (error) {
            handleError(error);
        } finally {
            hideLoader();
        }
    }

    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('#mealKeywordTable')) {
            $('#mealKeywordTable').DataTable().destroy();
        }
        $('#mealKeywordTable').DataTable({
            paging: true, ordering: true, searching: true, autoWidth: false,
            lengthMenu: [10, 25, 50, 100], pageLength: 10,
        });
    }

    function attachEventListeners() {
        $('.deleteBtn').on('click', function () {
            let id = $(this).data('id');
            $("#deleteID").val(id);
            $("#delete-modal").modal('show');
        });
    }
</script>
