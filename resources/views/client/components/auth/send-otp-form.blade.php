<a href="{{ url('/') }}" class="auth-cover-brand d-flex align-items-center gap-2">
  <span class="app-brand-logo demo">
      <img src="/upload/no_image.jpg" id="logo" width="100" height="40" alt="App Logo">
  </span>
</a>

<div class="authentication-inner row m-0">
  <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-5 pb-2">
    <img
      src="{{ asset('backend/assets/img/illustrations/auth-forgot-password-illustration-light.png') }}"
      class="auth-cover-illustration w-100"
      alt="auth-illustration"
      data-app-light-img="{{ asset('backend/assets/img/illustrations/auth-forgot-password-illustration-light.png') }}"
      data-app-dark-img="{{ asset('backend/assets/img/illustrations/auth-forgot-password-illustration-dark.png') }}" />

    <img
      src="{{ asset('backend/assets/img/illustrations/auth-cover-forgot-password-mask-light.png') }}"
      class="authentication-image"
      alt="mask"
      data-app-light-img="{{ asset('backend/assets/img/illustrations/auth-cover-forgot-password-mask-light.png') }}"
      data-app-dark-img="{{ asset('backend/assets/img/illustrations/auth-cover-forgot-password-mask-dark.png') }}" />
  </div>

  <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4">
    <div class="w-px-400 mx-auto">
      <h4 class="mb-2">Forgot Password? 🔒</h4>
      <p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>
      <form id="formAuthentication" class="mb-3">
        <div class="form-floating form-floating-outline mb-3">
          <input
            type="email"
            class="form-control"
            id="email"
            placeholder="Enter your email"
            autofocus />
          <label for="email">Email</label>
          <span class="error-message text-danger" id="email-error"></span>
        </div>
        <button type="button" onclick="VerifyEmail()" class="btn btn-primary d-grid w-100">Send Reset Link</button>
      </form>
      <div class="text-center">
        <a href="{{ route('client.login.page') }}" class="d-flex align-items-center justify-content-center">
          <i class="mdi mdi-chevron-left scaleX-n1-rtl mdi-24px"></i>
          Back to login
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  async function VerifyEmail() {
      let email = document.getElementById('email').value;
      document.getElementById('email-error').innerText = '';

      if (email.length === 0) {
          errorToast('Please enter your email address');
      } else {
          try {
              let res = await axios.post('/client/send-otp', { email: email });

              if (res.status === 200 && res.data['status'] === 'success') {
                  successToast(res.data['message']);
                  sessionStorage.setItem('email', email);
                  setTimeout(function () {
                      window.location.href = '/client/verifyOtp';
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
                  } else if (error.response.status === 401) {
                      errorToast(error.response.data.message || 'User not found');
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

