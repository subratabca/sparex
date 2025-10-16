<div class="modal fade" id="delete-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body p-md-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h5 class="mb-2 pb-1">Are you sure to delete?</h5>
          <p>Once delete, you can't get it back</p>
          <input class="d-none" id="deleteID"/>
        </div>
      </div>
      <div class="modal-footer p-md-0">
        <div class="col-12">
          <button onclick="itemDelete()" id="confirmDelete" class="btn btn-primary me-sm-3 me-1">Delete</button>
          <button
          type="reset"
          class="btn btn-outline-secondary"
          data-bs-dismiss="modal"
          aria-label="Close">
          Cancel
           </button>
          </div>
        </div>
      </div>
    </div>
</div>


<script>
     async function itemDelete() {
        showLoader();
        try {
            let id = document.getElementById('deleteID').value;
            $('#delete-modal').modal('hide');
            let res = await axios.post("/client/delete/banned/customer", {id: id});
            if (res.status === 200 && res.data.status === 'success') {
                successToast(res.data.message || "Data deleted successfully");
                window.location.href = '/client/banned/customers';
            } else {
                errorToast("Request failed! Please try again.");
            }
          } catch (error) {
              handleError(error);
          } finally {
              hideLoader();
          }
     }


function handleError(error) {
    let message = 'An unexpected error occurred';

    if (error.response) {
        const status = error.response.status;
        const serverMessage = error.response.data?.message;

        switch (status) {
            case 404:
                message = serverMessage || 'Banned customer not found';
                break;
            case 500:
                message = serverMessage || 'Server error. Please try again later.';
                break;
            default:
                message = serverMessage || message;
        }
    } else if (error.request) {
        message = 'No response received from the server.';
    } else {
        message = error.message || message;
    }

    errorToast(message);
}
</script>

