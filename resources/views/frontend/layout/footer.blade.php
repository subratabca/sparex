<footer class="landing-footer">
  <div class="footer-top">
    <div class="container">
      <div class="row gx-0 gy-4 g-md-5">
        <div class="col-lg-5">
          <a href="{{ route('home') }}" class="app-brand-link mb-4">
            <span class="app-brand-logo demo me-2">
              <span style="color: #666cff">
                <img id="footer-logo" src=" " width="100" height="40" alt="App Logo">
              </span>
            </span>
          </a>
          <p class="footer-text footer-logo-description mb-4" id="footer-description"> </p>
  <form id="save-form">
    <div class="d-flex mt-2 gap-3">
      <div class="form-floating form-floating-outline w-px-250">
        <input type="email" class="form-control bg-transparent text-white" id="newsletter-email" placeholder="Your email" />
        <label for="newsletter">Subscribe to newsletter</label>
        <span class="error-message text-danger" id="subscriber-email-error"></span>
      </div>
      <button type="submit" onclick="saveSubscriber(event)" id="save-btn" class="btn btn-primary">Subscribe</button>
    </div>
    <div id="server-response" class="mt-1"></div>
  </form>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <h6 class="footer-title mb-4">SpareX</h6>
          <ul class="list-unstyled mb-0">
            <li class="mb-3">
              <a href="{{ route('home') }}" target="_blank" class="footer-link">Home</a>
            </li>
            <li class="mb-3">
              <a href="{{ route('about') }}" target="_blank" class="footer-link">About</a>
            </li>
            <li class="mb-3">
              <a href="{{ route('contact.us.page') }}" target="_blank" class="footer-link">Contact Us</a>
            </li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <h6 class="footer-title mb-4">Pages</h6>
          <ul class="list-unstyled mb-0">
            <li class="mb-3">
              <a href="{{ route('login.page') }}" class="footer-link">Login</a>
            </li>
            <li class="mb-3">
              <a href="{{ route('register.page') }}" class="footer-link">Register</a>
            </li>
            <li class="mb-3">
              <a href="help-center-landing.html" class="footer-link">Help Center</a>
            </li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-4">
          <h6 class="footer-title mb-4">Company Info</h6>
          <ul class="list-unstyled mb-0">
            <li class="mb-0">
              <p href="" class="footer-link"><strong>Name:</strong> <span id="footer-company-name"></span></p>
            </li>
            <li class="mb-0">
              <p href="" class="footer-link"><strong>Email:</strong> <span id="footer-company-email"></span></p>
            </li>
            <li class="mb-0">
              <p href="" class="footer-link"><strong>Phone:</strong> <span id="footer-company-phone"></span></p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom py-3">
    <div
    class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
    <div class="mb-2 mb-md-0">
      <span class="footer-text"
      >©
      <script>
        document.write(new Date().getFullYear());
      </script>
      , Made with <i class="tf-icons mdi mdi-heart text-danger"></i> by
    </span>
    <a href="https://pixinvent.com" target="_blank" class="footer-link fw-medium footer-theme-link"
    >Subrata</a
    >
  </div>
  <div>
    <a href="https://github.com/pixinvent" class="footer-link me-2" target="_blank"
    ><i class="mdi mdi-github"></i
    ></a>
    <a href="https://www.facebook.com/pixinvents/" class="footer-link me-2" target="_blank"
    ><i class="mdi mdi-facebook"></i
    ></a>
    <a href="https://twitter.com/pixinvents" class="footer-link me-2" target="_blank"
    ><i class="mdi mdi-twitter"></i
    ></a>
    <a href="https://www.instagram.com/pixinvents/" class="footer-link" target="_blank"
    ><i class="mdi mdi-instagram"></i
    ></a>
  </div>
</div>
</div>
</footer>



<script>
  async function saveSubscriber(event) {
    event.preventDefault();

    let email = document.getElementById('newsletter-email').value;
    const responseDiv = document.getElementById('server-response');

    document.querySelectorAll('.error-message').forEach(errorMsg => {
      errorMsg.textContent = '';
    });
    responseDiv.innerHTML = '';

    let isValid = true;
    if (email.trim() === '') {
      document.getElementById('subscriber-email-error').textContent = 'Email is required';
      isValid = false;
    } else if (!isValidEmail(email)) {
      document.getElementById('subscriber-email-error').textContent = 'Invalid email format';
      isValid = false;
    }

    if (isValid) {
      let formData = new FormData();
      formData.append('email', email);

      try {
        let res = await axios.post("/store-newsletter-subscription-info", formData);

        if (res.status === 201 && res.data.status === "success") {
          responseDiv.innerHTML = `<div class="text-success">${res.data.message}</div>`;
          document.getElementById('save-form').reset();
        } else if (res.status === 200) {
          responseDiv.innerHTML = `<div class="text-info">${res.data.message}</div>`;
        }

      } catch (error) {
        if (error.response && error.response.status === 422) {
          const errors = error.response.data.errors;
          for (const [field, messages] of Object.entries(errors)) {
            document.getElementById(`${field}-error`).textContent = messages.join(' ');
          }
        } else {
          responseDiv.innerHTML = `<div class="text-danger">An error occurred: ${error.response?.data?.message || 'Unknown error'}</div>`;
        }
      }

      responseDiv.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
  }

  function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }

</script>