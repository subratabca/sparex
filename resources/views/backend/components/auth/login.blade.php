<a href="{{ url('/') }}" class="auth-cover-brand d-flex align-items-center gap-2">
  <span class="app-brand-logo demo">
    <img src="/upload/no_image.jpg" id="logo" width="100" height="40" alt="App Logo">
  </span>
</a>

<div class="authentication-inner row m-0">
  <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-5 pb-2">
    <img
    src="{{ asset('backend/assets/img/illustrations/auth-login-illustration-light.png') }}"
    class="auth-cover-illustration w-100"
    alt="auth-illustration"
    data-app-light-img="illustrations/auth-login-illustration-light.png"
    data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
    <img
    src="{{ asset('backend/assets/img/illustrations/auth-cover-login-mask-light.png') }}"
    class="authentication-image"
    alt="mask"
    data-app-light-img="{{ asset('backend/assets/img/illustrations/auth-cover-login-mask-light.png') }}"
    data-app-dark-img="{{ asset('backend/assets/img/illustrations/auth-cover-login-mask-dark.png') }}" />
  </div>

  <div
  class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-5 px-4 py-4">
  <div class="w-px-400 mx-auto pt-5 pt-lg-0">
    <a href="{{ route('home') }}" class="btn btn-primary mb-5">Back To Home Page</a>
    <h4 class="mb-2">Admin, Welcome to <span id='auth-page-website-name'></span> 👋</h4>
    <p class="mb-4">Please sign-in to your account and start the adventure</p>

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
      <div class="mb-3">
        <div class="form-password-toggle">
          <div class="input-group input-group-merge">
            <div class="form-floating form-floating-outline">
              <input
              type="password"
              id="password"
              class="form-control"
              placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
              aria-describedby="password" />
              <label for="password">Password</label>
              <span class="error-message text-danger" id="password-error"></span>
            </div>
            <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
          </div>
        </div>
      </div>
      <div class="mb-3 d-flex justify-content-between">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remember-me" />
          <label class="form-check-label" for="remember-me"> Remember Me </label>
        </div>
        <a href="{{ url('/admin/sendOtp') }}" class="float-end mb-1">
          <span>Forgot Password?</span>
        </a>
      </div>
      <button type="button" onclick="SubmitLogin()" id="register-btn" class="btn btn-primary d-grid w-100">Sign in</button>
    </form>

    <p class="text-center mt-2">
      <span>New on our platform?</span>
      <a href="{{ route('admin.registration.page') }}">
        <span>Create an account</span>
      </a>
    </p>

    <div class="divider my-4">
      <div class="divider-text">or</div>
    </div>

    <div class="d-flex justify-content-center gap-2">
      <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-facebook">
        <i class="tf-icons mdi mdi-24px mdi-facebook"></i>
      </a>

      <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-twitter">
        <i class="tf-icons mdi mdi-24px mdi-twitter"></i>
      </a>

      <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-google-plus">
        <i class="tf-icons mdi mdi-24px mdi-google"></i>
      </a>
    </div>
  </div>
</div>
<!-- /Login -->
</div>

<script>
  async function SubmitLogin() {
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;

    document.getElementById('email-error').innerText = '';
    document.getElementById('password-error').innerText = '';

    if (email.length === 0) {
      errorToast("Email is required");
    } else if (password.length === 0) {
      errorToast("Password is required");
    } else {
      try {
        let res = await axios.post("/admin/login", { email: email, password: password });
        if (res.status === 200 && res.data['status'] === 'success') {
          window.location.href="/admin/dashboard";
        } else {
          errorToast(res.data['message']);
        }
      } catch (error) {
        if (error.response) {
          if (error.response.status === 401) {
            errorToast(error.response.data.message);
          } else if (error.response.status === 422) {
            const errors = error.response.data.errors;
            for (const key in errors) {
              if (errors.hasOwnProperty(key)) {
                const errorMessage = errors[key][0];
                document.getElementById(`${key}-error`).innerText = errorMessage;
              }
            }
          } else if (error.response.status === 500) {
            errorToast(error.response.data.message || "An unexpected error occurred. Please try again later.");
          } else {
            errorToast(error.response.data.message || 'Login failed');
          }
        } else {
          errorToast('Login failed. Please check your network connection.');
        }
      }
    }
  }
</script>