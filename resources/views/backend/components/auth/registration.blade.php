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
    <a href="{{ route('home') }}" class="btn btn-primary mb-5">Back To Home Page</a>
    <h4 class="mb-2">Admin - Adventure starts here at <span id='auth-page-website-name'></span> 🚀</h4>
    <p class="mb-4" id='registration-page-slogan-name'>Make your app management easy and fun!</p>

    <form id="formAuthentication" class="mb-3">
      <div class="form-floating form-floating-outline mb-3">
        <input
        type="text"
        class="form-control"
        id="firstName"
        placeholder="Enter your first name"
        autofocus />
        <label for="username">First Name</label>
        <span class="error-message text-danger" id="firstName-error"></span>
      </div>
      <div class="form-floating form-floating-outline mb-3">
        <input type="email" class="form-control" id="email" placeholder="Enter your email" />
        <label for="email">Email</label>
        <span class="error-message text-danger" id="email-error"></span>
      </div>
      <div class="mb-3 form-password-toggle">
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
      <button type="button" onclick="Registration()" id="register-btn" class="btn btn-primary d-grid w-100">Sign up</button>
    </form>

    <p class="text-center mt-2">
      <span>Already have an account?</span>
      <a href="{{ route('admin.login.page') }}">
        <span>Sign in instead</span>
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

<script>
  async function Registration() {
    let email = document.getElementById('email').value;
    let firstName = document.getElementById('firstName').value;
    let password = document.getElementById('password').value;

    document.getElementById('firstName-error').innerText = '';
    document.getElementById('email-error').innerText = '';
    document.getElementById('password-error').innerText = '';

    if(email.length === 0){
      errorToast('Email is required')
    }
    else if(firstName.length === 0){
      errorToast('First Name is required')
    }
    else if(password.length === 0){
      errorToast('Password is required')
    }
    else{
      try {
        let res = await axios.post("/admin/registration", {
          email: email,
          firstName: firstName,
          password: password
        });

        if(res.status === 201 && res.data['status'] === 'success'){
          successToast(res.data['message']);
          setTimeout(function (){
            window.location.href = '/admin/login';
          }, 1000);
        }
        else{
          errorToast(res.data['message']);
        }
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.errors;
          for (const key in errors) {
            if (errors.hasOwnProperty(key)) {
              const errorMessage = errors[key][0]; 
              document.getElementById(`${key}-error`).innerText = errorMessage; 
            }
          }
        } else {
          errorToast(error.response ? error.response.data.message : 'Registration failed');
        }
      }
    }
  }
</script>