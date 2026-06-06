<x-app-layout>
    <x-slot name="title">Edit Case - {{ $case->case_id }}</x-slot>

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
                            <a href="{{ route('admin.cases.list') }}">Cases</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.cases.show', $case->id) }}">{{ $case->case_id }}</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>

                <!-- Case Header -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2"></i>
                                {{ __('master.edit_case') }} {{ $case->case_id }}
                            </h5>
                            <small class="text-muted">{{ __('master.last_updated') }} {{ $case->updated_at->format('d/m/Y') }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.cases.show', $case->id) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-eye me-1"></i> {{ __('master.view') }}
                            </a>
                            <a href="{{ route('admin.cases.list') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> {{ __('master.back') }}
                            </a>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.cases.update', $case->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Main Case Information -->
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        {{ __('master.case_information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="case_id" class="form-label">{{ __('master.case_id') }}</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="case_id" 
                                                       name="case_id" 
                                                       value="{{ $case->case_id }}" 
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">{{ __('master.status') }}</label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="draft" {{ $case->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="pending" {{ $case->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="in_planning" {{ $case->status === 'in_planning' ? 'selected' : '' }}>In Planning</option>
                                                    <option value="approval" {{ $case->status === 'approval' ? 'selected' : '' }}>Approval</option>
                                                    <option value="in_production" {{ $case->status === 'in_production' ? 'selected' : '' }}>In Production</option>
                                                    <option value="shipped" {{ $case->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                    <option value="rejected" {{ $case->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="treatment_type" class="form-label">{{ __('master.treatment_type') }}</label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="treatment_type" 
                                                       name="treatment_type" 
                                                       value="{{ $case->treatment_type }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="priority" class="form-label">{{ __('master.priority') }}</label>
                                                <select class="form-select" id="priority" name="priority">
                                                    <option value="low" {{ $case->priority === 'low' ? 'selected' : '' }}>Low</option>
                                                    <option value="medium" {{ $case->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                                    <option value="high" {{ $case->priority === 'high' ? 'selected' : '' }}>High</option>
                                                    <option value="urgent" {{ $case->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="treatment_treat" class="form-label">{{ __('master.treatment_description') }}</label>
                                        <textarea class="form-control" 
                                                  id="treatment_treat" 
                                                  name="treatment_treat" 
                                                  rows="4">{{ $case->treatment_treat }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-dollar-sign me-2"></i>
                                        {{ __('master.financial_information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">{{ __('master.total_price') }} (Tnd)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="price" 
                                                       name="price" 
                                                       step="0.01" 
                                                       min="0" 
                                                       value="{{ $case->price }}"
                                                       onchange="calculateRemainingBalance()">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                    <label for="advance_payment" class="form-label">{{ __('master.advance_payment') }} (Tnd)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="advance_payment" 
                                                       name="advance_payment" 
                                                       step="0.01" 
                                                       min="0" 
                                                       value="{{ $case->advance_payment }}"
                                                       onchange="calculateRemainingBalance()">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="remaining_balance" class="form-label">{{ __('master.remaining_balance') }} (Tnd)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="remaining_balance" 
                                                       name="remaining_balance" 
                                                       step="0.01" 
                                                       readonly 
                                                       value="{{ $case->remaining_balance }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dates Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ __('master.important_dates') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="accepted_date" class="form-label">{{ __('master.accepted_date') }}</label>
                                                <input type="datetime-local" 
                                                       class="form-control" 
                                                       id="accepted_date" 
                                                       name="accepted_date" 
                                                       value="{{ $case->accepted_date ? \Carbon\Carbon::parse($case->accepted_date)->format('Y-m-d\TH:i') : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="rejected_date" class="form-label">{{ __('master.rejected_date') }}</label>
                                                <input type="datetime-local" 
                                                       class="form-control" 
                                                       id="rejected_date" 
                                                       name="rejected_date" 
                                                       value="{{ $case->rejected_date ? \Carbon\Carbon::parse($case->rejected_date)->format('Y-m-d\TH:i') : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                        </div>

                        <!-- Sidebar - Assignments -->
                        <div class="col-md-4">
                            <!-- Patient Assignment -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user me-2"></i>
                                        {{ __('master.patient_assignment') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="patient_id" class="form-label">{{ __('master.patient') }}</label>
                                        <select class="form-select" id="patient_id" name="patient_id">
                                            <option value="">{{ __('master.select_patient') }}</option>
                                            @foreach(\App\Models\Patient::all() as $patient)
                                                <option value="{{ $patient->id }}" {{ $case->patient_id == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->name }} ({{ $patient->email ?? 'No email' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Doctor Assignment -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-md me-2"></i>
                                        {{ __('master.doctor_assignment') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="doctor_id" class="form-label">{{ __('master.doctor') }}</label>
                                        <select class="form-select" id="doctor_id" name="doctor_id" required>
                                            <option value="">{{ __('master.select_doctor') }}</option>
                                            @foreach(\App\Models\User::where('role_id', 2)->where('status', 'active')->get() as $doctor)
                                                <option value="{{ $doctor->id }}" {{ $case->doctor_id == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->name }} ({{ $doctor->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Technician Assignment -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-tools me-2"></i>
                                        {{ __('master.technician_assignment') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                            <label for="technician_id" class="form-label">{{ __('master.technician') }}</label>
                                        <select class="form-select" id="technician_id" name="technician_id">
                                            <option value="">{{ __('master.select_technician') }}</option>
                                            @foreach(\App\Models\User::where('role_id', 3)->where('status', 'active')->get() as $technician)
                                                <option value="{{ $technician->id }}" {{ $case->technician_id == $technician->id ? 'selected' : '' }}>
                                                    {{ $technician->name }} ({{ $technician->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Laboratory Assignment -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-flask me-2"></i>
                                        {{ __('master.laboratory_assignment') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="laboratory_id" class="form-label">{{ __('master.laboratory') }}</label>
                                        <select class="form-select" id="laboratory_id" name="laboratory_id">
                                            <option value="">{{ __('master.select_laboratory') }}</option>
                                            @foreach(\App\Models\User::where('role_id', 4)->where('status', 'active')->get() as $laboratory)
                                                <option value="{{ $laboratory->id }}" {{ $case->laboratory_id == $laboratory->id ? 'selected' : '' }}>
                                                    {{ $laboratory->name }} ({{ $laboratory->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> {{ __('master.update_case') }}
                                        </button>
                                        <a href="{{ route('admin.cases.show', $case->id) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i> {{ __('master.cancel') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


@push('scripts')
<script>
// Calculate remaining balance
function calculateRemainingBalance() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const advancePayment = parseFloat(document.getElementById('advance_payment').value) || 0;
    const remainingBalance = Math.max(0, price - advancePayment);
    
    document.getElementById('remaining_balance').value = remainingBalance.toFixed(2);
}

// Initialize calculation on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateRemainingBalance();
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const advancePayment = parseFloat(document.getElementById('advance_payment').value) || 0;
    
    if (advancePayment > price) {
        e.preventDefault();
        alert('Advance payment cannot exceed total price');
        return false;
    }
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

.form-label {
    font-weight: 600;
    color: #495057;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
@endpush

</x-app-layout>