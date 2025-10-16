<div class="modal fade" id="delete-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-simple modal-enable-otp modal-dialog-centered">
		<div class="modal-content p-3 p-md-5">
			<div class="modal-body p-md-0">
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				<div class="text-center mb-4">
					<h5 class="mb-2 pb-1">Are you sure to delete?</h5>
					<p>Once delete, you can't get it back</p>
					<input type="hidden" id="deleteID"/>
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
			let order_id = document.getElementById('deleteID').value;
			$('#delete-modal').modal('hide');
			let res = await axios.post("/admin/order/delete", {order_id: order_id});
			if (res.status === 200 && res.data.status === 'success') {
				successToast(res.data.message || "Data deleted successfully");
				window.location.reload();
			} else {
				errorToast("Request failed! Please try again.");
			}
		} catch (error) {
			handleError(error);
		} finally{
			hideLoader();
		}
	}

	function handleError(error) {
		let message = "An unexpected error occurred.";
		if (error.response) {
			const { status, data } = error.response;
			switch (status) {
			case 500:
				message = data?.error || "An internal server error occurred. Please try again later.";
				break;
			case 404:
				message = data?.message || "There is no order found.";
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