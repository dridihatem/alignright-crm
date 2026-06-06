<x-app-layout>
    <x-slot name="title">Treatment Plans - {{ $case->case_id }}</x-slot>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ti ti-file-text me-2"></i>
                        Treatment Plans for Case: {{ $case->case_id }}
                    </h5>
                    <div>
                        <a href="{{ route('doctor.cases.show', $case->id) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Case
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTreatmentPlanModal">
                            <i class="ti ti-plus me-1"></i>Create Treatment Plan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover" id="treatmentPlansTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($treatmentPlans as $plan)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        <i class="ti ti-file-text"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $plan->name }}</h6>
                                                    <small class="text-muted">{{ Str::limit($plan->description, 50) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($plan->status === 'pending')
                                                <span class="badge bg-label-warning">Pending Review</span>
                                            @elseif($plan->status === 'accepted')
                                                <span class="badge bg-label-success">Accepted</span>
                                            @elseif($plan->status === 'rejected')
                                                <span class="badge bg-label-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>{{ $plan->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a href="{{ $plan->link }}" target="_blank" class="dropdown-item">
                                                            <i class="ti ti-external-link me-1"></i>View Plan
                                                        </a>
                                                    </li>
                                                    @if($plan->status === 'pending')
                                                        <li>
                                                            <a href="{{ route('doctor.treatment_plans.show_accept_form', $plan->id) }}" class="dropdown-item text-success">
                                                                <i class="ti ti-check me-1"></i>Accept & Set Price
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('doctor.treatment_plans.show_reject_form', $plan->id) }}" class="dropdown-item text-danger">
                                                                <i class="ti ti-x me-1"></i>Reject
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a href="{{ route('doctor.treatment_plans.show', $plan->id) }}" class="dropdown-item">
                                                            <i class="ti ti-eye me-1"></i>Details
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ti ti-file-text text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted mt-2">No treatment plans found</p>
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTreatmentPlanModal">
                                                    Create First Treatment Plan
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Treatment Plan Modal -->
<div class="modal fade" id="createTreatmentPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('doctor.treatment_plans.create', $case->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Treatment Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Treatment Plan Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label for="link" class="form-label">Treatment Plan Link *</label>
                            <input type="url" class="form-control @error('link') is-invalid @enderror" 
                                   id="link" name="link" value="{{ old('link') }}" 
                                   placeholder="https://example.com/treatment-plan" required>
                            @error('link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Additional notes about the treatment plan">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Treatment Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#treatmentPlansTable').DataTable({
                order: [[2, 'desc']], // Sort by created date descending
                pageLength: 10,
                language: {
                    search: "Search treatment plans:",
                    lengthMenu: "Show _MENU_ treatment plans per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ treatment plans",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
