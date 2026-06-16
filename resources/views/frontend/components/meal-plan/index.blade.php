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
                <div class="d-flex flex-wrap gap-2">
                    {{-- Shown only when a health profile already exists --}}
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="update-health-btn" style="display:none;">
                        <i class="mdi mdi-account-heart-outline me-1"></i> Update Health Info
                    </button>
                    <button type="button" class="btn btn-gradient rounded-pill px-4" id="open-planner-btn">
                        <i class="mdi mdi-creation me-1"></i> <span id="planner-btn-label">Generate My Plan</span>
                    </button>
                </div>
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
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <h6 class="fw-bold mb-0"><i class="mdi mdi-tag-multiple-outline me-1"></i>Filter by Keywords</h6>
                    {{-- Select All toggle --}}
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="keyword-select-all">
                        <label class="form-check-label fw-semibold small" for="keyword-select-all">Select All</label>
                    </div>
                </div>
                <div id="keyword-checkboxes" class="d-flex flex-wrap gap-3 mb-3"></div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" id="keyword-input" class="form-control" style="max-width:400px;"
                           placeholder="Type a food name or pick keywords...">
                    <button id="search-btn" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="mdi mdi-magnify me-1"></i>Search
                    </button>
                </div>
                <small class="text-muted d-block mt-1">Tip: type a food name (e.g. "egg roll"), pick keywords, or use Select All — then search.</small>
                <small id="keyword-error" class="text-danger mt-1 d-block" style="display:none;"></small>
            </div>

            {{-- Search results --}}
            <div id="results-heading" class="d-flex align-items-center gap-2 mb-3" style="display:none !important;">
                <h6 class="fw-bold mb-0"><i class="mdi mdi-silverware-variant me-1 text-primary"></i>Matching Meals</h6>
                <span class="badge bg-light text-dark" id="results-count"></span>
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
                <h5 class="modal-title fw-semibold"><i class="mdi mdi-clipboard-pulse-outline me-2"></i><span id="aiPlannerTitleText">Smart Meal Planner</span></h5>
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
                            <label class="form-label fw-semibold">Activity Level</label>
                            <select id="ai-activity" class="form-select">
                                @foreach(\App\Models\UserHealthProfile::ACTIVITY_LEVELS as $val => $label)
                                    <option value="{{ $val }}" {{ $val === 'light' ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Goal</label>
                            <select id="ai-goal" class="form-select">
                                <option value="">Auto (based on BMI)</option>
                                @foreach(\App\Models\UserHealthProfile::GOALS as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dietary Preference</label>
                            <select id="ai-diet" class="form-select">
                                @foreach(\App\Models\UserHealthProfile::DIETS as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Compare Against</label>
                            <select id="ai-period" class="form-select">
                                <option value="last_week">Last Week's Orders</option>
                                <option value="last_month">Last Month's Orders</option>
                            </select>
                        </div>

                        {{-- Chronic conditions — multi-select with Select All --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">Do you have any of these conditions?</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="conditions-select-all">
                                    <label class="form-check-label small fw-semibold" for="conditions-select-all">Select All</label>
                                </div>
                            </div>
                            <div class="row g-2 bg-light rounded-3 p-3" id="conditions-list">
                                @foreach(\App\Models\UserHealthProfile::CONDITIONS as $val => $label)
                                    <div class="col-sm-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input condition-checkbox" type="checkbox"
                                                   value="{{ $val }}" id="cond-{{ $val }}">
                                            <label class="form-check-label" for="cond-{{ $val }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-1">Optional — this only helps us flag relevant considerations, not give medical advice.</small>
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
                            <i class="mdi mdi-creation me-1"></i><span id="ai-generate-btn-label">Generate Plan</span>
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
.suggest-meal-card { transition:transform .2s ease, box-shadow .2s ease; }
.suggest-meal-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,.08) !important; }
.meal-badge { width:30px;height:30px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0; }

/* ===== Redesigned search result cards ===== */
.product-result-card { transition:transform .2s ease, box-shadow .2s ease; }
.product-result-card:hover { transform:translateY(-5px); box-shadow:0 12px 30px rgba(0,0,0,.12) !important; }
.product-result-card .result-img { height:185px; width:100%; object-fit:cover; }
.cal-pill {
    position:absolute; bottom:10px; left:10px;
    background:rgba(25,135,84,.94); color:#fff; font-size:.72rem; font-weight:600;
    padding:.28rem .65rem; border-radius:999px;
}
.nutrient-fab {
    position:absolute; top:10px; right:10px; width:36px; height:36px; border-radius:50%;
    background:rgba(255,255,255,.92); border:none; color:#198754;
    display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,.18);
    transition:all .2s ease;
}
.nutrient-fab:hover { background:#198754; color:#fff; transform:scale(1.08); }
.result-title { color:#1c2434; }
.result-title:hover { color:#0d6efd; }
.result-meal-badge { font-size:.68rem; }
.condition-badge { font-size:.7rem; }
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

// Condition value → label (mirror of UserHealthProfile::CONDITIONS for display)
const CONDITION_LABELS = {
    diabetes:'Diabetes', hypertension:'High blood pressure', high_cholesterol:'High cholesterol',
    heart_disease:'Heart disease', kidney_disease:'Kidney disease', thyroid:'Thyroid disorder',
    celiac:'Celiac / gluten intolerance', lactose:'Lactose intolerance', ibs:'IBS / digestive issues',
    anemia:'Anaemia', obesity:'Obesity', pcos:'PCOS'
};

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

        // Keyword Select All toggle (static element — bind once)
        document.getElementById('keyword-select-all').addEventListener('change', function () {
            toggleSelectAllKeywords(this.checked);
        });

        // Conditions Select All toggle + two-way sync
        document.getElementById('conditions-select-all').addEventListener('change', function () {
            document.querySelectorAll('.condition-checkbox').forEach(cb => { cb.checked = this.checked; });
        });
        document.getElementById('conditions-list').addEventListener('change', (e) => {
            if (!e.target.classList.contains('condition-checkbox')) return;
            const boxes = Array.from(document.querySelectorAll('.condition-checkbox'));
            document.getElementById('conditions-select-all').checked = boxes.length > 0 && boxes.every(cb => cb.checked);
        });

        initAiPlanner();
        bindWeeklyPlanButtons();

        await checkHealthProfile();

    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

/* ===================== Profile check on load ===================== */
async function checkHealthProfile() {
    try {
        const res = await axios.get('/get/health-profile');
        if (res.data.status === 'success' && res.data.data.has_profile) {
            if (res.data.data.profile) prefillPlannerForm(res.data.data.profile);
            document.getElementById('planner-btn-label').textContent = 'Update My Plan';
            // profile exists → reveal the Update Health Info button
            document.getElementById('update-health-btn').style.display = 'inline-block';
            renderSuggestions(res.data.data);
        } else {
            // no profile → show order-based suggestions if any, then open "Update Health Info"
            document.getElementById('update-health-btn').style.display = 'none';
            const d = res.data.data || {};
            if (d.has_history && (d.weekly_plan || []).length) {
                renderSuggestions(d);
            }
            openPlanner('health');
        }
    } catch (error) {
        document.getElementById('update-health-btn').style.display = 'none';
        openPlanner('health');
    }
}

function prefillPlannerForm(p) {
    document.getElementById('ai-gender').value      = p.gender ?? '';
    document.getElementById('ai-age').value         = p.age ?? '';
    document.getElementById('ai-weight').value      = p.weight ?? '';
    document.getElementById('ai-height').value      = p.height ?? '';
    document.getElementById('ai-period').value      = p.period ?? 'last_week';
    document.getElementById('ai-description').value = p.description ?? '';
    document.getElementById('ai-activity').value    = p.activity_level ?? 'light';
    document.getElementById('ai-goal').value        = p.goal ?? '';
    document.getElementById('ai-diet').value        = p.dietary_preference ?? 'none';

    const saved = p.conditions || [];
    document.querySelectorAll('.condition-checkbox').forEach(cb => { cb.checked = saved.includes(cb.value); });
    const boxes = Array.from(document.querySelectorAll('.condition-checkbox'));
    document.getElementById('conditions-select-all').checked = boxes.length > 0 && boxes.every(cb => cb.checked);
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

    // reset Select All when keywords reload
    document.getElementById('keyword-select-all').checked = false;

    try {
        showLoader();
        const res = await axios.get(`/get/meal-keywords/${mealTypeId}`);
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

/* ===================== Keyword + Select All handling ===================== */
function parseTerms(value) {
    return value.split(',').map(t => t.trim()).filter(Boolean);
}

function syncKeywordInput() {
    const input        = document.getElementById('keyword-input');
    const allKeywords  = Array.from(document.querySelectorAll('.keyword-checkbox')).map(cb => cb.value.toLowerCase());
    const checked      = Array.from(document.querySelectorAll('.keyword-checkbox:checked')).map(cb => cb.value);

    // keep free-typed terms (anything not in the keyword list), then re-add checked keywords
    let terms = parseTerms(input.value).filter(t => !allKeywords.includes(t.toLowerCase()));
    checked.forEach(k => terms.push(k));
    input.value = terms.join(', ');

    updateSelectAllState();
}

function updateSelectAllState() {
    const boxes     = Array.from(document.querySelectorAll('.keyword-checkbox'));
    const selectAll = document.getElementById('keyword-select-all');
    if (!selectAll || boxes.length === 0) { if (selectAll) selectAll.checked = false; return; }
    selectAll.checked = boxes.every(cb => cb.checked);
}

function handleKeywordSelection() {
    syncKeywordInput();
}

// Select All switch → check/uncheck every keyword box
function toggleSelectAllKeywords(checked) {
    document.querySelectorAll('.keyword-checkbox').forEach(cb => { cb.checked = checked; });
    syncKeywordInput();
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

document.getElementById('keyword-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('search-btn').click(); }
});

async function searchProducts(keywords, page = 1) {
    const list = document.getElementById('product-list');
    list.innerHTML = `<div class="text-muted text-center py-3">Searching products...</div>`;
    try {
        showLoader();
        const res = await axios.post(`/search/products?page=${page}`, {
            keywords,
            meal_type_id: selectedMealTypeId,
            latitude:     userLatitude,
            longitude:    userLongitude
        });
        if (res.status === 200 && res.data.status === 'success' && res.data.products.data.length > 0) {
            renderProducts(res.data.products.data, res.data.total);
            updatePagination(res.data.products);
        } else {
            document.getElementById('results-heading').style.display = 'none';
            list.innerHTML = `
                <div class="col-12">
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-food-off-outline" style="font-size:2.5rem;"></i>
                        <p class="mb-0 mt-2">No meals found. Try different keywords or a food name.</p>
                    </div>
                </div>`;
            document.querySelector('.pagination').innerHTML = '';
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

/* ===================== attractive result cards ===================== */
function renderProducts(products, total) {
    allProducts = products;
    const list = document.getElementById('product-list');
    list.innerHTML = '';

    // results heading
    const heading = document.getElementById('results-heading');
    heading.style.display = 'flex';
    document.getElementById('results-count').textContent = `${total ?? products.length} found`;

    products.forEach((p, index) => {
        const mealTypesHTML = (p.meal_types && p.meal_types.length > 0)
            ? p.meal_types.map(mt => `<span class="badge rounded-pill bg-primary-subtle text-primary result-meal-badge me-1 mb-1 text-capitalize">${mt.name}</span>`).join('')
            : '';
        const providedBy = (p.client_info && p.client_info.last_name)
            ? `<small class="text-muted d-block mb-2"><i class="mdi mdi-storefront-outline me-1"></i>${`${p.client_info.first_name} ${p.client_info.last_name}`.toLowerCase().replace(/\b\w/g, l => l.toUpperCase())}</small>` : '';
        const calPill = (p.nutrients && p.nutrients.calories)
            ? `<span class="cal-pill"><i class="mdi mdi-fire"></i> ${p.nutrients.calories} ${p.nutrients.calories_unit}</span>` : '';
        const productName = p.name.replace(/\b\w/g, l => l.toUpperCase());
        const hasNutrient = p.nutrients && Object.values(p.nutrients).some(v => v !== null && v !== '');
        const img = p.image ? `{{ asset('upload/product/medium') }}/${p.image}` : `{{ asset('upload/product/medium/no_image.jpg') }}`;

        list.insertAdjacentHTML('beforeend', `
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden product-result-card">
                    <div class="position-relative">
                        <a href="/meal/details/${p.id}">
                            <img src="${img}" class="result-img" alt="${p.name}">
                        </a>
                        ${calPill}
                        ${hasNutrient ? `<button class="nutrient-fab" title="View nutrients" onclick="openNutrientModal(${index})"><i class="mdi mdi-nutrition"></i></button>` : ''}
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <a href="/meal/details/${p.id}" class="text-decoration-none">
                            <h6 class="fw-bold mb-1 result-title">${productName}</h6>
                        </a>
                        ${providedBy}
                        <div class="d-flex flex-wrap mb-2">${mealTypesHTML}</div>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                            <span class="fw-bold text-primary fs-6">£${p.price}</span>
                            <button class="btn btn-sm btn-gradient rounded-pill px-3" onclick="openAddMealModal(${index})">
                                <i class="mdi mdi-plus"></i> Add
                            </button>
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
        const res = await axios.post('/store/meal-cart', {
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
let plannerMode = 'plan'; // 'plan' = generate suggestions | 'health' = update profile only

// Opens the planner modal in the given mode (top-level so checkHealthProfile can call it)
function openPlanner(mode = 'plan') {
    plannerMode = mode;
    document.getElementById('aiPlannerTitleText').textContent =
        mode === 'health' ? 'Update Health Info' : 'Smart Meal Planner';
    document.getElementById('ai-generate-btn-label').textContent =
        mode === 'health' ? 'Update' : 'Generate Plan';

    document.getElementById('ai-form-section').style.display    = 'block';
    document.getElementById('ai-loading-section').style.display = 'none';
    new bootstrap.Modal(document.getElementById('aiPlannerModal')).show();
}

function initAiPlanner() {
    document.getElementById('ai-generate-btn').addEventListener('click', () => {
        if (plannerMode === 'health') updateHealthInfo();
        else                          generateAiPlan();
    });

    document.getElementById('open-planner-btn').addEventListener('click', () => openPlanner('plan'));
    // Update Health Info opens the same prefilled form, but only saves the profile
    document.getElementById('update-health-btn').addEventListener('click', () => openPlanner('health'));
}

/* Update Health Info — saves the UserHealthProfile only (no plan regeneration) */
async function updateHealthInfo() {
    const gender = document.getElementById('ai-gender').value;
    const age    = document.getElementById('ai-age').value;
    const weight = document.getElementById('ai-weight').value;
    const height = document.getElementById('ai-height').value;
    const err    = document.getElementById('ai-error');

    err.style.display = 'none'; err.textContent = '';
    if (!gender || !age || !weight || !height) {
        err.textContent = 'Please fill in gender, age, weight, and height.';
        err.style.display = 'block';
        return;
    }

    const payload = {
        gender, age, weight, height,
        period:             document.getElementById('ai-period').value,
        description:        document.getElementById('ai-description').value,
        activity_level:     document.getElementById('ai-activity').value,
        goal:               document.getElementById('ai-goal').value,
        dietary_preference: document.getElementById('ai-diet').value,
        conditions:         Array.from(document.querySelectorAll('.condition-checkbox:checked')).map(cb => cb.value),
        update_only:        true,
    };

    try {
        showLoader();
        const res = await axios.post('/generate/meal-suggestion', payload);
        if (res.data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('aiPlannerModal'))?.hide();
            successToast(res.data.message || 'Health information updated successfully.');
            // Profile now exists → reflect it in the banner (first-time creation included)
            document.getElementById('planner-btn-label').textContent = 'Update My Plan';
            document.getElementById('update-health-btn').style.display = 'inline-block';
        } else {
            err.textContent = res.data.message || 'Failed to update health info.';
            err.style.display = 'block';
        }
    } catch (error) {
        const msg = error.response?.data?.message || error.response?.data?.errors || 'Failed to update health info.';
        err.textContent = typeof msg === 'object' ? Object.values(msg).flat().join(' ') : msg;
        err.style.display = 'block';
    } finally {
        hideLoader();
    }
}

async function generateAiPlan() {
    const gender      = document.getElementById('ai-gender').value;
    const age         = document.getElementById('ai-age').value;
    const weight      = document.getElementById('ai-weight').value;
    const height      = document.getElementById('ai-height').value;
    const period      = document.getElementById('ai-period').value;
    const description = document.getElementById('ai-description').value;
    const activity_level     = document.getElementById('ai-activity').value;
    const goal               = document.getElementById('ai-goal').value;
    const dietary_preference = document.getElementById('ai-diet').value;
    const conditions = Array.from(document.querySelectorAll('.condition-checkbox:checked')).map(cb => cb.value);
    const err = document.getElementById('ai-error');

    err.style.display = 'none'; err.textContent = '';
    if (!gender || !age || !weight || !height) {
        err.textContent = 'Please fill in gender, age, weight, and height.';
        err.style.display = 'block';
        return;
    }

    document.getElementById('ai-form-section').style.display    = 'none';
    document.getElementById('ai-loading-section').style.display = 'block';

    try {
        const res = await axios.post('/generate/meal-suggestion', {
            gender, age, weight, height, period, description,
            activity_level, goal, dietary_preference, conditions
        });
        if (res.data.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('aiPlannerModal'))?.hide();
            document.getElementById('planner-btn-label').textContent = 'Update My Plan';
            document.getElementById('update-health-btn').style.display = 'inline-block';
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

    const hasProfileData = data.bmi !== undefined && data.bmi !== null;
    let summaryHtml = `
        <i class="mdi mdi-account-heart-outline fs-5"></i>
        <div>`;
    if (hasProfileData) {
        summaryHtml += `
            <strong>BMI: ${data.bmi} (${data.bmi_category})</strong> &middot;
            Daily target <strong>${data.target_calories} kcal</strong>.
            Below is a suggested plan for the next 7 days, by date and meal type.`;
    } else {
        summaryHtml += `
            <strong>Based on your previous orders.</strong>
            Below is a suggested plan for the next 7 days, by date and meal type.
            <div class="mt-1 small text-muted"><i class="mdi mdi-information-outline me-1"></i>
            Add your health profile to get calorie-targeted suggestions.</div>`;
    }

    // Surface noted conditions as a flag (not medical advice)
    if (data.conditions && data.conditions.length) {
        const badges = data.conditions
            .map(c => `<span class="badge bg-light text-dark condition-badge me-1">${CONDITION_LABELS[c] || c}</span>`)
            .join('');
        summaryHtml += `
            <div class="mt-2 small text-muted">
                <i class="mdi mdi-heart-pulse me-1"></i>Noted conditions: ${badges}
                — please consult a professional for condition-specific dietary advice.
            </div>`;
    }
    summaryHtml += `</div>`;
    document.getElementById('suggestion-summary').innerHTML = summaryHtml;

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
                    <a href="/meal/details/${p.id}" class="text-decoration-none text-dark">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img src="${img}" class="rounded" style="width:54px;height:54px;object-fit:cover;">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">${name}</div>
                                <small class="text-success">${p.calories} ${p.calories_unit}</small>
                                <div class="fw-bold text-primary small">£${p.price}</div>
                            </div>
                        </div>
                    </a>
                    <div class="d-flex gap-1 mb-1">
                        <button class="btn btn-sm btn-success rounded-pill flex-grow-1 suggest-add-btn"
                                data-product-id="${p.id}" data-date="${day.date}"
                                data-meal-type-id="${meal.meal_type_id}" data-meal-type-name="${meal.meal_type}">
                            <i class="mdi mdi-plus"></i> Add
                        </button>
                        <button class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1 suggest-setmeal-btn"
                                data-meal-type-id="${meal.meal_type_id}">
                            <i class="mdi mdi-silverware-variant"></i> Set Meal
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill w-100 suggest-similar-btn"
                            data-name="${p.name}" data-meal-type-id="${meal.meal_type_id}">
                        <i class="mdi mdi-magnify"></i> Find Similar
                    </button>`;
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

/* ===== Set Meal: select that meal type, check Select All, show its food ===== */
async function setMeal(mealTypeId) {
    const btn = Array.from(document.querySelectorAll('#meal-type-buttons button'))
        .find(b => b.dataset.mealTypeId == mealTypeId);

    if (btn) {
        await selectMealType({ target: btn }, mealTypeId);   // loads keywords + clears input
    } else {
        selectedMealTypeId = mealTypeId;
        await loadMealKeywords(mealTypeId);
    }

    // tick Select All → checks every keyword + fills the search input
    const selectAll = document.getElementById('keyword-select-all');
    selectAll.checked = true;
    toggleSelectAllKeywords(true);

    // search using all selected keywords
    const terms = parseTerms(document.getElementById('keyword-input').value);
    if (terms.length) {
        lastSearchTerms = terms;
        await searchProducts(terms);
    }

    document.getElementById('keyword-section').scrollIntoView({ behavior: 'smooth' });
}

/* ===== Find Similar: search the food name across all clients ===== */
async function findSimilarFood(foodName, mealTypeId) {
    const btn = Array.from(document.querySelectorAll('#meal-type-buttons button'))
        .find(b => b.dataset.mealTypeId == mealTypeId);

    if (btn && selectedMealTypeId !== mealTypeId) {
        await selectMealType({ target: btn }, mealTypeId);
    } else if (!selectedMealTypeId) {
        selectedMealTypeId = mealTypeId;
    }

    document.getElementById('keyword-input').value = foodName;
    lastSearchTerms = [foodName];
    await searchProducts([foodName]);

    document.getElementById('product-list').scrollIntoView({ behavior: 'smooth' });
}

/* ===== Suggestion plan button delegation ===== */
function bindWeeklyPlanButtons() {
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

        const setMealBtn = e.target.closest('.suggest-setmeal-btn');
        if (setMealBtn) {
            setMeal(parseInt(setMealBtn.dataset.mealTypeId));
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