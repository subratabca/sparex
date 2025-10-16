@php
  $currentRoute = Route::currentRouteName();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
        <img id="logo" src="/upload/no_image.jpg" width="100" height="40" alt="App Logo">
      </span>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M11.4854 4.88844C11.0081 4.41121 10.2344 4.41121 9.75715 4.88844L4.51028 10.1353C4.03297 10.6126 4.03297 11.3865 4.51028 11.8638L9.75715 17.1107C10.2344 17.5879 11.0081 17.5879 11.4854 17.1107C11.9626 16.6334 11.9626 15.8597 11.4854 15.3824L7.96672 11.8638C7.48942 11.3865 7.48942 10.6126 7.96672 10.1353L11.4854 6.61667C11.9626 6.13943 11.9626 5.36568 11.4854 4.88844Z" fill="currentColor" fill-opacity="0.6"/>
        <path d="M15.8683 4.88844L10.6214 10.1353C10.1441 10.6126 10.1441 11.3865 10.6214 11.8638L15.8683 17.1107C16.3455 17.5879 17.1192 17.5879 17.5965 17.1107C18.0737 16.6334 18.0737 15.8597 17.5965 15.3824L14.0778 11.8638C13.6005 11.3865 13.6005 10.6126 14.0778 10.1353L17.5965 6.61667C18.0737 6.13943 18.0737 5.36568 17.5965 4.88844C17.1192 4.41121 16.3455 4.41121 15.8683 4.88844Z" fill="currentColor" fill-opacity="0.38"/>
      </svg>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <li class="menu-item {{ $currentRoute == 'admin.dashboard' ? 'active' : '' }}">
      <a href="{{ route('admin.dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-home-outline"></i>
        <div data-i18n="Dashboards">Dashboards</div>
      </a>
    </li>

    <li class="menu-item {{ str_starts_with($currentRoute, 'bar.chart') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-chart-box"></i>
        <div data-i18n="Graph Reports">Graph Reports</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute == 'bar.chart.order' ? 'active' : '' }}">
          <a href="{{ route('bar.chart.order') }}" class="menu-link">
            <div data-i18n="Bar Chart">Bar Chart</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ request()->routeIs(['products*', 'create.product*']) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-food"></i>
        <div data-i18n="Sparex">Sparex</div>
        <div class="badge bg-primary rounded-pill ms-auto">2</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('create.product*') ? 'active' : '' }}">
          <a href="{{ route('create.product') }}" class="menu-link">
            <div data-i18n="Add New Product">Add New Product</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('products*') ? 'active' : '' }}">
          <a href="{{ route('products') }}" class="menu-link">
            <div data-i18n="Product List">Product List</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ $currentRoute == 'admin.orders' ? 'active' : '' }}">
      <a href="{{ route('admin.orders') }}" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-flip-to-front"></i>
        <div data-i18n="Orders">Orders</div>
      </a>
    </li>

    <li class="menu-item {{ $currentRoute == 'clients' ? 'active' : '' }}">
      <a href="{{ route('clients') }}" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-account-outline"></i>
        <div data-i18n="Clients">Clients</div>
      </a>
    </li>

    <li class="menu-item {{ in_array($currentRoute, ['customers', 'admin.banned.customers']) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon mdi mdi-account-group"></i>
        <div data-i18n="Customers">Customers</div>
        <div class="badge bg-primary rounded-pill ms-auto">2</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute == 'customers' ? 'active' : '' }}">
          <a href="{{ route('customers') }}" class="menu-link">
            <div data-i18n="Customers">Customer List</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'admin.banned.customers' ? 'active' : '' }}">
          <a href="{{ route('admin.banned.customers') }}" class="menu-link">
            <div data-i18n="Banned Customers">Banned Customers</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ in_array($currentRoute, ['admin.complaints', 'admin.customer-complains']) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon mdi mdi-cross-bolnisi"></i>
        <div data-i18n="Complaints">Complaints</div>
        <div class="badge bg-primary rounded-pill ms-auto">2</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute == 'admin.complaints' ? 'active' : '' }}">
          <a href="{{ route('admin.complaints') }}" class="menu-link">
            <div data-i18n="Product">Product</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'admin.customer-complains' ? 'active' : '' }}">
          <a href="{{ route('admin.customer-complains') }}" class="menu-link">
            <div data-i18n="Customer">Customer</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ in_array($currentRoute, ['admin.customer-payments', 'admin.client-payments']) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon mdi mdi-account-credit-card"></i>
        <div data-i18n="Payment History">Payment History</div>
        <div class="badge bg-primary rounded-pill ms-auto">2</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute == 'admin.customer-payments' ? 'active' : '' }}">
          <a href="{{ route('admin.customer.payments') }}" class="menu-link">
            <div data-i18n="Customer Payment">Customer Payment</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'admin.client-payments' ? 'active' : '' }}">
          <a href="{{ route('admin.client.payments') }}" class="menu-link">
            <div data-i18n="Client Payment">Client Payment</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ $currentRoute == 'audits' ? 'active' : '' }}">
      <a href="{{ route('audits') }}" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-bug-check"></i>
        <div data-i18n="Audit Logs">Audit Logs</div>
      </a>
    </li>

    <li class="menu-item {{ $currentRoute == 'admin.contact.message' ? 'active' : '' }}">
      <a href="{{ route('admin.contact.message') }}" class="menu-link">
        <i class="menu-icon tf-icons mdi mdi-message-outline"></i>
        <div data-i18n="Contact Messages">Contact Messages</div>
      </a>
    </li>

    <li class="menu-header fw-medium mt-4">
      <span class="menu-header-text" data-i18n="Settings & Pages">Apps &amp; Pages</span>
    </li>

    <li class="menu-item {{ in_array($currentRoute, ['site.settings', 'categories', 'heros', 'abouts', 'terms.conditions']) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-view-grid-outline"></i>
        <div data-i18n="Front Settings">Settings</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute == 'site.settings' ? 'active' : '' }}">
          <a href="{{ route('site.settings') }}" class="menu-link">
            <div data-i18n="Site Settings">Site Settings</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'categories' ? 'active' : '' }}">
          <a href="{{ route('categories') }}" class="menu-link">
            <div data-i18n="Category">Category</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'heros' ? 'active' : '' }}">
          <a href="{{ route('heros') }}" class="menu-link">
            <div data-i18n="Hero">Hero</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'abouts' ? 'active' : '' }}">
          <a href="{{ route('abouts') }}" class="menu-link">
            <div data-i18n="About">About</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'terms.conditions' ? 'active' : '' }}">
          <a href="{{ route('terms.conditions') }}" class="menu-link">
            <div data-i18n="Terms & Conditions">Terms & Conditions</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-header fw-medium mt-4">
      <span class="menu-header-text" data-i18n="Reports">Reports</span>
    </li>

    <li class="menu-item {{ in_array($currentRoute, ['current.stock', 'search.stock']) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-credit-card-outline"></i>
        <div data-i18n="Stock">Stock</div>
        <div class="badge bg-primary rounded-pill ms-auto">2</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute == 'current.stock' ? 'active' : '' }}">
          <a href="{{ route('current.stock') }}" class="menu-link">
            <div data-i18n="Current Stock">Current Stock</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'search.stock' ? 'active' : '' }}">
          <a href="{{ route('search.stock') }}" class="menu-link">
            <div data-i18n="Search Stock">Search Stock</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ in_array($currentRoute, ['todays.order', 'search.order']) ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons mdi mdi-credit-card-outline"></i>
        <div data-i18n="Order">Order</div>
        <div class="badge bg-primary rounded-pill ms-auto">2</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $currentRoute == 'todays.order' ? 'active' : '' }}">
          <a href="{{ route('todays.order') }}" class="menu-link">
            <div data-i18n="Todays Order">Todays Order</div>
          </a>
        </li>
        <li class="menu-item {{ $currentRoute == 'search.order' ? 'active' : '' }}">
          <a href="{{ route('search.order') }}" class="menu-link">
            <div data-i18n="Search Order">Search Order</div>
          </a>
        </li>
      </ul>
    </li>
  </ul>
</aside>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        settingInfo(); 
    });

    async function settingInfo() {
      showLoader();
      try {
          const response = await axios.get('/setting-list');

          if (response.status === 200) {
              const data = response.data.data;
              document.getElementById('logo').src = data['logo'] ? "/upload/site-setting/" + data['logo'] : "/upload/no_image.jpg";
          }
      } catch (error) {
          handleError(error);
      }finally{
          hideLoader();
      }
    }


    function handleError(error) {
      if (error.response) {
          const status = error.response.status;
          const message = error.response.data.message || 'An unexpected error occurred';
          if (status === 500) {
              errorToast(message || 'Server Error');
          } else {
              errorToast(message);
          }
      }
    }
</script>