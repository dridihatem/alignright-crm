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
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <a href="{{ route('doctor.cases.create') }}" class="btn rounded-pill btn-primary">  <i class="icon-base ti tabler-plus icon-md"></i>{{ __('master.add_case') }}</a>
                         <a class="btn rounded-pill btn-secondary float-end text-white" href="{{ route('doctor.cases.exportPdf') }}" target="_blank">  <i class="icon-base ti tabler-file-export icon-md"></i> {{ __('master.export_pdf') }}</a>
                      </div>
                </div>
            </div>

          <div class="col-md-12 col-xxl-12 mb-6">
            <div class="card h-100">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#cases-tab" role="tab">
                                <i class="icon-base ti tabler-file-text me-1"></i>{{ __('master.cases') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#invoices-tab" role="tab">
                                <i class="icon-base ti tabler-receipt me-1"></i>{{ __('master.invoices') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Cases Tab -->
                        <div class="tab-pane fade show active" id="cases-tab" role="tabpanel">
                            @include('partials.cases_by_patient', ['patientGroups' => $patientGroups, 'caseShowRoute' => 'doctor.cases.show', 'tableId' => 'doctorPatientsCasesTable'])
                        </div>
                        
                        <!-- Invoices Tab -->
                        <div class="tab-pane fade" id="invoices-tab" role="tabpanel">
                            <div class="table-responsive mb-4">
                                <table class="table table-sm" id="invoices_table">
                                    <thead class="border-top">
                                        <tr>
                                            <th>{{ __('master.invoice_number') }}</th>
                                            <th>{{ __('master.case_id') }}</th>
                                            <th>{{ __('master.patient_name') }}</th>
                                            <th>{{ __('master.amount') }}</th>
                                            <th>{{ __('master.status') }}</th>
                                            <th>{{ __('master.due_date') }}</th>
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
    </div>

    @push('scripts')
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js') }}"></script>
    <script src="{{ asset('assets/js/forms-pickers.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables-all.js') }}"></script>

    <script>
        $(document).ready(function() {
           
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
     
  
            
            if ($.fn.DataTable.isDataTable('#invoices_table')) {
                $('#invoices_table').DataTable().destroy();
            }


            // Initialize DataTable for invoices
            var invoicesTable = $('#invoices_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("doctor.invoices.getInvoices") }}',
                    type: 'GET'
                },
                columns: [
                    {data: 'invoice_number', name: 'invoice_number'},
                    {data: 'case_id', name: 'case_id'},
                    {data: 'patient_name', name: 'patient_name'},
                    {data: 'amount', name: 'amount'},
                    {data: 'status_badge', name: 'status', orderable: false, searchable: false},
                    {data: 'due_date', name: 'due_date'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],
                order: [[5, 'desc']], // Order by due date descending
                pageLength: 10,
                responsive: true,
                language: {
                    search: "{{ __('master.search') }}:",
                    lengthMenu: "{{ __('master.show') }} _MENU_ {{ __('master.entries') }}",
                    info: "{{ __('master.showing') }} _START_ {{ __('master.to') }} _END_ {{ __('master.of') }} _TOTAL_ {{ __('master.entries') }}",
                    infoEmpty: "{{ __('master.showing') }} 0 {{ __('master.to') }} 0 {{ __('master.of') }} 0 {{ __('master.entries') }}",
                    infoFiltered: "({{ __('master.filtered_from') }} _MAX_ {{ __('master.total_entries') }})",
                    infoPostFix: "",
                    loadingRecords: "{{ __('master.loading') }}...",
                    zeroRecords: "{{ __('master.no_matching_records_found') }}",
                    emptyTable: "{{ __('master.no_data_available_in_table') }}",
                    paginate: {
                        first: "{{ __('master.first') }}",
                        previous: "{{ __('master.previous') }}",
                        next: "{{ __('master.next') }}",
                        last: "{{ __('master.last') }}"
                    }
                }
            });

            // Handle PDF export button click
            $('#export-pdf').on('click', function(e) {
                e.preventDefault();
                
                // Get current filter values
                var filters = $('#searchForm').serialize();
                
                // Redirect to export PDF with filters
                window.open('{{ route("doctor.cases.exportPdf") }}?' + filters, '_blank');
            });

        });
    </script>
    @endpush


</x-app-layout>



   

