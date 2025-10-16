<div class="row">
  <div class="col-xl-6 col-lg-6 col-md-6 order-1 order-md-0">
    <div class="card mb-4">
      <div class="card-body">
        <div class="user-avatar-section">
          <div class="d-flex align-items-center flex-column">
            <img class="img-fluid rounded mb-3 mt-4" src="" id="customer-image" height="120" width="120" alt="User avatar" />
            <div class="user-info text-center">
              <h4><span id="customer-name"> </span></h4>
              <span class="badge bg-label-danger rounded-pill">Customer</span>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-between flex-wrap my-2 py-3">
          <div class="d-flex align-items-center me-4 mt-3 gap-3">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class="mdi mdi-check mdi-24px"></i>
              </div>
            </div>
            <div>
              <h4 class="mb-0" id="orders-count"> </h4>
              <span>Orders</span>
            </div>
          </div>
          <div class="d-flex align-items-center mt-3 gap-3">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class="mdi mdi-star-outline mdi-24px"></i>
              </div>
            </div>
            <div>
              <h4 class="mb-0" id="complaints-count"></h4>
              <span>Product Complaints</span>
            </div>
          </div>
          <div class="d-flex align-items-center mt-3 gap-3">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class="mdi mdi-cross-bolnisi mdi-24px"></i>
              </div>
            </div>
            <div>
              <h4 class="mb-0" id="received-complaints-count"></h4>
              <span>Received Complaints</span>
            </div>
          </div>
        </div>
        <h5 class="pb-3 border-bottom mb-3">Personal Details</h5>
        <div class="info-container">
          <ul class="list-unstyled mb-4">
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Name:</span>
              <span id="customer-name1"></span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Email:</span>
              <span id="customer-email"> </span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Contact:</span>
              <span id="customer-phone"> </span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Registration Date:</span>
              <span id="customer-registration-date"> </span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Registration Time:</span>
              <span id="customer-registration-time"> </span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Email Verified:</span>
              <span id="customer-email-verified"> </span>
            </li>
          </ul>
          <div class="d-flex justify-content-center">
            <a href="/client/customers" class="btn btn-outline-primary">Back to customer list</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-6 col-lg-6 col-md-6 order-1 order-md-0">
    <div class="card mb-4">
      <div class="card-body">
        <div class="user-avatar-section">
          <div class="d-flex align-items-center justify-content-center">
            <div class="d-flex flex-row align-items-center">
              <div class="text-center">
                <img class="img-fluid rounded mb-3 mt-4" src="" id="doc-image1" height="120" width="120" alt="User avatar 1" />
                <br />
                <a href="#" id="download-doc1" class="btn btn-primary btn-sm mt-2">Download</a>
              </div>

              <div class="text-center ms-3">
                <img class="img-fluid rounded mb-3 mt-4" src="" id="doc-image2" height="120" width="120" alt="User avatar 2" />
                <br />
                <a href="#" id="download-doc2" class="btn btn-primary btn-sm mt-2">Download</a>
              </div>
            </div>
          </div>

          <div class="user-info text-center mt-3">
            <h4><span id="customer-name2"> </span></h4>
            <span class="badge bg-label-danger rounded-pill">Customer</span>
          </div>
        </div>

        <h5 class="pb-3 border-bottom mb-3">Document Details</h5>
        <div class="info-container">
          <ul class="list-unstyled mb-4">
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Address:</span>
              <span id="customer-address"></span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Country:</span>
              <span id="customer-country"> </span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">County:</span>
              <span id="customer-county"> </span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">City:</span>
              <span id="customer-city"> </span>
            </li>
            <li class="mb-3">
              <span class="fw-medium text-heading me-2">Document Verified:</span>
              <span id="customer-document-verified"> </span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  document.addEventListener("DOMContentLoaded", function () {
    CustomerDetailsInfo();
  });

  async function CustomerDetailsInfo() {
    showLoader();
    try {
      let customerId = getCustomerIdFromUrl();
      let res = await axios.get("/client/get/customer/details/" + customerId);
      if (res.status === 200 && res.data.status === 'success') {
        let imageUrl = res.data.data['image']
          ? `/upload/customer-profile/small/${res.data.data['image']}`
          : `/upload/no_image.jpg`;

        let docImage1 = res.data.data['doc_image1']
        ? `/upload/customer-document/medium/${res.data.data['doc_image1']}`
        : `/upload/no_image.jpg`;

        let docImage2 = res.data.data['doc_image2']
        ? `/upload/customer-document/medium/${res.data.data['doc_image2']}`
        : `/upload/no_image.jpg`;


        let docImage1Large = res.data.data['doc_image1']
        ? `/client/download/doc-image1/${res.data.data['id']}`
        : null;

        let docImage2Large = res.data.data['doc_image2']
        ? `/client/download/doc-image2/${res.data.data['id']}`
        : null;

        let firstName = res.data.data['firstName'];
        let lastName = res.data.data['lastName'];
        let fullName = lastName ? `${firstName} ${lastName}` : firstName;

        let mobileNumber = res.data.data['mobile'];
        let phoneBadge = mobileNumber
          ? `<span class="badge bg-success">${mobileNumber}</span>`
          : `<span class="badge bg-info">Contact Number Not Found</span>`;

          let createdAt = new Date(res.data.data['created_at']);

          let registrationDate = createdAt.toLocaleDateString('en-GB', {
              day: '2-digit',
              month: 'long',
              year: 'numeric'
          });

          let registrationTime = createdAt.toLocaleTimeString('en-US', {
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit',
              hour12: true
          });

        let isEmailVerified = res.data.data['is_email_verified'];
        let emailStatus = isEmailVerified === 1
          ? `<span class="badge bg-success">Yes</span>`
          : `<span class="badge bg-danger">No</span>`;

        let isDocumentVerified = res.data.data['status'];
        let documentStatus = isDocumentVerified === 1
          ? `<span class="badge bg-success">Yes</span>`
          : `<span class="badge bg-danger">No</span>`;

        let address = res.data.data['address1'] ? res.data.data['address1'] : 'N/A';
        let country = res.data.data['country']?.['name'] || 'N/A';
        let county = res.data.data['county']?.['name'] || 'N/A';
        let city = res.data.data['city']?.['name'] || 'N/A';
          
        document.getElementById('customer-image').src = imageUrl;
        document.getElementById('customer-name').innerText = fullName;
        document.getElementById('orders-count').innerText = res.data.data['total_orders'];
        document.getElementById('complaints-count').innerText = res.data.data['total_product_complaints'];
        document.getElementById('received-complaints-count').innerText = res.data.data['total_received_complaints'];
        document.getElementById('customer-name1').innerText = fullName;
        document.getElementById('customer-email').innerText = res.data.data['email'];
        document.getElementById('customer-phone').innerHTML = phoneBadge;
        document.getElementById('customer-registration-date').innerText = registrationDate;
        document.getElementById('customer-registration-time').innerText = registrationTime;
        document.getElementById('customer-email-verified').innerHTML = emailStatus;


        document.getElementById('doc-image1').src = docImage1;
        document.getElementById('doc-image2').src = docImage2;
        document.getElementById('customer-name2').innerText = fullName;
        document.getElementById('customer-address').innerText = address;
        document.getElementById('customer-country').innerText = country;
        document.getElementById('customer-county').innerText = county;
        document.getElementById('customer-city').innerText = city;
        document.getElementById('customer-document-verified').innerHTML = documentStatus;

        if (docImage1Large) {
          document.getElementById('download-doc1').href = docImage1Large;
          document.getElementById('download-doc1').setAttribute('download', res.data.data['doc_image1']);
        } else {
          document.getElementById('download-doc1').classList.add('disabled');
        }

        if (docImage2Large) {
          document.getElementById('download-doc2').href = docImage2Large;
          document.getElementById('download-doc2').setAttribute('download', res.data.data['doc_image2']);
        } else {
          document.getElementById('download-doc2').classList.add('disabled');
        }

      } else {
          errorToast(res.data.message || "Failed to fetch client info.");
      }
    } catch (error) {
      handleError(error);
    }finally {
      hideLoader();      
    }
  }


  function getCustomerIdFromUrl() {
    let url = window.location.pathname;
    let segments = url.split('/');
    return segments[segments.length - 1];
  }


  function handleError(error) {
      let message = "An unexpected error occurred.";

      if (error.response) {
          const { status, data } = error.response;
          switch (status) {
              case 500:
                  message = data?.message || "Internal server error. Please try again later.";
                  break;
              case 404:
                  message = data?.message || "Data not found.";
                  break;
              default:
                  message = data?.message || "Something went wrong.";
          }

      } else if (error.request) {
          message = "No response from the server. Please check your internet connection.";
      } else {
          message = error.message;
      }

      errorToast(message);
  }
</script>

