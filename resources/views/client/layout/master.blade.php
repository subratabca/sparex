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
  </body>
</html>

<script>
document.addEventListener("DOMContentLoaded", async function () {
  showLoader();
  try {
      const response = await axios.get('/client/limited/notification/list');

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


function displayNotifications(unreadNotifications, readNotifications) {
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
                return `/client/order/details/${notification.data.order_id}?notification_id=${notificationId}`;
            } else if (notification.data.complaint_id) {
                return `/client/complaint/details/${notification.data.complaint_id}?notification_id=${notificationId}`;
            } else if (notification.data.product_id) {
                return `/client/product/details/${notification.data.product_id}?notification_id=${notificationId}`;
            } else if (notification.data.client_id) {
                return `/client/account/details/${notification.data.client_id}?notification_id=${notificationId}`;
            } else if (notification.data.customer_complain_id) {
                return `/client/customer-complain/details/${notification.data.customer_complain_id}?notification_id=${notificationId}`;
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
        const response = await axios.delete(`/client/delete/notification/${notificationId}`);

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
          const response = await axios.get('/client/markAsRead');

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
</style>