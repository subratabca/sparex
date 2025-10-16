<div class="card pd-20 pd-sm-40">
    <h6 class="card-body-title">Slider List</h6>
    <div><a href="" class="btn btn-info mg-b-10 float-right" data-toggle="modal" data-target="#create-modal"><i class="fa fa-plus"></i> Create New</a></div>

    <div class="table-wrapper">
        <table id="datatable1" class="table display responsive nowrap">
            <thead>
                <tr>
                    <th class="wd-5p">Sl</th>
                    <th class="wd-10p">Title</th>
                    <th class="wd-10p">Description</th>
                    <th class="wd-10p">Image</th>
                    <th class="wd-20p">Action</th>
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
        let res=await axios.get("/admin/slider-list");
        let tableList=$("#tableList");
        let tableData=$("#datatable1");

        tableData.DataTable().destroy();
        tableList.empty();

        res.data.forEach(function (item, index) {
            let limitedDescription = item['description'].substring(0, 20);
            let row = `<tr>
                        <td>${index + 1}</td>
                        <td>${item['title']}</td>
                        <td>${limitedDescription}...</td>
                        <td>${item['image'] ? `<img src="/upload/slider/${item['image']}" width="100" height="50">` : `<img src="/upload/no_image.jpg" width="100" height="50">`}
                        </td>
                        <td>
                            <button data-path="/upload/slider/${item['image']}" data-id="${item['id']}" class="btn editBtn btn-sm btn-outline-success">Edit</button>

                            <button data-path="/upload/slider/${item['image']}" data-id="${item['id']}" class="btn deleteBtn btn-sm btn-outline-danger">Delete</button>
                        </td>
                     </tr>`;
            tableList.append(row);
        });

        $('.editBtn').on('click', async function () {
            let id= $(this).data('id');
            let filePath= $(this).data('path');
            await FillUpUpdateForm(id,filePath)
            $("#update-modal").modal('show');
        })

        $('.deleteBtn').on('click',function () {
            let id= $(this).data('id');
            let filePath= $(this).data('path');
            $("#deleteID").val(id);
            $("#deleteFilePath").val(filePath);
            $("#delete-modal").modal('show');
        })

        tableData.DataTable({
            responsive: true
        });
    }
</script>
