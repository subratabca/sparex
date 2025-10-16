      <section id="landingContact" class="section-py bg-body landing-contact">
        <div class="container bg-icon-left">
          <h6 class="text-center fw-semibold d-flex justify-content-center align-items-center mb-4">
            <img
              src="{{ asset('frontend/assets/img/front-pages/icons/section-tilte-icon.png') }}"
              alt="section title icon"
              class="me-2" />
            <span class="text-uppercase">contact us</span>
          </h6>
          <h3 class="text-center mb-2"><span class="fw-bold">Lets work</span> together</h3>
          <p class="text-center fw-medium mb-3 mb-md-5 pb-3">Any question or remark? just write us a message</p>
          <div class="row gy-4">
            <div class="col-lg-5">
              <div class="card h-100">
                <div class="bg-primary rounded text-white card-body">
                  <p class="fw-medium mb-1">Let’s contact with us</p>
                  <p class="display-6 mb-4">Share your ideas or requirement with our experts.</p>
                  <img
                    src="{{ asset('frontend/assets/img/front-pages/landing-page/let’s-contact.png') }}"
                    alt="let’s contact"
                    class="w-100 mb-2 pb-1" />
                  <p class="mb-0">
                    Looking for more customisation, more features, and more anything? Don’t worry, We’ve provide you
                    with an entire team of experienced professionals.
                  </p>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="card">
                <div class="card-body">
                  <h5 class="mb-4">Share your ideas</h5>
                  <form id="save-form">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <div class="form-floating form-floating-outline">
                          <input type="text" class="form-control" id="contact-name" placeholder="Name" />
                          <label for="basic-default-fullname">Full name</label>
                          <span class="error-message text-danger" id="name-error"></span>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-floating form-floating-outline">
                          <input
                            type="text"
                            class="form-control"
                            id="contact-phone" placeholder="Phone Number">
                          <label for="basic-default-email">Phone number</label>
                          <span class="error-message text-danger" id="phone-error"></span>
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="form-floating form-floating-outline">
                          <input
                            type="email"
                            class="form-control"
                            id="contact-email" placeholder="Email"/>
                          <label for="basic-default-email">Email address</label>
                          <span class="error-message text-danger" id="email-error"></span>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-floating form-floating-outline">
                          <textarea
                            class="form-control h-px-200"
                            id="contact-message" placeholder="Message"></textarea>
                          <label for="basic-default-message">Message</label>
                          <span class="error-message text-danger" id="message-error"></span>
                        </div>
                      </div>
                    </div>
                    <button type="submit" onclick="saveContact(event)" id="save-btn" class="btn btn-primary mt-3">Send inquiry</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
<script>
    async function saveContact(event) {
        event.preventDefault();

        let name = document.getElementById('contact-name').value;
        let email = document.getElementById('contact-email').value;
        let phone = document.getElementById('contact-phone').value;
        let message = document.getElementById('contact-message').value;

        // Clear previous error messages
        document.querySelectorAll('.error-message').forEach(errorMsg => {
            errorMsg.textContent = '';
        });

        // Validation
        let isValid = true;
        if (name.trim() === '') {
            document.getElementById('name-error').textContent = 'Name is required';
            isValid = false;
        }
        if (email.trim() === '') {
            document.getElementById('email-error').textContent = 'Email is required';
            isValid = false;
        }else if (!isValidContactEmail(email)) {
          document.getElementById('email-error').textContent = 'Invalid email format';
          isValid = false;
        }

        if (phone.trim() === '') {
            document.getElementById('phone-error').textContent = 'Phone number is required';
            isValid = false;
        }
        if (message.trim() === '') {
            document.getElementById('message-error').textContent = 'Message is required';
            isValid = false;
        }

        if (isValid) {
            let formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('message', message);
            const config = {
                headers: {
                    'content-type': 'multipart/form-data',
                },
            };

            try {
                let res = await axios.post("/store-contact-info", formData, config);

                if (res.status === 201 && res.data.status === "success") {
                    successToast(res.data.message);
                    document.getElementById('save-form').reset();
                } else {
                    errorToast("Request failed with status: " + res.status);
                }

            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    for (const [field, messages] of Object.entries(errors)) {
                        document.getElementById(`${field}-error`).textContent = messages.join(' ');
                    }
                } else {
                    errorToast("An error occurred: " + (error.response?.data?.message || 'Unknown error'));
                }
            }
        }
    }

    function isValidContactEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
</script>


