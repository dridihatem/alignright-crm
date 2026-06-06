<x-app-layout>
    <x-slot name="title">Reject Treatment Plan - {{ $treatmentPlan->name }}</x-slot>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ti ti-x me-2"></i>
                        Reject Treatment Plan
                    </h5>
                    <div>
                        <a href="{{ route('doctor.treatment_plans.index', $case->id) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Treatment Plans
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Treatment Plan Details -->
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="ti ti-file-text me-1"></i>
                                        Treatment Plan Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Plan Name:</label>
                                        <p class="mb-0">{{ $treatmentPlan->name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Description:</label>
                                        <p class="mb-0">{{ $treatmentPlan->description ?: 'No description provided' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Plan Link:</label>
                                        <div>
                                            <a href="{{ ensure_https_url($treatmentPlan->link) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-external-link me-1"></i>View Treatment Plan
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Created:</label>
                                        <p class="mb-0">{{ $treatmentPlan->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Case Information -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="ti ti-user me-1"></i>
                                        Case Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Case ID:</label>
                                        <p class="mb-0">{{ $case->case_id }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Patient:</label>
                                        <p class="mb-0">{{ $case->patient->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Treatment Type:</label>
                                        <p class="mb-0">{{ $case->treatment_type ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Current Status:</label>
                                        <p class="mb-0">
                                            <span class="badge bg-label-secondary">{{ ucfirst($case->status) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Form -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0">
                                        <i class="ti ti-x me-1"></i>
                                        Rejection Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('doctor.treatment_plans.reject', $treatmentPlan->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="reason" class="form-label">Rejection Reason</label>
                                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                                      id="reason" 
                                                      name="reason" 
                                                      rows="4" 
                                                      placeholder="Please provide a reason for rejecting this treatment plan. This will help improve future plans."
                                                      maxlength="500">{{ old('reason') }}</textarea>
                                            @error('reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Providing a reason helps the team understand what needs to be improved.
                                            </small>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('doctor.treatment_plans.index', $case->id) }}" 
                                               class="btn btn-secondary">
                                                <i class="ti ti-x me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="ti ti-x me-1"></i>
                                                Reject Treatment Plan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                <h6 class="alert-heading">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    Important Information
                                </h6>
                                <ul class="mb-0">
                                    <li>Rejecting this treatment plan will mark it as rejected</li>
                                    <li>The case status will remain unchanged</li>
                                    <li>You can create a new treatment plan if needed</li>
                                    <li>This action cannot be undone easily - please review carefully</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Character counter for reason textarea
            $('#reason').on('input', function() {
                let maxLength = 500;
                let currentLength = $(this).val().length;
                let remaining = maxLength - currentLength;
                
                if (remaining < 0) {
                    $(this).val($(this).val().substring(0, maxLength));
                    remaining = 0;
                }
                
                // Update character count display
                let counter = $(this).siblings('.form-text');
                if (counter.length === 0) {
                    counter = $('<small class="form-text text-muted"></small>');
                    $(this).after(counter);
                }
                counter.text(remaining + ' characters remaining');
            });

            // Form validation
            $('form').on('submit', function(e) {
                let reason = $('#reason').val().trim();

                if (!reason) {
                    e.preventDefault();
                    alert('Please provide a reason for rejecting this treatment plan.');
                    $('#reason').focus();
                    return false;
                }

                // Confirm action
                if (!confirm('Are you sure you want to reject this treatment plan? This action cannot be easily undone.')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
