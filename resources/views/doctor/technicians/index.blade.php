<x-app-layout>
    @push('styles')
   @endpush

   <!-- Content -->
   <div class="container-xxl flex-grow-1 container-p-y">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
           

          <div class="col-md-12 col-xxl-12 mb-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                  <div class="card-title mb-0">
                    <h5 class="mb-1">{{ __('master.technician_list') }}</h5>
                  </div>
                  <div class="card-header-action">
                    <a href="{{ route('doctor.technicians.create') }}" class="btn btn-primary">{{ __('master.add_technician') }}</a>
                  </div>
                 
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-4">
                    <table class="table  table-sm" id="technician_list_table">
                        <thead class="border-top">
                            <tr>
                                <th>{{ __('master.technician_name') }}</th>   
                                <th>{{ __('master.technician_email') }}</th>
                                <th>{{ __('master.technician_count_cases') }}</th>
                                <th>{{ __('master.technician_photo') }}</th>
                                <th>{{ __('master.technician_status') }}</th>
                                <th>{{ __('master.action') }}</th>
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
   </div>
   @push('scripts')
   
   <script src="{{ asset('assets/js/dataTables-all.js') }}"></script>
   @endpush
</x-app-layout>
