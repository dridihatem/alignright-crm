<x-app-layout>
    <x-slot name="title">{{ __('master.commercial_list') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.commercial_list') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="icon-base ti tabler-chart-line me-2"></i>
                        {{ __('master.commercial_list') }}
                    </h5>
                    <small class="text-muted">{{ __('master.commercial_details') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.commercial.create') }}" class="btn btn-primary btn-sm">
                        <i class="icon-base ti tabler-plus me-1"></i> {{ __('master.add_commercial') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Commercial Users Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive mb-4">
                    <table class="table table-sm" id="commercialTable">
                        <thead class="border-top">
                            <tr>
                                <th>{{ __('master.photo') }}</th>
                                <th>{{ __('master.name') }}</th>
                                <th>{{ __('master.email') }}</th>
                                <th>{{ __('master.phone') }}</th>
                                <th>{{ __('master.status') }}</th>
                                <th>{{ __('master.created_at') }}</th>
                                <th>{{ __('master.actions') }}</th>
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
            $('#commercialTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.commercial.getcommercial') }}",
                columns: [
                    {data: 'photo', name: 'photo', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'status_badge', name: 'status'},
                    {data: 'created_at_formatted', name: 'created_at'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],
                order: [[1, 'asc']],
                responsive: true,
                pageLength: 10
            });
        });
    </script>
    @endpush
</x-app-layout>

