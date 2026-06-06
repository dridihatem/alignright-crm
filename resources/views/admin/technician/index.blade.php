<x-app-layout>
    <x-slot name="title">{{ __('master.technician_list') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.technician_list') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>
                        {{ __('master.technician_list') }}
                    </h5>
                    <small class="text-muted">{{ __('master.technician_details') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.technicians.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> {{ __('master.add_technician') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Technicians Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="techniciansTable">
                        <thead>
                            <tr>
                                <th>{{ __('master.technician_photo') }}</th>
                                <th>{{ __('master.technician_name') }}</th>
                                <th>{{ __('master.technician_email') }}</th>
                                <th>{{ __('master.technician_count_cases') }}</th>
                                <th>{{ __('master.technician_status') }}</th>
                                <th>{{ __('master.technician_action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#techniciansTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.technicians.gettechnicians') }}",
                columns: [
                    {data: 'technician_photo', name: 'technician_photo', orderable: false, searchable: false},
                    {data: 'technician_name', name: 'technician_name'},
                    {data: 'technician_email', name: 'technician_email'},
                    {data: 'technician_count_cases', name: 'technician_count_cases'},
                    {data: 'technician_status', name: 'technician_status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[1, 'asc']],
               
            });
        });
    </script>
    @endpush
</x-app-layout>
