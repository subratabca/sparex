<div class="card mb-4">
<h5 class="card-header">Change Password</h5>
<div class="card-body">

    <div class="row">
      <div class="mb-3 col-md-6 form-password-toggle">
        <div class="input-group input-group-merge">
          <div class="form-floating form-floating-outline">
            <input
              class="form-control"
              type="password"
              id="oldpassword"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
            <label for="currentPassword">Current Password</label>
            
          </div>
          <span class="input-group-text cursor-pointer"
            ><i class="mdi mdi-eye-off-outline"></i
          ></span>
        </div>
        <span class="error-message text-danger" id="oldpassword-error"></span>
      </div>
    </div>
    <div class="row g-3 mb-4">
      <div class="col-md-6 form-password-toggle">
        <div class="input-group input-group-merge">
          <div class="form-floating form-floating-outline">
            <input
              class="form-control"
              type="password"
              id="newpassword"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
            <label for="newPassword">New Password</label>
          </div>
          <span class="input-group-text cursor-pointer"
            ><i class="mdi mdi-eye-off-outline"></i>
          </span>
        </div>
        <span class="error-message text-danger" id="newpassword-error"></span>
      </div>
      <div class="col-md-6 form-password-toggle">
        <div class="input-group input-group-merge">
          <div class="form-floating form-floating-outline">
            <input
              class="form-control"
              type="password"
              id="cpassword"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
            <label for="confirmPassword">Confirm New Password</label>
          </div>
          <span class="input-group-text cursor-pointer"
            ><i class="mdi mdi-eye-off-outline"></i
          ></span>
        </div>
        <span class="error-message text-danger" id="cpassword-error"></span>
      </div>
    </div>
    <h6 class="text-body">Password Requirements:</h6>
    <ul class="ps-3 mb-0">
      <li class="mb-1">Minimum 8 characters long - the more, the better</li>
      <li class="mb-1">At least one lowercase character</li>
      <li>At least one number, symbol, or whitespace character</li>
    </ul>
    <div class="mt-4">
      <button onclick="onUpdate()" class="btn btn-primary me-2">Update</button>
    </div>
</div>
</div>

<script>
    async function onUpdate() {
        let oldpassword = document.getElementById('oldpassword').value;
        let newpassword = document.getElementById('newpassword').value;
        let cpassword = document.getElementById('cpassword').value;

        document.getElementById('oldpassword-error').innerText = '';
        document.getElementById('newpassword-error').innerText = '';
        document.getElementById('cpassword-error').innerText = '';

        if (oldpassword.length === 0) {
            document.getElementById('oldpassword-error').innerText = 'Old Password is required';
        } else if (newpassword.length === 0) {
            document.getElementById('newpassword-error').innerText = 'New Password is required';
        } else if (cpassword.length === 0) {
            document.getElementById('cpassword-error').innerText = 'Confirm Password is required';
        } else if (newpassword !== cpassword) {
            document.getElementById('newpassword-error').innerText = 'New Password and Confirm Password must be the same';
        } else {
            try {
                let res = await axios.post("/admin/password/update", {
                    oldpassword: oldpassword,
                    newpassword: newpassword,
                    newpassword_confirmation: cpassword
                });

                if (res.status === 200 && res.data.status === 'success') {
                    successToast(res.data.message);
                    setTimeout(() => {
                        window.location.href = "/admin/update/password";
                    }, 2000); 
                } else {
                    errorToast(res.data.message || 'An unexpected error occurred');
                }
            } catch (error) {
                if (error.response) {
                    const status = error.response.status;
                    if (status === 404) {
                        errorToast(error.response.data.message || 'User not found');
                    } else if (status === 400) {
                        const message = error.response.data.message || 'Bad request (400)';
                        document.getElementById('oldpassword-error').innerText = message;
                    } else if (status === 422) {
                        const errors = error.response.data.errors || {};
                        for (const key in errors) {
                          if (errors.hasOwnProperty(key)) {
                            const errorMessage = errors[key][0]; 
                            document.getElementById(`${key}-error`).innerText = errorMessage; 
                          }
                        }
                    } else {
                        errorToast(error.response.data.message || 'An unexpected error occurred');
                    }
                } else {
                    errorToast('An error occurred: ' + error.message);
                }
            }
        }
    }
</script>