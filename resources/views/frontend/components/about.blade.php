<section class="section-py bg-body first-section-pt">
  <div class="container">
    <div class="card g-3 mt-5">
    <div class="card-body row g-3">
      <div class="col-lg-8">
        <div class="card academy-content shadow-none border">
          <div class="p-2">
            <div class="cursor-pointer">
                <div class="gallery-main">
                  <img id="image" src="/upload/no_image.jpg" alt="Main Image" style="width: 100%; height: 400px;" />
                </div>
            </div>
          </div>
          <div class="card-body">
            <hr class="mb-4 mt-2" />
            <h5>Description</h5>
            <h6 id="title"></h6>
            <p class="mb-4" id="description"> </p>
            
            <hr class="my-4" />

          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="accordion stick-top" id="courseContent">

          <!-- Donator Section -->
          <div class="accordion-item shadow-none border border-bottom-0 my-0">
            <div class="accordion-header border-0" id="headingDonator">
              <button
                type="button"
                class="accordion-button bg-lighter rounded-0"
                data-bs-toggle="collapse"
                data-bs-target="#donatorContent"
                aria-expanded="true"
                aria-controls="donatorContent">
                <span class="h5 mb-1">How to use as a Donator</span>
              </button>
            </div>
            <div id="donatorContent" class="accordion-collapse collapse show" data-bs-parent="#courseContent">
              <div class="accordion-body py-3 border-top" id="donator">
                
              </div>
            </div>
          </div>

          <!-- Donatee Section -->
          <div class="accordion-item shadow-none border border-bottom-0 my-0">
            <div class="accordion-header border-0" id="headingDonatee">
              <button
                type="button"
                class="accordion-button bg-lighter rounded-0"
                data-bs-toggle="collapse"
                data-bs-target="#donateeContent"
                aria-expanded="false"
                aria-controls="donateeContent">
                <span class="h5 mb-1">How to use as a Donatee</span>
              </button>
            </div>
            <div id="donateeContent" class="accordion-collapse collapse" data-bs-parent="#courseContent">
              <div class="accordion-body py-3 border-top" id="donatee">
                
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
    </div>
  </div>
</section>

<script>
    async function AboutInfo() {
        showLoader();
        try {
            let res = await axios.get("/about-page-info");

            if (res.status === 200 && res.data.status === "success") {
                const firstData = res.data.data;

                const image = "{{ asset('upload/about/') }}" + "/" + firstData.image;
                document.getElementById('image').src = image;
                document.getElementById('title').innerText = firstData.title;
                document.getElementById('description').innerHTML = firstData.description;
                document.getElementById('donator').innerHTML = firstData.donator;
                document.getElementById('donatee').innerHTML = firstData.donatee;

            } else {
                errorToast("Error fetching about page data: " + res.data.message);
            }

        } catch (error) {
            if (error.response) {
                if (error.response.status === 404) {
                    errorToast("Data not found: " + error.response.data.message);
                } else if (error.response.status === 500) {
                    errorToast("An error occurred on the server: " + error.response.data.message);
                } else {
                    errorToast("An unknown error occurred: " + error.response.data.message);
                }
            } else {
                errorToast("Failed to connect to the server");
            }
        } finally{
          hideLoader();
        }
    }
</script>








