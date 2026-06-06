<x-app-layout>
    <x-slot name="title">{{ __('master.laboratory_list') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.laboratory_list') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-flask me-2"></i>
                        {{ __('master.laboratory_list') }}
                    </h5>
                    <small class="text-muted">{{ __('master.laboratory_details') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.laboratories.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> {{ __('master.add_laboratory') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Laboratories Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive mb-4">
                    <table class="table  table-sm" id="laboratoriesTable">
                        <thead class="border-top">
                            <tr>
                                <th>{{ __('master.laboratory_photo') }}</th>
                                <th>{{ __('master.laboratory_name') }}</th>
                                <th>{{ __('master.laboratory_email') }}</th>
                                <th>{{ __('master.laboratory_count_cases') }}</th>
                                <th>{{ __('master.laboratory_status') }}</th>
                                <th>{{ __('master.laboratory_action') }}</th>
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
            $('#laboratoriesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.laboratories.getlaboratories') }}",
                columns: [
                    {data: 'laboratory_photo', name: 'laboratory_photo', orderable: false, searchable: false},
                    {data: 'laboratory_name', name: 'laboratory_name'},
                    {data: 'laboratory_email', name: 'laboratory_email'},
                    {data: 'laboratory_count_cases', name: 'laboratory_count_cases'},
                    {data: 'laboratory_status', name: 'laboratory_status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[1, 'asc']],
                
            });
        });
    </script>
    @endpush
</x-app-layout>
