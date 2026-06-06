<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />   
    @endpush 
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
          <div class="col-md-12 col-xxl-12 mb-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                  <div class="card-title mb-0">
                    <h5 class="mb-1">{{ __('master.cases') }}</h5>
                  </div>
                </div>
                <div class="card-body">
                    @include('partials.cases_by_patient', ['patientGroups' => $patientGroups, 'caseShowRoute' => 'technician.cases.show', 'tableId' => 'technicianPatientsCasesTable'])
                </div>
            </div>
        </div>
        </div>
    </div>
    @push('scripts')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js') }}"></script>
    <script src="{{ asset('assets/js/forms-pickers.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables-all-technician.js') }}"></script>

    <script>
       


        // Use event delegation for dynamically added elements
        $(document).on('click', '.open-technician-modal', function(e) {
            e.preventDefault();
            var caseId = $(this).data('case-id');
            $('#technician_case_id').val(caseId);
        });

        $(document).on('click', '.open-laboratory-modal', function(e) {
            e.preventDefault();
            var caseId = $(this).data('case-id');
            $('#laboratory_case_id').val(caseId);
        });

        

    </script>   
@endpush


</x-app-layout>



   

