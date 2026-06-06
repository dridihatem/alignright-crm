<x-app-layout>
    <x-slot name="title">Doctor Details - {{ $doctor->name }}</x-slot>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.doctors.list') }}">Doctors</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $doctor->name }}</li>
                    </ol>
                </nav>

                <!-- Doctor Details Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-md me-2"></i>
                            Doctor Information
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('admin.doctors.list') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Doctor Photo -->
                            <div class="col-md-3 text-center">
                                <img src="{{ $doctor->photo_url }}" 
                                     alt="Doctor Photo" 
                                     class="rounded-circle mb-3" 
                                     width="150" height="150"
                                     style="object-fit: cover;">
                                <div class="mt-2">
                                    <span class="badge bg-label-{{ $doctor->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($doctor->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Doctor Information -->
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Full Name</label>
                                            <p class="mb-0">{{ $doctor->name }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Email</label>
                                            <p class="mb-0">{{ $doctor->email }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Phone</label>
                                            <p class="mb-0">{{ $doctor->phone ?? 'Not provided' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Role</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-primary">Doctor</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Registration Date</label>
                                            <p class="mb-0">{{ $doctor->created_at->format('M d, Y H:i') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Last Updated</label>
                                            <p class="mb-0">{{ $doctor->updated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Total Cases</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-info">{{ $cases->count() }}</span>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Address</label>
                                            <p class="mb-0">{{ $doctor->address ?? 'Not provided' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="fas fa-notes-medical"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-0">{{ $cases->count() }}</h5>
                                            <small class="text-muted">Total Cases</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm">
                                            <span class="avatar-initial rounded bg-label-warning">
                                                <i class="fas fa-clock"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-0">{{ $cases->where('status', 'pending')->count() }}</h5>
                                            <small class="text-muted">Pending Cases</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm">
                                            <span class="avatar-initial rounded bg-label-success">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-0">{{ $cases->where('status', 'shipped')->count() }}</h5>
                                            <small class="text-muted">Completed Cases</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm">
                                            <span class="avatar-initial rounded bg-label-info">
                                                <i class="fas fa-dollar-sign"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="card-title mb-0">
                                            <h5 class="mb-0">Tnd {{ number_format($cases->sum('price'), 2) }}</h5>
                                            <small class="text-muted">Total Revenue</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cases DataTable Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-folder-open me-2"></i>
                            Cases ({{ $cases->count() }})
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm" onclick="exportCases()">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="refreshTable()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive mb-4">
                            <table class="table table-sm" id="doctorCasesTable">
                                <thead class="border-top">
                                    <tr>
                                        <th>Case ID</th>
                                        <th>Patient</th>
                                        <th>Treatment Type</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                        <th>Created Date</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#doctorCasesTable').DataTable({
        processing: true,
        serverSide: false, // We'll load all data at once for better performance
        ajax: {
            url: '{{ route("admin.doctors.cases", $doctor->id) }}',
            type: 'GET'
        },
        columns: [
            {data: 'case_id', name: 'case_id'},
            {data: 'patient_name', name: 'patient_name'},
            {data: 'treatment_type', name: 'treatment_type'},
            {data: 'status', name: 'status'},
            {data: 'price', name: 'price'},
            {data: 'created_date', name: 'created_date'},
            {data: 'updated_date', name: 'updated_date'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        order: [[5, 'desc']], // Sort by created date descending
        pageLength: 25,
        responsive: true,
        language: {
            search: "Search cases:",
            lengthMenu: "Show _MENU_ cases per page",
            info: "Showing _START_ to _END_ of _TOTAL_ cases",
            infoEmpty: "No cases found",
            infoFiltered: "(filtered from _MAX_ total cases)"
        }
    });

    // Refresh table function
    window.refreshTable = function() {
        table.ajax.reload(null, false);
    };

    // Export cases function
    window.exportCases = function() {
        window.location.href = '{{ route("admin.doctors.cases.export", $doctor->id) }}';
    };
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.breadcrumb {
    background: none;
    padding: 0;
    margin-bottom: 1rem;
}


.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.375rem;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    border-radius: inherit;
    font-size: 0.875rem;
    font-weight: 600;
    color: #fff;
}

/* DataTables customization */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 1rem;
}

.dataTables_wrapper .dataTables_info {
    padding-top: 1rem;
}

.dataTables_wrapper .dataTables_paginate {
    padding-top: 1rem;
}
</style>
@endpush


</x-app-layout>
