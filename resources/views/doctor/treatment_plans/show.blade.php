<x-app-layout>
    <x-slot name="title">Treatment Plan Details - {{ $treatmentPlan->name }}</x-slot>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ti ti-file-text me-2"></i>
                        Treatment Plan Details
                    </h5>
                    <div>
                        <a href="{{ route('doctor.treatment_plans.index', $case->id) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Treatment Plans
                        </a>
                        @if($treatmentPlan->status === 'pending')
                            <a href="{{ route('doctor.treatment_plans.show_accept_form', $treatmentPlan->id) }}" class="btn btn-success">
                                <i class="ti ti-check me-1"></i>Accept & Set Price
                            </a>
                            <a href="{{ route('doctor.treatment_plans.show_reject_form', $treatmentPlan->id) }}" class="btn btn-danger">
                                <i class="ti ti-x me-1"></i>Reject
                            </a>
                        @endif
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

                    <div class="row">
                        <!-- Treatment Plan Information -->
                        <div class="col-md-8">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="ti ti-file-text me-1"></i>
                                        Treatment Plan Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Plan Name:</label>
                                            <p class="mb-0">{{ $treatmentPlan->name }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Status:</label>
                                            <p class="mb-0">
                                                @if($treatmentPlan->status === 'pending')
                                                    <span class="badge bg-label-warning">Pending Review</span>
                                                @elseif($treatmentPlan->status === 'accepted')
                                                    <span class="badge bg-label-success">Accepted</span>
                                                @elseif($treatmentPlan->status === 'rejected')
                                                    <span class="badge bg-label-danger">Rejected</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Description:</label>
                                        <div class="border rounded p-3 bg-light">
                                            {{ $treatmentPlan->description ?: 'No description provided' }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Treatment Plan Link:</label>
                                        <div>
                                            <a href="{{ ensure_https_url($treatmentPlan->link) }}" target="_blank" class="btn btn-primary">
                                                <i class="ti ti-external-link me-1"></i>View Treatment Plan
                                            </a>
                                        </div>
                                    </div>

                                    @if($treatmentPlan->type_file)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">File Type:</label>
                                            <p class="mb-0">{{ $treatmentPlan->type_file }}</p>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Created:</label>
                                            <p class="mb-0">{{ $treatmentPlan->created_at->format('M d, Y H:i') }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Last Updated:</label>
                                            <p class="mb-0">{{ $treatmentPlan->updated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Case Information -->
                        <div class="col-md-4">
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
                                        <label class="form-label fw-bold">Case Status:</label>
                                        <p class="mb-0">
                                            <span class="badge bg-label-secondary">{{ ucfirst($case->status) }}</span>
                                        </p>
                                    </div>
                                    @if($case->price)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Case Price:</label>
                                            <p class="mb-0">${{ number_format($case->price, 2) }}</p>
                                        </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Doctor:</label>
                                        <p class="mb-0">{{ $case->doctor->name ?? 'N/A' }}</p>
                                    </div>
                                    @if($case->technician)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Technician:</label>
                                            <p class="mb-0">{{ $case->technician->name ?? 'N/A' }}</p>
                                        </div>
                                    @endif
                                    @if($case->laboratory)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Laboratory:</label>
                                            <p class="mb-0">{{ $case->laboratory->name ?? 'N/A' }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status History -->
                    @if($treatmentPlan->status !== 'pending')
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">
                                            <i class="ti ti-history me-1"></i>
                                            Status History
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="timeline">
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-primary"></div>
                                                <div class="timeline-content">
                                                    <h6 class="timeline-title">Treatment Plan Created</h6>
                                                    <p class="timeline-text">{{ $treatmentPlan->created_at->format('M d, Y H:i') }}</p>
                                                </div>
                                            </div>
                                            
                                            @if($treatmentPlan->status === 'accepted')
                                                <div class="timeline-item">
                                                    <div class="timeline-marker bg-success"></div>
                                                    <div class="timeline-content">
                                                        <h6 class="timeline-title">Treatment Plan Accepted</h6>
                                                        <p class="timeline-text">{{ $treatmentPlan->updated_at->format('M d, Y H:i') }}</p>
                                                        <p class="timeline-text text-success">
                                                            <i class="ti ti-check me-1"></i>
                                                            Case moved to "In Production" status
                                                        </p>
                                                    </div>
                                                </div>
                                            @elseif($treatmentPlan->status === 'rejected')
                                                <div class="timeline-item">
                                                    <div class="timeline-marker bg-danger"></div>
                                                    <div class="timeline-content">
                                                        <h6 class="timeline-title">Treatment Plan Rejected</h6>
                                                        <p class="timeline-text">{{ $treatmentPlan->updated_at->format('M d, Y H:i') }}</p>
                                                        @if(str_contains($treatmentPlan->description, 'Rejection Reason:'))
                                                            <div class="alert alert-danger mt-2">
                                                <strong>Rejection Reason:</strong><br>
                                                {{ Str::after($treatmentPlan->description, 'Rejection Reason:') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    @push('styles')
    <style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .timeline-title {
        margin: 0 0 5px 0;
        font-weight: 600;
        color: #495057;
    }

    .timeline-text {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
    </style>
    @endpush
</x-app-layout>
