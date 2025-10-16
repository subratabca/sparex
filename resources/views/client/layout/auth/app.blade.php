<!DOCTYPE html>
<html
lang="en"
class="light-style layout-wide customizer-hide"
dir="ltr"
data-theme="theme-default"
data-assets-path="{{ asset('frontend/assets/') }}"
data-template="vertical-menu-template">
<head>
  <meta charset="utf-8" />
  <meta
  name="viewport"
  content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title')</title>

  <meta name="description" content="" />

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/img/favicon/favicon.ico') }}" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
rel="stylesheet" />

<!-- Icons -->
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/fonts/materialdesignicons.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/fonts/flag-icons.css') }}" />

<!-- Menu waves for no-customizer fix -->
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/node-waves/node-waves.css') }}" />

<!-- Core CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
<link rel="stylesheet" href="{{ asset('frontend/assets/css/demo.css') }}" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/typeahead-js/typeahead.css') }}" />
<!-- Vendor -->
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />

<!-- Page CSS -->
<!-- Page -->
<link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/pages/page-auth.css') }}" />

<!-- Helpers -->
<script src="{{ asset('frontend/assets/vendor/js/helpers.js') }}"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
<!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
<!-- <script src="{{ asset('frontend/assets/vendor/js/template-customizer.js') }}"></script> -->
<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<!-- <script src="{{ asset('frontend/assets/js/config.js') }}"></script> -->

<script src="{{ asset('backend/custom-js/axios.min.js') }}"></script>
<link href="{{ asset('backend/custom-css/toastify.min.css') }}" rel="stylesheet" />
<script src="{{asset('backend/custom-js/toastify-js.js')}}"></script>
<script src="{{asset('backend/custom-js/config.js')}}"></script>
</head>

<body>
  <div id="bouncing-loader" class="loading-spinner" style="display: none;">
    <div class="spinner-container">
      <div class="spinner-circle"></div>
      <div class="spinner-circle"></div>
      <div class="spinner-circle"></div>
    </div>
  </div>

  <div class="authentication-wrapper authentication-cover">

    @yield('content')

  </div>
<!-- / Content -->

<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('frontend/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/libs/hammer/hammer.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/libs/i18n/i18n.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/js/menu.js') }}"></script>

<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('frontend/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('frontend/assets/js/main.js') }}"></script>

<!-- Page JS -->
<script src="{{ asset('frontend/assets/js/pages-auth.js') }}"></script>
</body>
</html>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    await settingInfo(); 
  });

  async function settingInfo() {
    showLoader();
    try {
      const response = await axios.get('/setting-list');

      if (response.status === 200) {
        const data = response.data.data;
        document.getElementById('logo').src = data['logo'] ? "/upload/site-setting/" + data['logo'] : "/upload/no_image.jpg";

        const currentPath = window.location.pathname;
        if (currentPath === "/client/login" || currentPath === "/client/registration") {
          document.getElementById('auth-page-website-name').innerText = data['website_name'] || "SpareX";
        }

        if (currentPath === "/client/registration") {
          document.getElementById('registration-page-slogan-name').innerText = data['slogan'] || "Make your app management easy and fun!";
        }
      }
    } catch (error) {
      handleError(error);
    } finally{
      hideLoader();
    }
  }


  function handleError(error) {
    if (error.response) {
      const status = error.response.status;
      const message = error.response.data.message || 'An unexpected error occurred';
      if (status === 500) {
        errorToast(message || 'Server Error');
      } else {
        errorToast(message);
      }
    } else {
      errorToast('No response received from the server.');
    }
  }
</script>