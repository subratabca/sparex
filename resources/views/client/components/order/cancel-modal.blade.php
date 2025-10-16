<div class="modal fade" id="cancel-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel3">Cancel Reason</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="save-form">
          <div class="row">
            <input type="text" class="d-none" id="itemID">
            <div class="col-md-12 my-4">
              <div class="card">
                <div class="card-body">
                  @include('client.components.editor3')
                  <div id="snow-editor3"></div>
                  <span class="error-message text-danger" id="cancel_message-error"></span>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="item-cancel-btn">Confirm Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
var quill;

document.addEventListener("DOMContentLoaded", function () {
    // Initialize Quill editor
    quill = new Quill('#snow-editor3', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: '#toolbar3',
                handlers: {
                    image: imageHandler // optional image handler
                }
            }
        }
    });

    // Delegated click for Confirm Cancel button
    $(document).on('click', '#item-cancel-btn', async function () {
        let itemId = $('#itemID').val();
        let reason = quill.root.innerHTML.trim();

        if (!reason || reason === '<p><br></p>') {
            $('#cancel_message-error').text('Please provide a reason for cancellation.');
            return;
        } else {
            $('#cancel_message-error').text('');
        }

        await confirmCancel(itemId, reason);
    });
});


function openCancelModal(itemId) {
    $('#itemID').val(itemId);
    quill.root.innerHTML = ''; 
    $('#cancel_message-error').text('');
    $('#cancel-modal').modal('show');
}

async function confirmCancel(itemId, reason) {
    try {
        showLoader();
        let res = await axios.post('/client/order/cancel-item', {
            item_id: itemId,
            action_reason: reason
        });

        if (res.status === 200 && res.data.status === 'success') {
            successToast(res.data.message || 'Item cancelled successfully!');
            $('#cancel-modal').modal('hide');
            await loadOrderDetails(); // reload order details
        } else {
            errorToast(res.data.message || 'Cancellation failed');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}
</script>
