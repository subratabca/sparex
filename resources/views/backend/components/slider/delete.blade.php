        <div id="delete-modal" class="modal fade">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content tx-size-sm">
              <div class="modal-header pd-x-20">
                <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold">Delete</h6>
                <button type="button" id="modal-close" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body pd-20">
                <p class="mb-3">Once delete, you can't get it back.</p>
                <input class="d-none" id="deleteID"/>
                <input class="d-none" id="deleteFilePath"/>
              </div>
              <div class="modal-footer">
                <button  onclick="itemDelete()" id="confirmDelete" class="btn btn-danger pd-x-20">Delete</button>
                <button  class="btn btn-secondary pd-x-20" data-dismiss="modal">Close</button>
                <a href="{{ route('sliders') }}" class="btn btn-success">Back</a>
              </div>
            </div>
          </div>
        </div>


<script>
     async  function  itemDelete(){
            let id = document.getElementById('deleteID').value;
            let deleteFilePath = document.getElementById('deleteFilePath').value;
            $('#delete-modal').modal('hide');
            let res = await axios.post("/admin/delete-slider",{id:id,file_path:deleteFilePath})
            if(res.status === 200){
                successToast("Data deleted successfully")
                await getList();
            }
            else{
                errorToast("Request fail!")
            }
     }
</script>
