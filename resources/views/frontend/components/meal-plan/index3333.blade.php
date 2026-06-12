@extends('frontend.components.dashboard.dashboard-master')

@section('dashboard-content')

<div class="container-fluid px-lg-4">

    {{-- ===== Smart Meal Planner Banner ===== --}}
    <div class="card border-0 shadow-sm mb-4 ai-planner-card">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="ai-icon-circle"><i class="mdi mdi-clipboard-pulse-outline"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1">Plan Next Week Smartly</h5>
                        <p class="text-muted mb-0">Get personalised meal suggestions based on your profile and past orders.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-gradient rounded-pill px-4" id="open-planner-btn">
                    <i class="mdi mdi-creation me-1"></i> <span id="planner-btn-label">Generate My Plan</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== Suggested Weekly Plan (shown when profile exists) ===== --}}
    <div class="card border-0 shadow-sm mb-4" id="suggestion-section" style="display:none;">
        <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold mb-0"><i class="mdi mdi-calendar-star me-2 text-primary"></i>Your Next Week Suggested Meal Plan</h5>
            <span class="badge bg-light text-dark" id="suggestion-period-badge"></span>
        </div>
        <div class="card-body">
            <div class="alert alert-info border-0 d-flex align-items-start gap-2" id="suggestion-summary"></div>
            <div id="suggestion-analysis" class="row g-2 mb-4"></div>
            <div id="weekly-plan" class="accordion"></div>
        </div>
    </div>

    {{-- ===== Search & Filter Card ===== --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h5 class="fw-bold mb-0"><i class="mdi mdi-food-fork-drink me-2 text-primary"></i>Browse Meals</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('meal.order') }}" class="btn btn-outline-primary rounded-pill">
                        <span class="mdi mdi-format-list-bulleted me-1"></span>Order List
                    </a>
                    <button type="button" class="btn btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#creditLimitModal">
                        <span class="mdi mdi-cash-plus me-1"></span>Add Credit
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-sm-6 col-md-3">
                    <label for="meal-date" class="form-label fw-semibold small text-muted"><i class="mdi mdi-calendar me-1"></i>Select Date</label>
                    <input type="date" id="meal-date" class="form-control">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label for="meal-time" class="form-label fw-semibold small text-muted"><i class="mdi mdi-clock-outline me-1"></i>Select Time</label>
                    <input type="time" id="meal-time" class="form-control" value="12:00">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-muted"><i class="mdi mdi-silverware me-1"></i>Meal Type</label>
                    <div id="meal-type-buttons" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>

            {{-- Keyword filter + editable search --}}
            <div id="keyword-section" class="bg-light rounded-3 p-3 mb-4" style="display:none;">
                <h6 class="fw-bold mb-2"><i class="mdi mdi-tag-multiple-outline me-1"></i>Filter by Keywords</h6>
                <div id="keyword-checkboxes" class="d-flex flex-wrap gap-3 mb-3"></div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" id="keyword-input" class="form-control" style="max-width:400px;"
                           placeholder="Type a food name or pick keywords...">
                    <button id="search-btn" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="mdi mdi-magnify me-1"></i>Search
                    </button>
                </div>
                <small class="text-muted d-block mt-1">Tip: you can type a food name (e.g. "egg roll") or tick keywords — or both.</small>
                <small id="keyword-error" class="text-danger mt-1 d-block" style="display:none;"></small>
            </div>

            <div class="row gy-4 mb-4" id="product-list"></div>

            <nav class="d-flex justify-content-center">
                <ul class="pagination mb-0"></ul>
            </nav>
        </div>
    </div>
</div>

{{-- ===== Smart Planner Modal (form only) ===== --}}
<div class="modal fade" id="aiPlannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-semibold"><i class="mdi mdi-clipboard-pulse-outline me-2"></i>Smart Meal Planner</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="ai-form-section">
                    <p class="text-muted mb-4">Tell us about yourself and we'll suggest meals for next week based on your health profile and ordering history.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gender</label>
                            <select id="ai-gender" class="form-select">
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Age (years)</label>
                            <input type="number" id="ai-age" class="form-control" min="1" max="120" placeholder="e.g. 30">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Weight (kg)</label>
                            <input type="number" id="ai-weight" class="form-control" min="1" max="500" placeholder="e.g. 70">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Height (cm)</label>
                            <input type="number" id="ai-height" class="form-control" min="30" max="300" placeholder="e.g. 175">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Compare Against</label>
                            <select id="ai-period" class="form-select">
                                <option value="last_week">Last Week's Orders</option>
                                <option value="last_month">Last Month's Orders</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Describe Your Health Goals</label>
                            <textarea id="ai-description" class="form-control" rows="3"
                                      placeholder="e.g. I want to lose weight, I'm vegetarian, I exercise 3x a week..."></textarea>
                        </div>
                    </div>
                    <div class="alert alert-light border mt-3 mb-0 py-2 px-3 small text-muted">
                        <i class="mdi mdi-information-outline me-1"></i>
                        Suggestions are general guidance, not medical advice. Consult a professional for health conditions.
                    </div>
                    <small id="ai-error" class="text-danger mt-2 d-block" style="display:none;"></small>
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="ai-generate-btn" class="btn btn-gradient rounded-pill px-4">
                            <i class="mdi mdi-creation me-1"></i>Generate Plan
                        </button>
                    </div>
                </div>

                <div id="ai-loading-section" class="text-center py-5" style="display:none;">
                    <div class="spinner-grow text-primary" role="status"></div>
                    <div class="spinner-grow text-primary mx-2" role="status"></div>
                    <div class="spinner-grow text-primary" role="status"></div>
                    <p class="text-muted mt-3 mb-0">Analysing your orders and building suggestions...</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Add to Meal Plan Modal ===== --}}
<div class="modal fade" id="addMealPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold">Add to Meal Plan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="modal-product-image" src="{{ asset('upload/product/small/no_image.jpg') }}"
                         class="rounded-3 shadow-sm" style="width:160px;height:160px;object-fit:cover;">
                </div>
                <h6 id="modal-product-name" class="fw-bold text-center mb-2"></h6>
                <div id="modal-provided-by" class="text-center mb-3"></div>
                <div class="mb-3">
                    <label for="modal-meal-type" class="form-label fw-semibold">Meal Type</label>
                    <select id="modal-meal-type" class="form-select"><option value="">Select a meal type</option></select>
                </div>
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label for="modal-meal-date" class="form-label fw-semibold">Date</label>
                        <input type="date" id="modal-meal-date" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="modal-meal-time" class="form-label fw-semibold">Time</label>
                        <input type="time" id="modal-meal-time" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="meal-quantity" class="form-label fw-semibold">Quantity</label>
                    <input type="number" id="meal-quantity" class="form-control" value="1" min="1">
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-add-meal" class="btn btn-primary rounded-pill px-4">Add to Plan</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== View Nutrient Modal ===== --}}
<div class="modal fade" id="viewNutrientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-semibold">Product Nutrients</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="nutrient-list" class="row g-2"></div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.ai-planner-card { background: linear-gradient(135deg,#f5f7ff 0%,#eef2ff 100%); border-left:4px solid #6366f1 !important; }
.ai-icon-circle { width:54px;height:54px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:1.6rem;flex-shrink:0; }
.btn-gradient { background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none; }
.btn-gradient:hover { opacity:.92;color:#fff; }
.bg-gradient-primary { background:linear-gradient(135deg,#6366f1,#8b5cf6); }
#meal-type-buttons button { transition:all .25s ease; }
#meal-type-buttons button:hover { transform:translateY(-2px); }
#meal-type-buttons button.active { background-color:#0d6efd;color:#fff;box-shadow:0 4px 12px rgba(13,110,253,.35); }
.form-check-label { text-transform:capitalize; }
#product-list .card { transition:transform .2s ease,box-shadow .2s ease; }
#product-list .card:hover { transform:translateY(-4px);box-shadow:0 8px 24px rgba(0,0,0,.1) !important; }
.suggest-meal-card { transition:transform .2s ease; }
.suggest-meal-card:hover { transform:translateY(-3px); }
.meal-badge { width:30px;height:30px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0; }
</style>
@endpush

@push('scripts')
<script>
let userLatitude       = null;
let userLongitude      = null;
let selectedMealTypeId = null;
let selectedProduct    = null;
let allProducts        = [];
let customerMealTimes  = {};
let selectedMealTime   = null;
let lastSearchTerms    = [];
let suggestionProductMap = {};
const MEAL_DEFAULT_TIMES = { breakfast:'08:00', lunch:'12:00', snacks:'16:00', dinner:'19:00' };

document.addEventListener('DOMContentLoaded', async () => {
    try {
        showLoader();
        setDateRange();
        await loadMealTypes();
        await getUserLocation();

        const urlParams        = new URLSearchParams(window.location.search);
        const selectedTypeName = urlParams.get('meal_type');
        const selectedDate     = urlParams.get('meal_date');

        if (selectedTypeName) {
            const btn = Array.from(document.querySelectorAll('#meal-type-buttons button'))
                .find(b => b.textContent.trim().toLowerCase() === selectedTypeName.toLowerCase());
            if (btn) btn.click();
        }
        if (selectedDate) document.getElementById('meal-date').value = selectedDate;

        document.getElementById('meal-time').addEventListener('change', function () { selectedMealTime = this.value; });
        document.getElementById('meal-date').addEventListener('change', updateTimeForSelectedMealType);
        selectedMealTime = document.getElementById('meal-time').value;

        initAiPlanner();
        bindWeeklyPlanAddButtons();

        await checkHealthProfile();   // ← requirement 2

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

/* ===================== Requirement 2: profile check on load ===================== */
async function checkHealthProfile() {
    try {
        const res = await axios.get('/user/get/health-profile');
        if (res.data.status === 'success' && res.data.data.has_profile) {
            if (res.data.data.profile) prefillPlannerForm(res.data.data.profile);
            document.getElementById('planner-btn-label').textContent = 'Update My Plan';
            renderSuggestions(res.data.data);
        } else {
            // No profile yet → open the planner modal so the user fills it
            new bootstrap.Modal(document.getElementById('aiPlannerModal')).show();
        }
    } catch (error) {
        // If the check fails, fail open by showing the modal
        new bootstrap.Modal(document.getElementById('aiPlannerModal')).show();
    }
}

function prefillPlannerForm(p) {
    document.getElementById('ai-gender').value      = p.gender ?? '';
    document.getElementById('ai-age').value         = p.age ?? '';
    document.getElementById('ai-weight').value      = p.weight ?? '';
    document.getElementById('ai-height').value      = p.height ?? '';
    document.getElementById('ai-period').value      = p.period ?? 'last_week';
    document.getElementById('ai-description').value = p.description ?? '';
}

/* ===== Time helpers ===== */
async function updateTimeForSelectedMealType() {
    const date = document.getElementById('meal-date').value;
    if (!date || !selectedMealTypeId) return;
    const existingTime = customerMealTimes[`${date}_${selectedMealTypeId}`];
    if (existingTime) {
        document.getElementById('meal-time').value = existingTime;
        selectedMealTime = existingTime;
    } else {
        setDefaultTimeForMealType();
    }
}

function setDefaultTimeForMealType() {
    const activeBtn = document.querySelector('#meal-type-buttons button.active');
    if (!activeBtn) return;
    const name = activeBtn.textContent.trim().toLowerCase();
    const t = MEAL_DEFAULT_TIMES[name] || '12:00';
    document.getElementById('meal-time').value = t;
    selectedMealTime = t;
}

async function getUserLocation() {
    if (navigator.geolocation) {
        try {
            const pos = await new Promise((res, rej) => navigator.geolocation.getCurrentPosition(res, rej));
            userLatitude  = pos.coords.latitude;
            userLongitude = pos.coords.longitude;
        } catch (e) { /* optional */ }
    }
}

function setDateRange() {
    const dateInput = document.getElementById('meal-date');
    const today     = new Date();
    const tomorrow  = new Date(today); tomorrow.setDate(today.getDate() + 1);
    const maxDate   = new Date(today); maxDate.setDate(today.getDate() + 7);
    dateInput.min   = tomorrow.toISOString().split('T')[0];
    dateInput.max   = maxDate.toISOString().split('T')[0];
    dateInput.value = tomorrow.toISOString().split('T')[0];
}

/* ===== Meal types ===== */
async function loadMealTypes() {
    const container = document.getElementById('meal-type-buttons');
    container.innerHTML = `<div class="text-muted">Loading meal types...</div>`;
    try {
        const res = await axios.get('/get/meal-types');
        if (res.status === 200 && res.data.status === 'success' && res.data.data.length > 0) {
            container.innerHTML = '';
            res.data.data.forEach(mt => {
                const btn = document.createElement('button');
                btn.className = 'btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold text-capitalize shadow-sm';
                btn.textContent = mt.name;
                btn.dataset.mealTypeId = mt.id;
                btn.onclick = async (e) => await selectMealType(e, mt.id);
                container.appendChild(btn);
            });
        } else {
            container.innerHTML = `<div class="text-danger">No meal types found.</div>`;
        }
    } catch (error) {
        container.innerHTML = `<div class="text-danger">Failed to load meal types.</div>`;
    }
}

async function selectMealType(event, mealTypeId) {
    document.querySelectorAll('#meal-type-buttons button').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    selectedMealTypeId = mealTypeId;

    // reset search field when switching meal type
    document.getElementById('keyword-input').value = '';

    await updateTimeForSelectedMealType();
    await loadMealKeywords(mealTypeId);
}

async function loadMealKeywords(mealTypeId) {
    const section   = document.getElementById('keyword-section');
    const container = document.getElementById('keyword-checkboxes');
    section.style.display = 'block';
    container.innerHTML   = `<div class="text-muted">Loading keywords...</div>`;
    try {
        showLoader();
        const res = await axios.get(`/user/get/meal-keywords/${mealTypeId}`);
        if (res.status === 200 && res.data.status === 'success' && res.data.data.length > 0) {
            container.innerHTML = '';
            res.data.data.forEach(kw => {
                const div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = `
                    <input class="form-check-input keyword-checkbox" type="checkbox" value="${kw.name}" id="kw-${kw.id}">
                    <label class="form-check-label" for="kw-${kw.id}">${kw.name}</label>`;
                container.appendChild(div);
            });
            document.querySelectorAll('.keyword-checkbox').forEach(chk =>
                chk.addEventListener('change', handleKeywordSelection));
        } else {
            container.innerHTML = `<div class="text-danger">No keywords found for this meal type.</div>`;
        }
    } catch (error) {
        container.innerHTML = `<div class="text-danger">Failed to load keywords.</div>`;
        handleError(error);
    } finally {
        hideLoader();
    }
}

/* ===================== Requirement 3: editable search (name + keywords) ===================== */
function parseTerms(value) {
    return value.split(',').map(t => t.trim()).filter(Boolean);
}

function handleKeywordSelection() {
    const input        = document.getElementById('keyword-input');
    const allKeywords  = Array.from(document.querySelectorAll('.keyword-checkbox')).map(cb => cb.value.toLowerCase());
    const checked      = Array.from(document.querySelectorAll('.keyword-checkbox:checked')).map(cb => cb.value);

    // keep free-typed terms (anything not in the keyword list), then re-add checked keywords
    let terms = parseTerms(input.value).filter(t => !allKeywords.includes(t.toLowerCase()));
    checked.forEach(k => terms.push(k));

    input.value = terms.join(', ');
}

document.getElementById('search-btn').addEventListener('click', async () => {
    const err   = document.getElementById('keyword-error');
    const terms = parseTerms(document.getElementById('keyword-input').value);
    err.style.display = 'none';
    err.textContent   = '';

    if (!selectedMealTypeId) {
        err.textContent = 'Please select a meal type.';
        err.style.display = 'block';
        return;
    }
    if (terms.length === 0) {
        err.textContent = 'Please type a food name or select at least one keyword.';
        err.style.display = 'block';
        return;
    }

    lastSearchTerms = terms;
    await searchProducts(terms);
});

// allow pressing Enter in the search box
document.getElementById('keyword-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('search-btn').click(); }
});

async function searchProducts(keywords, page = 1) {
    const list = document.getElementById('product-list');
    list.innerHTML = `<div class="text-muted text-center py-3">Searching products...</div>`;
    try {
        showLoader();
        const res = await axios.post(`/user/search/products?page=${page}`, {
            keywords,
            meal_type_id: selectedMealTypeId,
            latitude:     userLatitude,
            longitude:    userLongitude
        });
        if (res.status === 200 && res.data.status === 'success' && res.data.products.data.length > 0) {
            renderProducts(res.data.products.data);
            updatePagination(res.data.products);
        } else {
            list.innerHTML = `<div class="text-danger text-center py-3">No products found.</div>`;
            document.querySelector('.pagination').innerHTML = '';
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function renderProducts(products) {
    allProducts = products;
    const list = document.getElementById('product-list');
    list.innerHTML = '';

    products.forEach((p, index) => {
        const mealTypesHTML = (p.meal_types && p.meal_types.length > 0)
            ? p.meal_types.map(mt => `<span class="badge rounded-pill bg-primary text-white me-1 mb-1">${mt.name}</span>`).join('')
            : '<span class="text-muted">N/A</span>';
        const category   = p.category?.name ?? 'N/A';
        const providedBy = (p.client_info && p.client_info.last_name)
            ? `<p class="text-muted small mb-2"><strong>Provided by:</strong> ${p.client_info.first_name} ${p.client_info.last_name}</p>` : '';
        const caloriesInfo = (p.nutrients && p.nutrients.calories)
            ? `<p class="text-success small mb-2"><strong>Calories:</strong> ${p.nutrients.calories} ${p.nutrients.calories_unit}</p>` : '';
        const productName = p.name.replace(/\b\w/g, l => l.toUpperCase());
        const hasNutrient = p.nutrients && Object.values(p.nutrients).some(v => v !== null && v !== '');

        list.insertAdjacentHTML('beforeend', `
            <div class="col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm border-0 rounded-3">
                    <a href="/user/meal/details/${p.id}">
                        <img src="{{ asset('upload/product/medium') }}/${p.image}" class="card-img-top rounded-top-3"
                             alt="${p.name}" style="height:200px;object-fit:cover;">
                    </a>
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="fw-bold mb-2">${productName}</h6>
                        ${caloriesInfo}
                        <p class="text-muted mb-1"><strong>Category:</strong> ${category}</p>
                        <div class="mb-2"><strong>Available for:</strong><br>${mealTypesHTML}</div>
                        ${providedBy}
                        <p class="fw-semibold text-primary mb-3">£${p.price}</p>
                        <div class="d-flex justify-content-center gap-2 mt-auto">
                            <button class="btn btn-outline-primary rounded-pill px-3" onclick="openAddMealModal(${index})">Add to Plan</button>
                            ${hasNutrient ? `<button class="btn btn-outline-success rounded-pill px-3" onclick="openNutrientModal(${index})">Nutrients</button>` : ''}
                        </div>
                    </div>
                </div>
            </div>`);
    });
}

function openNutrientModal(index) {
    const product = allProducts[index];
    const list = document.getElementById('nutrient-list');
    list.innerHTML = '';
    if (!product.nutrients) return;
    Object.keys(product.nutrients).forEach(key => {
        if (key.endsWith('_unit')) return;
        const value = product.nutrients[key];
        const unit  = product.nutrients[key + '_unit'] ?? '';
        if (value !== null && value !== '') {
            const div = document.createElement('div');
            div.className = 'col-6';
            div.innerHTML = `<strong>${formatNutrientName(key)}:</strong> ${value} ${unit}`;
            list.appendChild(div);
        }
    });
    new bootstrap.Modal(document.getElementById('viewNutrientModal')).show();
}

function formatNutrientName(key) { return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()); }

function openAddMealModal(index) {
    const product = allProducts[index];
    selectedProduct = product;

    document.getElementById('modal-product-name').textContent = product.name || 'N/A';
    document.getElementById('modal-provided-by').innerHTML = (product.client_info && product.client_info.last_name)
        ? `<p class="text-muted small mb-2"><strong>Provided by:</strong> ${product.client_info.first_name} ${product.client_info.last_name}</p>` : '';

    const sel = document.getElementById('modal-meal-type');
    sel.innerHTML = '<option value="">Select a meal type</option>';
    (product.meal_types || []).forEach(mt => {
        const selected = (mt.id === selectedMealTypeId) ? 'selected' : '';
        sel.insertAdjacentHTML('beforeend', `<option value="${mt.id}" ${selected}>${mt.name}</option>`);
    });

    setupModalDates(document.getElementById('meal-date').value);
    document.getElementById('modal-meal-time').value = document.getElementById('meal-time').value || '12:00';

    document.getElementById('modal-product-image').src = product.image
        ? `{{ asset('upload/product/small') }}/${product.image}`
        : `{{ asset('upload/product/small/no_image.jpg') }}`;

    new bootstrap.Modal(document.getElementById('addMealPlanModal')).show();
}

function setupModalDates(preferred) {
    const today = new Date();
    const tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1);
    const maxDate = new Date(today); maxDate.setDate(today.getDate() + 7);
    const min = tomorrow.toISOString().split('T')[0];
    const max = maxDate.toISOString().split('T')[0];
    const mDate = document.getElementById('modal-meal-date');
    mDate.min = min; mDate.max = max;
    let val = preferred || min;
    if (val < min) val = min;
    if (val > max) val = max;
    mDate.value = val;
}

document.getElementById('confirm-add-meal').addEventListener('click', async () => {
    const sel = document.getElementById('modal-meal-type');
    const mealTypeId = parseInt(sel.value);
    const selectedDate = document.getElementById('modal-meal-date').value;
    const selectedTime = document.getElementById('modal-meal-time').value;
    const quantity = parseInt(document.getElementById('meal-quantity').value);

    if (!selectedProduct)           return errorToast('No product selected.');
    if (!mealTypeId)                return errorToast('Please select a meal type.');
    if (!selectedDate)              return errorToast('Please choose a date.');
    if (!selectedTime)              return errorToast('Please choose a time.');
    if (!quantity || quantity <= 0) return errorToast('Invalid quantity.');

    const key = `${selectedDate}_${mealTypeId}`;
    const existingTime = customerMealTimes[key];
    if (existingTime && existingTime !== selectedTime) {
        const name = sel.options[sel.selectedIndex]?.text || 'this meal type';
        return errorToast(`For ${selectedDate}, all ${name} items must have the same time (${existingTime}).`);
    }

    try {
        showLoader();
        const res = await axios.post('/user/store/meal-cart', {
            product_id:   selectedProduct.id,
            meal_type_id: mealTypeId,
            meal_date:    selectedDate,
            meal_time:    selectedTime,
            quantity:     quantity
        });
        if (res.status === 201 && res.data.status === 'success') {
            successToast(res.data.message || 'Meal added successfully!');
            customerMealTimes[key] = selectedTime;
            bootstrap.Modal.getInstance(document.getElementById('addMealPlanModal')).hide();
            await updateMealCartCount();
        } else {
            errorToast(res.data.message || 'Failed to add to meal plan.');
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

function updatePagination(paginationData) {
    const container = document.querySelector('.pagination');
    container.innerHTML = '';
    const { current_page, last_page } = paginationData;
    const createItem = (label, page, disabled = false, active = false) => {
        const li = document.createElement('li');
        li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link'; a.href = '#'; a.textContent = label;
        if (!disabled && !active) {
            a.addEventListener('click', e => { e.preventDefault(); searchProducts(lastSearchTerms, page); });
        }
        li.appendChild(a);
        return li;
    };
    container.appendChild(createItem('« Prev', current_page - 1, current_page === 1));
    for (let i = 1; i <= last_page; i++) container.appendChild(createItem(i, i, false, i === current_page));
    container.appendChild(createItem('Next »', current_page + 1, current_page === last_page));
}

/* ===================== SMART MEAL PLANNER ===================== */
function initAiPlanner() {
    document.getElementById('ai-generate-btn').addEventListener('click', generateAiPlan);
    document.getElementById('open-planner-btn').addEventListener('click', () => {
        document.getElementById('ai-form-section').style.display    = 'block';
        document.getElementById('ai-loading-section').style.display = 'none';
        new bootstrap.Modal(document.getElementById('aiPlannerModal')).show();
    });
}

async function generateAiPlan() {
    const gender      = document.getElementById('ai-gender').value;
    const age         = document.getElementById('ai-age').value;
    const weight      = document.getElementById('ai-weight').value;
    const height      = document.getElementById('ai-height').value;
    const period      = document.getElementById('ai-period').value;
    const description = document.getElementById('ai-description').value;
    const err         = document.getElementById('ai-error');

    err.style.display = 'none'; err.textContent = '';
    if (!gender || !age || !weight || !height) {
        err.textContent = 'Please fill in gender, age, weight, and height.';
        err.style.display = 'block';
        return;
    }

    document.getElementById('ai-form-section').style.display    = 'none';
    document.getElementById('ai-loading-section').style.display = 'block';

    try {
        const res = await axios.post('/user/generate/meal-suggestion', { gender, age, weight, height, period, description });
        if (res.data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('aiPlannerModal'))?.hide();
            document.getElementById('planner-btn-label').textContent = 'Update My Plan';
            renderSuggestions(res.data.data);
            document.getElementById('suggestion-section').scrollIntoView({ behavior: 'smooth' });
        } else {
            err.textContent = res.data.message || 'Failed to generate suggestions.';
            err.style.display = 'block';
            document.getElementById('ai-form-section').style.display = 'block';
            document.getElementById('ai-loading-section').style.display = 'none';
        }
    } catch (error) {
        const msg = error.response?.data?.message || error.response?.data?.errors || 'Failed to generate suggestions.';
        err.textContent = typeof msg === 'object' ? Object.values(msg).flat().join(' ') : msg;
        err.style.display = 'block';
        document.getElementById('ai-form-section').style.display = 'block';
        document.getElementById('ai-loading-section').style.display = 'none';
    }
}

/* ===== Render the inline weekly suggestion plan (by date & meal type) ===== */
function renderSuggestions(data) {
    suggestionProductMap = {};

    document.getElementById('suggestion-period-badge').textContent =
        data.period === 'last_month' ? 'Based on last month' : 'Based on last week';

    document.getElementById('suggestion-summary').innerHTML = `
        <i class="mdi mdi-account-heart-outline fs-5"></i>
        <div>
            <strong>BMI: ${data.bmi} (${data.bmi_category})</strong> &middot;
            Daily target <strong>${data.target_calories} kcal</strong>.
            Below is a suggested plan for the next 7 days, by date and meal type.
        </div>`;

    // Analysis cards
    const a = data.analysis || {};
    const analysisEl = document.getElementById('suggestion-analysis');
    if (data.has_history) {
        const cards = [
            { label: 'Avg Daily Calories', value: `${a.avg_daily_calories} kcal`, icon: 'mdi-fire',          color: 'danger'  },
            { label: 'Avg Protein',        value: `${a.avg_protein} g`,           icon: 'mdi-food-drumstick', color: 'primary' },
            { label: 'Avg Carbs',          value: `${a.avg_carbohydrates} g`,     icon: 'mdi-barley',         color: 'warning' },
            { label: 'Avg Fat',            value: `${a.avg_fat} g`,               icon: 'mdi-oil',            color: 'info'    },
        ];
        analysisEl.innerHTML = cards.map(c => `
            <div class="col-6 col-md-3">
                <div class="border rounded-3 p-2 text-center h-100">
                    <i class="mdi ${c.icon} text-${c.color} fs-5"></i>
                    <div class="fw-bold">${c.value}</div>
                    <small class="text-muted">${c.label}</small>
                </div>
            </div>`).join('');
    } else {
        analysisEl.innerHTML = `<div class="col-12"><div class="alert alert-warning border-0 mb-0">
            No past order history for this period — suggestions are based on your calorie target only.</div></div>`;
    }

    // Weekly plan accordion (by date)
    const mealIcons = {
        Breakfast: { icon: 'mdi-coffee-outline',        bg: 'bg-warning' },
        Lunch:     { icon: 'mdi-food-outline',          bg: 'bg-primary' },
        Snacks:    { icon: 'mdi-food-apple-outline',    bg: 'bg-success' },
        Dinner:    { icon: 'mdi-silverware-fork-knife', bg: 'bg-danger'  },
    };

    const container = document.getElementById('weekly-plan');
    container.innerHTML = '';

    (data.weekly_plan || []).forEach((day, idx) => {
        let mealsHtml = '';
        day.meals.forEach(meal => {
            const cfg = mealIcons[meal.meal_type] || { icon: 'mdi-food', bg: 'bg-secondary' };
            let body;

            if (meal.product) {
                suggestionProductMap[meal.product.id] = meal.product;
                const p = meal.product;
                const img = p.image ? `/upload/product/small/${p.image}` : '/upload/no_image.jpg';
                const name = (p.name || '').replace(/\b\w/g, l => l.toUpperCase());
                body = `
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="${img}" class="rounded" style="width:54px;height:54px;object-fit:cover;">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">${name}</div>
                            <small class="text-success">${p.calories} ${p.calories_unit}</small>
                            <div class="fw-bold text-primary small">£${p.price}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="/user/meal/details/${p.id}" class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1">View</a>
                        <button class="btn btn-sm btn-outline-success rounded-pill flex-grow-1 suggest-add-btn"
                                data-product-id="${p.id}" data-date="${day.date}"
                                data-meal-type-id="${meal.meal_type_id}" data-meal-type-name="${meal.meal_type}">Add</button>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill flex-grow-1 suggest-similar-btn"
                                data-name="${p.name}" data-meal-type-id="${meal.meal_type_id}">Similar</button>
                    </div>`;
            } else {
                body = `<div class="text-muted small text-center py-3">No matching product.</div>`;
            }

            mealsHtml += `
                <div class="col-md-3 col-sm-6">
                    <div class="card border h-100 suggest-meal-card">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="meal-badge ${cfg.bg} text-white"><i class="mdi ${cfg.icon}"></i></span>
                                <div>
                                    <div class="fw-semibold">${meal.meal_type}</div>
                                    <small class="text-muted">~${meal.calorie_target} kcal</small>
                                </div>
                            </div>
                            ${body}
                        </div>
                    </div>
                </div>`;
        });

        container.insertAdjacentHTML('beforeend', `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button ${idx !== 0 ? 'collapsed' : ''}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#planDay${idx}">
                        <i class="mdi mdi-calendar-outline me-2 text-primary"></i>
                        <span class="fw-semibold">${day.day_label}</span>
                    </button>
                </h2>
                <div id="planDay${idx}" class="accordion-collapse collapse ${idx === 0 ? 'show' : ''}"
                     data-bs-parent="#weekly-plan">
                    <div class="accordion-body">
                        <div class="row g-3">${mealsHtml}</div>
                    </div>
                </div>
            </div>`);
    });

    document.getElementById('suggestion-section').style.display = 'block';
}

async function findSimilarFood(foodName, mealTypeId) {
    // Activate the matching meal-type button so keywords + time defaults sync
    const btn = Array.from(document.querySelectorAll('#meal-type-buttons button'))
        .find(b => b.dataset.mealTypeId == mealTypeId);

    if (btn && selectedMealTypeId !== mealTypeId) {
        await selectMealType({ target: btn }, mealTypeId);
    } else if (!selectedMealTypeId) {
        selectedMealTypeId = mealTypeId;
    }

    // Put the food name into the (now editable) search box and search by name
    document.getElementById('keyword-input').value = foodName;
    lastSearchTerms = [foodName];
    await searchProducts([foodName]);

    // Bring the browse/results section into view
    document.getElementById('product-list').scrollIntoView({ behavior: 'smooth' });
}

/* ===== Add a suggested product to the plan ===== */
function bindWeeklyPlanAddButtons() {
    const wp = document.getElementById('weekly-plan');

    wp.addEventListener('click', (e) => {
        const addBtn = e.target.closest('.suggest-add-btn');
        if (addBtn) {
            openSuggestionAdd(
                addBtn.dataset.date,
                parseInt(addBtn.dataset.mealTypeId),
                addBtn.dataset.mealTypeName,
                parseInt(addBtn.dataset.productId)
            );
            return;
        }

        const similarBtn = e.target.closest('.suggest-similar-btn');
        if (similarBtn) {
            findSimilarFood(similarBtn.dataset.name, parseInt(similarBtn.dataset.mealTypeId));
        }
    });
}

function openSuggestionAdd(date, mealTypeId, mealTypeName, productId) {
    const product = suggestionProductMap[productId];
    if (!product) return;

    selectedProduct = product;

    document.getElementById('modal-product-name').textContent = (product.name || '').replace(/\b\w/g, l => l.toUpperCase());
    document.getElementById('modal-provided-by').innerHTML = product.client_name
        ? `<p class="text-muted small mb-2"><strong>Provided by:</strong> ${product.client_name}</p>` : '';

    const sel = document.getElementById('modal-meal-type');
    sel.innerHTML = `<option value="${mealTypeId}" selected>${mealTypeName}</option>`;

    setupModalDates(date);
    document.getElementById('modal-meal-time').value = MEAL_DEFAULT_TIMES[mealTypeName.toLowerCase()] || '12:00';

    document.getElementById('modal-product-image').src = product.image
        ? `{{ asset('upload/product/small') }}/${product.image}`
        : `{{ asset('upload/product/small/no_image.jpg') }}`;

    new bootstrap.Modal(document.getElementById('addMealPlanModal')).show();
}
</script>
@endpush