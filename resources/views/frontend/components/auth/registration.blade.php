<a href="{{ url('/') }}" class="auth-cover-brand d-flex align-items-center gap-2">
  <span class="app-brand-logo demo">
      <img src="/upload/no_image.jpg" id="logo" width="100" height="40" alt="App Logo">
  </span>
</a>

<div class="authentication-inner row m-0">
  <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-5 pb-2">
    <img
    src="{{ asset('frontend/assets/img/illustrations/auth-register-illustration-light.png') }}"
    class="auth-cover-illustration w-100"
    alt="auth-illustration"
    data-app-light-img="{{ asset('frontend/assets/img/illustrations/auth-register-illustration-light.png') }}"
    data-app-dark-img="{{ asset('frontend/assets/img/illustrations/auth-register-illustration-dark.png') }}" />
    <img
    src="{{ asset('frontend/assets/img/illustrations/auth-cover-register-mask-light.png') }}"
    class="authentication-image"
    alt="mask"
    data-app-light-img="{{ asset('frontend/assets/img/illustrations/auth-cover-register-mask-light.png') }}"
    data-app-dark-img="{{ asset('frontend/assets/img/illustrations/auth-cover-register-mask-dark.png') }}" />
  </div>

  <div
  class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-5 px-4 py-4">
  <div class="w-px-400 mx-auto pt-5 pt-lg-0">
    <a href="{{ route('home') }}" class="btn btn-primary mb-5">Back To Home Page</a>
    <h4 class="mb-2">Customer - Adventure starts here at <span id='auth-page-website-name'></span> 🚀</h4>
    <p class="mb-4" id='registration-page-slogan-name'>Make your app management easy and fun!</p>

    <form id="formAuthentication" class="mb-3">
      <div id="success-message" class="alert alert-success d-none"></div>
      <div class="form-floating form-floating-outline mb-3">
        <input
        type="text"
        class="form-control"
        id="registration-firstName"
        placeholder="Enter your first name"
        autofocus />
        <label for="username">First Name</label>
        <span class="error-message text-danger" id="firstName-error"></span>
      </div>
      <div class="form-floating form-floating-outline mb-3">
        <input type="email" class="form-control" id="registration-email" placeholder="Enter your email" />
        <label for="email">Email</label>
        <span class="error-message text-danger" id="email-error"></span>
      </div>
      <div class="mb-3 form-password-toggle">
        <div class="input-group input-group-merge">
          <div class="form-floating form-floating-outline">
            <input
            type="password"
            id="registration-password"
            class="form-control"
            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
            aria-describedby="password" />
            <label for="password">Password</label>
            <span class="error-message text-danger" id="password-error"></span>
          </div>
          <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
        </div>
      </div>
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="accept_registration_tnc"/>
          <label class="form-check-label" for="terms-conditions">
            I agree to
            <a href="/user/registration/terms-conditions/customer_registration" target="_blank">privacy policy & terms</a>
          </label>
        </div>
        <span class="error-message text-danger" id="accept_registration_tnc-error"></span>
      </div>
      <button type="button" onclick="Register(event)" id="register-btn" class="btn btn-primary d-grid w-100">Sign up</button>
    </form>

    <p class="text-center mt-2">
      <span>Already have an account?</span>
      <a href="{{ route('login.page') }}">
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
  async function Register(event) {
      event.preventDefault();

      let email = document.getElementById('registration-email').value;
      let firstName = document.getElementById('registration-firstName').value;
      let password = document.getElementById('registration-password').value;
      let accept_registration_tnc = document.getElementById('accept_registration_tnc').checked ? 1 : 0;

      document.getElementById('firstName-error').innerText = '';
      document.getElementById('email-error').innerText = '';
      document.getElementById('password-error').innerText = '';
      document.getElementById('accept_registration_tnc-error').innerText = '';

      if(firstName.length === 0){
        document.getElementById('firstName-error').innerText = 'First name is required';
      }
      else if(email.length === 0){
        document.getElementById('email-error').innerText = 'Email is required';
      }
      else if(password.length === 0){
        document.getElementById('password-error').innerText = 'Password is required';
      }
      else if (!accept_registration_tnc) {
          errorToast("You must accept the terms and conditions for registration!");
      }
      else{
          try {
              let res = await axios.post("/user/registration", {
                  email: email,
                  firstName: firstName,
                  password: password,
                  accept_registration_tnc : accept_registration_tnc
              });

              if(res.status === 201 && res.data['status'] === 'success'){
                  document.getElementById('success-message').innerText = res.data['message'];
                  document.getElementById('success-message').classList.remove('d-none'); 
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