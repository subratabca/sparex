<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h4 class="card-header">Update your documents</h4>
            <hr>
            <div class="card-body">
                <form id="formAccountSettings">
                    <div class="row gy-4">
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="firstName" autofocus />
                                <label for="firstName">First Name</label>
                                <span class="error-message text-danger" id="firstName-error"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="lastName" />
                                <label for="lastName">Last Name</label>
                                <span class="error-message text-danger" id="lastName-error"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control"  id="mobile" />
                                <label for="mobile">Phone Number</label>
                                <span class="error-message text-danger" id="mobile-error"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" disabled id="email" />
                                <label for="email">Email</label>
                                <span class="error-message text-danger" id="email-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2 gy-4">
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="address1" placeholder="Enter house and street number" />
                                <label for="address1">Address1<span class="text-danger">*</span></label>
                                <span class="error-message text-danger" id="address1-error"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" class="form-control" id="address2" placeholder="Enter address2" />
                                <label for="exampleFormControlInput1">Address2</label>
                                <span class="error-message text-danger" id="address2-error"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" class="form-control" id="zip-code" placeholder="Enter zip code" />
                                <label for="zip-code">Postcode(Example: EC1A 1BB)<span class="text-danger">*</span></label>
                                <span class="error-message text-danger" id="zip_code-error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <select id="countrySelect" class="form-select w-100" aria-label="Select Country"></select>
                                <label for="countrySelect">Select Country<span class="text-danger">*</span></label>
                            </div>
                            <span class="error-message text-danger" id="country-error"></span>
                        </div>

                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <select id="countySelect" class="form-select w-100" aria-label="Select County"></select>
                                <label for="countySelect">Select County<span class="text-danger">*</span></label>
                            </div>
                            <span class="error-message text-danger" id="county-error"></span>
                        </div>

                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline">
                                <select id="citySelect" class="form-select w-100" aria-label="Select City"></select>
                                <label for="citySelect">Select City<span class="text-danger">*</span></label>
                            </div>
                            <span class="error-message text-danger" id="city-error"></span>
                        </div>
                    </div>
                    <hr>
                    <p class="mb-4"><span class="text-danger">You must upload any of 2 documens</span> (Passport,NID,Bank Statement,Driving license,Utility bill)</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start align-items-sm-center gap-4">
                                <img src="{{ asset('/upload/no_image.jpg') }}" alt="user-avatar" class="d-block w-px-120 h-px-120 rounded" id="docImg1" />
                                <div class="button-wrapper">
                                    <label for="image1" class="btn btn-primary me-2 mb-3" tabindex="0">
                                        <span class="d-none d-sm-block">Upload Document Image1</span>
                                        <i class="mdi mdi-tray-arrow-up d-block d-sm-none"></i>
                                        <input type="file" id="image1" onChange="mainImgUrl1(this)" class="account-file-input upload" hidden accept="image/png, image/jpeg, application/pdf" />
                                    </label>
                                    <div class="small">Allowed JPG, JPEG, PNG or PDF. Max size of 2MB</div>
                                </div>
                            </div>
                            <span class="error-message text-danger" id="image1-error"></span>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-start align-items-sm-center gap-4">
                                <img src="{{ asset('/upload/no_image.jpg') }}" alt="user-avatar" class="d-block w-px-120 h-px-120 rounded" id="docImg2" />
                                <div class="button-wrapper">
                                    <label for="image2" class="btn btn-primary me-2 mb-3" tabindex="0">
                                        <span class="d-none d-sm-block">Upload Document Image2</span>
                                        <i class="mdi mdi-tray-arrow-up d-block d-sm-none"></i>
                                        <input type="file" id="image2" onChange="mainImgUrl2(this)" class="account-file-input" hidden accept="image/png, image/jpeg, application/pdf" />
                                    </label>
                                    <div class="small">Allowed JPG, JPEG, PNG or PDF. Max size of 2MB</div>
                                </div>
                            </div>
                            <span class="error-message text-danger" id="image2-error"></span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button onclick="onDocumentUpdate(event)" class="btn btn-primary me-2">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    // Inline placeholder shown for PDF documents (a PDF can't render inside an <img>)
    const PDF_ICON = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" rx="8" fill="#f8d7da"/><text x="60" y="70" font-size="26" text-anchor="middle" fill="#b02a37" font-family="Arial" font-weight="bold">PDF</text></svg>'
    );

    function isPdfName(name) {
        return typeof name === 'string' && name.toLowerCase().endsWith('.pdf');
    }

    function previewDocument(input, imgId) {
        if (!(input.files && input.files[0])) return;
        const file = input.files[0];
        const img  = document.getElementById(imgId);
        if (!img) return;

        if (file.type === 'application/pdf' || isPdfName(file.name)) {
            img.src = PDF_ICON; // show a PDF badge instead of a broken image
            return;
        }
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; };
        reader.readAsDataURL(file);
    }

    function mainImgUrl1(input) { previewDocument(input, 'docImg1'); }
    function mainImgUrl2(input) { previewDocument(input, 'docImg2'); }
</script>

<script>
// Track whether documents already exist so updates don't force a re-upload
let existingDoc1 = false;
let existingDoc2 = false;

document.addEventListener("DOMContentLoaded", async function () {
    await DocDetailsInfo();
});

// ===== Load client profile + populate the form =====
async function DocDetailsInfo() {
    showLoader();
    try {
        const response = await axios.get('/restaurant/profile/info');

        if (response.status === 200) {
            const userData = response.data.data;

            document.getElementById('firstName').value = userData.firstName ?? '';
            document.getElementById('lastName').value  = userData.lastName ?? '';
            document.getElementById('mobile').value     = userData.mobile ?? '';
            document.getElementById('email').value      = userData.email ?? '';
            document.getElementById('address1').value   = userData.address1 ?? '';
            document.getElementById('address2').value   = userData.address2 ?? '';
            document.getElementById('zip-code').value   = userData.zip_code ?? '';

            existingDoc1 = !!userData.doc_image1;
            existingDoc2 = !!userData.doc_image2;

            document.getElementById('docImg1').src = userData['doc_image1']
                ? (isPdfName(userData['doc_image1']) ? PDF_ICON : "/upload/client-document/small/" + userData['doc_image1'])
                : "/upload/no_image.jpg";
            document.getElementById('docImg2').src = userData['doc_image2']
                ? (isPdfName(userData['doc_image2']) ? PDF_ICON : "/upload/client-document/small/" + userData['doc_image2'])
                : "/upload/no_image.jpg";

            // Country → County → City cascade with pre-selection (global helper in config.js)
            await initLocationCascade({
                countryId: 'countrySelect',
                countyId:  'countySelect',
                cityId:    'citySelect',
                country:   userData.country_id || '',
                county:    userData.county_id  || '',
                city:      userData.city_id    || '',
            });
        }
    } catch (error) {
        handleError(error); // global (config.js)
    } finally {
        hideLoader();
    }
}

// ===== Submit document update =====
async function onDocumentUpdate(event) {
    event.preventDefault();

    const firstName  = document.getElementById('firstName').value;
    const lastName   = document.getElementById('lastName').value;
    const mobile     = document.getElementById('mobile').value;
    const address1   = document.getElementById('address1').value;
    const address2   = document.getElementById('address2').value;
    const zip_code   = document.getElementById('zip-code').value;
    const country_id = document.getElementById('countrySelect').value;
    const county_id  = document.getElementById('countySelect').value;
    const city_id    = document.getElementById('citySelect').value;

    const doc_image1 = document.getElementById('image1').files[0];
    const doc_image2 = document.getElementById('image2').files[0];

    // Clear previous inline errors
    [
        'firstName', 'lastName', 'mobile', 'address1', 'address2',
        'country', 'county', 'city', 'zip_code', 'image1', 'image2',
    ].forEach(k => document.getElementById(`${k}-error`).innerText = '');

    // Inline validation (document images required only when none exist yet)
    if (firstName.length === 0) {
        document.getElementById('firstName-error').innerText = 'First name is required';
    } else if (lastName.length === 0) {
        document.getElementById('lastName-error').innerText = 'Last name is required';
    } else if (mobile.length === 0) {
        document.getElementById('mobile-error').innerText = 'Mobile is required';
    } else if (address1.length === 0) {
        document.getElementById('address1-error').innerText = 'Address1 is required';
    } else if (country_id.length === 0) {
        document.getElementById('country-error').innerText = 'Country is required';
    } else if (county_id.length === 0) {
        document.getElementById('county-error').innerText = 'County is required';
    } else if (city_id.length === 0) {
        document.getElementById('city-error').innerText = 'City is required';
    } else if (zip_code.length === 0) {
        document.getElementById('zip_code-error').innerText = 'Zip code is required';
    } else if (!doc_image1 && !existingDoc1) {
        document.getElementById('image1-error').innerText = 'Document image1 is required!.';
    } else if (!doc_image2 && !existingDoc2) {
        document.getElementById('image2-error').innerText = 'Document image2 is required!.';
    } else {
        const formData = new FormData();
        formData.append('firstName', firstName);
        formData.append('lastName', lastName);
        formData.append('mobile', mobile);
        formData.append('address1', address1);
        formData.append('address2', address2);
        formData.append('country_id', country_id);
        formData.append('county_id', county_id);
        formData.append('city_id', city_id);
        formData.append('zip_code', zip_code);
        // Only send a file when a new one is selected — otherwise the existing document is kept
        if (doc_image1) formData.append('doc_image1', doc_image1);
        if (doc_image2) formData.append('doc_image2', doc_image2);

        try {
            showLoader();
            const res = await axios.post("/restaurant/store/document/info", formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            if (res.data.status === 'success') {
                successToast(res.data.message || 'Document updated successfully'); // green (config.js)
                if (doc_image1) existingDoc1 = true;
                if (doc_image2) existingDoc2 = true;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                errorToast(res.data.message || 'An unexpected error occurred');
            }
        } catch (error) {
            // Map 422 validation errors to the inline fields, otherwise use the global handler
            if (error.response?.status === 422) {
                const errors = error.response.data.errors || {};
                const aliases = { doc_image1: 'image1', doc_image2: 'image2' }; // field → error-span id
                Object.keys(errors).forEach(key => {
                    const el = document.getElementById(`${aliases[key] || key}-error`);
                    if (el) el.innerText = errors[key][0];
                    else errorToast(errors[key][0]); // don't let an error be swallowed
                });
            } else {
                handleError(error); // global (config.js)
            }
        } finally {
            hideLoader();
        }
    }
}
</script>
