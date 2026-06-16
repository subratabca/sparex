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

    <script src="{{ asset('common/custom-js/axios.min.js') }}"></script>
    <link href="{{ asset('common/custom-css/toastify.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('common/custom-js/toastify-js.js') }}"></script>
    <script src="{{ asset('common/custom-js/config.js') }}"></script>
    <script src="https://js.stripe.com/v3/"></script>


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

/* ===== New meal-order popup ===== */
.no-modal { border-radius: 18px; }
.no-header { background: linear-gradient(135deg,#6366f1 0%,#8b5cf6 60%,#ec4899 100%); padding: 1.1rem 1.5rem; }
.no-bell {
  width: 46px; height: 46px; border-radius: 50%; background: rgba(255,255,255,.2);
  display:flex; align-items:center; justify-content:center; font-size: 1.5rem;
}
.no-summary { background:#fff; border:1px solid #eef0f4; border-radius:14px; padding:1rem 1.1rem; box-shadow:0 2px 10px rgba(0,0,0,.04); }
.no-track { background:#eef2ff; color:#6366f1; }
.no-chip { background:#f1f5f9; border-radius:20px; padding:.3rem .8rem; font-size:.8rem; font-weight:600; color:#0f172a; }
.no-chip-amt { background:#ecfdf5; color:#047857; }
.no-items { display:flex; flex-direction:column; gap:.5rem; }
.no-item { display:flex; gap:.7rem; align-items:center; background:#fff; border:1px solid #eef0f4; border-radius:12px; padding:.6rem .8rem; }
.no-item-thumb {
  width:46px; height:46px; border-radius:10px; background:#f1f5f9; flex-shrink:0;
  display:flex; align-items:center; justify-content:center; font-size:1.4rem; color:#94a3b8; overflow:hidden;
}
.no-item-thumb img { width:100%; height:100%; object-fit:cover; }
.no-accept-btn { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; font-weight:600; }
.no-accept-btn:hover { filter:brightness(.95); color:#fff; }
</style>

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

            @include('client.layout.left-sidebar')

            <div class="layout-page">

                @include('client.layout.header')

                <div class="content-wrapper">

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">@yield('breadcum')</h4>
                        @yield('content')
                    </div>

                    @include('client.layout.footer')

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

    <!-- Rider-accepted popup (auto-shows when a rider accepts one of this client's deliveries) -->
    <div class="modal fade" id="riderAcceptedModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="mdi mdi-bike-fast me-2"></i>Rider Accepted Your Delivery</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex align-items-center gap-3 mb-3">
              <img id="raRiderImg" src="/upload/no_image.jpg" alt="Rider"
                   class="rounded-circle" style="width:64px;height:64px;object-fit:cover;border:2px solid #e9ecef;">
              <div>
                <div class="fw-semibold fs-5" id="raRiderName">Rider</div>
                <div class="text-muted small"><i class="mdi mdi-phone-outline me-1"></i><span id="raRiderMobile">N/A</span></div>
              </div>
            </div>
            <div class="border-top pt-3 small">
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Order Number</span>
                <span class="fw-semibold" id="raOrderNumber">N/A</span>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-muted">Tracking</span>
                <span class="fw-semibold" id="raTracking">N/A</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <a href="#" id="raDetailsBtn" class="btn btn-success">Details</a>
          </div>
        </div>
      </div>
    </div>

    <script>
    (function () {
        const RA_POLL_MS = 10000;                 // how often to check for new acceptances
        const RA_SEEN_KEY = 'raSeenAcceptances';  // dedupe across page navigations (per tab)
        let raPollTimer = null;
        let raModal = null;
        let raLastCount = null;   // last unread count we rendered to the bell badge

        function raGetSeen() {
            try { return new Set(JSON.parse(sessionStorage.getItem(RA_SEEN_KEY) || '[]')); }
            catch (e) { return new Set(); }
        }
        function raAddSeen(id) {
            const s = raGetSeen(); s.add(id);
            sessionStorage.setItem(RA_SEEN_KEY, JSON.stringify([...s]));
        }

        async function raFetch() {
            try {
                const res = await axios.get('/restaurant/get/delivery-acceptances');
                if (res.status === 200 && res.data.status === 'success') return res.data;
            } catch (e) { /* silent — popup is non-blocking */ }
            return null;
        }

        // Live-update the bell badge count (no page refresh).
        function raUpdateBadge(count) {
            const c = (count === undefined || count === null) ? 0 : count;
            const badge  = document.getElementById('notificationCount');
            const badge1 = document.getElementById('notificationCount1');
            if (badge)  badge.innerText  = c;
            if (badge1) badge1.innerText = c;

            // When the count changes, refresh the dropdown list so new items
            // appear there too — also without a refresh.
            if (raLastCount !== null && c !== raLastCount &&
                typeof refreshNotificationDropdown === 'function') {
                refreshNotificationDropdown();
            }
            raLastCount = c;
        }

        function raShow(item) {
            const el = document.getElementById('riderAcceptedModal');
            if (!el || typeof bootstrap === 'undefined') return;

            const rider = item.rider || {};
            document.getElementById('raRiderName').textContent   = rider.name || 'Rider';
            document.getElementById('raRiderMobile').textContent = rider.mobile || 'N/A';
            document.getElementById('raRiderImg').src = rider.image
                ? ('/upload/delivery-profile/small/' + rider.image)
                : '/upload/no_image.jpg';
            document.getElementById('raOrderNumber').textContent = item.order_number || 'N/A';
            document.getElementById('raTracking').textContent    = item.order_tracking || 'N/A';
            document.getElementById('raDetailsBtn').href =
                `/restaurant/meal-order/details/${item.meal_order_id}?notification_id=${item.notification_id}`;

            raModal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            raModal.show();
        }

        async function raPoll() {
            const payload = await raFetch();
            if (!payload) return;

            // 1) Live-update the bell count regardless of acceptances.
            raUpdateBadge(payload.unread_count);

            // 2) Pop the rider-accepted modal for any new acceptance.
            const items = payload.data || [];
            if (!items.length) return;
            const seen = raGetSeen();
            const fresh = items.filter(i => !seen.has(i.notification_id));
            if (!fresh.length) return;

            // Show the most recent unseen acceptance; mark all fresh ones as seen
            // so they don't re-pop on the next poll / navigation.
            const latest = fresh[0];
            fresh.forEach(i => raAddSeen(i.notification_id));
            raShow(latest);
        }

        document.addEventListener('DOMContentLoaded', function () {
            raPoll();
            clearInterval(raPollTimer);
            raPollTimer = setInterval(raPoll, RA_POLL_MS);
        });
    })();
    </script>

    <!-- New meal-order popup (auto-shows when a customer places an order with this client's items) -->
    <div class="modal fade" id="newOrderModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 overflow-hidden no-modal">
          <div class="modal-header no-header text-white border-0">
            <div class="d-flex align-items-center gap-3">
              <span class="no-bell"><i class="mdi mdi-food-outline"></i></span>
              <div>
                <h5 class="modal-title fw-bold mb-0">New Meal Order</h5>
                <small class="opacity-75">A customer just ordered your food</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-3 p-md-4 bg-light">
            <div id="noBody" class="d-flex flex-column gap-3"></div>
          </div>
          <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            <a href="#" id="noDetailsBtn" class="btn no-accept-btn rounded-pill px-4"><i class="mdi mdi-eye-outline me-1"></i>View Details</a>
          </div>
        </div>
      </div>
    </div>

    <script>
    (function () {
        const NO_POLL_MS  = 12000;
        const NO_SEEN_KEY = 'noSeenOrderIds';
        let   noModal = null;
        let   noQueue = [];

        function noLoadSeen() {
            try { return new Set(JSON.parse(localStorage.getItem(NO_SEEN_KEY) || '[]')); }
            catch (e) { return new Set(); }
        }
        function noSaveSeen(set) {
            try { localStorage.setItem(NO_SEEN_KEY, JSON.stringify(Array.from(set).slice(-50))); } catch (e) {}
        }
        let noSeen = noLoadSeen();

        async function noFetch() {
            try {
                const res = await axios.get('/restaurant/get/new-meal-orders');
                if (res.status === 200 && res.data.status === 'success') return res.data.data || [];
            } catch (e) { /* silent */ }
            return [];
        }

        function fmtCurrency(v) {
            return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(parseFloat(v) || 0);
        }

        function noShowNext() {
            const el = document.getElementById('newOrderModal');
            if (!el || typeof bootstrap === 'undefined') return;
            if (el.classList.contains('show')) return;
            const o = noQueue.shift();
            if (!o) return;

            const itemsHtml = (o.items || []).map(i => `
                <div class="no-item">
                    <div class="no-item-thumb">${i.product_image
                        ? `<img src="/upload/product/small/${i.product_image}" onerror="this.src='/upload/no_image.jpg'">`
                        : `<i class="mdi mdi-silverware-variant"></i>`}</div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">${i.product_name} <span class="text-muted">× ${i.quantity}</span></div>
                        <div class="small text-muted">
                            ${i.meal_type ? `<span class="me-2"><i class="mdi mdi-tag-outline"></i> ${i.meal_type}</span>` : ''}
                            ${i.meal_date ? `<span class="me-2"><i class="mdi mdi-calendar-outline"></i> ${i.meal_date}</span>` : ''}
                            ${i.meal_time ? `<span><i class="mdi mdi-clock-outline"></i> ${i.meal_time}</span>` : ''}
                        </div>
                    </div>
                </div>`).join('');

            document.getElementById('noBody').innerHTML = `
                <div class="no-summary">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge no-track"><i class="mdi mdi-pound"></i>${o.order_number || 'N/A'}</span>
                        <span class="text-muted small"><i class="mdi mdi-account-outline me-1"></i>${o.customer_name || 'Customer'}</span>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <span class="no-chip"><i class="mdi mdi-food-fork-drink"></i> ${o.item_count} item(s)</span>
                        <span class="no-chip no-chip-amt"><i class="mdi mdi-cash"></i> ${fmtCurrency(o.total_amount)}</span>
                    </div>
                </div>
                <div class="no-items">${itemsHtml || '<div class="text-muted">No items.</div>'}</div>`;

            document.getElementById('noDetailsBtn').href =
                `/restaurant/meal-order/details/${o.meal_order_id}?notification_id=${o.notification_id}`;

            const elModal = document.getElementById('newOrderModal');
            noModal = bootstrap.Modal.getInstance(elModal) || new bootstrap.Modal(elModal);
            if (!elModal.dataset.noHiddenBound) {
                elModal.dataset.noHiddenBound = '1';
                elModal.addEventListener('hidden.bs.modal', () => setTimeout(noShowNext, 300));
            }
            noModal.show();
        }

        async function noPoll() {
            const orders = await noFetch();
            if (!orders.length) return;
            const fresh = orders.filter(o => !noSeen.has(o.notification_id));
            if (!fresh.length) return;
            fresh.forEach(o => noSeen.add(o.notification_id));
            noSaveSeen(noSeen);
            noQueue.push(...fresh);
            noShowNext();
        }

        document.addEventListener('DOMContentLoaded', function () {
            noPoll();
            setInterval(noPoll, NO_POLL_MS);
        });
    })();
    </script>
  </body>

@stack('scripts')
<script>
document.addEventListener("DOMContentLoaded", async function () {
  showLoader();
  try {
      const response = await axios.get('/restaurant/limited/notification/list');

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
          document.getElementById('login-user-img').src = userData['image'] ? "/upload/client-profile/small/" + userData['image'] : "/upload/no_image.jpg";

          document.getElementById('login-user-img1').src = userData['image'] ? "/upload/client-profile/small/" + userData['image'] : "/upload/no_image.jpg";

      }
  } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
});

// Re-fetch the limited notification list and re-render the bell dropdown,
// so new notifications appear without a page refresh.
async function refreshNotificationDropdown() {
    try {
        const response = await axios.get('/restaurant/limited/notification/list');
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

    // No notifications case
    if (
        (!unreadNotifications || unreadNotifications.length === 0) &&
        (!readNotifications || readNotifications.length === 0)
    ) {
        notificationsContainer.innerHTML =
            '<li class="list-group-item">No notifications</li>';
        return;
    }

    // Function to get notification link based on type
    function getNotificationLink(notification) {
        if (!notification.data) return '#';

        const notificationId = notification.id; // Use only the notification ID
        const data = notification.data;

        switch (data.type) {
            case 'meal_order':
            case 'client_meal_order':
            case 'delivery_accepted':
                return `/restaurant/meal-order/details/${data.meal_order_id}?notification_id=${notificationId}`;

            case 'client_order':
                return `/restaurant/account/details/${data.client_id}?notification_id=${notificationId}`;

            case 'order':
                return `/restaurant/order/details/${data.order_id}?notification_id=${notificationId}`;

            case 'complaint':
                return `/restaurant/complaint/details/${data.complaint_id}?notification_id=${notificationId}`;

            case 'product':
                return `/restaurant/product/details/${data.product_id}?notification_id=${notificationId}`;

            case 'customer_complain':
                return `/restaurant/customer-complain/details/${data.customer_complain_id}?notification_id=${notificationId}`;

            default:
                return '#';
        }
    }

    // Render unread notifications
    if (unreadNotifications && unreadNotifications.length > 0) {
        unreadNotifications.forEach(notification => {
            const link = getNotificationLink(notification);

            notificationsHTML += `
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                    <div class="d-flex gap-2">
                        <a href="${link}">
                            <div class="d-flex flex-column flex-grow-1 overflow-hidden w-px-200">
                                <h6 class="mb-1 text-truncate">
                                    <strong>${notification.data.data}</strong>
                                </h6>
                                <small class="text-truncate text-body">
                                    ${new Date(notification.created_at).toLocaleString()}
                                </small>
                            </div>
                        </a>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <small class="text-muted">Unread</small>
                        </div>
                    </div>
                    <button
                        class="delete-notification-btn btn btn-danger btn-sm mt-2"
                        onclick="deleteNotification('${notification.id}')">
                        Delete
                    </button>
                </li>
            `;
        });
    }

    // Render read notifications
    if (readNotifications && readNotifications.length > 0) {
        readNotifications.forEach(notification => {
            const link = getNotificationLink(notification);

            notificationsHTML += `
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                    <div class="d-flex gap-2">
                        <a href="${link}">
                            <div class="d-flex flex-column flex-grow-1 overflow-hidden w-px-200">
                                <h6 class="mb-1 text-truncate">
                                    ${notification.data.data}
                                </h6>
                                <small class="text-truncate text-body">
                                    ${new Date(notification.created_at).toLocaleString()}
                                </small>
                            </div>
                        </a>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <small class="text-muted">Read</small>
                        </div>
                    </div>
                    <button
                        class="delete-notification-btn btn btn-danger btn-sm mt-2"
                        onclick="deleteNotification('${notification.id}')">
                        Delete
                    </button>
                </li>
            `;
        });
    }

    notificationsContainer.innerHTML = notificationsHTML;
}


function displayNotifications11(unreadNotifications, readNotifications) {
    const notificationsContainer = document.querySelector('.dropdown-notifications-list ul');
    let notificationsHTML = '';

    if ((unreadNotifications && unreadNotifications.length === 0) &&
        (readNotifications && readNotifications.length === 0)) {
        notificationsContainer.innerHTML = '<li class="list-group-item">No notifications</li>';
        return;
    }


    function getNotificationLink(notification) {
        if (notification.data) {
            let notificationId = notification.data.original_notification_id ?? notification.id; // Get original ID if reminder

            if (notification.data.order_id) {
                return `/restaurant/order/details/${notification.data.order_id}?notification_id=${notificationId}`;
            } else if (notification.data.complaint_id) {
                return `/restaurant/complaint/details/${notification.data.complaint_id}?notification_id=${notificationId}`;
            } else if (notification.data.product_id) {
                return `/restaurant/product/details/${notification.data.product_id}?notification_id=${notificationId}`;
            } else if (notification.data.client_id) {
                return `/restaurant/account/details/${notification.data.client_id}?notification_id=${notificationId}`;
            } else if (notification.data.customer_complain_id) {
                return `/restaurant/customer-complain/details/${notification.data.customer_complain_id}?notification_id=${notificationId}`;
            } else if (notification.data.meal_order_id) {
                return `/restaurant/meal-order/details/${notification.data.meal_order_id}?notification_id=${notificationId}`;
            }
        }
        return '#';
    }

    if (unreadNotifications && unreadNotifications.length > 0) {
        unreadNotifications.forEach(notification => {
            const link = getNotificationLink(notification); 
            notificationsHTML += `
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                    <div class="d-flex gap-2">
                        <a href="${link}"><div class="d-flex flex-column flex-grow-1 overflow-hidden w-px-200">
                            <h6 class="mb-1 text-truncate"><strong>${notification.data.data}</strong></h6>
                            <small class="text-truncate text-body">${new Date(notification.created_at).toLocaleString()}</small>
                        </div></a>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            <small class="text-muted">Unread</small>
                        </div>
                    </div>
                    <button class="delete-notification-btn btn btn-danger btn-sm mt-2" onclick="deleteNotification('${notification.id}')">Delete</button>
                </li>`;
        });
    }

    if (readNotifications && readNotifications.length > 0) {
        readNotifications.forEach(notification => {
            const link = getNotificationLink(notification); 
            notificationsHTML += `
                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                    <div class="d-flex gap-2">
                        <a href="${link}"><div class="d-flex flex-column flex-grow-1 overflow-hidden w-px-200">
                            <h6 class="mb-1 text-truncate">${notification.data.data}</h6>
                            <small class="text-truncate text-body">${new Date(notification.created_at).toLocaleString()}</small>
                        </div></a>
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
        const response = await axios.delete(`/restaurant/delete/notification/${notificationId}`);

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
          const response = await axios.get('/restaurant/markAsRead');

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

</script>

</html>