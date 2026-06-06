<x-app-layout>
    <x-slot name="title">{{ __('master.laboratory_details') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.laboratories.list') }}">{{ __('master.laboratory_list') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.laboratory_details') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-flask me-2"></i>
                        {{ __('master.laboratory_details') }}
                    </h5>
                    <small class="text-muted">{{ __('master.laboratory_information') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.laboratories.edit', $laboratory->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> {{ __('master.edit') }}
                    </a>
                    <a href="{{ route('admin.laboratories.list') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('master.back') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Laboratory Information -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('master.laboratory_information') }}</h6>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $laboratory->photo_url }}" 
                             alt="{{ $laboratory->name }}" 
                             class="rounded-circle mb-3" 
                             width="120" height="120"
                             style="object-fit: cover;">
                        
                        <h5 class="mb-1">{{ $laboratory->name }}</h5>
                        <p class="text-muted mb-3">{{ $laboratory->email }}</p>
                        
                        @if($laboratory->status == 'active')
                            <span class="badge bg-success mb-3">{{ __('master.active') }}</span>
                        @else
                            <span class="badge bg-danger mb-3">{{ __('master.inactive') }}</span>
                        @endif

                        <div class="row text-start">
                            @if($laboratory->phone)
                                <div class="col-12 mb-2">
                                    <strong>{{ __('master.phone') }}:</strong> {{ $laboratory->phone }}
                                </div>
                            @endif
                            
                            @if($laboratory->address)
                                <div class="col-12 mb-2">
                                    <strong>{{ __('master.address') }}:</strong> {{ $laboratory->address }}
                                </div>
                            @endif
                            
                            @if($laboratory->specialization)
                                <div class="col-12 mb-2">
                                    <strong>{{ __('master.specialization') }}:</strong> {{ $laboratory->specialization }}
                                </div>
                            @endif
                            
                            @if($laboratory->license_number)
                                <div class="col-12 mb-2">
                                    <strong>{{ __('master.license_number') }}:</strong> {{ $laboratory->license_number }}
                                </div>
                            @endif
                            
                            @if($laboratory->bio)
                                <div class="col-12 mb-2">
                                    <strong>{{ __('master.biography') }}:</strong>
                                    <p class="mb-0">{{ $laboratory->bio }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('master.statistics') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="mb-1 text-primary">{{ $cases->count() }}</h4>
                                    <small class="text-muted">{{ __('master.total_cases') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="mb-1 text-success">{{ $cases->where('status', 'shipped')->count() }}</h4>
                                <small class="text-muted">{{ __('master.completed_cases') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cases -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ __('master.cases') }} ({{ $cases->count() }})</h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshCases()">
                                <i class="fas fa-sync-alt me-1"></i> {{ __('master.refresh_cases') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($cases->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover laboratory-cases-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('master.case_id') }}</th>
                                            <th>{{ __('master.patient') }}</th>
                                            <th>{{ __('master.treatment_type') }}</th>
                                            <th>{{ __('master.status') }}</th>
                                            <th>{{ __('master.created_date') }}</th>
                                            <th>{{ __('master.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cases as $case)
                                            <tr>
                                                <td>
                                                    <strong>{{ $case->case_id }}</strong>
                                                </td>
                                                <td>
                                                    @if($case->patient)
                                                        <div class="d-flex align-items-center">
                                                            @if($case->patient->photo)
                                                                <img src="{{ $case->patient->photo_url }}" 
                                                                     alt="{{ $case->patient->name }}" 
                                                                     class="rounded-circle me-2" 
                                                                     width="30" height="30"
                                                                     style="object-fit: cover;">
                                                            @endif
                                                            <span>{{ $case->patient->name }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">{{ __('master.no_patient') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($case->treatment_type)
                                                        <span class="badge bg-label-info">{{ $case->treatment_type }}</span>
                                                    @else
                                                        <span class="text-muted">{{ __('master.not_set') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @switch($case->status)
                                                        @case('pending')
                                                            <span class="badge bg-label-warning">{{ __('master.pending') }}</span>
                                                            @break
                                                        @case('draft')
                                                            <span class="badge bg-label-secondary">{{ __('master.draft') }}</span>
                                                            @break
                                                        @case('in_planning')
                                                            <span class="badge bg-label-info">{{ __('master.in_planning') }}</span>
                                                            @break
                                                        @case('approval')
                                                            <span class="badge bg-label-success">{{ __('master.approval') }}</span>
                                                            @break
                                                        @case('in_production')
                                                            <span class="badge bg-label-primary">{{ __('master.in_production') }}</span>
                                                            @break
                                                        @case('shipped')
                                                            <span class="badge bg-label-success">{{ __('master.shipped') }}</span>
                                                            @break
                                                        @case('rejected')
                                                            <span class="badge bg-label-danger">{{ __('master.rejected') }}</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-label-secondary">{{ ucfirst($case->status) }}</span>
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <small>{{ $case->created_at->format('d/m/Y H:i') }}</small>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                type="button" 
                                                                data-bs-toggle="dropdown" 
                                                                aria-expanded="false">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('admin.cases.show', $case->id) }}">
                                                                    <i class="fas fa-eye me-2"></i>{{ __('master.view') }}
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('admin.cases.edit', $case->id) }}">
                                                                    <i class="fas fa-edit me-2"></i>{{ __('master.edit') }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('master.no_cases_found') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable for laboratory cases
            $('.laboratory-cases-table').DataTable({
                processing: true,
                serverSide: false,
                pageLength: 10,
                responsive: true,
                order: [[4, 'desc']], // Sort by created date descending
                language: {
                    search: "{{ __('master.search_cases') }}:",
                    lengthMenu: "{{ __('master.show_cases_per_page') }} _MENU_",
                    info: "{{ __('master.showing_cases') }} _START_ {{ __('master.to') }} _END_ {{ __('master.of') }} _TOTAL_",
                    infoEmpty: "{{ __('master.no_cases_found') }}",
                    infoFiltered: "({{ __('master.filtered_from') }} _MAX_ {{ __('master.total_cases') }})",
                    emptyTable: "{{ __('master.no_cases_found') }}",
                    paginate: {
                        first: "{{ __('master.first') }}",
                        last: "{{ __('master.last') }}",
                        next: "{{ __('master.next') }}",
                        previous: "{{ __('master.previous') }}"
                    }
                },
                columnDefs: [
                    {
                        targets: -1, // Actions column
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        function refreshCases() {
            location.reload();
        }
    </script>
    @endpush
</x-app-layout>
