<a href="{{ url('/') }}" class="auth-cover-brand d-flex align-items-center gap-2">
  <span class="app-brand-logo demo">
      <img src="/upload/no_image.jpg" id="logo"  width="100" height="40" alt="App Logo">
  </span>
</a>

<div class="authentication-inner row m-0">
  <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-5 pb-2">
    <img
    src="{{ asset('backend/assets/img/illustrations/auth-register-illustration-light.png') }}"
    class="auth-cover-illustration w-100"
    alt="auth-illustration"
    data-app-light-img="{{ asset('backend/assets/img/illustrations/auth-register-illustration-light.png') }}"
    data-app-dark-img="{{ asset('backend/assets/img/illustrations/auth-register-illustration-dark.png') }}" />
    <img
    src="{{ asset('backend/assets/img/illustrations/auth-cover-register-mask-light.png') }}"
    class="authentication-image"
    alt="mask"
    data-app-light-img="{{ asset('backend/assets/img/illustrations/auth-cover-register-mask-light.png') }}"
    data-app-dark-img="{{ asset('backend/assets/img/illustrations/auth-cover-register-mask-dark.png') }}" />
  </div>

  <div
  class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-5 px-4 py-4">
  <div class="w-px-400 mx-auto pt-5 pt-lg-0">
    <h4 class="mb-2">Reset Password 🔒</h4>
    <p class="mb-4">Your new password must be different from previously used passwords</p>
    <form id="formAuthentication" class="mb-3">
      <div class="mb-3 form-password-toggle">
        <div class="input-group input-group-merge">
          <div class="form-floating form-floating-outline">
            <input
            type="password"
            id="password"
            class="form-control"
            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
            aria-describedby="password" />
            <label for="password">New Password</label>
            <span class="error-message text-danger" id="password-error"></span>
          </div>
          <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
        </div>
      </div>
      <div class="mb-3 form-password-toggle">
        <div class="input-group input-group-merge">
          <div class="form-floating form-floating-outline">
            <input
            type="password"
            id="cpassword"
            class="form-control"
            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
            aria-describedby="password" />
            <label for="confirm-password">Confirm Password</label>
            <span class="error-message text-danger" id="cpassword-error"></span>
          </div>
          <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
        </div>
      </div>
      <button type="button" onclick="ResetPass()" class="btn btn-primary d-grid w-100 mb-3">Set new password</button>
      <div class="text-center">
        <a href="{{ route('admin.login.page') }}" class="d-flex align-items-center justify-content-center">
          <i class="mdi mdi-chevron-left scaleX-n1-rtl mdi-24px"></i>
          Back to login
        </a>
      </div>
    </form>
  </div>
</div>

<script>
  async function ResetPass() {
    let password = document.getElementById('password').value;
    let cpassword = document.getElementById('cpassword').value;
    let email = sessionStorage.getItem('email');

    document.getElementById('password-error').innerText = '';
    document.getElementById('cpassword-error').innerText = '';

    if (!email) {
      errorToast('Please provide email for forgot password');
      setTimeout(function () {
        window.location.href = "/admin/sendOtp";
      }, 1000);
      return;
    }

    if (password.length === 0) {
      document.getElementById('password-error').innerText = 'Password is required';
    } else if (cpassword.length === 0) {
      document.getElementById('cpassword-error').innerText = 'Confirm Password is required';
    } else if (password !== cpassword) {
      document.getElementById('cpassword-error').innerText = 'Password and Confirm Password must be the same';
    } else {
      try {
        let res = await axios.post("/admin/reset-password", { 
          password: password,
          email: email
        });

        if (res.status === 200 && res.data['status'] === 'success') {
          successToast(res.data['message']);
          sessionStorage.clear();
          setTimeout(function () {
            window.location.href = "/admin/login";
          }, 1000);
        } else {
          errorToast(res.data['message']);
        }
      } catch (error) {
        if (error.response) {
          if (error.response.status === 422) {
            const errors = error.response.data.errors;
            for (const key in errors) {
              if (errors.hasOwnProperty(key)) {
                const errorMessage = errors[key][0];
                document.getElementById(`${key}-error`).innerText = errorMessage;
              }
            }
          } else if (error.response.status === 404) {
            errorToast(error.response.data.message || 'User not found');
          } else if (error.response.status === 500) {
            errorToast(error.response.data.message || 'Something went wrong. Please try again later.');
          } else {
            errorToast(error.response.data.message || 'An unexpected error occurred.');
          }
        } else {
          errorToast('An unexpected error occurred.');
        }
      }
    }
  }
</script>