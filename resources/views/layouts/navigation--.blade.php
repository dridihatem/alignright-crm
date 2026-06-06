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
            <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
              <i class="icon-base ti tabler-menu-2 icon-md"></i>
              <i class="icon-base ti tabler-x icon-md" style="display: none;"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
            <ul class="navbar-nav flex-row align-items-center ms-md-auto">
             
            


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
                  @if(auth()->user()->isDoctor())
                  <li class="dropdown-item">
                   
                      <i class="icon-base ti tabler-code me-3 icon-md"></i
                      ><span class="align-middle badge bg-label-primary" id="code-parrent">{{ auth()->user()->code_parrent}}</span> <span class="align-middle" id="copy-code"><i class="icon-base ti tabler-copy"></i></span>
                 
                  </li>
                  @if (!auth()->user()->google_access_token)
                  <li class="dropdown-item">
                    <a href="{{ route('google.auth') }}" class="btn btn-primary">
                      <i class="icon-base ti tabler-brand-google-drive me-3 icon-md"></i>
                      <span class="align-middle">{{ __('master.connect_google_drive') }}</span>
                    </a>
                  </li>
                  @else
                  <li class="dropdown-item">
                   
                      <i class="icon-base ti tabler-brand-google-drive me-3 icon-md"></i>
                      <span class="align-middle">{{ __('master.google_drive_connected') }}</span>
                  
                  </li>
                @endif
                  @endif
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
                  @endif
                </small>
              </div>
              
              <ul class="menu-inner">
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

             
             
                
                <li class="menu-item {{ Route::is('doctor.patients') || Route::is('doctor.patients.show') || Route::is('doctor.patients.edit') || Route::is('doctor.patients.create')   ? 'active' : '' }}">
                  <a href="{{ route('doctor.patients') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user-pentagon"></i>
                    <div>{{ __('master.patients') }}</div>
                  </a>
                  
                </li>


             
               

                <li class="menu-item {{ Route::is('doctor.calendar.index') || Route::is('doctor.calendar.show') || Route::is('doctor.calendar.edit') || Route::is('doctor.calendar.create')   ? 'active' : '' }}">
                  <a href="{{ route('doctor.calendar.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-calendar-week"></i>
                    <div>{{ __('master.calendar') }}</div>
                  </a>
                
                </li>



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
  document.getElementById('copy-code').addEventListener('click', function() {
    navigator.clipboard.writeText(document.getElementById('code-parrent').textContent);
    toastr.success('{{ __('master.code_copied') }}');
  });
  


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
</script>
