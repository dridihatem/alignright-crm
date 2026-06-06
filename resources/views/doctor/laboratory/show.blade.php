<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-profile.css') }}" />
   @endpush

   <!-- Content -->
   <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
          <div class="card mb-6">
            <div class="user-profile-header-banner">
                <img src="{{ asset('assets/img/pages/profile-banner.png') }}" alt="Banner image" class="rounded-top">
            </div>
            <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
              <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                <img src="{{ $laboratory->photo_url }}" alt="user image" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img">
              </div>
              <div class="flex-grow-1 mt-3 mt-lg-5">
                <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                  <div class="user-profile-info">
                    <h4 class="mb-2 mt-lg-6">{{ $laboratory->name }}</h4>
                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="icon-base ti tabler-mail icon-lg"></i><span class="fw-medium">{{ $laboratory->email }}</span>
                      </li>
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="icon-base ti tabler-user-check icon-lg"></i><span class="fw-medium">{{ $laboratory->status }}</span>
                      </li>
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="icon-base ti tabler-calendar icon-lg"></i><span class="fw-medium">{{ $laboratory->created_at->format('d M Y') }}</span>
                      </li>

                    </ul>
                  </div>
                 <a href="javascript:void(0)" class="btn btn-primary mb-1 waves-effect waves-light">
                            <i class="icon-base ti tabler-file-text icon-xs me-2"></i>{{ $cases->count() }} {{ __('master.cases') }}
                          </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> 
      
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title">{{ __('master.cases') }}</h5>
            </div>  
            <div class="card-body">
                <div class="table-responsive mb-4">
                     <input type="hidden" id="laboratory_id" value="{{ $laboratory->id }}">
                <table class="table table-bordered" id="laboratory_cases_table">
                    <thead class="border-top ">
                        <tr>
                            <th>{{ __('master.case_id') }}</th>
                            <th>{{ __('master.patient_name') }}</th>
                            <th>{{ __('master.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                    </tbody>
                </table>    
                </div>
            </div>  
          </div>
        </div>
      </div>    
      
    
   </div>
   @push('scripts')
   <script src="{{ asset('assets/js/dataTables-all.js') }}"></script>
   @endpush
</x-app-layout>
