<section id="landingHero" class="section-py landing-hero">
  <div class="container">
    <div class="hero-text-box text-center">
      <h1 class="text-primary hero-title" id="hero-title"></h1>
      <h2 class="h6 mb-4 pb-1 lh-lg" id="hero-description"></h2>
    </div>
    <div class="text-center">
      <a style="margin-top: 5px; margin-bottom: 5px;" href="{{ route('client.registration.page') }}" class="btn btn-info">Donator - Get early access</a>
      <a style="margin-top: 5px;margin-bottom: 5px;" href="{{ route('register.page') }}" class="btn btn-primary">Donatee - Get early access</a>
    </div>

    <div class="position-relative hero-animation-img">
      <a href="{{ url('vertical-menu-template/app-ecommerce-dashboard.html') }}" target="_blank">
        <div class="hero-dashboard-img text-center">
          <img
            src="" id="hero-image"
            alt="hero dashboard"
            class="animation-img"
            data-speed="2"
            data-app-light-img="{{ asset('frontend/assets/img/front-pages/landing-page/hero-dashboard-light.png') }}"
            data-app-dark-img="{{ asset('frontend/assets/img/front-pages/landing-page/hero-dashboard-dark.png') }}" />
        </div>
        <div class="position-absolute hero-elements-img">
          <img
            src="{{ asset('frontend/assets/img/front-pages/landing-page/hero-elements-light.png') }}"
            alt="hero elements"
            class="animation-img"
            data-speed="4"
            data-app-light-img="{{ asset('frontend/assets/img/front-pages/landing-page/hero-elements-light.png') }}"
            data-app-dark-img="{{ asset('frontend/assets/img/front-pages/landing-page/hero-elements-dark.png') }}" />
        </div>
      </a>
    </div>
  </div>
</section>

<script>
    async function getHeroInfo() {
        try {
            let res = await axios.get("/hero-page-info");
            if (res.status === 200 && res.data.status === "success") {
                const firstData = res.data.data;
                const image = "{{ asset('upload/hero/') }}" + "/" + firstData.image;
                document.getElementById('hero-image').src = image;
                document.getElementById('hero-title').innerText = firstData.title;
                document.getElementById('hero-description').innerHTML = firstData.description;
            } else {
                errorToast("Error fetching about page data: " + res.data.message);
            }

        } catch (error) {
            handleError(error);
        }
    }
</script>