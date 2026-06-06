<x-app-layout>
    <x-slot name="title">{{ __('master.cases') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.cases') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>
                        {{ __('master.cases') }}
                    </h5>
                    <small class="text-muted">{{ __('master.all_cases_by_doctor') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.cases.table') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-table me-1"></i> {{ __('master.table_view') }}
                    </a>
                </div>
                
            </div>
        </div>

        <!-- Cases by Doctor -->
        @if($doctors->count() > 0)
            @foreach($doctors as $doctor)
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="{{ $doctor->photo_url }}" 
                                     alt="{{ $doctor->name }}" 
                                     class="rounded-circle me-3" 
                                     width="40" height="40"
                                     style="object-fit: cover;">
                                <div>
                                    <h6 class="mb-0">{{ $doctor->name }}</h6>
                                    <small class="text-muted">{{ $doctor->email }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-label-primary">{{ $doctor->cases->count() }} {{ __('master.cases') }}</span>
                                <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> {{ __('master.view_doctor') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($doctor->cases->count() > 0)
                        <div class="table-responsive mb-4">
                            <table class="table  table-sm doctor-cases-table" data-doctor-id="{{ $doctor->id }}">
                                    <thead class="border-top">
                                        <tr>
                                            <th>{{ __('master.case_id') }}</th>
                                            <th>{{ __('master.patient') }}</th>
                                            <th>{{ __('master.treatment_type') }}</th>
                                            <th>{{ __('master.status') }}</th>
                                            <th>{{ __('master.price') }}</th>
                                            <th>{{ __('master.created_date') }}</th>
                                            <th>{{ __('master.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($doctor->cases as $case)
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
                                                    @if($case->price)
                                                        <strong class="text-success">Tnd {{ number_format($case->price, 2) }}</strong>
                                                    @else
                                                        <span class="text-muted">{{ __('master.not_set') }}</span>
                                                    @endif
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
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" 
                                                                   href="{{ route('admin.cases.delete', $case->id) }}"
                                                                   onclick="return confirm('{{ __('master.are_you_sure') }}')">
                                                                    <i class="fas fa-trash me-2"></i>{{ __('master.delete') }}
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
                                <p class="text-muted">{{ __('master.no_cases_for_doctor') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Unassigned Cases -->
        @if($unassignedCases->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                {{ __('master.unassigned_cases') }}
                            </h6>
                            <small class="text-muted">{{ __('master.cases_without_doctor') }}</small>
                        </div>
                        <span class="badge bg-warning">{{ $unassignedCases->count() }} {{ __('master.cases') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover unassigned-cases-table">
                            <thead>
                                <tr>
                                    <th>{{ __('master.case_id') }}</th>
                                    <th>{{ __('master.patient') }}</th>
                                    <th>{{ __('master.treatment_type') }}</th>
                                    <th>{{ __('master.status') }}</th>
                                    <th>{{ __('master.price') }}</th>
                                    <th>{{ __('master.created_date') }}</th>
                                    <th>{{ __('master.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unassignedCases as $case)
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
                                            @if($case->price)
                                                <strong class="text-success">Tnd {{ number_format($case->price, 2) }}</strong>
                                            @else
                                                <span class="text-muted">{{ __('master.not_set') }}</span>
                                            @endif
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
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" 
                                                           href="{{ route('admin.cases.delete', $case->id) }}"
                                                           onclick="return confirm('{{ __('master.are_you_sure') }}')">
                                                            <i class="fas fa-trash me-2"></i>{{ __('master.delete') }}
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
                </div>
            </div>
        @endif

        <!-- No Cases Message -->
        @if($doctors->count() == 0 && $unassignedCases->count() == 0)
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-4"></i>
                    <h5 class="text-muted">{{ __('master.no_cases_found') }}</h5>
                    <p class="text-muted">{{ __('master.no_cases_available') }}</p>
                    <a href="{{ route('admin.cases.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('master.create_first_case') }}
                    </a>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
   
    @endpush

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTables for doctor cases tables
            $('.doctor-cases-table').each(function() {
                var doctorId = $(this).data('doctor-id');
                $(this).DataTable({
                    processing: true,
                    serverSide: false,
                    pageLength: 10,
                    responsive: true,
                    order: [[5, 'desc']], // Sort by created date descending
                    language: {
                        search: "{{ __('master.search_cases') }}:",
                        lengthMenu: "{{ __('master.show_cases_per_page') }} _MENU_",
                        info: "{{ __('master.showing_cases') }} _START_ {{ __('master.to') }} _END_ {{ __('master.of') }} _TOTAL_",
                        infoEmpty: "{{ __('master.no_cases_found') }}",
                        infoFiltered: "({{ __('master.filtered_from') }} _MAX_ {{ __('master.total_cases') }})",
                        emptyTable: "{{ __('master.no_cases_for_doctor') }}",
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

            // Initialize DataTable for unassigned cases
            $('.unassigned-cases-table').DataTable({
                processing: true,
                serverSide: false,
                pageLength: 10,
                responsive: true,
                order: [[5, 'desc']], // Sort by created date descending
                language: {
                    search: "{{ __('master.search_cases') }}:",
                    lengthMenu: "{{ __('master.show_cases_per_page') }} _MENU_",
                    info: "{{ __('master.showing_cases') }} _START_ {{ __('master.to') }} _END_ {{ __('master.of') }} _TOTAL_",
                    infoEmpty: "{{ __('master.no_cases_found') }}",
                    infoFiltered: "({{ __('master.filtered_from') }} _MAX_ {{ __('master.total_cases') }})",
                    emptyTable: "{{ __('master.no_unassigned_cases') }}",
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
    </script>
    @endpush
</x-app-layout>
