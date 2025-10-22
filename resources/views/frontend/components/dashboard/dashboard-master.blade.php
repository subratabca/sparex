<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">
    <div class="layout-page">
        <div class="content-wrapper">  
            <div class="container-xxl flex-grow-1 container-p-y">
              <!-- Header -->
              <div class="row">
                <div class="col-12">
                  <div class="card mb-4">
                    <div class="user-profile-header-banner">
                      <img src="{{ asset('frontend/assets/img/pages/profile-banner.png') }}" alt="Banner image" class="rounded-top" />
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                      <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                        <img
                          src=""
                          alt="user image" id="common-image"
                          class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
                      </div>
                      <div class="flex-grow-1 mt-3 mt-sm-5">
                        <div
                          class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                          <div class="user-profile-info">
                            <h4 id="common-userName">John Doe</h4>
                            <ul
                              class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                              <li class="list-inline-item">
                                <i class="mdi mdi-phone me-1 mdi-20px"></i>
                                <span class="fw-medium" id="common-mobile">Phone</span>
                              </li>
                              <li class="list-inline-item">
                                <i class="mdi mdi-email me-1 mdi-20px"></i
                                ><span class="fw-medium" id="common-email">Email</span>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!--/ Header -->
              <div class="row g-4">
                <!-- Left-Navigation -->
                <div class="col-12 col-lg-2">
                  <div class="d-flex justify-content-between flex-column mb-3 mb-md-0">
                    <ul class="nav nav-align-left nav-pills flex-column">
                      <li class="nav-item mb-1">
                        <a class="nav-link {{ Route::is('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">
                          <i class="mdi mdi-storefront-outline me-2"></i>
                          <span class="align-middle">Dashboard</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link {{ Route::is('orders') ? 'active' : '' }}" href="{{ route('orders') }}">
                          <i class="mdi mdi-credit-card-outline me-2"></i>
                          <span class="align-middle">Orders</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link dropdown-toggle {{ Route::is(['complaints', 'customer.complains']) ? 'active' : '' }}" 
                           href="#" id="productComplaintsDropdown" role="button" data-bs-toggle="dropdown" 
                           aria-expanded="false">
                          <i class="mdi mdi-cross-bolnisi me-2"></i>
                          <span class="align-middle">Complaints</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="productComplaintsDropdown">
                          <li>
                            <a class="dropdown-item {{ Route::is('complaints') ? 'active' : '' }}" href="{{ route('complaints') }}">
                              <i class="mdi mdi-package-variant me-2"></i>
                              <span>Product</span>
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item {{ Route::is('customer.complains') ? 'active' : '' }}" href="{{ route('customer.complains') }}">
                              <i class="mdi mdi-account-outline me-2"></i>
                              <span>Customer</span>
                            </a>
                          </li>
                        </ul>
                      </li>
        <li class="nav-item mb-1">
          <a class="nav-link dropdown-toggle {{ Route::is(['customer.meal.types', 'customer.menus', 'meal.orders']) ? 'active' : '' }}" 
             href="#" id="menuTypesDropdown" role="button" data-bs-toggle="dropdown" 
             aria-expanded="false">
            <i class="mdi mdi-cross-bolnisi me-2"></i>
            <span class="align-middle">Meal Settings</span>
          </a>
          <ul class="dropdown-menu" aria-labelledby="productComplaintsDropdown">
            <li>
              <a class="dropdown-item {{ Route::is('customer.meal.types') ? 'active' : '' }}" href="{{ route('customer.meal.types') }}">
                <i class="mdi mdi-package-variant me-2"></i>
                <span>Meal Types</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ Route::is('customer.menus') ? 'active' : '' }}" href="{{ route('customer.menus') }}">
                <i class="mdi mdi-account-outline me-2"></i>
                <span>Menus</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ Route::is('meal.orders') ? 'active' : '' }}" href="{{ route('meal.orders') }}">
                <i class="mdi mdi-account-outline me-2"></i>
                <span>Orders</span>
              </a>
            </li>
          </ul>
        </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link {{ Route::is('followers') ? 'active' : '' }}" href="{{ route('followers') }}">
                          <i class="mdi mdi mdi-alpha-f-box-outline me-2"></i>
                          <span class="align-middle">Followed Clients</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link {{ Route::is('user.profile') ? 'active' : '' }}" href="{{ route('user.profile') }}">
                          <i class="mdi mdi-square-edit-outline me-2"></i>
                          <span class="align-middle">Update Profile</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link {{ Route::is('user.update.password') ? 'active' : '' }}" href="{{ route('user.update.password') }}">
                          <i class="mdi mdi-lock-reset me-2"></i>
                          <span class="align-middle">Update Password</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link {{ Route::is('user.update.document') ? 'active' : '' }}" href="{{ route('user.update.document') }}">
                          <i class="mdi mdi-square-edit-outline me-2"></i>
                          <span class="align-middle">Verify Document</span>
                        </a>
                      </li>
                     <li class="nav-item mb-1">
                        <a class="nav-link {{ Route::is('user.email.share.list') ? 'active' : '' }}" href="{{ route('user.email.share.list') }}">
                          <i class="mdi mdi-email-multiple me-2"></i>
                          <span class="align-middle">Email Share List</span>
                        </a>
                      </li> 
                      <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}">
                          <i class="mdi mdi-logout me-2"></i>
                          <span class="align-middle">Logout</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <!-- /Left-Navigation -->

                <!-- Content -->
                <div class="col-12 col-lg-10 pt-4 pt-lg-0">
                    @yield('dashboard-content')
                </div>
                <!-- /Content-->
              </div>
              
            </div>
        </div>
    </div>
  </div>
</div>
