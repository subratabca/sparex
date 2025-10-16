<nav class="layout-navbar container shadow-none py-0">
  <div class="navbar navbar-expand-lg landing-navbar border-top-0 px-3 px-md-4">
    <!-- Menu logo wrapper: Start -->
    <div class="navbar-brand app-brand demo d-flex py-0 py-lg-2 me-4">
      <!-- Mobile menu toggle: Start-->
      <button
        class="navbar-toggler border-0 px-0 me-2"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <i class="tf-icons mdi mdi-menu mdi-24px align-middle"></i>
      </button>
      <!-- Mobile menu toggle: End-->
      <a href="{{ route('home') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
          <img id="logo" src=" " width="100" height="40" alt="App Logo">

      </span>

      </a>
    </div>
    <!-- Menu logo wrapper: End -->
    <!-- Menu wrapper: Start -->
    <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
      <button
        class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <i class="tf-icons mdi mdi-close"></i>
      </button>
      <ul class="navbar-nav me-auto p-3 p-lg-0">
        <li class="nav-item">
          <a class="nav-link fw-medium" aria-current="page" href="{{ route('home') }}">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-medium" href="{{ route('about') }}">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-medium text-nowrap" href="{{ route('contact.us.page') }}">Contact us</a>
        </li>
        <li>
          <a href="{{ route('client.login.page') }}" class="btn btn-info px-2 px-sm-2">
            <span class="tf-icons mdi mdi-account me-md-1"></span><span class="d-none d-md-block">Signin as a Donator</span>
          </a
          >
        </li>
        <li style="margin-left:5px">
          <a href="{{ route('login.page') }}" class="btn btn-primary btn-info px-2 px-sm-2">
            <span class="tf-icons mdi mdi-account me-md-1"></span><span class="d-none d-md-block">Signin as a Donatee</span>
          </a
          >
        </li>
      </ul>
    </div>
    <div class="landing-menu-overlay d-lg-none"></div>
    <!-- Menu wrapper: End -->
    <!-- Toolbar: Start -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- navbar button: Start -->
        <li class="nav-item dropdown" id="cartItem" style="display: none;">
            <a class="nav-link fw-medium" title="Cartlists" href="{{ route('mycart') }}"><i class="mdi mdi-cart-outline mdi-24px"></i>
              <span class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border" id="cartCount"></span>
            </a>
        </li>
        <li class="nav-item dropdown" id="wishlistItem" style="display: none;">
            <a class="nav-link fw-medium" title="Wishlists" href="{{ route('wishlists') }}"><i class="mdi mdi-heart-outline mdi-24px"></i>
            <span
                class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border" id="wishlistCount"></span></a>
        </li>

        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1" id="notificationsItem" style="display: none;">
            <a
                class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                href="javascript:void(0);"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false">
                <i class="mdi mdi-bell-outline mdi-24px" title="Notifications"></i>
                <span 
                    class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border" id="notificationCount"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end py-0">
                <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">Notification<span id="notificationCount1" class="badge rounded-pill bg-label-primary">0 New</span></h6>
                        <a href="javascript:void(0);" onclick="markAllAsRead()" style="color:green">Mark All As Read</a>
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush">
                        <!-- Notifications will be populated here dynamically -->
                    </ul>
                </li>
                <li class="dropdown-menu-footer border-top p-2 mt-4">
                    <a href="{{ route('notifications') }}" class="btn btn-primary d-flex justify-content-center">
                        View all notifications
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1" id="userAccountItem" style="display: none;">
            <a class="nav-link btn btn-text-secondary  dropdown-toggle hide-arrow"
                href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" 
                aria-expanded="false" id="login-user-name">
            </a>
            <ul class="dropdown-menu dropdown-menu-end py-0">
                <li class="dropdown-menu-header border-bottom">
                    <a href="{{ route('user.dashboard') }}"><div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">Dashboard</h6>
                    </div></a>
                </li>
                <li class="dropdown-menu-header border-bottom">
                    <a href="{{ route('user.update.document') }}"><div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">Upload Document</h6>
                    </div></a>
                </li>
                <li class="dropdown-menu-header border-bottom">
                    <a href="javascript:void(0);" onclick="handleLogout()">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h6 class="mb-0 me-auto">Logout</h6>
                        </div>
                    </a>
                </li>
            </ul>
        </li>

        <li id="loginRegisterItem">
            <a href="{{ route('login.page') }}" class="btn btn-primary px-2 px-sm-4 px-lg-2 px-xl-4">
                <span class="tf-icons mdi mdi-account me-md-1"></span><span class="d-none d-md-block">Signin as a donatee</span>
            </a>
        </li>
    </ul>
    <!-- Toolbar: End -->
  </div>
</nav>


<script>
document.addEventListener("DOMContentLoaded", async function () {
    if (isTokenValid()) {
        // Show user account and other authenticated items
        document.getElementById('wishlistItem').style.display = 'block';
        document.getElementById('notificationsItem').style.display = 'block';
        document.getElementById('userAccountItem').style.display = 'block';
        document.getElementById('loginRegisterItem').style.display = 'none';

        await updateCartCount(); 
        await updateWishlistCount();

        showLoader();
        try {
            const response = await axios.get('/user/limited/notification/list');
            if (response.status === 200) {
                const userData = response.data.data;
                const unreadNotifications = response.data.unreadNotifications;
                const readNotifications = response.data.readNotifications;
                
                const notificationCount = unreadNotifications.length || '0';
                
                document.getElementById('notificationCount').innerText = notificationCount;
                document.getElementById('notificationCount1').innerText = notificationCount;
                
                await displayNotifications(unreadNotifications, readNotifications);

                let fullName = userData.firstName;
                if (userData.lastName) {
                    fullName += ' ' + userData.lastName;
                }
                
                document.getElementById('login-user-name').innerText = fullName || 'Account';

                //dashboard-master.blade.php
                document.getElementById('common-image').src = userData['image'] ? "/upload/customer-profile/small/" + userData['image'] : "/upload/no_image.jpg";
                document.getElementById('common-userName').innerText = fullName;
                document.getElementById('common-mobile').innerText = userData.mobile;
                document.getElementById('common-email').innerText = userData.email;
                //end dashboard-master.blade.php
            }
        } catch (error) {
            handleError(error);
        } finally {
            hideLoader();
        }
    } else {
        document.getElementById('loginRegisterItem').style.display = 'block';
        document.getElementById('wishlistItem').style.display = 'none';
        document.getElementById('notificationsItem').style.display = 'none';
        document.getElementById('userAccountItem').style.display = 'none';
    }
});

async function displayNotifications(unreadNotifications, readNotifications) {
    const notificationsContainer = document.querySelector('.dropdown-notifications-list ul');
    let notificationsHTML = '';

    if ((unreadNotifications && unreadNotifications.length === 0) &&
        (readNotifications && readNotifications.length === 0)) {
        notificationsContainer.innerHTML = '<li class="list-group-item">No notifications</li>';
        return;
    }

    function getNotificationLink(notification) {
        if (notification.data.customer_id) {
            return `/user/details/${notification.data.customer_id}?notification_id=${notification.id}`;
        } else if (notification.data.order_id) {
            return `/user/order/details/${notification.data.order_id}?notification_id=${notification.id}`;
        } else if (notification.data.complaint_id) {
            return `/user/complaint/details/${notification.data.complaint_id}?notification_id=${notification.id}`;
        } else if (notification.data.customer_complain_id) {
            return `/user/customer-complain-details/${notification.data.customer_complain_id}?notification_id=${notification.id}`;
        }else {
            return '#';
        }
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
        const response = await axios.delete(`/user/delete/notification/${notificationId}`);

        if (response.status === 200) {
            successToast(response.data.message || 'Request success');
            window.location.reload();
        } else {
            errorToast(response.data.message || 'Failed to delete notification');
        }
    } catch (error) {
        handleError(error);
    }
}

async function markAllAsRead() {
    try {
        const response = await axios.get('/user/markAsRead');

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
        handleError(error);
    }
}

async function handleLogout() {
    showLoader();
    try {
        let res = await axios.get("{{ route('logout') }}");
        if (res.status === 200) {
            successToast(res.data.message || 'Successfully logged out');
            window.location.href = '/user/login';
        } else {
            errorToast(res.data.message || "Request failed");
        }
    } catch (error) {
        if (error.response) {
            if (error.response.status === 500) {
                errorToast(error.response.data.error || "An internal server error occurred.");
            } else {
                errorToast("Request failed!");
            }
        }
    } finally {
        hideLoader();
    }
}

async function updateCartCount() {
    try {
        const res = await axios.get('/user/cart/count');
        if (res.status === 200) {
            const count = res.data.count;
            const cartCountElem = document.getElementById('cartCount');
            const cartItemElem = document.getElementById('cartItem');
            
            if (count > 0) {
                cartItemElem.style.display = 'block';
                cartCountElem.innerText = count;
                cartCountElem.style.display = 'inline-block';
            } else {
                cartItemElem.style.display = 'none';
                cartCountElem.innerText = '0'; 
            }
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}

async function updateWishlistCount() {
    try {
        const response = await axios.get('/user/wishlist/count');
        if (response.data.status === 'success') {
            const count = response.data.count;
            const wishlistCountElem = document.getElementById('wishlistCount');
            const wishlistItemElem = document.getElementById('wishlistItem');
            
            if (count > 0) {
                wishlistCountElem.textContent = count;
                wishlistItemElem.style.display = 'block';
            } else {
                wishlistItemElem.style.display = 'none';
            }
        }
    } catch (error) {
        handleError(error);
    } finally {
        hideLoader();
    }
}


function handleError(error) {
    if (error.response) {
        const status = error.response.status;
        const message = error.response.data.message || 'An unexpected error occurred';

        if (status === 400) {
            errorToast(message || 'Bad Request');
        } else if (status === 401) {
            errorToast(message || 'Server Error');
        } else if (status === 500) {
            errorToast(message || 'Server Error');
        } else {
            errorToast(message);
        }
    }
}
</script>



               




