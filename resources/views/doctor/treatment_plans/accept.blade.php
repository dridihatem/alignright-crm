<x-app-layout>
    <x-slot name="title">Accept Treatment Plan - {{ $treatmentPlan->name }}</x-slot>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ti ti-check me-2"></i>
                        Accept Treatment Plan & Set Price
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

                    <!-- Price Review & Team Assignment Form -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="ti ti-currency-dollar me-1"></i>
                                        Price Review & Team Assignment
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('doctor.treatment_plans.accept', $treatmentPlan->id) }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <!-- Price Section -->
                                            <div class="col-md-6">
                                                <h6 class="mb-3 text-success">
                                                    <i class="ti ti-currency-dollar me-1"></i>
                                                    Set Treatment Price
                                                </h6>
                                                <div class="mb-3">
                                                    <label for="price" class="form-label">Treatment Price (Tnd) *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" 
                                                               class="form-control @error('price') is-invalid @enderror" 
                                                               id="price" 
                                                               name="price" 
                                                               value="{{ old('price') }}" 
                                                               step="0.01" 
                                                               min="0" 
                                                               required
                                                               placeholder="0.00">
                                                    </div>
                                                    @error('price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-text text-muted">
                                                        This price will be set for the case and used for billing.
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Team Assignment Section -->
                                            <div class="col-md-6">
                                                <h6 class="mb-3 text-info">
                                                    <i class="ti ti-users me-1"></i>
                                                    Assign Team Members
                                                </h6>
                                                <div class="mb-3">
                                                    <label for="technician_id" class="form-label">Technician *</label>
                                                    <select class="form-select @error('technician_id') is-invalid @enderror" 
                                                            id="technician_id" 
                                                            name="technician_id" 
                                                            required>
                                                        <option value="">Select Technician</option>
                                                        @foreach($technicians ?? [] as $technician)
                                                            <option value="{{ $technician->id }}" 
                                                                    {{ old('technician_id') == $technician->id ? 'selected' : '' }}>
                                                                {{ $technician->name }} 
                                                                @if($technician->status === 'active')
                                                                    <span class="badge bg-success">Active</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Inactive</span>
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('technician_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="laboratory_id" class="form-label">Laboratory *</label>
                                                    <select class="form-select @error('laboratory_id') is-invalid @enderror" 
                                                            id="laboratory_id" 
                                                            name="laboratory_id" 
                                                            required>
                                                        <option value="">Select Laboratory</option>
                                                        @foreach($laboratories ?? [] as $laboratory)
                                                            <option value="{{ $laboratory->id }}" 
                                                                    {{ old('laboratory_id') == $laboratory->id ? 'selected' : '' }}>
                                                                {{ $laboratory->name }}
                                                                @if($laboratory->status === 'active')
                                                                    <span class="badge bg-success">Active</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Inactive</span>
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('laboratory_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between">
                                                    <a href="{{ route('doctor.treatment_plans.index', $case->id) }}" 
                                                       class="btn btn-secondary">
                                                        <i class="ti ti-x me-1"></i>Cancel
                                                    </a>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="ti ti-check me-1"></i>
                                                        Accept Treatment Plan & Set Price
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                <h6 class="alert-heading">
                                    <i class="ti ti-info-circle me-1"></i>
                                    Important Information
                                </h6>
                                <ul class="mb-0">
                                    <li>Accepting this treatment plan will set the case status to "In Production"</li>
                                    <li>The price you set will be used for billing and financial calculations</li>
                                    <li>Team members will be notified of their assignment</li>
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
            // Add price formatting
            $('#price').on('input', function() {
                let value = $(this).val();
                if (value && !isNaN(value)) {
                    $(this).val(parseFloat(value).toFixed(2));
                }
            });

            // Form validation
            $('form').on('submit', function(e) {
                let price = $('#price').val();
                let technician = $('#technician_id').val();
                let laboratory = $('#laboratory_id').val();

                if (!price || price <= 0) {
                    e.preventDefault();
                    alert('Please enter a valid price greater than 0.');
                    return false;
                }

                if (!technician) {
                    e.preventDefault();
                    alert('Please select a technician.');
                    return false;
                }

                if (!laboratory) {
                    e.preventDefault();
                    alert('Please select a laboratory.');
                    return false;
                }

                // Confirm action
                if (!confirm('Are you sure you want to accept this treatment plan? This will set the price and assign the team.')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
