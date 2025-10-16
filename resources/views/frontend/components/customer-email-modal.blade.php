<!-- Add Tagify CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">

<div class="modal fade" id="email-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-add-new-address">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body p-md-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="address-title mb-2 pb-1">Share Item Details</h3>
          <p class="address-subtitle">Add email addresses to share</p>
        </div>
        <form id="save-email-form" class="row g-4">
          <input type="text" class="d-none" id="itemID">
          <div class="col-12">
            <div class="form-floating form-floating-outline">
              <input type="text" id="emailInput" class="form-control" placeholder="Enter email addresses" />
              <label for="emailInput">Email addresses</label>
              <span class="error-message text-danger" id="email-error"></span>
            </div>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Send Email</button>
            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Add Tagify JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let emailInput = document.getElementById('emailInput');
    let emailError = document.getElementById('email-error');

    // Initialize Tagify
    let tagify = new Tagify(emailInput, {
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        whitelist: [],
        delimiters: ", ",
        maxTags: 10,
        enforceWhitelist: false,
        dropdown: {
            enabled: 0
        }
    });

    // Handle form submission
    document.getElementById('save-email-form').addEventListener('submit', async function(event) {
        event.preventDefault();

        let itemID = document.getElementById('itemID').value;
        emailError.innerText = '';

        let emails = tagify.value.map(email => email.value.trim());

        if (!emails.length) {
            emailError.innerText = 'Please enter at least one valid email address.';
            return;
        }

        // Validate emails
        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let invalidEmails = emails.filter(email => !emailRegex.test(email));

        if (invalidEmails.length > 0) {
            emailError.innerText = `Invalid email(s): ${invalidEmails.join(", ")}`;
            return;
        }

        // Prepare form data
        let formData = new FormData();
        formData.append('itemID', itemID);
        formData.append('emails', JSON.stringify(emails));

        showLoader();

        try {
            let res = await axios.post('/user/share-item-with-email', formData, {
                headers: { 'content-type': 'multipart/form-data' }
            });

            if (res.status === 200) {
                successToast(res.data.message || 'Item shared successfully');
                document.getElementById('save-email-form').reset();
                tagify.removeAllTags();
                $('#email-modal').modal('hide'); 
            } else {
                emailError.innerText = res.data.message || 'Request failed';
            }
        } catch (error) {
            if (error.response && error.response.status === 422) {
                let errors = error.response.data.errors;
                for (let key in errors) {
                    emailError.innerText = errors[key][0];
                    break;
                }
            } else if (error.response) {
                emailError.innerText = error.response.data.message || 'An error occurred.';
            } else {
                emailError.innerText = 'Request failed!';
            }
        } finally {
            hideLoader();
        }
    });
});
</script>
