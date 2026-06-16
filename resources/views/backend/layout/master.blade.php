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
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}"  />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/quill/typography.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/quill/katex.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/quill/editor.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />
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

            @include('backend.layout.left-sidebar')

            <div class="layout-page">

                @include('backend.layout.header')

                <div class="content-wrapper">

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">@yield('breadcum')</h4>
                        @yield('content')
                    </div>

                    @include('backend.layout.footer')

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
    <script src="{{ asset('backend/assets/vendor/libs/chartjs/chartjs.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('backend/assets/js/main.js') }}"></script>


    <!-- Page JS -->
    <script src="{{ asset('backend/assets/js/form-basic-inputs.js') }}"></script>
    <script src="{{ asset('backend/assets/js/forms-selects.js') }}"></script>
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
    <script src="{{ asset('backend/assets/js/charts-chartjs.js') }}"></script>

    <!-- New meal-order popup (auto-shows when a customer places an order) -->
    <div class="modal fade" id="adminNewOrderModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 overflow-hidden ano-modal">
          <div class="modal-header ano-header text-white border-0">
            <div class="d-flex align-items-center gap-3">
              <span class="ano-bell"><i class="mdi mdi-cart-plus"></i></span>
              <div>
                <h5 class="modal-title fw-bold mb-0">New Meal Order Placed</h5>
                <small class="opacity-75">A customer placed a new order</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-3 p-md-4 bg-light">
            <div id="anoBody" class="d-flex flex-column gap-3"></div>
          </div>
          <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            <a href="#" id="anoDetailsBtn" class="btn ano-accept-btn rounded-pill px-4"><i class="mdi mdi-eye-outline me-1"></i>View Details</a>
          </div>
        </div>
      </div>
    </div>

    <script>
    (function () {
        const ANO_POLL_MS  = 12000;
        const ANO_SEEN_KEY = 'anoSeenOrderIds';
        let   anoModal = null;
        let   anoQueue = [];
        let   anoLastCount = null;

        function anoLoadSeen() {
            try { return new Set(JSON.parse(localStorage.getItem(ANO_SEEN_KEY) || '[]')); }
            catch (e) { return new Set(); }
        }
        function anoSaveSeen(set) {
            try { localStorage.setItem(ANO_SEEN_KEY, JSON.stringify(Array.from(set).slice(-50))); } catch (e) {}
        }
        let anoSeen = anoLoadSeen();

        async function anoFetch() {
            try {
                const res = await axios.get('/admin/get/new-meal-orders');
                if (res.status === 200 && res.data.status === 'success') return res.data;
            } catch (e) { /* silent */ }
            return null;
        }

        function fmtCurrency(v) {
            return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(parseFloat(v) || 0);
        }

        // Live-update the bell badge (no page refresh).
        function anoUpdateBadge(count) {
            const c = (count === undefined || count === null) ? 0 : count;
            const badge  = document.getElementById('notificationCount');
            const badge1 = document.getElementById('notificationCount1');
            if (badge)  badge.innerText  = c;
            if (badge1) badge1.innerText = c;
            if (anoLastCount !== null && c !== anoLastCount &&
                typeof refreshNotificationDropdown === 'function') {
                refreshNotificationDropdown();
            }
            anoLastCount = c;
        }

        function anoShowNext() {
            const el = document.getElementById('adminNewOrderModal');
            if (!el || typeof bootstrap === 'undefined') return;
            if (el.classList.contains('show')) return;
            const o = anoQueue.shift();
            if (!o) return;

            const clientsHtml = (o.clients || []).map(c => {
                const items = (c.items || []).map(i => `
                    <li>${i.product_name} <span class="text-muted">× ${i.quantity}</span>
                        <span class="ano-meta">${[i.meal_type, i.meal_date].filter(Boolean).join(' • ')}</span>
                    </li>`).join('');
                return `
                    <div class="ano-client">
                        <div class="ano-client-head">
                            <i class="mdi mdi-storefront-outline me-1"></i>${c.client_name}
                            <span class="badge bg-label-primary ms-2">${c.item_count} item(s)</span>
                        </div>
                        <ul class="ano-client-items">${items}</ul>
                    </div>`;
            }).join('');

            document.getElementById('anoBody').innerHTML = `
                <div class="ano-summary">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge ano-track"><i class="mdi mdi-pound"></i>${o.order_number || 'N/A'}</span>
                        <span class="text-muted small"><i class="mdi mdi-account-outline me-1"></i>${o.customer_name || 'Customer'}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="ano-chip"><i class="mdi mdi-storefront"></i> ${o.client_count} client(s)</span>
                        <span class="ano-chip"><i class="mdi mdi-food-fork-drink"></i> ${o.item_count} item(s)</span>
                        <span class="ano-chip ano-chip-amt"><i class="mdi mdi-cash"></i> ${fmtCurrency(o.total_amount)}</span>
                    </div>
                </div>
                <div class="ano-clients">${clientsHtml || '<div class="text-muted">No items.</div>'}</div>`;

            document.getElementById('anoDetailsBtn').href =
                `/admin/meal-order/details/${o.meal_order_id}?notification_id=${o.notification_id}`;

            const elModal = document.getElementById('adminNewOrderModal');
            anoModal = bootstrap.Modal.getInstance(elModal) || new bootstrap.Modal(elModal);
            if (!elModal.dataset.anoHiddenBound) {
                elModal.dataset.anoHiddenBound = '1';
                elModal.addEventListener('hidden.bs.modal', () => setTimeout(anoShowNext, 300));
            }
            anoModal.show();
        }

        async function anoPoll() {
            const payload = await anoFetch();
            if (!payload) return;

            // 1) Live bell count
            anoUpdateBadge(payload.unread_count);

            // 2) Pop new orders
            const orders = payload.data || [];
            const fresh = orders.filter(o => !anoSeen.has(o.notification_id));
            if (!fresh.length) return;
            fresh.forEach(o => anoSeen.add(o.notification_id));
            anoSaveSeen(anoSeen);
            anoQueue.push(...fresh);
            anoShowNext();
        }

        document.addEventListener('DOMContentLoaded', function () {
            anoPoll();
            setInterval(anoPoll, ANO_POLL_MS);
        });
    })();
    </script>

    <style>
    .ano-modal { border-radius: 18px; }
    .ano-header { background: linear-gradient(135deg,#0ea5e9 0%,#6366f1 60%,#8b5cf6 100%); padding: 1.1rem 1.5rem; }
    .ano-bell {
      width: 46px; height: 46px; border-radius: 50%; background: rgba(255,255,255,.2);
      display:flex; align-items:center; justify-content:center; font-size: 1.5rem;
    }
    .ano-summary { background:#fff; border:1px solid #eef0f4; border-radius:14px; padding:1rem 1.1rem; box-shadow:0 2px 10px rgba(0,0,0,.04); }
    .ano-track { background:#eef2ff; color:#6366f1; }
    .ano-chip { background:#f1f5f9; border-radius:20px; padding:.3rem .8rem; font-size:.8rem; font-weight:600; color:#0f172a; }
    .ano-chip-amt { background:#ecfdf5; color:#047857; }
    .ano-clients { display:flex; flex-direction:column; gap:.6rem; }
    .ano-client { background:#fff; border:1px solid #eef0f4; border-radius:12px; padding:.7rem .9rem; }
    .ano-client-head { font-weight:700; color:#0f172a; }
    .ano-client-items { margin:.4rem 0 0; padding-left:1.1rem; }
    .ano-client-items li { margin-bottom:.2rem; }
    .ano-meta { display:block; font-size:.75rem; color:#94a3b8; }
    .ano-accept-btn { background:linear-gradient(135deg,#0ea5e9,#6366f1); color:#fff; border:none; font-weight:600; }
    .ano-accept-btn:hover { filter:brightness(.95); color:#fff; }
    </style>
  </body>
</html>

<script>
document.addEventListener("DOMContentLoaded", async function () {
  try {
      const response = await axios.get('/admin/limited/notification/list');

      if (response.status === 200) {
          const userData = response.data.data;
          const unreadNotifications = response.data.unreadNotifications;
          const readNotifications = response.data.readNotifications;

          const notificationCount = unreadNotifications.length || '0';
          document.getElementById('notificationCount').innerText = notificationCount;
          document.getElementById('notificationCount1').innerText = notificationCount;
          displayNotifications(unreadNotifications, readNotifications);
          
          document.getElementById('login-user-role-name').innerText = userData.role || 'Account';
          document.getElementById('login-user-name').innerText = userData.firstName || 'No User';
          document.getElementById('login-user-img').src = userData['image'] ? "/upload/admin-profile/small/" + userData['image'] : "/upload/no_image.jpg";

          document.getElementById('login-user-img1').src = userData['image'] ? "/upload/admin-profile/small/" + userData['image'] : "/upload/no_image.jpg";
      }
  } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
})


// Re-fetch the limited notification list and re-render the bell dropdown,
// so new notifications appear without a page refresh.
async function refreshNotificationDropdown() {
    try {
        const response = await axios.get('/admin/limited/notification/list');
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
        if (!notification || !notification.data) return '#';
        
        const data = notification.data;
        
        if (data.order_id) {
            return `/admin/order/details/${data.order_id}?notification_id=${notification.id}`;
        } else if (data.meal_order_id) {
            return `/admin/meal-order/details/${data.meal_order_id}?notification_id=${notification.id}`;
        } else if (data.complaint_id) {
            return `/admin/complaint/details/${data.complaint_id}?notification_id=${notification.id}`;
        } else if (data.product_id) {
            return `/admin/product/details/${data.product_id}?notification_id=${notification.id}`;
        } else if (data.client_id) {
            return `/admin/client/details/${data.client_id}?notification_id=${notification.id}`;
        } else if (data.customer_id) {
            return `/admin/customer/details/${data.customer_id}?notification_id=${notification.id}`;
        } else if (data.customer_complain_id) {
            return `/admin/customer-complain/details/${data.customer_complain_id}?notification_id=${notification.id}`;
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
        const response = await axios.delete(`/admin/delete/notification/${notificationId}`);

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
          const response = await axios.get('/admin/markAsRead');

          if (response.status === 200 && response.data.status === 'success') {
              document.getElementById('notificationCount').innerText = response.data.unreadCount;

              document.getElementById('notificationCount1').innerText = response.data.unreadCount === 0 ? '0 New' : `${response.data.unreadCount} New`;

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
</style>