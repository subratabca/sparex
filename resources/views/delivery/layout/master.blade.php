<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('backend/assets') }}/"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title')</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('backend/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/fonts/materialdesignicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/fonts/flag-icons.css') }}" />

    <!-- Menu waves for no-customizer fix -->
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/node-waves/node-waves.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('backend/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/swiper/swiper.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/select2/select2.css') }}"  />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/tagify/tagify.css') }}"  />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}"  />

    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/quill/typography.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/quill/katex.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/quill/editor.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" 
    />

    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/leaflet/leaflet.css') }}" />
    <script src="{{ asset('backend/assets/vendor/libs/leaflet/leaflet.js') }}"></script>

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/css/pages/cards-statistics.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/css/pages/cards-analytics.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/css/pages/page-profile.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('backend/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('backend/assets/js/config.js') }}"></script>

    <script src="{{ asset('backend/custom-js/axios.min.js') }}"></script>
    <link href="{{ asset('backend/custom-css/toastify.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('backend/custom-js/toastify-js.js') }}"></script>
    <script src="{{ asset('backend/custom-js/config.js') }}"></script>
  </head>

  <body>
    <div id="bouncing-loader" class="loading-spinner" style="display: none;">
      <div class="spinner-container">
        <div class="spinner-circle"></div>
        <div class="spinner-circle"></div>
        <div class="spinner-circle"></div>
      </div>
    </div>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            @include('delivery.layout.left-sidebar')

            <div class="layout-page">

                @include('delivery.layout.header')

                <div class="content-wrapper">

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">@yield('breadcum')</h4>
                        @yield('content')
                    </div>

                    @include('delivery.layout.footer')

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>

        <div class="drag-target"></div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('backend/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/js/menu.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('backend/assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/tagify/tagify.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/bloodhound/bloodhound.js') }}"></script>

    <script src="{{ asset('backend/assets/vendor/libs/quill/katex.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/quill/quill.js') }}"></script>

    <script src="{{ asset('backend/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/swiper/swiper.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script src="{{ asset('backend/assets/vendor/libs/cleavejs/cleave.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script>

    <script src="{{ asset('backend/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('backend/assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('backend/assets/js/form-basic-inputs.js') }}"></script>
    <script src="{{ asset('backend/assets/js/forms-selects.js') }}"></script>
    <script src="{{ asset('backend/assets/js/forms-tagify.js') }}"></script>
    <script src="{{ asset('backend/assets/js/forms-typeahead.js') }}"></script>
    <script src="{{ asset('backend/assets/js/forms-editors.js') }}"></script>
    <script src="{{ asset('backend/assets/js/app-user-view-account.js') }}"></script>
    <script src="{{ asset('backend/assets/js/pages-account-settings-account.js') }}"></script>
    <script src="{{ asset('backend/assets/js/dashboards-analytics.js') }}"></script>
    <script src="{{ asset('backend/assets/js/tables-datatables-basic.js') }}"></script>
    <script src="{{ asset('backend/assets/js/tables-datatables-advanced.js') }}"></script>
    <script src="{{ asset('backend/assets/js/modal-enable-otp.js') }}"></script>
    <script src="{{ asset('backend/assets/js/form-basic-inputs.js') }}"></script>

    <script src="{{ asset('backend/assets/js/maps-leaflet.js') }}"></script>

    {{-- ===== Pending Delivery Requests Popup ===== --}}
    <div class="modal fade" id="pendingDeliveryModal" tabindex="-1" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 overflow-hidden pd-modal">
                <div class="modal-header pd-header text-white border-0">
                    <div class="d-flex align-items-center gap-3">
                        <span class="pd-bell"><i class="mdi mdi-bell-ring-outline"></i></span>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">New Delivery Requests</h5>
                            <small class="opacity-75">Accept before another rider grabs it</small>
                        </div>
                    </div>
                    <span class="badge bg-white text-dark rounded-pill fs-6 px-3" id="pdCountBadge">0</span>
                </div>

                <div class="modal-body p-3 p-md-4 bg-light">
                    <div id="pdRequestList" class="d-flex flex-column gap-3"></div>
                    <div id="pdEmptyState" class="text-center text-muted py-5 d-none">
                        <i class="mdi mdi-check-decagram-outline" style="font-size:3rem;color:#22c55e;"></i>
                        <p class="mb-0 mt-2">All caught up — no pending requests right now.</p>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Maybe later
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
(function () {
    let pdModalInstance = null;
    let pdPollTimer     = null;   // refresh timer while the popup is open
    let pdBgTimer       = null;   // always-on background poll (detects new requests)
    let pdSeenIds       = new Set();   // ledger ids already shown to this rider
    let pdMaps          = {};     // ledgerId -> Leaflet map instance

    const PD_BG_POLL_MS = 10000;  // how often to check for new delivery requests

    document.addEventListener('DOMContentLoaded', async function () {
        const requests = await fetchPendingDeliveries();
        if (requests.length > 0) {
            renderPendingDeliveries(requests);
            openPendingModal();
        }
        // Seed "seen" with whatever is already pending so they don't re-pop,
        // then start the background poll so new requests appear without a refresh.
        pdSeenIds = new Set(requests.map(r => r.delivery_charge_ledger_id));
        clearInterval(pdBgTimer);
        pdBgTimer = setInterval(pollPendingInBackground, PD_BG_POLL_MS);
    });

    // Background poll: while the popup is closed, watch for newly available
    // delivery requests (e.g. a client just accepted an order) and pop the modal
    // automatically. While the popup is open, its own pdPollTimer keeps it in sync.
    async function pollPendingInBackground() {
        const el = document.getElementById('pendingDeliveryModal');
        if (el && el.classList.contains('show')) return;   // open → handled by pdPollTimer

        const requests   = await fetchPendingDeliveries();
        const currentIds = requests.map(r => r.delivery_charge_ledger_id);
        const newIds     = currentIds.filter(id => !pdSeenIds.has(id));

        if (newIds.length > 0) {
            renderPendingDeliveries(requests);
            openPendingModal();
        }
        pdSeenIds = new Set(currentIds);
    }

    async function fetchPendingDeliveries() {
        try {
            const res = await axios.get('/delivery/get/pending-deliveries');
            if (res.status === 200 && res.data.status === 'success') {
                return res.data.data || [];
            }
        } catch (e) { /* silent — popup is non-blocking */ }
        return [];
    }

    function openPendingModal() {
        const el = document.getElementById('pendingDeliveryModal');
        pdModalInstance = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        pdModalInstance.show();

        // Maps are built while the modal is still hidden (0×0) — recalc once visible
        if (!el.dataset.pdShownBound) {
            el.dataset.pdShownBound = '1';
            el.addEventListener('shown.bs.modal', () => {
                Object.values(pdMaps).forEach(m => { try { m.invalidateSize(); } catch (e) {} });
            });
        }

        // Poll every 25s while open so accepted-by-others requests vanish
        clearInterval(pdPollTimer);
        pdPollTimer = setInterval(refreshPendingDeliveries, 25000);

        el.addEventListener('hidden.bs.modal', () => clearInterval(pdPollTimer), { once: true });
    }

    async function refreshPendingDeliveries() {
        const requests = await fetchPendingDeliveries();
        renderPendingDeliveries(requests);
        // Keep "seen" current while the popup is open so these don't re-pop after close.
        pdSeenIds = new Set(requests.map(r => r.delivery_charge_ledger_id));
        if (requests.length === 0) {
            clearInterval(pdPollTimer);
            setTimeout(() => pdModalInstance?.hide(), 1500);
        }
    }

    function fmtCurrency(v) {
        return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' })
            .format(parseFloat(v) || 0);
    }

    function renderPendingDeliveries(requests) {
        const list  = document.getElementById('pdRequestList');
        const empty = document.getElementById('pdEmptyState');
        document.getElementById('pdCountBadge').textContent = requests.length;

        if (!requests.length) {
            list.innerHTML = '';
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');

        list.innerHTML = requests.map(r => {
            const id = r.delivery_charge_ledger_id;
            const rLat = parseFloat(r.restaurant?.latitude), rLng = parseFloat(r.restaurant?.longitude);
            const cLat = parseFloat(r.shipping?.latitude),  cLng = parseFloat(r.shipping?.longitude);
            const hasR = !isNaN(rLat) && !isNaN(rLng);
            const hasC = !isNaN(cLat) && !isNaN(cLng);

            let mapHtml;
            if (hasR || hasC) {
                const dir = (hasR && hasC)
                    ? `<a href="https://www.google.com/maps/dir/?api=1&origin=${rLat},${rLng}&destination=${cLat},${cLng}"
                          target="_blank" class="btn btn-sm btn-light rounded-pill"
                          style="position:absolute;top:8px;right:8px;z-index:500;box-shadow:0 1px 4px rgba(0,0,0,.2);"
                          title="Get directions"><i class="mdi mdi-directions"></i></a>`
                    : '';
                mapHtml = `<div class="pd-map-wrap">
                        <div id="pdMap-${id}" class="pd-map"></div>
                        <div class="pd-map-legend">
                            <span><i class="mdi mdi-storefront text-danger"></i>Restaurant</span>
                            <span><i class="mdi mdi-home-map-marker text-success"></i>Customer</span>
                        </div>${dir}
                    </div>`;
            } else {
                mapHtml = `<div class="pd-map-wrap"><div class="pd-map-empty">
                        <i class="mdi mdi-map-marker-off-outline" style="font-size:2rem;"></i>
                        <div class="small mt-1">Map not available for this order</div>
                    </div></div>`;
            }

            return `
            <div class="pd-card" data-ledger-id="${id}">
                <div class="pd-card-top">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge pd-track"><i class="mdi mdi-pound"></i>${r.order_tracking || 'N/A'}</span>
                        <span class="badge bg-warning text-dark">${r.meal_type}</span>
                        <span class="text-muted small"><i class="mdi mdi-calendar-outline me-1"></i>${r.delivery_date}</span>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-lg-5 pd-addr-col">
                        <div class="pd-addr">
                            <div class="pd-addr-head text-danger"><i class="mdi mdi-storefront-outline"></i> Pickup — Restaurant</div>
                            <div class="fw-semibold">${r.restaurant.name}</div>
                            ${r.restaurant.mobile ? `<div class="small text-muted"><i class="mdi mdi-phone-outline me-1"></i>${r.restaurant.mobile}</div>` : ''}
                            <div class="small text-muted">${r.restaurant.address || 'Address not available'}</div>
                        </div>
                        <div class="pd-addr">
                            <div class="pd-addr-head text-success"><i class="mdi mdi-map-marker-radius-outline"></i> Drop-off — Customer</div>
                            ${r.shipping.name ? `<div class="fw-semibold">${r.shipping.name}</div>` : ''}
                            ${r.shipping.phone ? `<div class="small text-muted"><i class="mdi mdi-phone-outline me-1"></i>${r.shipping.phone}</div>` : ''}
                            <div class="small text-muted">${r.shipping.address || 'Address not available'}</div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        ${mapHtml}
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                    <div class="d-flex gap-2">
                        <div class="pd-stat">
                            <div class="pd-stat-label"><i class="mdi mdi-map-marker-distance"></i> Distance</div>
                            <div class="pd-stat-value">${r.distance_km ?? '0'} km</div>
                        </div>
                        <div class="pd-stat pd-stat-charge">
                            <div class="pd-stat-label"><i class="mdi mdi-cash"></i> Delivery Charge</div>
                            <div class="pd-stat-value">${fmtCurrency(r.delivery_charge)}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="${r.notification_id ? `/delivery/view/notification/${r.notification_id}` : `/delivery/meal-order/details/${r.delivery_charge_ledger_id}`}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Details</a>
                        <button class="btn btn-sm pd-accept-btn rounded-pill px-4"
                                onclick="acceptPendingDelivery(${r.delivery_charge_ledger_id}, this)">
                            <i class="mdi mdi-check-circle-outline me-1"></i>Accept
                        </button>
                    </div>
                </div>
            </div>`;
        }).join('');

        buildCardMaps(requests);
    }

    // Build a small route map inside each request card
    function buildCardMaps(requests) {
        // tear down any maps from a previous render
        Object.values(pdMaps).forEach(m => { try { m.remove(); } catch (e) {} });
        pdMaps = {};

        requests.forEach(r => {
            const rLat = parseFloat(r.restaurant?.latitude), rLng = parseFloat(r.restaurant?.longitude);
            const cLat = parseFloat(r.shipping?.latitude),  cLng = parseFloat(r.shipping?.longitude);
            const hasR = !isNaN(rLat) && !isNaN(rLng);
            const hasC = !isNaN(cLat) && !isNaN(cLng);
            if (!hasR && !hasC) return;

            const el = document.getElementById('pdMap-' + r.delivery_charge_ledger_id);
            if (!el || typeof L === 'undefined') return;

            const map = L.map(el, { scrollWheelZoom: false, attributionControl: false });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

            const pin = (color, icon) => L.divIcon({
                className: '',
                html: `<div style="background:${color};width:30px;height:30px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.35);"><i class="mdi ${icon}" style="transform:rotate(45deg);color:#fff;font-size:.95rem;"></i></div>`,
                iconSize: [30, 30], iconAnchor: [15, 30], popupAnchor: [0, -30]
            });

            const bounds = [];
            if (hasR) { L.marker([rLat, rLng], { icon: pin('#ef4444', 'mdi-storefront') }).addTo(map).bindPopup('<b>Restaurant</b>'); bounds.push([rLat, rLng]); }
            if (hasC) { L.marker([cLat, cLng], { icon: pin('#22c55e', 'mdi-home-map-marker') }).addTo(map).bindPopup('<b>Customer</b>'); bounds.push([cLat, cLng]); }

            if (hasR && hasC) {
                L.polyline([[rLat, rLng], [cLat, cLng]], { color: '#6366f1', weight: 3, dashArray: '6,8' }).addTo(map);
                map.fitBounds(bounds, { padding: [30, 30] });
            } else {
                map.setView(bounds[0], 14);
            }

            pdMaps[r.delivery_charge_ledger_id] = map;
            setTimeout(() => map.invalidateSize(), 250);
        });
    }

    // Exposed globally for the inline onclick
    window.acceptPendingDelivery = async function (ledgerId, btn) {
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Accepting...';
        try {
            const res = await axios.post('/delivery/accept/meal/delivery', {
                delivery_charge_ledger_id: ledgerId
            });
            if (res.status === 200 && res.data.status === 'success') {
                successToast(res.data.message || 'Delivery accepted!');
                const card = document.querySelector(`.pd-card[data-ledger-id="${ledgerId}"]`);
                if (card) {
                    card.classList.add('pd-card-accepted');
                    setTimeout(() => { card.remove(); afterCardRemoved(); }, 400);
                }
            } else {
                errorToast(res.data.message || 'Failed to accept delivery.');
                btn.disabled = false; btn.innerHTML = original;
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'This order may have already been accepted.';
            errorToast(msg);
            // Likely taken by someone else — refresh the list
            refreshPendingDeliveries();
        }
    };

    function afterCardRemoved() {
        const remaining = document.querySelectorAll('#pdRequestList .pd-card').length;
        document.getElementById('pdCountBadge').textContent = remaining;
        if (remaining === 0) {
            document.getElementById('pdEmptyState').classList.remove('d-none');
            clearInterval(pdPollTimer);
            setTimeout(() => pdModalInstance?.hide(), 1200);
        }
    }
})();
</script>

{{-- ===== Order Ready-for-Pickup live popup (no page refresh) ===== --}}
<div class="modal fade" id="pickupReadyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 overflow-hidden pu-modal">
            <div class="modal-header pu-header text-white border-0">
                <div class="d-flex align-items-center gap-3">
                    <span class="pu-bell"><i class="mdi mdi-package-variant-closed-check"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Order Ready for Pickup</h5>
                        <small class="opacity-75">The restaurant has prepared an order for you</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4 bg-light">
                <div id="puBody" class="d-flex flex-column gap-3"></div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <a href="#" id="puDetailsBtn" class="btn pu-accept-btn rounded-pill px-4">
                    <i class="mdi mdi-eye-outline me-1"></i>View Details
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const PU_POLL_MS  = 12000;            // how often to check for ready-for-pickup orders
    const PU_SEEN_KEY = 'puSeenPickupIds';
    let   puModalInstance = null;
    let   puQueue = [];                   // pickups waiting to be shown one-by-one

    function puLoadSeen() {
        try { return new Set(JSON.parse(localStorage.getItem(PU_SEEN_KEY) || '[]')); }
        catch (e) { return new Set(); }
    }
    function puSaveSeen(set) {
        try {
            // keep the list bounded so it can't grow forever
            const arr = Array.from(set).slice(-50);
            localStorage.setItem(PU_SEEN_KEY, JSON.stringify(arr));
        } catch (e) {}
    }
    let puSeen = puLoadSeen();

    document.addEventListener('DOMContentLoaded', function () {
        pollPickupNotifications();
        setInterval(pollPickupNotifications, PU_POLL_MS);
    });

    async function pollPickupNotifications() {
        let res;
        try {
            res = await axios.get('/delivery/poll/pickup-notifications');
        } catch (e) { return; /* silent — non-blocking */ }

        if (res.status !== 200 || res.data.status !== 'success') return;

        // 1) Live-update the bell count (no refresh)
        updateNotificationBadges(res.data.unread_count);

        // 2) Detect brand-new ready-for-pickup notifications and pop them
        const pickups = res.data.pickups || [];
        const fresh   = pickups.filter(p => !puSeen.has(p.id));

        if (fresh.length > 0) {
            fresh.forEach(p => puSeen.add(p.id));
            puSaveSeen(puSeen);
            puQueue.push(...fresh);
            // refresh the dropdown list so the new item is there without a reload
            if (typeof refreshNotificationDropdown === 'function') refreshNotificationDropdown();
            showNextPickup();
        }
    }

    function updateNotificationBadges(count) {
        const c = (count === undefined || count === null) ? 0 : count;
        const badge  = document.getElementById('notificationCount');
        const badge1 = document.getElementById('notificationCount1');
        if (badge)  badge.innerText  = c;
        if (badge1) badge1.innerText = c;
    }

    function fmtCurrency(v) {
        if (v === null || v === undefined) return '';
        return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(parseFloat(v) || 0);
    }

    function showNextPickup() {
        const el = document.getElementById('pickupReadyModal');
        if (!el) return;
        // don't stack popups — wait until the current one is closed
        if (el.classList.contains('show')) return;
        const p = puQueue.shift();
        if (!p) return;

        const itemsHtml = (p.items && p.items.length)
            ? p.items.map(i => `<li>${i.product_name}${i.quantity ? ` <span class="text-muted">× ${i.quantity}</span>` : ''}</li>`).join('')
            : '<li class="text-muted">No items listed</li>';

        document.getElementById('puBody').innerHTML = `
            <div class="pu-time-card">
                <div class="pu-time-label"><i class="mdi mdi-clock-time-four-outline me-1"></i>Scheduled Pickup Time</div>
                <div class="pu-time-value">${p.pickup_time || 'ASAP'}</div>
            </div>

            <div class="pu-info">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    ${p.order_tracking ? `<span class="badge pu-track"><i class="mdi mdi-pound"></i>${p.order_tracking}</span>` : ''}
                    ${p.meal_type ? `<span class="badge bg-warning text-dark">${p.meal_type}</span>` : ''}
                    ${p.delivery_date ? `<span class="text-muted small"><i class="mdi mdi-calendar-outline me-1"></i>${p.delivery_date}</span>` : ''}
                </div>

                <div class="pu-row"><i class="mdi mdi-storefront-outline text-danger"></i>
                    <div>
                        <div class="fw-semibold">${p.client_name || 'Restaurant'}</div>
                        ${p.client_mobile ? `<div class="small text-muted"><i class="mdi mdi-phone-outline me-1"></i>${p.client_mobile}</div>` : ''}
                        ${p.pickup_address ? `<div class="small text-muted">${p.pickup_address}</div>` : ''}
                    </div>
                </div>

                ${(p.dropoff_address || p.dropoff_name) ? `
                <div class="pu-row"><i class="mdi mdi-map-marker-radius-outline text-success"></i>
                    <div>
                        ${p.dropoff_name ? `<div class="fw-semibold">${p.dropoff_name}</div>` : ''}
                        ${p.dropoff_phone ? `<div class="small text-muted"><i class="mdi mdi-phone-outline me-1"></i>${p.dropoff_phone}</div>` : ''}
                        ${p.dropoff_address ? `<div class="small text-muted">${p.dropoff_address}</div>` : ''}
                    </div>
                </div>` : ''}

                <div class="d-flex flex-wrap gap-2 mt-2">
                    ${p.meal_time ? `<span class="pu-chip"><i class="mdi mdi-silverware-fork-knife"></i> ${p.meal_time}</span>` : ''}
                    ${p.distance_km != null ? `<span class="pu-chip"><i class="mdi mdi-map-marker-distance"></i> ${p.distance_km} km</span>` : ''}
                    ${p.delivery_charge != null ? `<span class="pu-chip pu-chip-charge"><i class="mdi mdi-cash"></i> ${fmtCurrency(p.delivery_charge)}</span>` : ''}
                </div>

                <div class="pu-items mt-3">
                    <div class="pu-items-head"><i class="mdi mdi-format-list-bulleted me-1"></i>Items</div>
                    <ul class="mb-0">${itemsHtml}</ul>
                </div>
            </div>`;

        const detailsBtn = document.getElementById('puDetailsBtn');
        detailsBtn.href = '/delivery/view/notification/' + p.id;

        const elModal = document.getElementById('pickupReadyModal');
        puModalInstance = bootstrap.Modal.getInstance(elModal) || new bootstrap.Modal(elModal);

        if (!elModal.dataset.puHiddenBound) {
            elModal.dataset.puHiddenBound = '1';
            // when one popup closes, show the next queued pickup (if any)
            elModal.addEventListener('hidden.bs.modal', () => setTimeout(showNextPickup, 300));
        }
        puModalInstance.show();
    }
})();
</script>
  </body>
</html>

<script>
document.addEventListener("DOMContentLoaded", async function () {
  showLoader();
  try {
      const response = await axios.get('/delivery/limited/notification/list');

      if (response.status === 200) {
          const userData = response.data.data;
          const unreadNotifications = response.data.unreadNotifications;
          const readNotifications = response.data.readNotifications;

          const notificationCount = unreadNotifications.length || '0';
          document.getElementById('notificationCount').innerText = notificationCount;
          document.getElementById('notificationCount1').innerText = notificationCount;
          displayNotifications(unreadNotifications, readNotifications);

          let firstName = userData['firstName'];;
          let lastName = userData['lastName'];
          let fullName = lastName ? `${firstName} ${lastName}` : firstName;
          
          document.getElementById('login-user-role-name').innerText = userData.role || 'Account';
          document.getElementById('login-user-name').innerText = fullName || 'No User';
          document.getElementById('login-user-img').src = userData['image'] ? "/upload/delivery-profile/small/" + userData['image'] : "/upload/no_image.jpg";

          document.getElementById('login-user-img1').src = userData['image'] ? "/upload/delivery-profile/small/" + userData['image'] : "/upload/no_image.jpg";

      }
  } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

// Re-fetch the limited notification list and re-render the bell dropdown.
// Used by the ready-for-pickup poller so a new notification shows in the
// dropdown without a page refresh.
async function refreshNotificationDropdown() {
    try {
        const response = await axios.get('/delivery/limited/notification/list');
        if (response.status === 200) {
            displayNotifications(
                response.data.unreadNotifications || [],
                response.data.readNotifications || []
            );
        }
    } catch (error) { /* silent — non-blocking */ }
}


function displayNotifications(unreadNotifications, readNotifications) {
    const notificationsContainer = document.querySelector('.dropdown-notifications-list ul');
    let notificationsHTML = '';

    if ((unreadNotifications && unreadNotifications.length === 0) &&
        (readNotifications && readNotifications.length === 0)) {
        notificationsContainer.innerHTML = '<li class="list-group-item">No notifications</li>';
        return;
    }

    function getNotificationLink(notification) {
        // Parse the notification data
        let notificationData = notification.data;
        
        // If data is a string, parse it as JSON
        if (typeof notificationData === 'string') {
            try {
                notificationData = JSON.parse(notificationData);
            } catch (e) {
                console.error('Error parsing notification data:', e);
                return '#';
            }
        }
        
        // Check if we have the nested data structure
        const nestedData = notificationData.data || {};
        
        // For delivery notifications, use the notification ID and delivery_charge_ledger_id
        if (nestedData.delivery_charge_ledger_id) {
            return `/delivery/view/notification/${notification.id}`;
        } else if (notificationData.delivery_charge_ledger_id) {
            return `/delivery/view/notification/${notification.id}?delivery_charge_ledger_id=${notificationData.delivery_charge_ledger_id}`;
        }
        
        return '#';
    }

    function getNotificationTitle(notification) {
        // Parse the notification data
        let notificationData = notification.data;
        
        // If data is a string, parse it as JSON
        if (typeof notificationData === 'string') {
            try {
                notificationData = JSON.parse(notificationData);
            } catch (e) {
                console.error('Error parsing notification data:', e);
                return 'Notification';
            }
        }
        
        // Return the title from notification data
        // The title is at the top level of the notification.data object
        return notificationData.title || 'Notification';
    }

    function formatNotificationTime(createdAt) {
        try {
            const date = new Date(createdAt);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) {
                return 'Just now';
            } else if (diffMins < 60) {
                return `${diffMins} min${diffMins === 1 ? '' : 's'} ago`;
            } else if (diffHours < 24) {
                return `${diffHours} hour${diffHours === 1 ? '' : 's'} ago`;
            } else if (diffDays < 7) {
                return `${diffDays} day${diffDays === 1 ? '' : 's'} ago`;
            } else {
                return date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        } catch (e) {
            return 'Recently';
        }
    }

    if (unreadNotifications && unreadNotifications.length > 0) {
        unreadNotifications.forEach(notification => {
            const link = getNotificationLink(notification);
            const title = getNotificationTitle(notification);
            const timeAgo = formatNotificationTime(notification.created_at);
            
            notificationsHTML += `
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                    <div class="d-flex gap-2">
                        <a href="${link}" class="d-flex gap-2 w-100 text-decoration-none">
                            <div class="d-flex flex-column flex-grow-1 overflow-hidden w-px-200">
                                <h6 class="mb-1 text-truncate"><strong>${title}</strong></h6>
                                <small class="text-truncate text-muted">${timeAgo}</small>
                            </div>
                        </a>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <span class="badge bg-danger">New</span>
                        </div>
                    </div>
                    <button class="delete-notification-btn btn btn-danger btn-sm mt-2" onclick="deleteNotification('${notification.id}')">Delete</button>
                </li>`;
        });
    }

    if (readNotifications && readNotifications.length > 0) {
        readNotifications.forEach(notification => {
            const link = getNotificationLink(notification);
            const title = getNotificationTitle(notification);
            const timeAgo = formatNotificationTime(notification.created_at);
            
            notificationsHTML += `
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                    <div class="d-flex gap-2">
                        <a href="${link}" class="d-flex gap-2 w-100 text-decoration-none">
                            <div class="d-flex flex-column flex-grow-1 overflow-hidden w-px-200">
                                <h6 class="mb-1 text-truncate">${title}</h6>
                                <small class="text-truncate text-muted">${timeAgo}</small>
                            </div>
                        </a>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <small class="text-muted">Read</small>
                        </div>
                    </div>
                    <button class="delete-notification-btn btn btn-danger btn-sm mt-2" onclick="deleteNotification('${notification.id}')">Delete</button>
                </li>`;
        });
    }

    notificationsContainer.innerHTML = notificationsHTML;
}

async function deleteNotification(notificationId) {
    try {
        const response = await axios.delete(`/delivery/delete/notification/${notificationId}`);

        if (response.status === 200) {
            successToast(response.data.message || 'Request success');
            window.location.reload();
        } else {
            errorToast(response.data.message || 'Failed to delete notification');
        }
    } catch (error) {
        if (error.response) {
            const status = error.response.status;
            const message = error.response.data.message || 'An unexpected error occurred';

            if (status === 404) {
                if (error.response.data.status === 'failed to fetch user') {
                    errorToast(error.response.data.message || 'User not found');
                } else if (error.response.data.status === 'failed') {
                    errorToast(error.response.data.message || 'Notification not found');
                } else {
                    errorToast(message); 
                }
            } else if (status === 500) {
                errorToast('Server error: ' + message);
            } else {
                errorToast(message); 
            }
        } else {
            errorToast('Error: ' + error.message); 
        }
    }
}

async function markAllAsRead() {
      try {
          const response = await axios.get('/delivery/markAsRead');

          if (response.status === 200 && response.data.status === 'success') {
              document.getElementById('notificationCount').innerText = response.data.unreadCount === 0 ? '0 New' : `${response.data.unreadCount} New`;

              const notificationItems = document.querySelectorAll('.dropdown-notifications-actions small');
              notificationItems.forEach(item => {
                  item.innerText = 'Read';
                  item.classList.remove('text-muted');
                  item.classList.add('text-success');
              });

              successToast(response.data.message || 'Notifications marked as read');
              window.location.reload();
          }
      } catch (error) {
          if (error.response) {
              const status = error.response.status;
              const message = error.response.data.message || 'An unexpected error occurred';

              if (status === 400) {
                  errorToast(message || 'Bad Request');
              } else if (status === 404) {
                  errorToast(message || 'Not Found');
              } else if (status === 500) {
                  errorToast(message || 'Server Error');
              } else {
                  errorToast(message);
              }
          } else {
              errorToast('No response received from the server.');
          }
      }
}

function handleError(error) {
    if (error.response) {
        const status = error.response.status;
        const message = error.response.data.message || 'An unexpected error occurred';

        if (status === 400) {
            errorToast(message || 'Bad Request');
        } else if (status === 500) {
            errorToast(message || 'Server Error');
        } else {
            errorToast(message);
        }
    }
}
</script>


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



.pd-modal { border-radius: 18px; }
.pd-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 60%, #ec4899 100%);
    padding: 1.1rem 1.5rem;
}
.pd-bell {
    width: 46px; height: 46px; border-radius: 50%;
    background: rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center;
    font-size: 1.5rem; animation: pdRing 1.6s ease-in-out infinite;
}
@keyframes pdRing {
    0%,100% { transform: rotate(0); }
    20% { transform: rotate(12deg); } 40% { transform: rotate(-10deg); }
    60% { transform: rotate(6deg); } 80% { transform: rotate(-4deg); }
}
.pd-card {
    background: #fff; border: 1px solid #eef0f4; border-radius: 14px; padding: 1rem 1.1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,.04); transition: transform .35s ease, opacity .35s ease;
}
.pd-card-top { border-bottom: 1px dashed #eef0f4; padding-bottom: .6rem; }
.pd-card-accepted { transform: translateX(40px); opacity: 0; }
.pd-track { background:#eef2ff; color:#6366f1; }
.pd-addr { background:#f8f9fb; border-radius:10px; padding:.7rem .85rem; }
/* Left column: stack the two address boxes and let them evenly fill the map height */
.pd-addr-col { display:flex; flex-direction:column; gap:.6rem; }
.pd-addr-col .pd-addr { flex:1 1 0; }
.pd-addr-head { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; margin-bottom:.25rem; }
.pd-stat {
    background:#f1f5f9; border-radius:10px; padding:.45rem .9rem; text-align:center; min-width:110px;
}
.pd-stat-charge { background:#ecfdf5; }
.pd-stat-label { font-size:.7rem; color:#64748b; font-weight:600; }
.pd-stat-value { font-size:1.05rem; font-weight:800; color:#0f172a; }
.pd-accept-btn { background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border:none; font-weight:600; }
.pd-accept-btn:hover { filter:brightness(.95); color:#fff; }
.pd-accept-btn:disabled { opacity:.7; }

/* Route map inside the request card */
.pd-map-wrap { position:relative; height:100%; min-height:230px; border-radius:10px; overflow:hidden; border:1px solid #eef0f4; }
.pd-map { width:100%; height:100%; min-height:230px; }
.pd-map-empty {
    height:100%; min-height:230px; background:#f8f9fb; color:#94a3b8;
    display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:1rem;
}
.pd-map-legend {
    position:absolute; bottom:8px; left:8px; z-index:500;
    background:rgba(255,255,255,.92); border-radius:8px; padding:.25rem .55rem;
    font-size:.7rem; font-weight:600; display:flex; gap:.7rem; box-shadow:0 1px 4px rgba(0,0,0,.15);
}
.pd-map-legend span { display:flex; align-items:center; gap:.2rem; }

/* ===== Order Ready-for-Pickup popup ===== */
.pu-modal { border-radius: 18px; }
.pu-header {
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 60%, #4ade80 100%);
    padding: 1.1rem 1.5rem;
}
.pu-bell {
    width: 46px; height: 46px; border-radius: 50%;
    background: rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center;
    font-size: 1.5rem; animation: pdRing 1.6s ease-in-out infinite;
}
.pu-time-card {
    background: linear-gradient(135deg,#ecfdf5,#d1fae5); border:1px solid #a7f3d0;
    border-radius: 14px; padding: 1rem 1.2rem; text-align:center;
}
.pu-time-label { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#047857; }
.pu-time-value { font-size:1.5rem; font-weight:800; color:#065f46; margin-top:.15rem; }
.pu-info { background:#fff; border:1px solid #eef0f4; border-radius:14px; padding:1rem 1.1rem; box-shadow:0 2px 10px rgba(0,0,0,.04); }
.pu-track { background:#eef2ff; color:#6366f1; }
.pu-row { display:flex; gap:.6rem; align-items:flex-start; padding:.5rem 0; border-top:1px dashed #eef0f4; }
.pu-row:first-of-type { border-top:none; }
.pu-row > i { font-size:1.2rem; margin-top:.15rem; }
.pu-chip { background:#f1f5f9; border-radius:20px; padding:.3rem .8rem; font-size:.78rem; font-weight:600; color:#0f172a; }
.pu-chip-charge { background:#ecfdf5; color:#047857; }
.pu-items { background:#f8f9fb; border-radius:10px; padding:.7rem .85rem; }
.pu-items-head { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; color:#64748b; margin-bottom:.3rem; }
.pu-items ul { padding-left:1.1rem; }
.pu-accept-btn { background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border:none; font-weight:600; }
.pu-accept-btn:hover { filter:brightness(.95); color:#fff; }

</style>