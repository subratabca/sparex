<!DOCTYPE html>

<html
  lang="en"
  class="light-style layout-navbar-fixed layout-wide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('frontend/assets/') }}"
  data-template="front-pages">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title')</title>

    {{-- General Meta Tags --}}
    <meta name="description" content="@yield('meta_description', 'Default description here')" />
    <meta name="author" content="SpareX">

    {{-- Open Graph Meta Tags for Social Sharing --}}
    <meta property="og:title" content="@yield('og_title', 'Default Title')" />
    <meta property="og:description" content="@yield('og_description', 'Default Description')" />
    <meta property="og:image" content="@yield('og_image', asset('upload/no_image.jpg'))" />
    <meta property="og:image:secure_url" content="@yield('og_image_secure', asset('upload/no_image.jpg'))" />
    <meta property="og:image:width" content="@yield('og_image_width', '1200')" />
    <meta property="og:image:height" content="@yield('og_image_height', '630')" />
    <meta property="og:url" content="@yield('og_url', url('/'))" />
    <meta property="og:type" content="@yield('og_type', 'website')" />
    <meta property="og:site_name" content="SpareX" />
    
    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'Default Title')">
    <meta name="twitter:description" content="@yield('og_description', 'Default Description')">
    <meta name="twitter:image" content="@yield('og_image', asset('upload/no_image.jpg'))">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />

   <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/fonts/materialdesignicons.css') }}" />
    <!-- Ext Icons-->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/fonts/flag-icons.css') }}" /> 

    <!-- Menu waves for no-customizer fix -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/node-waves/node-waves.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/pages/front-page.css') }}" />


    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/nouislider/nouislider.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/swiper/swiper.css') }}" />
    <!-- Ext Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/quill/typography.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/quill/katex.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/quill/editor.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" /> 
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" /> 
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/pages/front-page-landing.css') }}" />
    <!-- Ext Page CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/pages/page-profile.css') }}" />
    <link href="{{ asset('common/custom-css/toastify.min.css') }}" rel="stylesheet" />

    @stack('styles')

    <style type="text/css">
    .loading-spinner {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(255, 255, 255, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    .spinner-container {
      display: flex;
    }

    .spinner-circle {
      width: 20px;
      height: 20px;
      margin: 0 5px;
      border-radius: 50%;
      background-color: #007bff;
      animation: bouncing 0.6s infinite alternate;
    }

    @keyframes bouncing {
      0% {
        transform: translateY(0);
      }
      100% {
        transform: translateY(-20px);
      }
    }
    </style>

    <!-- Helpers -->
    <script src="{{ asset('frontend/assets/vendor/js/helpers.js') }}"></script>

    <script src="{{ asset('common/custom-js/axios.min.js') }}"></script>
    <script src="{{asset('common/custom-js/toastify-js.js')}}"></script>
    <script src="{{asset('common/custom-js/config.js')}}"></script>


    <!-- Hotjar Tracking Code for SpareX -->
    <script>
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:5242363,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>
    <script src="//code.tidio.co/wddy2njbjfc4k1uu8l1sw0rgqdjw8evp.js" async></script>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-8L5Q5DVEHW"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
        
          gtag('config', 'G-8L5Q5DVEHW');
        </script>
  </head>

  <body>
    <script src="{{ asset('frontend/assets/vendor/js/dropdown-hover.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/js/mega-dropdown.js') }}"></script>

    <div id="bouncing-loader" class="loading-spinner" style="display: none;">
      <div class="spinner-container">
        <div class="spinner-circle"></div>
        <div class="spinner-circle"></div>
        <div class="spinner-circle"></div>
      </div>
    </div>
    
      @include('frontend.layout.header')

      <div data-bs-spy="scroll" class="scrollspy-example">

      @yield('content')

      </div>

      @include('frontend.layout.footer')

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/node-waves/node-waves.js') }}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('frontend/assets/vendor/libs/nouislider/nouislider.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/swiper/swiper.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/quill/quill.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>


    <!-- Main JS -->
    <script src="{{ asset('frontend/assets/js/front-main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('frontend/assets/js/front-page-landing.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/tables-datatables-basic.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/tables-datatables-advanced.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/maps-leaflet.js') }}"></script>
    <script src="https://js.stripe.com/v3/"></script>




<!-- Below script code is not working for autologout.will work later on this topic -->
<script>
function getTokenExpiry(token) {
    if (!token) return 0;
    try {
        // Decode Base64 safely
        const base64Payload = token.split('.')[1];
        const payload = JSON.parse(atob(base64Payload.replace(/-/g, '+').replace(/_/g, '/')));
        return payload.exp; // Unix timestamp
    } catch (e) {
        return 0;
    }
}

function startAutoLogout() {
    // Get token from cookie
    const tokenCookie = document.cookie
        .split(';')
        .map(c => c.trim())
        .find(c => c.startsWith('token='));

    if (!tokenCookie) return;

    const tokenValue = decodeURIComponent(tokenCookie.split('=')[1]);
    const expTime = getTokenExpiry(tokenValue);
    const currentTime = Math.floor(Date.now() / 1000);

    // Calculate timeout
    let timeout = (expTime - currentTime) * 1000;

    if (timeout <= 0) return; // Token already expired, middleware will handle

    const logoutUser = () => {
        if (typeof errorToast === 'function') {
            errorToast("Session expired. Redirecting to login.");
        } else {
            alert("Session expired. Redirecting to login.");
        }

        // Remove token cookie
        document.cookie = 'token=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT';

        // Redirect
        window.location.href = '/login';
    };

    setTimeout(logoutUser, timeout);
}

// Initialize auto logout on page load
startAutoLogout();
</script>
@stack('scripts')




</body>
</html> 


