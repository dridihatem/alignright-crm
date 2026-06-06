<!-- Layout wrapper -->
<div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
    <div class="layout-container">
      <!-- Navbar -->

      <nav class="layout-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
        <div class="container-xxl">
          <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
            <a href="{{ route('home') }}" class="app-brand-link">
              @if(\App\Helpers\SettingsHelper::getLogoUrl())
                <img src="{{ \App\Helpers\SettingsHelper::getLogoUrl() }}" alt="Logo" class="app-brand-logo demo" style="max-height: 60px; max-width: 200px;">
              @else
                <img src="{{ asset('assets/img/logo_align.png') }}" alt="Logo" class="app-brand-logo demo" style="width: 49px;">
              @endif
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
              <i class="icon-base ti tabler-x icon-sm d-flex align-items-center justify-content-center"></i>
            </a>
          </div>

          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)" id="mobile-menu-toggle">
              <i class="icon-base ti tabler-menu-2 icon-md" id="menu-open-icon"></i>
              <i class="icon-base ti tabler-x icon-md" id="menu-close-icon" style="display: none;"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
            <ul class="navbar-nav flex-row align-items-center ms-md-auto">

              @php
                  $searchRoute = null;
                  if (auth()->check()) {
                      $u = auth()->user();
                      if ($u->isAdmin()) { $searchRoute = route('admin.search'); }
                      elseif ($u->isDoctor()) { $searchRoute = route('doctor.search'); }
                      elseif ($u->isTechnician()) { $searchRoute = route('technician.search'); }
                      elseif ($u->isLaboratory()) { $searchRoute = route('laboratory.search'); }
                  }
              @endphp
              @if($searchRoute)
              <li class="nav-item position-relative d-none d-md-block me-3" id="global-search-wrap" style="flex: 0 0 auto; width: 420px; max-width: 42vw;">
                <span class="position-absolute top-50 translate-middle-y ms-3 text-muted" style="left:0; pointer-events:none;">
                  <i class="icon-base ti tabler-search icon-sm"></i>
                </span>
                <input type="text" id="global-search-input" autocomplete="off"
                       class="form-control rounded-pill ps-5"
                       data-endpoint="{{ $searchRoute }}"
                       placeholder="{{ __('master.search_by_patient_or_case_id') }}">
                <div id="global-search-results" class="dropdown-menu w-100 shadow mt-1" style="max-height: 360px; overflow-y:auto;"></div>
              </li>
              @endif

              @php
                  $chatRoleName = optional(auth()->user()->role)->name;
              @endphp
              @if(in_array($chatRoleName, ['admin','doctor','technician','laboratory'], true))
              <li class="nav-item me-2 me-xl-1">
                <a class="nav-link btn btn-icon btn-text-secondary rounded-pill position-relative" href="{{ route('messages.index') }}" title="{{ __('master.messages') }}">
                  <i class="icon-base ti tabler-brand-messenger icon-22px text-heading"></i>
                  @php $unreadMsgs = \App\Models\CaseMessage::unreadForUser(auth()->user()); @endphp
                  @if($unreadMsgs > 0)
                    <span class="badge rounded-pill text-bg-danger position-absolute" style="top:4px;right:2px;">{{ $unreadMsgs }}</span>
                  @endif
                </a>
              </li>
              @endif

              <li class="nav-item dropdown-language dropdown">
                <a
                  class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                  href="#"
                  data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-language icon-22px text-heading"></i>
                 
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}" 
                       href="{{ route('language.switch','en') }}" 
                       data-language="en" 
                       data-text-direction="ltr">
                      <span>English</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item {{ app()->getLocale() === 'fr' ? 'active' : '' }}" 
                       href="{{ route('language.switch', 'fr') }}" 
                       data-language="fr" 
                       data-text-direction="ltr">
                      <span>Français</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ Language -->

              <!-- Style Switcher -->
              

              <!-- Quick links  -->
            
@if(auth()->user()->isTechnician() || auth()->user()->isLaboratory() || auth()->user()->isDoctor())
@if(auth()->user()->isTechnician())
@php
  $notifications = \App\Models\Notification::where('technician_id', auth()->user()->id)->orderBy('created_at', 'desc')->get();
@endphp
@endif

@if(auth()->user()->isLaboratory())
@php
  $notifications = \App\Models\Notification::where('laboratory_id', auth()->user()->id)->orderBy('created_at', 'desc')->get();
@endphp
@endif

@if(auth()->user()->isDoctor())
@php
  $notifications = \App\Models\Notification::where('doctor_id', auth()->user()->id)->orderBy('created_at', 'desc')->get();
@endphp
@endif

              <!-- Notification -->
              <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
               
                <a
                  class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                  href="javascript:void(0);"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="outside"
                  aria-expanded="false">
                  <span class="position-relative">
                    <i class="icon-base ti tabler-bell icon-22px text-heading"></i>
                    <span class="badge rounded-pill text-bg-danger badge-notifications" id="notification-count">{{ $notifications->count() > 0 ? $notifications->count() : '' }}</span>
                  </span>
                </a>
               
                <ul class="dropdown-menu dropdown-menu-end p-0">
                  <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                      <h6 class="mb-0 me-auto">{{ __('master.notifications') }}</h6>
                      <div class="d-flex align-items-center h6 mb-0">
                        <span class="badge bg-label-primary me-2" id="notification-count1">{{ $notifications->count() > 0 ? $notifications->count() : '' }} {{ __('master.new') }}</span>
                       
                      </div>
                    </div>
                  </li>
                  <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush">
                      @foreach($notifications as $notification)
                      <li class="list-group-item list-group-item-action dropdown-notifications-item">
                        <div class="d-flex">
                         
                          <div class="flex-grow-1">
                            <h6 class="small mb-1">{{ $notification->title }}</h6>
                            <small class="mb-1 d-block text-body">{{ $notification->message }}</small>
                            <small class="text-body-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                          </div>
                          @if(auth()->user()->isDoctor())
                          <div class="flex-shrink-0 dropdown-notifications-actions">
                          
                            <a onclick="deleteNotification({{ $notification->id }})" class="dropdown-notifications-archive"
                              ><span class="icon-base ti tabler-x"></span
                            ></a>
                          </div>
                          @endif
                        </div>
                      </li>
                      @endforeach
                    
                     
                    
                    </ul>
                  </li>
                  
                </ul>
              </li>
              @endif
              <!--/ Notification -->

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a
                  class="nav-link dropdown-toggle hide-arrow p-0"
                  href="javascript:void(0);"
                  data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="{{ auth()->user()->photo_url }}" alt class="rounded-circle" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item mt-0" href="#">
                      <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-2">
                          <div class="avatar avatar-online">
                            <img src="{{ auth()->user()->photo_url }}" alt class="rounded-circle" />
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                          <small class="text-body-secondary">
                            @if(auth()->user()->isAdmin())
                                Admin
                            @endif
                            @if(auth()->user()->isDoctor())
                                Doctor
                            @endif
                            @if(auth()->user()->isTechnician())
                                Technicien
                            @endif
                            @if(auth()->user()->isLaboratory())
                                Laboratory
                            @endif 
                          </small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider my-1 mx-n2"></div>
                  </li>
                  
                  <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                      <i class="icon-base ti tabler-user me-3 icon-md"></i
                      ><span class="align-middle">{{ __('master.my_profile') }}</span>
                    </a>
                  </li>
                  @if(auth()->user()->isAdmin())
                  <li>
                    <a class="dropdown-item" href="{{ route('admin.settings') }}">
                      <i class="icon-base ti tabler-settings me-3 icon-md"></i
                      ><span class="align-middle">{{ __('master.settings') }}</span>
                    </a>
                  </li>
                  @endif
                 
                  <li>
                    <div class="dropdown-divider my-1 mx-n2"></div>
                  </li>
                  
                  <li>
                    <div class="d-grid px-2 pt-2 pb-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger d-flex">
                                <small class="align-middle">{{ __('master.logout') }}</small>
                                <i class="icon-base ti tabler-logout ms-2 icon-14px"></i>
                            </button>
                        </form>
                    </div>
                  </li>
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>
        </div>
      </nav>

      <!-- / Navbar -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Menu -->
          <aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu flex-grow-0">
            <div class="container-xxl d-flex h-100">
              <!-- Mobile Menu Header (hidden on desktop) -->
              <div class="mobile-menu-header d-xl-none w-100 text-center py-4 border-bottom border-light">
                <div class="avatar avatar-lg mx-auto mb-3">
                  <img src="{{ auth()->user()->photo_url }}" alt="User Avatar" class="rounded-circle" />
                </div>
                <h6 class="text-white mb-1">{{ auth()->user()->name }}</h6>
                <small class="text-white-50">
                  @if(auth()->user()->isAdmin())
                      Administrator
                  @elseif(auth()->user()->isDoctor())
                      Doctor
                  @elseif(auth()->user()->isTechnician())
                      Technician
                  @elseif(auth()->user()->isLaboratory())
                      Laboratory
                  @elseif(auth()->user()->isCommercial())
                      Commercial
                  @endif
                </small>
              </div>
              
              <ul class="menu-inner py-1">
                <!-- Dashboards -->
                @if(auth()->user()->isAdmin())
                <li class="menu-item {{ Route::is('home') ? 'active' : '' }}">
                  <a href="{{ route('home') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-smart-home"></i>
                    <div>{{ __('master.dashboard') }}</div>
                  </a>
                  
                </li>
                <li class="menu-item {{ Route::is('admin.doctors.*')    ? 'active' : '' }}">
                  <a href="{{ route('admin.doctors.list') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-stethoscope"></i>
                    <div>{{ __('master.doctors') }}</div>
                  </a>
                </li>
                <li class="menu-item {{ Route::is('admin.cases.*') ? 'active' : '' }}">
                  <a href="{{ route('admin.cases.list') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div>{{ __('master.cases') }}</div>
                  </a>
                </li>


                <li class="menu-item {{ Route::is('admin.price_manager.*') ? 'active' : '' }}">
                  <a href="{{ route('admin.price_manager.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-currency-dollar"></i>
                    <div>{{ __('master.price_management') }}</div>
                  </a>
                </li>


                <li class="menu-item {{ Route::is('admin.invoices.*') ? 'active' : '' }}">
                  <a href="{{ route('admin.invoices.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-receipt"></i>
                    <div>{{ __('master.invoice_management') }}</div>
                  </a>
                </li>
              
                
                <li class="menu-item {{ Route::is('admin.technicians.*')    ? 'active' : '' }}">
                  <a href="{{ route('admin.technicians.list') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-ruler-off"></i>
                    <div>{{ __('master.technician') }}</div>
                  </a>
                  
                </li>
                <li class="menu-item {{ Route::is('admin.laboratory.*')    ? 'active' : '' }}">
                    <a href="{{ route('admin.laboratories.list') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-microscope"></i>
                    <div>{{ __('master.laboratory') }}</div>
                  </a>
                  
                </li>
                <li class="menu-item {{ Route::is('admin.commercial.*') ? 'active' : '' }}">
                  <a href="{{ route('admin.commercial.list') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-chart-line"></i>
                    <div>{{ __('master.commercial') }}</div>
                  </a>
                </li>



               
               
                @elseif(auth()->user()->isDoctor())
                <li class="menu-item {{ Route::is('home') ? 'active' : '' }}">
                  <a href="{{ route('home') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-smart-home"></i>
                    <div>{{ __('master.dashboard') }}</div>
                  </a>
                  
                </li>

                <li class="menu-item {{ Route::is('doctor.cases') || Route::is('doctor.cases.show') || Route::is('doctor.cases.edit')  || Route::is('doctor.cases.create')   ? 'active' : '' }}">
                  <a href="{{ route('doctor.cases') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div>{{ __('master.cases') }}</div>
                    
                  </a>

                 
                </li>

                @elseif(auth()->user()->isCommercial())
                <li class="menu-item {{ Route::is('commercial.dashboard') ? 'active' : '' }}">
                  <a href="{{ route('commercial.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-chart-line"></i>
                    <div>{{ __('master.dashboard') }}</div>
                  </a>
                </li>

                <li class="menu-item {{ Route::is('commercial.invoices.*') ? 'active' : '' }}">
                  <a href="{{ route('commercial.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-receipt"></i>
                    <div>{{ __('master.invoices') }}</div>
                  </a>
                </li>

                <li class="menu-item {{ Route::is('commercial.doctors.*') ? 'active' : '' }}">
                  <a href="{{ route('commercial.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-users"></i>
                    <div>{{ __('master.doctors') }}</div>
                  </a>
                </li>

                <li class="menu-item {{ Route::is('commercial.cases.*') ? 'active' : '' }}">
                  <a href="{{ route('commercial.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div>{{ __('master.cases') }}</div>
                  </a>
                </li>

             
             


             
               

                <!-- <li class="menu-item {{ Route::is('doctor.calendar.index') || Route::is('doctor.calendar.show') || Route::is('doctor.calendar.edit') || Route::is('doctor.calendar.create')   ? 'active' : '' }}">
                  <a href="{{ route('doctor.calendar.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-calendar-week"></i>
                    <div>{{ __('master.calendar') }}</div>
                  </a>
                
                </li> -->



                @elseif(auth()->user()->isTechnician())
                <li class="menu-item {{ Route::is('home') ? 'active' : '' }}">
                  <a href="{{ route('home') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-smart-home"></i>
                    <div>{{ __('master.dashboard') }}</div>
                  </a>
                  
                </li>

                <li class="menu-item {{ Route::is('technician.cases.index') || Route::is('technician.cases.show') || Route::is('technician.cases.edit')  || Route::is('technician.cases.create')   ? 'active' : '' }}">
                  <a href="{{ route('technician.cases.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div>{{ __('master.cases') }}</div>
                    
                  </a>

                 
                </li>
             
               

                @elseif(auth()->user()->isLaboratory())
                <li class="menu-item {{ Route::is('home') ? 'active' : '' }}">
                  <a href="{{ route('home') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-smart-home"></i>
                    <div>{{ __('master.dashboard') }}</div>
                  </a>
                  
                </li>

                <li class="menu-item {{ Route::is('laboratory.cases.index') || Route::is('laboratory.cases.show') || Route::is('laboratory.cases.edit')  || Route::is('laboratory.cases.create')   ? 'active' : '' }}">
                  <a href="{{ route('laboratory.cases.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-briefcase"></i>
                    <div>{{ __('master.cases') }}</div>
                    
                  </a>

                 
                </li>
             
             


              
                @endif
               
                
               
             
         


               
              </ul>
            </div>
          </aside>
          <!-- / Menu -->


<script>
  @if(auth()->user()->isDoctor())
  


  function updateNotificationCount() {
    var notificationCount = document.getElementById('notification-count');
    if (notificationCount) {
      notificationCount.textContent = response.count;
    }
  }

  function updateNotificationCount1() {
    var notificationCount1 = document.getElementById('notification-count1');
    if (notificationCount1) {
      notificationCount1.textContent = response.count;
    }
  }



  function deleteNotification(notificationId) {
    $.ajax({
      url: "{{ route('doctor.notifications.delete', ['id' => ':id']) }}".replace(':id', notificationId),
      type: 'GET',
      success: function(response) {
        updateNotificationCount();
        updateNotificationCount1();
      }
    });
  }
  @endif

  // Custom Mobile Menu Toggle Implementation
  document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const layoutMenu = document.getElementById('layout-menu');
    const layoutWrapper = document.querySelector('.layout-wrapper');
    const menuOpenIcon = document.getElementById('menu-open-icon');
    const menuCloseIcon = document.getElementById('menu-close-icon');
    const body = document.body;

    console.log('Mobile Menu Elements:', {
      mobileToggle: !!mobileToggle,
      layoutMenu: !!layoutMenu,
      layoutWrapper: !!layoutWrapper,
      menuOpenIcon: !!menuOpenIcon,
      menuCloseIcon: !!menuCloseIcon
    });

    // Debug menu structure
    if (layoutMenu) {
      console.log('Layout Menu Details:', {
        id: layoutMenu.id,
        classes: layoutMenu.className,
        display: window.getComputedStyle(layoutMenu).display,
        position: window.getComputedStyle(layoutMenu).position,
        left: window.getComputedStyle(layoutMenu).left,
        zIndex: window.getComputedStyle(layoutMenu).zIndex
      });
      
      const menuInner = layoutMenu.querySelector('.menu-inner');
      if (menuInner) {
        console.log('Menu Inner found:', {
          classes: menuInner.className,
          children: menuInner.children.length,
          display: window.getComputedStyle(menuInner).display
        });
      } else {
        console.log('Menu Inner NOT found');
      }
    }

    if (mobileToggle && layoutMenu) {
      mobileToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Mobile hamburger menu clicked');
        
        // Toggle menu state
        const isMenuOpen = layoutMenu.classList.contains('menu-open');
        
        if (isMenuOpen) {
          closeMobileMenu();
        } else {
          openMobileMenu();
        }
      });

      // Close menu when clicking outside menu area (but not on tables or interactive elements)
      document.addEventListener('click', function(e) {
        if (layoutMenu.classList.contains('menu-open')) {
          // Don't close if clicking on tables, inputs, or other interactive elements
          if (!layoutMenu.contains(e.target) && 
              !mobileToggle.contains(e.target) &&
              !e.target.closest('table') &&
              !e.target.closest('.dataTables_wrapper') &&
              !e.target.closest('input') &&
              !e.target.closest('select') &&
              !e.target.closest('button')) {
            closeMobileMenu();
          }
        }
      });

      // Close menu on escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && layoutMenu.classList.contains('menu-open')) {
          closeMobileMenu();
        }
      });

      // Close menu on window resize if screen becomes larger
      window.addEventListener('resize', function() {
        if (window.innerWidth >= 1200 && layoutMenu.classList.contains('menu-open')) {
          closeMobileMenu();
        }
      });

      // Prevent touch/swipe gestures from opening menu
      document.addEventListener('touchstart', function(e) {
        // Only allow menu toggle via hamburger button
        if (e.target !== mobileToggle && !mobileToggle.contains(e.target)) {
          // Prevent any swipe gesture detection
          return;
        }
      });

      document.addEventListener('touchmove', function(e) {
        // Prevent swipe gestures on tables from triggering menu
        if (e.target.closest('table') || e.target.closest('.dataTables_wrapper')) {
          e.stopPropagation();
        }
      });

      // Disable any existing swipe handlers
      document.addEventListener('swipe', function(e) {
        e.preventDefault();
        e.stopPropagation();
      });

      document.addEventListener('swiperight', function(e) {
        e.preventDefault();
        e.stopPropagation();
      });

      // Override main.js swipe detection for tables
      let touchStartX = 0;
      let touchStartY = 0;

      document.addEventListener('touchstart', function(e) {
        // If touch starts on table or interactive element, disable swipe menu
        if (e.target.closest('table') || 
            e.target.closest('.dataTables_wrapper') ||
            e.target.closest('.table-responsive') ||
            e.target.closest('.card-datatable')) {
          
          // Mark this as a table interaction
          e.target.setAttribute('data-table-interaction', 'true');
          
          // Prevent the main.js swipe handler from working
          e.stopImmediatePropagation();
        }
      }, true); // Use capture phase

      document.addEventListener('touchmove', function(e) {
        // If this is a table interaction, prevent menu swipe
        if (e.target.hasAttribute('data-table-interaction') ||
            e.target.closest('[data-table-interaction]')) {
          e.stopImmediatePropagation();
        }
      }, true); // Use capture phase

      document.addEventListener('touchend', function(e) {
        // Clean up table interaction marker
        document.querySelectorAll('[data-table-interaction]').forEach(el => {
          el.removeAttribute('data-table-interaction');
        });
      }, true); // Use capture phase

      function openMobileMenu() {
        console.log('Opening mobile menu');
        
        // Add classes
        layoutMenu.classList.add('menu-open');
        layoutWrapper.classList.add('menu-open');
        body.classList.add('mobile-menu-open');
        
        // Debug after adding classes
        console.log('After opening - Menu classes:', layoutMenu.className);
        console.log('After opening - Menu position:', {
          left: window.getComputedStyle(layoutMenu).left,
          transform: window.getComputedStyle(layoutMenu).transform,
          display: window.getComputedStyle(layoutMenu).display,
          visibility: window.getComputedStyle(layoutMenu).visibility
        });
        
        // Toggle icons
        if (menuOpenIcon) menuOpenIcon.style.display = 'none';
        if (menuCloseIcon) menuCloseIcon.style.display = 'block';
        
        // Allow body scroll (removed overflow hidden)
        
        // Add staggered animation to menu items
        const menuItems = layoutMenu.querySelectorAll('.menu-item');
        console.log('Found menu items:', menuItems.length);
        
        menuItems.forEach((item, index) => {
          item.style.animationDelay = `${index * 0.1}s`;
          item.classList.add('menu-item-fade-in');
        });

        // Remove animation classes after animation
        setTimeout(() => {
          menuItems.forEach(item => {
            item.classList.remove('menu-item-fade-in');
            item.style.animationDelay = '';
          });
        }, 600);
      }

      function closeMobileMenu() {
        console.log('Closing mobile menu');
        
        // Add slide out animation
        layoutMenu.classList.add('menu-slide-out');
        
        // Remove classes after animation
        setTimeout(() => {
          layoutMenu.classList.remove('menu-open', 'menu-slide-out');
          layoutWrapper.classList.remove('menu-open');
          body.classList.remove('mobile-menu-open');
          
          // Toggle icons
          if (menuOpenIcon) menuOpenIcon.style.display = 'block';
          if (menuCloseIcon) menuCloseIcon.style.display = 'none';
          
          // Body scroll remains unaffected
        }, 300);
      }
    }
  });
</script>

<style>
/* Enhanced Mobile Menu Styles */
@media (max-width: 1199.98px) {
  /* Ensure proper mobile menu positioning - Multiple selectors for compatibility */
  #layout-menu,
  .layout-menu-horizontal,
  .layout-wrapper #layout-menu,
  .layout-wrapper.layout-without-menu #layout-menu {
    position: fixed !important;
    top: 64px !important;
    left: -100% !important;
    width: 100 !important;
    height: calc(100vh - 64px) !important;
    background: rgba(255, 255, 255, 0.98) !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    z-index: 1030 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    border-right: none !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border-radius: 0 20px 20px 0 !important;
    display: block !important;
    visibility: visible !important;
    /* Enhanced scrolling */
    -webkit-overflow-scrolling: touch !important;
    scroll-behavior: smooth !important;
  }

  /* Custom scrollbar for mobile menu */
  #layout-menu::-webkit-scrollbar {
    width: 6px !important;
  }

  #layout-menu::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1) !important;
    border-radius: 10px !important;
  }

  #layout-menu::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
  }

  #layout-menu::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6b4190 100%) !important;
  }

  /* Show menu when open - Multiple selectors for compatibility */
  #layout-menu.menu-open,
  .layout-menu-horizontal.menu-open,
  .layout-wrapper #layout-menu.menu-open,
  .layout-wrapper.layout-without-menu #layout-menu.menu-open,
  .layout-wrapper.menu-open #layout-menu {
    left: 0 !important;
    transform: translateX(0) !important;
  }

  /* Mobile menu inner styling */
  .layout-menu-horizontal .menu-inner {
    flex-direction: column !important;
         width: calc(100% - 2rem) !important; 
        padding: -0.5rem 1rem 2rem 1rem !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        border-radius: 15px !important;
        margin: 1rem !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        min-height: auto !important;
        max-height: none !important;
        flex-shrink: 0 !important;
  }

  /* Mobile menu items */
  .layout-menu-horizontal .menu-item {
    width: 100% !important;
    margin-bottom: 0.2rem !important;
    transform: translateX(-10px);
    opacity: 0;
    animation: slideInMenuItem 0.4s ease forwards;
  }

  .layout-menu-horizontal .menu-link {
    padding: 1rem 1.25rem !important;
    border-radius: 12px !important;
    color: #2c3e50 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display: flex !important;
    align-items: center !important;
    text-decoration: none !important;
    font-weight: 500 !important;
    background: rgba(255, 255, 255, 0.7) !important;
    border: 1px solid rgba(0, 0, 0, 0.05) !important;
    position: relative !important;
    overflow: hidden !important;
  }

  .layout-menu-horizontal .menu-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    transition: left 0.5s ease;
  }

  .layout-menu-horizontal .menu-link:hover::before {
    left: 100%;
  }

  .layout-menu-horizontal .menu-link:hover,
  .layout-menu-horizontal .menu-item.active .menu-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    transform: translateX(8px) scale(1.02) !important;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3) !important;
    border-color: transparent !important;
  }

  .layout-menu-horizontal .menu-item.active .menu-link {
    background: linear-gradient(135deg, #48c78e 0%, #06d6a0 100%) !important;
    box-shadow: 0 8px 25px rgba(72, 199, 142, 0.3) !important;
  }

  .layout-menu-horizontal .menu-icon {
    margin-right: 1rem !important;
    font-size: 1.4rem !important;
            color: rgb(26 26 26 / 80%) !important;

    transition: all 0.3s ease !important;
  }

  .layout-menu-horizontal .menu-link:hover .menu-icon {
    transform: scale(1.1) rotate(5deg) !important;
  }

  /* Mobile menu header styles */
  .mobile-menu-header {
    background: linear-gradient(135deg, #252527 0%, #1e72d9 100%) !important;
        border-bottom: none !important;
        border-radius: 15px !important;
        margin: 1rem !important;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        width: 93% !important;
  }

  .mobile-menu-header h6 {
    color: white !important;
    font-weight: 600 !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
  }

  .mobile-menu-header small {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 500 !important;
  }

  .mobile-menu-header .avatar {
    border: 3px solid rgba(255, 255, 255, 0.3) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
  }

  /* Remove overlay - no background when menu is open */
  .layout-wrapper.menu-open::before {
    display: none;
  }

  /* Container adjustments for mobile */
  .layout-menu-horizontal .container-xxl {
    width: 100% !important;
    max-width: none !important;
    padding: 0 !important;
    flex-direction: column !important;
    height: 100% !important;
    display: flex !important;
  }

  /* Ensure menu is visible on mobile */
  .layout-menu-horizontal {
    display: block !important;
  }
  
  .layout-menu-horizontal .menu-inner {
    display: flex !important;
  }
  
  .layout-menu-horizontal .menu-item {
    display: block !important;
  }
  
  .layout-menu-horizontal .menu-link {
    display: flex !important;
  }

  /* Animation classes */
  .menu-item-slide-in {
    animation: slideInFromLeft 0.3s ease forwards;
  }

  .menu-item-fade-in {
    animation: fadeInUp 0.4s ease forwards;
    opacity: 0;
    transform: translateY(20px);
  }

  /* Slide out animation for menu */
  #layout-menu.menu-slide-out {
    animation: slideOutLeft 0.3s ease forwards;
  }

  @keyframes slideInFromLeft {
    from {
      transform: translateX(-20px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes slideOutLeft {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(-100%);
      opacity: 0;
    }
  }

  @keyframes slideInMenuItem {
    from {
      transform: translateX(-10px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  /* Body class when mobile menu is open - allow scrolling */
  body.mobile-menu-open {
    /* Removed overflow hidden to allow page scrolling */
  }

  /* Enhanced hamburger button */
  #mobile-menu-toggle {
    position: relative;
    z-index: 1035;
    transition: all 0.3s ease;
  }

  #mobile-menu-toggle:hover {
    transform: scale(1.1);
  }

  #mobile-menu-toggle i {
    transition: all 0.3s ease;
  }

  /* Disable touch actions on tables and interactive elements to prevent menu trigger */
  table,
  .dataTables_wrapper,
  .table-responsive,
  .card-datatable {
    touch-action: pan-x pan-y !important;
    -webkit-touch-callout: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: none !important;
  }

  /* Prevent swipe gestures on table content */
  table td,
  table th,
  .dataTables_wrapper * {
    pointer-events: auto !important;
    touch-action: pan-x pan-y !important;
  }
}

/* Desktop horizontal menu adjustments */
@media (min-width: 1200px) {
  .layout-menu-horizontal .menu-inner {
    display: flex !important;
    flex-direction: row !important;
  }

  .layout-menu-horizontal .menu-item {
    margin-right: 1rem !important;
  }

  .mobile-menu-header {
    display: none !important;
  }
}

/* Global header search */
#global-search-results .gs-item { padding: .55rem .9rem; cursor: pointer; border-bottom: 1px solid var(--bs-border-color, #eef0f2); white-space: normal; }
#global-search-results .gs-item:last-child { border-bottom: 0; }
#global-search-results .gs-item:hover,
#global-search-results .gs-item.active { background-color: rgba(1,185,198,.08); }
#global-search-results .gs-cid { font-weight: 600; color: #01b9c6; }
#global-search-results .gs-meta { font-size: .8rem; color: #8a909d; }
#global-search-results .gs-empty { padding: .75rem .9rem; color: #8a909d; font-size: .85rem; }
</style>

@push('scripts')
<script>
(function () {
    const input = document.getElementById('global-search-input');
    const box = document.getElementById('global-search-results');
    if (!input || !box) return;

    const endpoint = input.getAttribute('data-endpoint');
    if (!endpoint) return;
    const labels = {
        searching: '{{ __('master.searching') }}',
        none: '{{ __('master.no_results_found') }}',
        patient: '{{ __('master.patient') }}',
        doctor: '{{ __('master.doctor') }}'
    };

    let timer = null;
    let activeIndex = -1;
    let items = [];

    function show() { box.classList.add('show'); }
    function hide() { box.classList.remove('show'); activeIndex = -1; }

    function render(results) {
        items = results || [];
        if (!items.length) {
            box.innerHTML = '<div class="gs-empty">' + labels.none + '</div>';
            show();
            return;
        }
        box.innerHTML = items.map(function (r, i) {
            const ref = r.reference ? ' &middot; ' + r.reference : '';
            const doc = r.doctor ? ' &middot; ' + labels.doctor + ': ' + escapeHtml(r.doctor) : '';
            return '<div class="gs-item" data-index="' + i + '" data-url="' + r.url + '">' +
                '<div class="gs-cid">' + escapeHtml(r.case_id || '') + '</div>' +
                '<div class="gs-meta">' + escapeHtml(r.patient || '') + ref + doc + '</div>' +
                '</div>';
        }).join('');
        show();
        box.querySelectorAll('.gs-item').forEach(function (el) {
            el.addEventListener('mousedown', function (e) {
                e.preventDefault();
                window.location.href = el.getAttribute('data-url');
            });
        });
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function search(term) {
        fetch(endpoint + '?q=' + encodeURIComponent(term), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) { render(data.results); })
        .catch(function () { hide(); });
    }

    input.addEventListener('input', function () {
        const term = input.value.trim();
        clearTimeout(timer);
        if (term.length < 2) { hide(); return; }
        box.innerHTML = '<div class="gs-empty">' + labels.searching + '</div>';
        show();
        timer = setTimeout(function () { search(term); }, 250);
    });

    input.addEventListener('keydown', function (e) {
        const nodes = box.querySelectorAll('.gs-item');
        if (!nodes.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = (activeIndex + 1) % nodes.length;
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = (activeIndex - 1 + nodes.length) % nodes.length;
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && nodes[activeIndex]) {
                e.preventDefault();
                window.location.href = nodes[activeIndex].getAttribute('data-url');
            }
            return;
        } else if (e.key === 'Escape') {
            hide();
            return;
        } else {
            return;
        }
        nodes.forEach(function (n) { n.classList.remove('active'); });
        if (nodes[activeIndex]) nodes[activeIndex].classList.add('active');
    });

    document.addEventListener('click', function (e) {
        if (!box.contains(e.target) && e.target !== input) hide();
    });
    input.addEventListener('focus', function () {
        if (items.length && input.value.trim().length >= 2) show();
    });
})();
</script>
@endpush
