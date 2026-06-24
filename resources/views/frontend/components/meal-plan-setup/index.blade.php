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
            <div class="d-flex align-items-center gap-2">
                <button id="meal-voice-btn" type="button" class="btn btn-sm btn-primary rounded-pill" title="Add or remove items by voice">
                    <i class="mdi mdi-microphone"></i> Speak
                </button>
                <span class="badge bg-light text-dark" id="suggestion-period-badge"></span>
            </div>
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

            <div id="browse-loadmore" class="d-flex justify-content-center"></div>
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
                    <div class="text-danger small mt-1" id="err-meal-type"></div>
                </div>
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label for="modal-meal-date" class="form-label fw-semibold">Date</label>
                        <input type="date" id="modal-meal-date" class="form-control">
                        <div class="text-danger small mt-1" id="err-meal-date"></div>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="modal-meal-time" class="form-label fw-semibold">Time</label>
                        <input type="time" id="modal-meal-time" class="form-control">
                        <div class="text-danger small mt-1" id="err-meal-time"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="modal-location" class="form-label fw-semibold">Delivery Location</label>
                    <select id="modal-location" class="form-select">
                        <option value="">Select a location</option>
                        <option value="__add__">＋ Add new location…</option>
                    </select>
                    <div class="form-text" id="location-bind-note" style="display:none;"></div>
                    <div class="text-danger small mt-1" id="err-location"></div>
                </div>

                {{-- Inline add-new-location (country → county → city cascade) --}}
                <div id="add-location-box" class="border rounded-3 p-3 mb-3" style="display:none; background:#f8f9fa;">
                    <div class="fw-semibold mb-2"><i class="mdi mdi-map-marker-plus me-1"></i>New location</div>
                    <div class="mb-2">
                        <input type="text" id="loc-label" class="form-control form-control-sm" placeholder="Label (e.g. Home, Office)">
                        <div class="text-danger small mt-1" id="err-loc-label"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><select id="loc-country" class="form-select form-select-sm"><option value="">Country</option></select><div class="text-danger small mt-1" id="err-loc-country"></div></div>
                        <div class="col-6"><select id="loc-county" class="form-select form-select-sm" disabled><option value="">County</option></select><div class="text-danger small mt-1" id="err-loc-county"></div></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><select id="loc-city" class="form-select form-select-sm" disabled><option value="">City</option></select><div class="text-danger small mt-1" id="err-loc-city"></div></div>
                        <div class="col-6"><input type="text" id="loc-zip" class="form-control form-control-sm" placeholder="Zip code"><div class="text-danger small mt-1" id="err-loc-zip"></div></div>
                    </div>
                    <div class="mb-2"><input type="text" id="loc-address1" class="form-control form-control-sm" placeholder="Address 1"><div class="text-danger small mt-1" id="err-loc-address1"></div></div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" id="loc-cancel" class="btn btn-sm btn-light">Cancel</button>
                        <button type="button" id="loc-save" class="btn btn-sm btn-primary">Save location</button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="meal-quantity" class="form-label fw-semibold">Quantity</label>
                    <input type="number" id="meal-quantity" class="form-control" value="1" min="1">
                    <div class="text-danger small mt-1" id="err-quantity"></div>
                </div>
                <div class="text-danger small mb-2" id="err-general"></div>
                <div class="text-end">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirm-add-meal" class="btn btn-primary rounded-pill px-4">Add to Plan</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Voice Assistant Modal ===== --}}
<div class="modal fade" id="voiceAssistantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold"><i class="mdi mdi-microphone me-2"></i>Voice Assistant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Live transcript / mic status --}}
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3 bg-light">
                    <button id="va-mic-btn" type="button" class="btn btn-primary rounded-circle" style="width:54px;height:54px;">
                        <i class="mdi mdi-microphone fs-4"></i>
                    </button>
                    <div class="flex-grow-1">
                        <div id="va-status" class="fw-semibold text-primary">Tap the mic and speak…</div>
                        <div id="va-transcript" class="text-muted small mt-1">e.g. “add egg” or “delete goan fish from dinner”</div>
                    </div>
                </div>

                {{-- Manual text fallback (works without a microphone) --}}
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text"><i class="mdi mdi-keyboard-outline"></i></span>
                    <input type="text" id="va-text-input" class="form-control" placeholder="…or type a command and press Enter">
                </div>

                {{-- The current question + dynamic options --}}
                <div id="va-question" class="fw-semibold mb-2"></div>
                <div id="va-options" class="d-flex flex-wrap gap-2 mb-2"></div>
                <div id="va-products" class="row g-2"></div>

                <div id="va-feedback" class="alert mb-0 mt-2" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
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

{{-- ===== Add Food to a suggested-plan group ===== --}}
<div class="modal fade" id="setupAddItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold">Add Food to <span id="setup-add-meal-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- keyword chips for this meal type --}}
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-semibold">Keywords</small>
                        <label class="small mb-0" style="cursor:pointer;">
                            <input type="checkbox" id="setup-add-select-all"> Select all
                        </label>
                    </div>
                    <div id="setup-add-keywords" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                    <input type="text" id="setup-add-search" class="form-control" placeholder="Search food by keyword (e.g. chicken), then press Enter">
                </div>

                <div id="setup-add-results" class="row g-2"></div>
                <nav class="mt-3"><ul class="pagination pagination-sm justify-content-center mb-0" id="setup-add-pagination"></ul></nav>
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

/* ===== Browse Meals (default 12 random + load more) ===== */
let browseSeed     = Math.floor(Math.random() * 1e9);   // stable random order for this page visit
let browsePage     = 1;
let browseLastPage = 1;

/* ===== Voice-edit state ===== */
let currentPlan     = [];   // the rendered weekly_plan (set in renderSuggestions)
let voiceAdded      = {};   // { "date|mealTypeId": [product, ...] }
let suggestedHidden = {};   // { "date|mealTypeId|productId": true }

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
        initVoiceAssistant();
        initMealLocations();
        initSetupAddItem();

        await checkHealthProfile();
        await loadSetupSuggestion();   // last multi-address order → editable plan (overrides health suggestion)
        await browseDefaultMeals();    // Browse Meals: 12 random meals by default + Load More

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
            healthWeeklyPlan = res.data.data;          // rendered by loadSetupSuggestion (editable grid)
        } else {
            // no profile → keep order-based suggestion (if any) for the grid, then open "Update Health Info"
            document.getElementById('update-health-btn').style.display = 'none';
            const d = res.data.data || {};
            if (d.has_history && (d.weekly_plan || []).length) {
                healthWeeklyPlan = d;
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

// Default browse: 12 random meals (mixed restaurants), with Load More
function browseDefaultMeals() {
    lastSearchTerms = [];
    return searchProducts([], 1, false);
}

async function searchProducts(keywords, page = 1, append = false) {
    const list = document.getElementById('product-list');
    if (!append) list.innerHTML = `<div class="text-muted text-center py-3">Loading meals...</div>`;
    try {
        showLoader();
        const body = {
            keywords,
            meal_type_id: selectedMealTypeId,
            latitude:     userLatitude,
            longitude:    userLongitude,
            per_page:     12
        };
        // stable random order only for the default browse (no keyword, no meal type)
        if ((!keywords || keywords.length === 0) && !selectedMealTypeId) body.seed = browseSeed;

        const res = await axios.post(`/search/products?page=${page}`, body);
        if (res.status === 200 && res.data.status === 'success' && res.data.products.data.length > 0) {
            renderProducts(res.data.products.data, res.data.total, append);
            browsePage     = res.data.products.current_page;
            browseLastPage = res.data.products.last_page;
            renderLoadMore();
        } else if (!append) {
            document.getElementById('results-heading').style.display = 'none';
            list.innerHTML = `
                <div class="col-12">
                    <div class="text-center text-muted py-5">
                        <i class="mdi mdi-food-off-outline" style="font-size:2.5rem;"></i>
                        <p class="mb-0 mt-2">No meals found. Try different keywords or a food name.</p>
                    </div>
                </div>`;
            document.getElementById('browse-loadmore').innerHTML = '';
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

function renderLoadMore() {
    const box = document.getElementById('browse-loadmore');
    box.innerHTML = (browsePage < browseLastPage)
        ? `<button class="btn btn-outline-primary rounded-pill px-4" onclick="loadMoreMeals()"><i class="mdi mdi-reload me-1"></i>Load More</button>`
        : '';
}

function loadMoreMeals() {
    searchProducts(lastSearchTerms, browsePage + 1, true);
}

/* ===================== attractive result cards ===================== */
function renderProducts(products, total, append = false) {
    const list = document.getElementById('product-list');
    if (!append) { allProducts = []; list.innerHTML = ''; }

    // results heading
    const heading = document.getElementById('results-heading');
    heading.style.display = 'flex';
    document.getElementById('results-count').textContent = `${total ?? products.length} found`;

    products.forEach((p) => {
        const index = allProducts.length;   // global index across appended pages
        allProducts.push(p);
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

    document.getElementById('modal-product-name').textContent = toTitleCase(product.name) || 'N/A';
    document.getElementById('modal-provided-by').innerHTML = (product.client_info && product.client_info.last_name)
        ? `<p class="text-muted small mb-2"><strong>Provided by:</strong> ${toTitleCase(product.client_info.first_name + ' ' + product.client_info.last_name)}</p>` : '';

    const sel = document.getElementById('modal-meal-type');
    sel.innerHTML = '<option value="">Select a meal type</option>';
    (product.meal_types || []).forEach(mt => {
        const selected = (mt.id === selectedMealTypeId) ? 'selected' : '';
        sel.insertAdjacentHTML('beforeend', `<option value="${mt.id}" ${selected}>${toTitleCase(mt.name)}</option>`);
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

    const locationId = document.getElementById('modal-location')?.value;

    clearModalErrors();
    let valid = true;
    if (!selectedProduct)                        { setModalError('err-general', 'No product selected.'); valid = false; }
    if (!mealTypeId)                             { setModalError('err-meal-type', 'Please select a meal type.'); valid = false; }
    if (!selectedDate)                           { setModalError('err-meal-date', 'Please choose a date.'); valid = false; }
    if (!selectedTime)                           { setModalError('err-meal-time', 'Please choose a time.'); valid = false; }
    if (!locationId || locationId === '__add__') { setModalError('err-location', 'Please select a delivery location.'); valid = false; }
    if (!quantity || quantity <= 0)              { setModalError('err-quantity', 'Please enter a valid quantity.'); valid = false; }
    if (!valid) return;

    const key = `${selectedDate}_${mealTypeId}`;
    const name = sel.options[sel.selectedIndex]?.text || 'this meal type';

    const existingTime = customerMealTimes[key];
    if (existingTime && existingTime !== selectedTime) {
        return setModalError('err-meal-time', `All ${name} items on ${selectedDate} must use the same time (${existingTime}).`);
    }
    const existingLoc = mealSetupLocations[key];
    if (existingLoc && String(existingLoc) !== String(locationId)) {
        return setModalError('err-location', `All ${name} items on ${selectedDate} must deliver to the same location.`);
    }

    try {
        showLoader();
        const res = await axios.post('/store/meal-cart', {
            product_id:   selectedProduct.id,
            meal_type_id: mealTypeId,
            meal_date:    selectedDate,
            meal_time:    selectedTime,
            location_id:  locationId,
            quantity:     quantity
        });
        if (res.status === 201 && res.data.status === 'success') {
            successToast(res.data.message || 'Meal added successfully!');
            customerMealTimes[key] = selectedTime;
            mealSetupLocations[key] = locationId;
            bootstrap.Modal.getInstance(document.getElementById('addMealPlanModal')).hide();
            await updateMealCartCount();
        } else {
            setModalError('err-general', res.data.message || 'Failed to add to meal plan.');
        }
    } catch (error) {
        const errs = error.response?.data?.errors;
        if (errs) {
            const map = { meal_type_id:'err-meal-type', meal_date:'err-meal-date', meal_time:'err-meal-time', location_id:'err-location', quantity:'err-quantity' };
            Object.keys(errs).forEach(f => setModalError(map[f] || 'err-general', errs[f][0]));
        } else {
            setModalError('err-general', error.response?.data?.message || 'Something went wrong. Please try again.');
        }
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
            // render the freshly generated plan in the editable grid
            healthWeeklyPlan = res.data.data;
            await loadMealLocations();
            setupPlan = healthPlanToSetup(res.data.data.weekly_plan || []);
            setupPlanSource = 'health';
            renderSetupPlan();
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
    currentPlan   = data.weekly_plan || [];   // keep the plan for voice-edit + submit
    voiceAdded    = {};                        // {slotKey: [product,...]} added by voice
    suggestedHidden = {};                      // {slotKey|productId: true} suggested items removed by voice

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

            const slotKey = `${day.date}|${meal.meal_type_id}`;
            const suggestedDel = meal.product
                ? `<button class="btn btn-sm btn-link text-danger p-0 voice-del-suggested" title="Remove this item"
                          data-slot="${slotKey}" data-product-id="${meal.product.id}"><i class="mdi mdi-close-circle fs-6"></i></button>`
                : '';

            mealsHtml += `
                <div class="col-md-3 col-sm-6">
                    <div class="card border h-100 suggest-meal-card" data-slot="${slotKey}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="meal-badge ${cfg.bg} text-white"><i class="mdi ${cfg.icon}"></i></span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">${meal.meal_type}</div>
                                    <small class="text-muted">~${meal.calorie_target} kcal</small>
                                </div>
                                ${suggestedDel}
                            </div>
                            <div class="suggested-slot" data-product-id="${meal.product ? meal.product.id : ''}">${body}</div>
                            <div class="voice-items mt-2" data-slot="${slotKey}"></div>
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

    // health/history fallback uses the legacy voice submit bar (the editable plan hides it again)
    const legacyBar = document.getElementById('voice-submit-bar');
    if (legacyBar) legacyBar.style.display = '';

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

    document.getElementById('modal-product-name').textContent = toTitleCase(product.name);
    document.getElementById('modal-provided-by').innerHTML = product.client_name
        ? `<p class="text-muted small mb-2"><strong>Provided by:</strong> ${toTitleCase(product.client_name)}</p>` : '';

    const sel = document.getElementById('modal-meal-type');
    sel.innerHTML = `<option value="${mealTypeId}" selected>${toTitleCase(mealTypeName)}</option>`;

    setupModalDates(date);
    document.getElementById('modal-meal-time').value = MEAL_DEFAULT_TIMES[mealTypeName.toLowerCase()] || '12:00';

    document.getElementById('modal-product-image').src = product.image
        ? `{{ asset('upload/product/small') }}/${product.image}`
        : `{{ asset('upload/product/small/no_image.jpg') }}`;

    new bootstrap.Modal(document.getElementById('addMealPlanModal')).show();
}

/* ==========================================================================
   VOICE ASSISTANT  —  add / remove plan items by voice, then submit to cart
   No backend / DB changes: edits live in the browser; submit reuses /store/meal-cart
   ========================================================================== */
const MEAL_TYPE_WORDS = { breakfast:'Breakfast', lunch:'Lunch', snack:'Snacks', snacks:'Snacks', dinner:'Dinner' };
const MONTHS = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

let vaRecognition     = null;
let vaListeningFor     = null;   // 'command' | 'mealtype' | 'date' | 'submit'
let vaState            = {};     // { action, food, product, mealTypeId, mealTypeName, date, dayLabel }
let vaSearchResults    = {};     // { productId: product } for the pick list
let vaDeleteCandidates = [];     // items shown when removing by pick
let vaLastTranscript   = '';     // most recent (possibly interim) transcript
let vaFinalHandled     = false;  // whether a final result was already processed
let vaSearchOrder      = [];     // ordered product ids in the pick list (for "first/second…")
let vaMealTypeOrder    = [];     // ordered meal-type options currently shown (for number voice)
let vaVoiceMode        = false;  // true once user starts by voice → auto-listen each step
let vaAutoTimer        = null;   // debounce handle for auto-restarting the mic
let vaNoSpeechRetries  = 0;      // consecutive silent listens before we stop auto-mode

/* ---------- speech-recognition helpers ---------- */
function vaSpeechSupported() {
    return 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
}
function vaGetRecognition() {
    if (vaRecognition) return vaRecognition;
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) return null;
    vaRecognition = new SR();
    vaRecognition.lang = 'en-US';
    vaRecognition.interimResults = true;     // show words live as they are spoken
    vaRecognition.continuous = false;
    vaRecognition.maxAlternatives = 1;
    vaRecognition.onresult = (e) => {
        let interim = '', final = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            const tr = e.results[i][0].transcript;
            if (e.results[i].isFinal) final += tr; else interim += tr;
        }
        const shown = (final || interim).trim();
        if (shown) { vaLastTranscript = shown; vaNoSpeechRetries = 0; document.getElementById('va-transcript').textContent = '“' + shown + '”'; }
        if (final.trim()) { vaFinalHandled = true; vaHandleSpeech(final.trim()); }
    };
    vaRecognition.onerror = (e) => {
        if (e.error === 'aborted') { vaMicIdle(); return; }
        if (e.error === 'no-speech') {
            if (vaVoiceMode && vaNoSpeechRetries < 6) { vaNoSpeechRetries++; vaMicIdle(); vaSetStatus('Listening… (go ahead)'); vaAutoListen(); return; }
            vaSetStatus('Paused — tap the mic to keep going.'); vaStopVoiceMode(); return;
        }
        vaSetStatus(({
            'audio-capture': 'No audio from the mic — check the Windows input device & level.',
            'not-allowed':   'Mic permission is blocked for this site.',
            'network':       'Speech recognition needs internet — check your connection.',
        })[e.error] || ('Mic error: ' + e.error));
        vaStopVoiceMode();
    };
    vaRecognition.onend = () => {
        vaMicIdle();
        // short words (e.g. "egg") may arrive only as interim — process them on end
        if (!vaFinalHandled && vaLastTranscript) {
            const t = vaLastTranscript; vaLastTranscript = ''; vaHandleSpeech(t);
        } else if (vaVoiceMode && vaListeningFor) {
            vaAutoListen();   // keep the mic alive while a step is still pending
        }
    };
    return vaRecognition;
}
function vaStartListening(forWhat) {
    const rec = vaGetRecognition();
    if (!rec) { vaSetStatus('Voice not supported here — type the command below.'); return; }
    vaListeningFor = forWhat;
    vaLastTranscript = ''; vaFinalHandled = false;
    try { rec.start(); vaMicActive(); vaSetStatus('Listening…'); } catch (_) {}
}
// Auto-restart the mic for the next step (only while in voice mode)
function vaAutoListen() {
    if (!vaVoiceMode || !vaSpeechSupported()) return;
    clearTimeout(vaAutoTimer);
    vaAutoTimer = setTimeout(() => { if (vaVoiceMode && vaListeningFor) vaStartListening(vaListeningFor); }, 300);
}
function vaStopVoiceMode() {
    vaVoiceMode = false;
    clearTimeout(vaAutoTimer);
    try { vaRecognition && vaRecognition.abort(); } catch (_) {}
    vaMicIdle();
}
function vaMicActive() { const b = document.getElementById('va-mic-btn'); b.classList.replace('btn-primary','btn-danger'); }
function vaMicIdle()   { const b = document.getElementById('va-mic-btn'); if (b.classList.contains('btn-danger')) b.classList.replace('btn-danger','btn-primary'); }
function vaSetStatus(msg) { document.getElementById('va-status').textContent = msg; }
function vaFeedback(msg, type='info') {
    const el = document.getElementById('va-feedback');
    el.className = `alert alert-${type} mb-0 mt-2`;
    el.textContent = msg; el.style.display = 'block';
}
function vaClearDynamic() {
    document.getElementById('va-question').innerHTML = '';
    document.getElementById('va-options').innerHTML  = '';
    document.getElementById('va-products').innerHTML = '';
    document.getElementById('va-feedback').style.display = 'none';
}

/* ---------- open ---------- */
function openVoiceAssistant() {
    if (!currentPlan.length) { errorToast('Please generate a meal plan first.'); return; }
    vaState = {};
    vaListeningFor = null;          // always start a fresh command on open
    vaVoiceMode = false;            // wait for the first mic tap before auto-listening
    vaClearDynamic();
    vaSetStatus(vaSpeechSupported() ? 'Tap the mic and speak…' : 'Type your command below (voice not supported in this browser).');
    document.getElementById('va-transcript').textContent = 'e.g. “add egg” or “delete goan fish from dinner”';
    document.getElementById('va-text-input').value = '';
    new bootstrap.Modal(document.getElementById('voiceAssistantModal')).show();
}

/* ---------- route speech/text by what we await ---------- */
function vaHandleSpeech(text) {
    const t = text.toLowerCase();
    if (vaListeningFor === 'submit')   { if (/\b(submit|store|confirm|cart|yes|ok)\b/.test(t)) submitPlanToCart(); return; }
    if (vaListeningFor === 'product') {
        const idx = vaParseOrdinal(t, vaSearchOrder.length);
        if (idx !== null && idx >= 0 && idx < vaSearchOrder.length) { vaPickProduct(vaSearchOrder[idx]); return; }
        vaSetStatus('Say a number (1, 2, 3…) or tap an item below.'); return;
    }
    if (vaListeningFor === 'mealtype') {
        for (const w in MEAL_TYPE_WORDS) if (t.includes(w)) { vaSetMealType(null, MEAL_TYPE_WORDS[w]); return; }
        const idx = vaParseOrdinal(t, vaMealTypeOrder.length);
        if (idx !== null && vaMealTypeOrder[idx]) { vaSetMealType(vaMealTypeOrder[idx].id ?? null, vaMealTypeOrder[idx].name); return; }
        vaSetStatus('Say a meal name or its number (1, 2, 3…).'); return;
    }
    if (vaListeningFor === 'date') {
        const dm = vaMatchDate(' ' + t + ' ');
        if (dm) { vaSetDate(dm.date, dm.label); return; }
        const days = vaPlanDays();
        const idx  = vaParseOrdinal(t, days.length);
        if (idx !== null && days[idx]) { vaSetDate(days[idx].date, days[idx].label); return; }
        vaSetStatus('Say a date or its number (1, 2, 3…).'); return;
    }
    if (vaListeningFor === 'command' && vaState.action === 'add' && !vaState.food) {
        vaState.food = t.replace(/\b(add|the|a|an|please|item|some)\b/g,' ').replace(/\s+/g,' ').trim();
        return vaContinueAdd();
    }
    vaProcessCommand(text);
}

/* ---------- parse a free-form command ---------- */
function vaProcessCommand(text) {
    const lower = ' ' + text.toLowerCase().trim() + ' ';

    let action = /\b(delete|remove|drop|take out)\b/.test(lower) ? 'delete' : 'add';

    let mealTypeName = null;
    for (const w in MEAL_TYPE_WORDS) {
        if (lower.includes(' ' + w + ' ') || lower.includes(' ' + w + 's ')) { mealTypeName = MEAL_TYPE_WORDS[w]; break; }
    }

    const dateMatch = vaMatchDate(lower);

    let food = text.toLowerCase()
        .replace(/\b(add|delete|remove|drop|take out|please|the|a|an|to|in|for|on|from|my|of|some|this|that|item)\b/g,' ')
        .replace(/\b(breakfast|lunch|snacks|snack|dinner)\b/g,' ')
        .replace(/\b(today|tomorrow)\b/g,' ')
        .replace(/\b\d{1,2}(st|nd|rd|th)?\b/g,' ')
        .replace(new RegExp('\\b(' + MONTHS.join('|') + ')[a-z]*\\b','g'),' ')
        .replace(/\s+/g,' ').trim();

    vaState = {
        action, food, product: null,
        mealTypeId:   mealTypeName ? vaMealTypeIdFor(mealTypeName) : null,
        mealTypeName: mealTypeName,
        date:     dateMatch ? dateMatch.date  : null,
        dayLabel: dateMatch ? dateMatch.label : null,
    };

    return action === 'delete' ? vaContinueDelete() : vaContinueAdd();
}

/* ---------- ADD path: pick product → meal type → date → commit ---------- */
function vaContinueAdd() {
    if (!vaState.food)       { vaClearDynamic(); vaSetStatus('What item would you like to add?');
                               document.getElementById('va-question').textContent = 'Say or type the food name (e.g. “egg”).';
                               vaListeningFor = 'command'; vaAutoListen(); return; }
    if (!vaState.product)    return vaSearchAndPick(vaState.food);
    if (!vaState.mealTypeId) return vaAskMealType();
    if (!vaState.date)       return vaAskDate();
    return vaCommitAdd();
}

async function vaSearchAndPick(food) {
    vaClearDynamic();
    vaSetStatus(`Searching “${food}”…`);
    document.getElementById('va-question').textContent = `Choose which “${food}” to add:`;
    try {
        const payload = { keywords: [food] };
        if (userLatitude && userLongitude) { payload.latitude = userLatitude; payload.longitude = userLongitude; }
        const res  = await axios.post('/search/products', payload);
        const list = res.data?.products?.data || [];
        if (!list.length) { vaFeedback(`No items found for “${food}”. Try another name.`, 'warning'); vaSetStatus('Tap the mic to try again.'); return; }

        vaSearchResults = {};
        document.getElementById('va-products').innerHTML = list.map((p, idx) => {
            vaSearchResults[p.id] = p;
            const img   = p.image ? `/upload/product/small/${p.image}` : '/upload/no_image.jpg';
            const cal   = p.nutrients?.calories ? `${p.nutrients.calories} ${p.nutrients.calories_unit||'kcal'}` : '';
            const price = (p.is_free || Number(p.price) === 0) ? 'Free' : `£${p.price}`;
            const mt    = (p.meal_types||[]).map(m => m.name).join(', ');
            return `
              <div class="col-md-6">
                <button type="button" class="btn btn-outline-primary w-100 text-start d-flex align-items-center gap-2 va-pick-product" data-id="${p.id}">
                    <span class="badge bg-primary rounded-circle flex-shrink-0" style="width:24px;height:24px;line-height:16px;">${idx + 1}</span>
                    <img src="${img}" style="width:46px;height:46px;object-fit:cover;border-radius:8px;">
                    <span class="flex-grow-1">
                        <span class="fw-semibold d-block">${(p.name||'').replace(/\b\w/g,l=>l.toUpperCase())}</span>
                        <small class="text-muted">${cal}${cal && price ? ' · ' : ''}${price}</small>
                        <small class="d-block text-muted">${mt}</small>
                    </span>
                </button>
              </div>`;
        }).join('');
        vaSearchOrder  = list.map(p => p.id);
        vaListeningFor = 'product';
        vaSetStatus('Tap an item below — or say “first”, “second”, “third”…');
        vaAutoListen();
    } catch (_) { vaFeedback('Search failed. Please try again.', 'danger'); }
}

function vaPickProduct(id) {
    const p = vaSearchResults[id];
    if (!p) return;
    vaState.product = {
        id: p.id, name: p.name, image: p.image,
        price: (p.is_free || Number(p.price) === 0) ? 0 : Number(p.price),
        is_free: !!(p.is_free || Number(p.price) === 0),
        calories: p.nutrients?.calories ?? '', calories_unit: p.nutrients?.calories_unit ?? 'kcal',
        meal_types: p.meal_types || [],
    };
    // drop a previously-said meal type if this product doesn't support it
    if (vaState.mealTypeName && !vaState.product.meal_types.some(m => m.name.toLowerCase() === vaState.mealTypeName.toLowerCase())) {
        vaState.mealTypeId = null; vaState.mealTypeName = null;
    }
    vaContinueAdd();
}

function vaAskMealType() {
    vaClearDynamic();
    vaSetStatus('Which meal?');
    document.getElementById('va-question').textContent = vaState.action === 'delete'
        ? `Remove ${vaState.food ? '“'+vaState.food+'” ' : ''}from which meal?`
        : `Add “${vaState.product.name}” to which meal?`;
    const opts = (vaState.action === 'add' && vaState.product?.meal_types?.length)
        ? vaState.product.meal_types
        : vaPlanMealTypes();
    vaMealTypeOrder = opts;
    document.getElementById('va-options').innerHTML = opts.map((m, i) =>
        `<button type="button" class="btn btn-outline-primary rounded-pill va-pick-mealtype" data-id="${m.id ?? ''}" data-name="${m.name}">${i + 1}. ${m.name}</button>`
    ).join('');
    vaSetStatus('Tap a meal — or say its number (1, 2, 3…).');
    vaListeningFor = 'mealtype';
    vaAutoListen();
}

function vaAskDate() {
    vaClearDynamic();
    document.getElementById('va-question').textContent =
        `${vaState.action === 'delete' ? 'Remove from' : 'Add to'} ${vaState.mealTypeName} on which date?`;
    const days = vaPlanDays();
    document.getElementById('va-options').innerHTML = days.map((d, i) =>
        `<button type="button" class="btn ${i===0?'btn-primary':'btn-outline-primary'} rounded-pill va-pick-date"
                 data-date="${d.date}" data-label="${d.label}">${i + 1}. ${d.label}${i===0?' (default)':''}</button>`
    ).join('');
    vaSetStatus('Tap a date — or say its number (1, 2, 3…).');
    vaListeningFor = 'date';
    vaAutoListen();
}

function vaSetMealType(id, name) {
    vaState.mealTypeName = name;
    vaState.mealTypeId   = id || vaMealTypeIdFor(name);
    return vaState.action === 'delete' ? vaContinueDelete() : vaContinueAdd();
}
function vaSetDate(date, label) {
    vaState.date = date; vaState.dayLabel = label;
    return vaState.action === 'delete' ? vaContinueDelete() : vaContinueAdd();
}

function vaParseOrdinal(t, len) {
    const words = { first:0, one:0, second:1, two:1, third:2, three:2, fourth:3, four:3,
                    fifth:4, five:4, sixth:5, six:5, seventh:6, seven:6, eighth:7, eight:7,
                    ninth:8, nine:8, tenth:9, ten:9 };
    if (/\blast\b/.test(t)) return (len || 1) - 1;
    for (const w in words) if (new RegExp('\\b' + w + '\\b').test(t)) return words[w];
    const m = t.match(/\b(?:number\s*|no\.?\s*)?(\d{1,2})(st|nd|rd|th)?\b/);
    if (m) return parseInt(m[1], 10) - 1;
    return null;
}
/* voice targets the editable setup plan when present, else the old suggestion */
function vaUsingSetup() { return Array.isArray(setupPlan) && setupPlan.length > 0; }
function vaPlanDays() {
    if (vaUsingSetup()) return setupPlan.map(d => ({
        date:  d.date,
        label: new Date(d.date + 'T00:00:00').toLocaleDateString('en-US', { weekday:'short', month:'short', day:'numeric' })
    }));
    return (currentPlan || []).map(d => ({ date: d.date, label: d.day_label }));
}
function vaPlanMealTypes() {
    const seen = {}, out = [];
    if (vaUsingSetup()) {
        for (const d of setupPlan) for (const g of d.groups)
            if (!seen[g.meal_type_id]) { seen[g.meal_type_id] = 1; out.push({ id:g.meal_type_id, name:g.meal_type }); }
    } else {
        for (const d of (currentPlan || [])) for (const m of d.meals)
            if (!seen[m.meal_type_id]) { seen[m.meal_type_id] = 1; out.push({ id:m.meal_type_id, name:m.meal_type }); }
    }
    return out;
}
function vaMealTypeIdFor(name) {
    const l = (name||'').toLowerCase();
    const mt = vaPlanMealTypes().find(m => (m.name||'').toLowerCase() === l);
    return mt ? mt.id : null;
}
function vaMatchDate(lower) {
    const days = vaPlanDays();
    for (const d of days) {
        const da     = parseInt(d.date.split('-')[2], 10);
        const monNm  = MONTHS[parseInt(d.date.split('-')[1], 10) - 1];
        const reNumMon = new RegExp('\\b' + da + '(st|nd|rd|th)?\\s*(of\\s*)?' + monNm + '[a-z]*\\b');
        const reMonNum = new RegExp('\\b' + monNm + '[a-z]*\\s*' + da + '(st|nd|rd|th)?\\b');
        if (reNumMon.test(lower) || reMonNum.test(lower)) return { date:d.date, label:d.label };
    }
    if (/\btomorrow\b/.test(lower) && days[0]) return { date:days[0].date, label:days[0].label };
    const m = lower.match(/\b([0-9]{1,2})(st|nd|rd|th)?\b/);
    if (m) { const num = parseInt(m[1],10); const d = days.find(d => parseInt(d.date.split('-')[2],10) === num); if (d) return { date:d.date, label:d.label }; }
    return null;
}

/* ---------- finish a voice command: close the assistant + focus the day ---------- */
function vaFinishAndFocus(di, msg) {
    vaState = {}; vaListeningFor = null;
    vaStopVoiceMode();
    bootstrap.Modal.getInstance(document.getElementById('voiceAssistantModal'))?.hide();
    successToast('✓ ' + msg);
    setTimeout(() => {
        const collapseEl = document.getElementById('setupDay' + di);
        if (collapseEl) {
            bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle:false }).show();
            collapseEl.scrollIntoView({ behavior:'smooth', block:'center' });
        }
    }, 350);
}

/* ---------- commit ADD ---------- */
function vaCommitAdd() {
    if (!vaState.mealTypeId) { vaFeedback('Could not match that meal type.', 'warning'); return; }
    if (vaUsingSetup()) return vaCommitAddSetup();

    const slotKey = `${vaState.date}|${vaState.mealTypeId}`;
    if (vaSlotHasProduct(slotKey, vaState.product.id)) {
        vaFeedback(`${vaState.product.name} is already in ${vaState.mealTypeName} on ${vaState.dayLabel}.`, 'warning'); return;
    }
    (voiceAdded[slotKey] = voiceAdded[slotKey] || []).push(vaState.product);
    renderVoiceItems(slotKey);
    document.getElementById('va-question').innerHTML = '';
    document.getElementById('va-options').innerHTML  = '';
    document.getElementById('va-products').innerHTML = '';
    vaFeedback(`✓ Added ${vaState.product.name} to ${vaState.mealTypeName} on ${vaState.dayLabel}.`, 'success');
    vaSetStatus('Done! Speak another command, or close.');
    vaState = {}; vaListeningFor = null;
}

function vaCommitAddSetup() {
    const di = setupPlan.findIndex(d => d.date === vaState.date);
    if (di < 0) { vaFeedback('That date is not in your plan.', 'warning'); return; }

    let gi = setupPlan[di].groups.findIndex(g => String(g.meal_type_id) === String(vaState.mealTypeId));
    if (gi < 0) {
        setupPlan[di].groups.push({ meal_type_id: vaState.mealTypeId, meal_type: vaState.mealTypeName, meal_time: '', location: null, items: [] });
        gi = setupPlan[di].groups.length - 1;
    }
    const g = setupPlan[di].groups[gi];
    if (g.items.some(it => String(it.product.id) === String(vaState.product.id))) {
        vaFeedback(`${vaState.product.name} is already in ${vaState.mealTypeName}.`, 'warning'); return;
    }
    const p = vaState.product;
    g.items.push({ product: { id:p.id, name:p.name, image:p.image, price:p.price }, client: null, quantity: 1 });
    renderSetupPlan();
    vaFinishAndFocus(di, `Added ${p.name} to ${vaState.mealTypeName}.`);
}

/* ---------- DELETE path ---------- */
function vaContinueDelete() {
    if (!vaState.mealTypeId) return vaAskMealType();
    if (!vaState.date)       return vaAskDate();
    return vaCommitDelete();
}
function vaCommitDelete() {
    if (vaUsingSetup()) return vaCommitDeleteSetup();

    const slotKey = `${vaState.date}|${vaState.mealTypeId}`;
    const items   = vaSlotItems(slotKey);
    if (!items.length) { vaFeedback(`Nothing to remove in ${vaState.mealTypeName} on ${vaState.dayLabel}.`, 'warning'); return; }

    if (vaState.food) {
        const target = items.find(it => (it.product.name||'').toLowerCase().includes(vaState.food.toLowerCase()));
        if (target) {
            vaRemoveItem(slotKey, target);
            document.getElementById('va-question').innerHTML = '';
            document.getElementById('va-options').innerHTML  = '';
            vaFeedback(`✓ Removed ${target.product.name} from ${vaState.mealTypeName} on ${vaState.dayLabel}.`, 'success');
            vaSetStatus('Done! Speak another command, or close.');
            vaState = {}; vaListeningFor = null; return;
        }
    }
    // no name match → let them pick which item to remove
    vaClearDynamic();
    document.getElementById('va-question').textContent = `Which item to remove from ${vaState.mealTypeName} on ${vaState.dayLabel}?`;
    vaDeleteCandidates = items;
    document.getElementById('va-options').innerHTML = items.map((it, i) =>
        `<button type="button" class="btn btn-outline-danger rounded-pill va-pick-remove" data-i="${i}">${it.product.name}</button>`
    ).join('');
    vaListeningFor = null;
}

function vaCommitDeleteSetup() {
    const di = setupPlan.findIndex(d => d.date === vaState.date);
    if (di < 0) { vaFeedback('That date is not in your plan.', 'warning'); return; }
    const gi = setupPlan[di].groups.findIndex(g => String(g.meal_type_id) === String(vaState.mealTypeId));
    if (gi < 0 || !setupPlan[di].groups[gi].items.length) {
        vaFeedback(`Nothing to remove in ${vaState.mealTypeName} on ${vaState.dayLabel}.`, 'warning'); return;
    }
    const g = setupPlan[di].groups[gi];

    let idx = -1;
    if (vaState.food) idx = g.items.findIndex(it => (it.product.name || '').toLowerCase().includes(vaState.food.toLowerCase()));

    if (idx < 0) {
        // couldn't match by name → let them pick
        vaClearDynamic();
        document.getElementById('va-question').textContent = `Which item to remove from ${vaState.mealTypeName}?`;
        vaDeleteCandidates = g.items.map((it, i) => ({ product: it.product, _setup: { di, gi, ii: i } }));
        document.getElementById('va-options').innerHTML = g.items.map((it, i) =>
            `<button type="button" class="btn btn-outline-danger rounded-pill va-pick-remove" data-i="${i}">${toTitleCase(it.product.name)}</button>`
        ).join('');
        vaListeningFor = null;
        return;
    }

    const removed = g.items[idx].product.name;
    g.items.splice(idx, 1);
    renderSetupPlan();
    vaFinishAndFocus(di, `Removed ${removed} from ${vaState.mealTypeName}.`);
}

function vaSlotItems(slotKey) {
    const [date, mtid] = slotKey.split('|');
    const out = [];
    const day = currentPlan.find(d => d.date === date);
    if (day) {
        const meal = day.meals.find(m => String(m.meal_type_id) === String(mtid));
        if (meal && meal.product && !suggestedHidden[`${slotKey}|${meal.product.id}`])
            out.push({ kind:'suggested', product: meal.product });
    }
    (voiceAdded[slotKey] || []).forEach((p, idx) => out.push({ kind:'added', product:p, index:idx }));
    return out;
}
function vaSlotHasProduct(slotKey, productId) {
    return vaSlotItems(slotKey).some(it => String(it.product.id) === String(productId));
}
function vaRemoveItem(slotKey, item) {
    if (item.kind === 'suggested') {
        suggestedHidden[`${slotKey}|${item.product.id}`] = true;
        const slot   = document.querySelector(`.suggest-meal-card[data-slot="${slotKey}"] .suggested-slot`);
        const delBtn = document.querySelector(`.voice-del-suggested[data-slot="${slotKey}"]`);
        if (slot)   slot.style.display = 'none';
        if (delBtn) delBtn.style.display = 'none';
    } else {
        (voiceAdded[slotKey] || []).splice(item.index, 1);
        renderVoiceItems(slotKey);
    }
}
function renderVoiceItems(slotKey) {
    const box = document.querySelector(`.voice-items[data-slot="${slotKey}"]`);
    if (!box) return;
    box.innerHTML = (voiceAdded[slotKey] || []).map((p, i) => {
        const img   = p.image ? `/upload/product/small/${p.image}` : '/upload/no_image.jpg';
        const name  = (p.name||'').replace(/\b\w/g, l => l.toUpperCase());
        const price = p.is_free ? '£0.00' : `£${Number(p.price).toFixed(2)}`;
        const cal   = p.calories ? `${p.calories} ${p.calories_unit||'kcal'}` : '';
        return `
          <div class="position-relative border rounded-3 p-2 mt-2" style="background:#eff6ff;">
            <button class="btn btn-sm text-danger p-0 voice-del-added position-absolute top-0 end-0 mt-1 me-1"
                    data-slot="${slotKey}" data-index="${i}" title="Remove" style="line-height:1;">
                <i class="mdi mdi-close-circle"></i>
            </button>
            <div class="d-flex align-items-center gap-2">
                <img src="${img}" class="rounded" style="width:54px;height:54px;object-fit:cover;flex-shrink:0;">
                <div class="flex-grow-1 pe-3">
                    <div class="fw-semibold small">${name}
                        <span class="badge bg-info text-dark ms-1" style="font-size:.6rem;vertical-align:middle;">added</span>
                    </div>
                    ${cal ? `<small class="text-success d-block">${cal}</small>` : ''}
                    <div class="fw-bold text-primary small">${price}</div>
                </div>
            </div>
          </div>`;
    }).join('');
}

/* ---------- submit the whole plan to the meal cart ---------- */
async function submitPlanToCart() {
    const items = [];
    for (const day of currentPlan) {
        for (const meal of day.meals) {
            const slotKey = `${day.date}|${meal.meal_type_id}`;
            const time    = MEAL_DEFAULT_TIMES[(meal.meal_type||'').toLowerCase()] || '12:00';
            if (meal.product && !suggestedHidden[`${slotKey}|${meal.product.id}`])
                items.push({ product_id: meal.product.id, meal_type_id: meal.meal_type_id, meal_date: day.date, meal_time: time });
            (voiceAdded[slotKey] || []).forEach(p =>
                items.push({ product_id: p.id, meal_type_id: meal.meal_type_id, meal_date: day.date, meal_time: time }));
        }
    }
    if (!items.length) { errorToast('No items in the plan to submit.'); return; }
    if (!confirm(`Add ${items.length} item(s) from your plan to the cart?`)) return;

    showLoader();
    let added = 0, dup = 0, failed = 0;
    for (const it of items) {
        try {
            const res = await axios.post('/store/meal-cart', { ...it, quantity: 1 });
            if (res.status === 201) added++; else dup++;
        } catch (err) {
            if (err.response?.status === 409) dup++; else failed++;
        }
    }
    hideLoader();
    await updateMealCartCount();

    let msg = `${added} item(s) added to cart`;
    if (dup)    msg += `, ${dup} already in cart`;
    if (failed) msg += `, ${failed} failed`;
    successToast(msg + '.');
    setTimeout(() => { window.location.href = "{{ route('meal.cart') }}"; }, 1300);
}

/* ---------- wiring ---------- */
function initVoiceAssistant() {
    document.getElementById('meal-voice-btn')?.addEventListener('click', openVoiceAssistant);
    document.getElementById('va-mic-btn')?.addEventListener('click', () => {
        vaVoiceMode = true; vaNoSpeechRetries = 0;        // hands-free: keep listening each step
        vaStartListening(vaListeningFor || 'command');
    });

    document.getElementById('va-text-input')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            vaStopVoiceMode();                            // switched to typing → stop auto-listening
            const v = e.target.value.trim(); if (v) vaHandleSpeech(v); e.target.value = '';
        }
    });

    // stop the mic when the assistant is closed
    document.getElementById('voiceAssistantModal')?.addEventListener('hidden.bs.modal', vaStopVoiceMode);

    document.getElementById('va-options')?.addEventListener('click', (e) => {
        const mt = e.target.closest('.va-pick-mealtype');
        if (mt) { vaSetMealType(mt.dataset.id ? parseInt(mt.dataset.id) : null, mt.dataset.name); return; }
        const dt = e.target.closest('.va-pick-date');
        if (dt) { vaSetDate(dt.dataset.date, dt.dataset.label); return; }
        const rm = e.target.closest('.va-pick-remove');
        if (rm) {
            const it = vaDeleteCandidates[parseInt(rm.dataset.i)];
            if (!it) return;
            if (it._setup) {
                const { di, gi, ii } = it._setup;
                const removed = setupPlan[di].groups[gi].items[ii]?.product.name || it.product.name;
                setupPlan[di].groups[gi].items.splice(ii, 1);
                renderSetupPlan();
                vaFinishAndFocus(di, `Removed ${removed} from ${vaState.mealTypeName || 'the plan'}.`);
            } else {
                vaRemoveItem(`${vaState.date}|${vaState.mealTypeId}`, it);
                document.getElementById('va-options').innerHTML  = '';
                document.getElementById('va-question').innerHTML = '';
                vaFeedback(`✓ Removed ${it.product.name}.`, 'success');
                vaState = {}; vaListeningFor = null;
            }
        }
    });

    document.getElementById('va-products')?.addEventListener('click', (e) => {
        const pick = e.target.closest('.va-pick-product');
        if (pick) vaPickProduct(parseInt(pick.dataset.id));
    });

    // manual × buttons directly on the plan cards
    document.getElementById('weekly-plan')?.addEventListener('click', (e) => {
        const ds = e.target.closest('.voice-del-suggested');
        if (ds) { vaRemoveItem(ds.dataset.slot, { kind:'suggested', product:{ id: ds.dataset.productId } }); return; }
        const da = e.target.closest('.voice-del-added');
        if (da) { (voiceAdded[da.dataset.slot] || []).splice(parseInt(da.dataset.index), 1); renderVoiceItems(da.dataset.slot); }
    });

    document.getElementById('plan-submit-btn')?.addEventListener('click', submitPlanToCart);
    document.getElementById('plan-submit-voice-btn')?.addEventListener('click', () => {
        if (!vaSpeechSupported()) return submitPlanToCart();   // no mic → submit directly
        vaStartListening('submit');
    });
}

/* ==========================================================================
   DELIVERY LOCATIONS (Meal Plan Setup only)
   List + add-new with country → county → city cascade.
   location_id rides along in the add-to-cart payload + is bound per (date, meal type).
   ========================================================================== */
/* ==========================================================================
   SUGGESTED PLAN = editable clone of the customer's last multi-address order.
   Falls back silently to the health/history suggestion when there is none.
   ========================================================================== */
let setupPlan        = [];
let setupPlanSource  = '';      // 'last_order' | 'health'
let healthWeeklyPlan = null;    // health/history suggestion data (stored on load)
const SETUP_ICONS = {
    Breakfast:{i:'mdi-coffee-outline',c:'bg-warning'}, Lunch:{i:'mdi-food-outline',c:'bg-primary'},
    Snacks:{i:'mdi-food-apple-outline',c:'bg-success'}, Dinner:{i:'mdi-silverware-fork-knife',c:'bg-danger'}
};

async function loadSetupSuggestion() {
    try {
        const res = await axios.get('/get/setup-suggestion');
        if (res.data.status === 'success' && res.data.source === 'last_order' && (res.data.days || []).length) {
            await loadMealLocations();        // ensure saved places are ready for the dropdowns
            setupPlan = res.data.days;
            setupPlanSource = 'last_order';
            renderSetupPlan();
            return;
        }
    } catch (_) {}

    // no last order → render the health/history suggestion in the SAME editable grid
    if (healthWeeklyPlan && (healthWeeklyPlan.weekly_plan || []).length) {
        await loadMealLocations();
        setupPlan = healthPlanToSetup(healthWeeklyPlan.weekly_plan);
        setupPlanSource = 'health';
        renderSetupPlan();
    }
}

// convert a health/history weekly_plan into the editable setupPlan shape (no locations yet)
function healthPlanToSetup(weeklyPlan) {
    return (weeklyPlan || []).map(day => ({
        date: day.date,
        groups: (day.meals || []).filter(m => m.product).map(m => ({
            meal_type_id: m.meal_type_id,
            meal_type:    m.meal_type,
            meal_time:    MEAL_DEFAULT_TIMES[(m.meal_type || '').toLowerCase()] || '12:00',
            location:     null,
            items: [{
                product:  { id:m.product.id, name:m.product.name, image:m.product.image, price:m.product.price },
                client:   m.product.client_name ? { name: m.product.client_name } : null,
                quantity: 1
            }]
        }))
    }));
}

function setupLocationOptions(selectedId) {
    let html = '<option value="">Select location</option>';
    (mealLocations || []).forEach(l => {
        const place = l.city ? `${toTitleCase(l.label)} (${toTitleCase(l.city.name)})` : toTitleCase(l.label);
        html += `<option value="${l.id}"${String(l.id) === String(selectedId) ? ' selected' : ''}>${place}</option>`;
    });
    return html;
}

function renderSetupPlan() {
    const isHealth = setupPlanSource === 'health';
    document.getElementById('suggestion-period-badge').textContent = isHealth ? 'Based on your health profile' : 'Based on your last order';
    document.getElementById('suggestion-summary').innerHTML = isHealth
        ? `<i class="mdi mdi-account-heart-outline fs-5"></i><div><strong>Suggested for the next ${setupPlan.length} day(s) from your health profile.</strong> Edit anything, set a delivery location per meal, then add it all to your cart.</div>`
        : `<i class="mdi mdi-history fs-5"></i><div><strong>Your last order, ready for the next ${setupPlan.length} day(s).</strong> Edit anything below, then add it all to your cart.</div>`;
    const analysisEl = document.getElementById('suggestion-analysis');
    if (analysisEl) analysisEl.innerHTML = '';

    const container = document.getElementById('weekly-plan');
    container.innerHTML = '';

    setupPlan.forEach((day, di) => {
        const dLabel = new Date(day.date + 'T00:00:00').toLocaleDateString('en-US', { weekday:'long', month:'short', day:'numeric' });
        let groupsHtml = '';

        day.groups.forEach((g, gi) => {
            const cfg = SETUP_ICONS[g.meal_type] || { i:'mdi-food', c:'bg-secondary' };
            let itemsHtml = '';
            g.items.forEach((it, ii) => {
                const img = it.product?.image ? `/upload/product/small/${it.product.image}` : '/upload/no_image.jpg';
                itemsHtml += `
                  <div class="d-flex align-items-center gap-2 py-2 border-top">
                    <img src="${img}" class="rounded" style="width:46px;height:46px;object-fit:cover;">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">${toTitleCase(it.product?.name || '')}</div>
                        ${it.client ? `<small class="text-muted">Provided By: ${toTitleCase(it.client.name)}</small>` : ''}
                    </div>
                    <input type="number" min="1" value="${it.quantity}" class="form-control form-control-sm" style="width:64px;"
                           onchange="setupQty(${di},${gi},${ii},this.value)">
                    <button class="btn btn-sm btn-link text-danger p-0" title="Remove" onclick="setupRemoveItem(${di},${gi},${ii})"><i class="mdi mdi-close-circle"></i></button>
                  </div>`;
            });

            groupsHtml += `
              <div class="col-md-6">
                <div class="card border h-100">
                  <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="meal-badge ${cfg.c} text-white"><i class="mdi ${cfg.i}"></i></span>
                        <div class="fw-semibold flex-grow-1">${toTitleCase(g.meal_type)}</div>
                    </div>
                    <div class="row g-2 mb-1">
                        <div class="col-7">
                            <label class="form-label small mb-0 text-muted">Location</label>
                            <select class="form-select form-select-sm" onchange="setupLoc(${di},${gi},this.value)">${setupLocationOptions(g.location?.id)}</select>
                        </div>
                        <div class="col-5">
                            <label class="form-label small mb-0 text-muted">Time</label>
                            <input type="time" class="form-control form-control-sm" value="${g.meal_time || ''}" onchange="setupTime(${di},${gi},this.value)">
                        </div>
                    </div>
                    ${itemsHtml || '<div class="text-muted small py-2">No items.</div>'}
                    <button class="btn btn-sm btn-outline-success w-100 mt-2" onclick="openSetupAddItem(${di},${gi})">
                        <i class="mdi mdi-plus"></i> Add item
                    </button>
                  </div>
                </div>
              </div>`;
        });

        container.insertAdjacentHTML('beforeend', `
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button ${di !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#setupDay${di}">
                <i class="mdi mdi-calendar-outline me-2 text-primary"></i><span class="fw-semibold">${dLabel}</span>
              </button>
            </h2>
            <div id="setupDay${di}" class="accordion-collapse collapse ${di === 0 ? 'show' : ''}" data-bs-parent="#weekly-plan">
              <div class="accordion-body"><div class="row g-3">${groupsHtml}</div></div>
            </div>
          </div>`);
    });

    let bar = document.getElementById('setup-addall-bar');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'setup-addall-bar';
        bar.className = 'd-flex justify-content-end mt-3';
        container.parentNode.appendChild(bar);
    }
    bar.innerHTML = `<button class="btn btn-success rounded-pill px-4" onclick="setupAddAllToCart()"><i class="mdi mdi-cart-plus me-1"></i> Add this plan to cart</button>`;

    // hide the legacy voice submit bar so only one submit button shows
    const legacyBar = document.getElementById('voice-submit-bar');
    if (legacyBar) legacyBar.style.display = 'none';

    document.getElementById('suggestion-section').style.display = 'block';
}

function setupQty(di, gi, ii, v) { setupPlan[di].groups[gi].items[ii].quantity = Math.max(1, parseInt(v) || 1); }
function setupTime(di, gi, v)     { setupPlan[di].groups[gi].meal_time = v; }
function setupLoc(di, gi, v) {
    const loc = (mealLocations || []).find(l => String(l.id) === String(v));
    setupPlan[di].groups[gi].location = loc ? { id: loc.id, label: loc.label, city: loc.city ? loc.city.name : null } : null;
}
function setupRemoveItem(di, gi, ii) { setupPlan[di].groups[gi].items.splice(ii, 1); renderSetupPlan(); }

async function setupAddAllToCart() {
    const payload = [];
    for (const day of setupPlan) {
        for (const g of day.groups) {
            if (!g.items.length) continue;
            if (!g.location || !g.location.id) { errorToast(`Please set a location for ${g.meal_type} on ${day.date}.`); return; }
            if (!g.meal_time)                  { errorToast(`Please set a time for ${g.meal_type} on ${day.date}.`); return; }
            for (const it of g.items) {
                payload.push({
                    product_id:   it.product.id,
                    meal_type_id: g.meal_type_id,
                    meal_date:    day.date,
                    meal_time:    g.meal_time,
                    location_id:  g.location.id,
                    quantity:     it.quantity,
                });
            }
        }
    }
    if (!payload.length) { errorToast('Nothing to add.'); return; }
    if (!confirm(`Add ${payload.length} item(s) to your cart?`)) return;

    showLoader();
    let added = 0, dup = 0, failed = 0;
    for (const it of payload) {
        try {
            const r = await axios.post('/store/meal-cart', it);
            if (r.status === 201) added++; else dup++;
        } catch (e) {
            if (e.response?.status === 409) dup++; else failed++;
        }
    }
    hideLoader();
    let msg = `${added} added`;
    if (dup)    msg += `, ${dup} already in cart`;
    if (failed) msg += `, ${failed} failed`;
    successToast(msg + '.');
    setTimeout(() => { window.location.href = '{{ route("meal.checkout.multi") }}'; }, 1300);
}

/* ----- Add a food into a suggested-plan group (keyword chips + pagination) ----- */
let setupAddCtx        = { di:null, gi:null };
let setupAddMealType   = null;
let setupSearchResults = {};

function openSetupAddItem(di, gi) {
    setupAddCtx = { di, gi };
    const g = setupPlan[di].groups[gi];
    setupAddMealType = g.meal_type_id;

    const d = new Date(setupPlan[di].date + 'T00:00:00').toLocaleDateString('en-US', { month:'short', day:'numeric' });
    document.getElementById('setup-add-meal-label').textContent = `${toTitleCase(g.meal_type)} · ${d}`;
    document.getElementById('setup-add-search').value = '';
    document.getElementById('setup-add-select-all').checked = false;
    document.getElementById('setup-add-results').innerHTML = '';
    document.getElementById('setup-add-pagination').innerHTML = '';

    loadSetupKeywords(g.meal_type_id);
    setupSearchFood(1);                 // initial: all foods for this meal type
    new bootstrap.Modal(document.getElementById('setupAddItemModal')).show();
}

async function loadSetupKeywords(mealTypeId) {
    const box = document.getElementById('setup-add-keywords');
    box.innerHTML = '<span class="text-muted small">Loading…</span>';
    try {
        const res = await axios.get(`/get/meal-keywords/${mealTypeId}`);
        const kws = res.data?.data || [];
        if (!kws.length) { box.innerHTML = '<span class="text-muted small">No keywords for this meal type.</span>'; return; }
        box.innerHTML = kws.map(k => `
            <label class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 p-2 fw-normal" style="cursor:pointer;">
                <input type="checkbox" class="setup-kw" value="${k.name}"> ${toTitleCase(k.name)}
            </label>`).join('');
    } catch (_) { box.innerHTML = '<span class="text-danger small">Failed to load keywords.</span>'; }
}

function setupSelectedTerms() {
    const checked = Array.from(document.querySelectorAll('.setup-kw:checked')).map(c => c.value);
    const typed   = (document.getElementById('setup-add-search').value || '').split(/[, ]+/).map(s => s.trim()).filter(Boolean);
    return [...new Set([...checked, ...typed])];
}

async function setupSearchFood(page = 1) {
    const box = document.getElementById('setup-add-results');
    box.innerHTML = '<div class="text-muted small">Searching…</div>';
    document.getElementById('setup-add-pagination').innerHTML = '';
    try {
        const res  = await axios.post('/search/products', { keywords: setupSelectedTerms(), meal_type_id: setupAddMealType, page });
        const pg   = res.data?.products || {};
        const list = pg.data || [];
        if (!list.length) { box.innerHTML = '<div class="text-muted small">No items found.</div>'; return; }

        setupSearchResults = {};
        box.innerHTML = list.map(p => {
            setupSearchResults[p.id] = p;
            const img    = p.image ? `/upload/product/small/${p.image}` : '/upload/no_image.jpg';
            const price  = (p.is_free || Number(p.price) === 0) ? 'Free' : `£${p.price}`;
            const cal    = p.nutrients?.calories ? `${p.nutrients.calories} ${p.nutrients.calories_unit||'kcal'}` : '';
            const client = p.client_info ? `${p.client_info.first_name||''} ${p.client_info.last_name||''}`.trim() : '';
            return `<div class="col-md-6">
                <button type="button" class="btn btn-outline-primary w-100 text-start d-flex align-items-center gap-2 setup-pick-food" data-id="${p.id}">
                    <img src="${img}" style="width:42px;height:42px;object-fit:cover;border-radius:8px;">
                    <span class="flex-grow-1"><span class="fw-semibold d-block">${toTitleCase(p.name)}</span>
                    <small class="text-muted">${cal}${cal && price ? ' · ' : ''}${price}${client ? ' · ' + toTitleCase(client) : ''}</small></span>
                </button></div>`;
        }).join('');

        setupRenderPagination(pg);
    } catch (_) { box.innerHTML = '<div class="text-danger small">Search failed. Please try again.</div>'; }
}

function setupRenderPagination(pg) {
    const ul   = document.getElementById('setup-add-pagination');
    const cur  = pg.current_page || 1;
    const last = pg.last_page || 1;
    if (last <= 1) { ul.innerHTML = ''; return; }
    let html = `<li class="page-item ${cur === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-pg="${cur - 1}">«</a></li>`;
    for (let i = 1; i <= last; i++) html += `<li class="page-item ${i === cur ? 'active' : ''}"><a class="page-link" href="#" data-pg="${i}">${i}</a></li>`;
    html += `<li class="page-item ${cur === last ? 'disabled' : ''}"><a class="page-link" href="#" data-pg="${cur + 1}">»</a></li>`;
    ul.innerHTML = html;
}

function setupPickFood(id) {
    const p = setupSearchResults[id];
    if (!p) return;
    const g = setupPlan[setupAddCtx.di].groups[setupAddCtx.gi];
    if (g.items.some(it => String(it.product.id) === String(id))) { errorToast('That item is already in this meal.'); return; }
    g.items.push({
        product:  { id: p.id, name: p.name, image: p.image, price: p.price },
        client:   p.client_info ? { id: p.client_info.id, name: `${p.client_info.first_name||''} ${p.client_info.last_name||''}`.trim() } : null,
        quantity: 1,
    });
    bootstrap.Modal.getInstance(document.getElementById('setupAddItemModal'))?.hide();
    renderSetupPlan();
    successToast(`${toTitleCase(p.name)} added to ${toTitleCase(g.meal_type)}.`);
}

function initSetupAddItem() {
    document.getElementById('setup-add-search')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); setupSearchFood(1); }
    });

    // keyword chip toggles → re-search + keep "select all" in sync
    document.getElementById('setup-add-keywords')?.addEventListener('change', e => {
        if (!e.target.classList.contains('setup-kw')) return;
        const all = Array.from(document.querySelectorAll('.setup-kw'));
        document.getElementById('setup-add-select-all').checked = all.length > 0 && all.every(c => c.checked);
        setupSearchFood(1);
    });

    document.getElementById('setup-add-select-all')?.addEventListener('change', e => {
        document.querySelectorAll('.setup-kw').forEach(c => { c.checked = e.target.checked; });
        setupSearchFood(1);
    });

    document.getElementById('setup-add-results')?.addEventListener('click', e => {
        const b = e.target.closest('.setup-pick-food');
        if (b) setupPickFood(parseInt(b.dataset.id));
    });

    document.getElementById('setup-add-pagination')?.addEventListener('click', e => {
        const a = e.target.closest('a[data-pg]');
        if (!a) return;
        e.preventDefault();
        const li = a.closest('.page-item');
        if (li.classList.contains('disabled') || li.classList.contains('active')) return;
        setupSearchFood(parseInt(a.dataset.pg));
    });
}

let mealLocations      = [];   // saved customer locations
let mealSetupLocations = {};   // { "date_mealTypeId": location_id } — session binding

const MODAL_ERROR_IDS = ['err-meal-type','err-meal-date','err-meal-time','err-location','err-quantity','err-general',
                         'err-loc-label','err-loc-country','err-loc-county','err-loc-city','err-loc-zip','err-loc-address1'];

function toTitleCase(s) {
    return (s || '').toString().toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
}
function setModalError(id, msg) {
    const el = document.getElementById(id);
    if (el) el.textContent = msg;
}
function clearModalErrors() {
    MODAL_ERROR_IDS.forEach(id => { const el = document.getElementById(id); if (el) el.textContent = ''; });
}

async function loadMealLocations(selectId) {
    try {
        const res = await axios.get('/get/my-meal-locations');
        if (res.data.status === 'success') {
            mealLocations = res.data.data || [];
            renderLocationOptions(selectId);
        }
    } catch (_) {}
}

function renderLocationOptions(selectId) {
    const sel = document.getElementById('modal-location');
    if (!sel) return;
    let html = '<option value="">Select a location</option>';
    mealLocations.forEach(l => {
        const place = l.city ? `${toTitleCase(l.label)} (${toTitleCase(l.city.name)})` : toTitleCase(l.label);
        html += `<option value="${l.id}"${String(l.id) === String(selectId) ? ' selected' : ''}>${place}</option>`;
    });
    html += '<option value="__add__">＋ Add new location…</option>';
    sel.innerHTML = html;
}

async function loadCountriesForLocation() {
    const cSel = document.getElementById('loc-country');
    if (cSel.options.length > 1) return;   // already loaded
    try {
        const res = await axios.get('/countries');
        (res.data.data || res.data || []).forEach(c => cSel.insertAdjacentHTML('beforeend', `<option value="${c.id}">${c.name}</option>`));
    } catch (_) {}
}

/* Auto-select + lock the location/time for an already-used (date, meal type) group */
function applyGroupBinding() {
    const locSel    = document.getElementById('modal-location');
    const note      = document.getElementById('location-bind-note');
    const timeInput = document.getElementById('modal-meal-time');
    if (!locSel) return;

    // reset to editable first
    locSel.disabled = false;
    if (note) note.style.display = 'none';
    if (timeInput) timeInput.readOnly = false;

    const mealTypeId = document.getElementById('modal-meal-type')?.value;
    const date       = document.getElementById('modal-meal-date')?.value;
    if (!mealTypeId || !date) return;

    const key = `${date}_${mealTypeId}`;

    const boundLoc = mealSetupLocations[key];
    if (boundLoc) {
        document.getElementById('add-location-box').style.display = 'none';
        locSel.value = String(boundLoc);
        locSel.disabled = true;
        if (note) {
            note.style.display = 'block';
            note.innerHTML = '<i class="mdi mdi-lock-outline me-1"></i>Auto-set — all items for this date &amp; meal type deliver to the same location.';
        }
    }

    const boundTime = customerMealTimes[key];
    if (boundTime && timeInput) { timeInput.value = boundTime; timeInput.readOnly = true; }
}

/* Pre-load existing cart groups so the binding survives a page reload */
async function preloadCartBindings() {
    try {
        const res  = await axios.get('/get/meal-cart');
        const cart = res.data?.data?.meal_cart || {};
        Object.keys(cart).forEach(date => {
            Object.keys(cart[date]).forEach(mealTypeId => {
                const first = Object.values(cart[date][mealTypeId])[0];
                if (!first) return;
                const key = `${date.slice(0, 10)}_${mealTypeId}`;
                if (first.meal_time)    customerMealTimes[key]  = first.meal_time.slice(0, 5);
                if (first.location?.id) mealSetupLocations[key] = first.location.id;
            });
        });
    } catch (_) {}
}

function initMealLocations() {
    const locSel = document.getElementById('modal-location');
    if (!locSel) return;

    loadMealLocations();
    preloadCartBindings();

    // reload locations + apply group binding whenever the Add-to-Plan modal opens
    document.getElementById('addMealPlanModal')?.addEventListener('show.bs.modal', () => {
        document.getElementById('add-location-box').style.display = 'none';
        clearModalErrors();
        loadMealLocations().then(applyGroupBinding);
    });

    // re-apply binding when the date or meal type changes inside the modal
    document.getElementById('modal-meal-type')?.addEventListener('change', applyGroupBinding);
    document.getElementById('modal-meal-date')?.addEventListener('change', applyGroupBinding);

    locSel.addEventListener('change', (e) => {
        const box = document.getElementById('add-location-box');
        if (e.target.value === '__add__') { box.style.display = 'block'; loadCountriesForLocation(); }
        else { box.style.display = 'none'; }
    });

    document.getElementById('loc-cancel')?.addEventListener('click', () => {
        document.getElementById('add-location-box').style.display = 'none';
        locSel.value = '';
    });

    document.getElementById('loc-country')?.addEventListener('change', async (e) => {
        const cy = document.getElementById('loc-county'), ci = document.getElementById('loc-city');
        ci.innerHTML = '<option value="">City</option>'; ci.disabled = true;
        cy.innerHTML = '<option value="">County</option>'; cy.disabled = true;
        if (!e.target.value) return;
        try {
            const res = await axios.get(`/counties/${e.target.value}`);
            (res.data.data || res.data || []).forEach(c => cy.insertAdjacentHTML('beforeend', `<option value="${c.id}">${c.name}</option>`));
            cy.disabled = false;
        } catch (_) {}
    });

    document.getElementById('loc-county')?.addEventListener('change', async (e) => {
        const ci = document.getElementById('loc-city');
        ci.innerHTML = '<option value="">City</option>'; ci.disabled = true;
        if (!e.target.value) return;
        try {
            const res = await axios.get(`/cities/${e.target.value}`);
            (res.data.data || res.data || []).forEach(c => ci.insertAdjacentHTML('beforeend', `<option value="${c.id}">${c.name}</option>`));
            ci.disabled = false;
        } catch (_) {}
    });

    document.getElementById('loc-save')?.addEventListener('click', async () => {
        const payload = {
            label:      document.getElementById('loc-label').value.trim(),
            address1:   document.getElementById('loc-address1').value.trim(),
            zip_code:   document.getElementById('loc-zip').value.trim(),
            country_id: document.getElementById('loc-country').value,
            county_id:  document.getElementById('loc-county').value,
            city_id:    document.getElementById('loc-city').value,
        };
        ['err-loc-label','err-loc-country','err-loc-county','err-loc-city','err-loc-zip','err-loc-address1'].forEach(id => setModalError(id, ''));
        let ok = true;
        if (!payload.label)      { setModalError('err-loc-label', 'Please enter a label.'); ok = false; }
        if (!payload.country_id) { setModalError('err-loc-country', 'Please select a country.'); ok = false; }
        if (!payload.county_id)  { setModalError('err-loc-county', 'Please select a county.'); ok = false; }
        if (!payload.city_id)    { setModalError('err-loc-city', 'Please select a city.'); ok = false; }
        if (!payload.zip_code)   { setModalError('err-loc-zip', 'Please enter a zip code.'); ok = false; }
        if (!payload.address1)   { setModalError('err-loc-address1', 'Please enter an address.'); ok = false; }
        if (!ok) return;
        try {
            showLoader();
            const res = await axios.post('/store/meal-location', payload);
            if (res.status === 201 && res.data.status === 'success') {
                successToast('Location added.');
                mealLocations.push(res.data.data);
                renderLocationOptions(res.data.data.id);
                document.getElementById('add-location-box').style.display = 'none';
                ['loc-label', 'loc-zip', 'loc-address1'].forEach(id => document.getElementById(id).value = '');
            } else {
                setModalError('err-loc-label', res.data.message || 'Failed to add location.');
            }
        } catch (err) {
            const errs = err.response?.data?.errors;
            if (errs) {
                const map = { label:'err-loc-label', country_id:'err-loc-country', county_id:'err-loc-county', city_id:'err-loc-city', zip_code:'err-loc-zip', address1:'err-loc-address1' };
                Object.keys(errs).forEach(f => setModalError(map[f] || 'err-loc-label', errs[f][0]));
            } else {
                setModalError('err-loc-label', err.response?.data?.message || 'Failed to add location.');
            }
        } finally { hideLoader(); }
    });
}
</script>
@endpush