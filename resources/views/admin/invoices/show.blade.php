<x-app-layout>
    <x-slot name="title">{{ __('master.invoice') }} #{{ $invoice->invoice_number }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.invoices.index') }}">{{ __('master.invoices') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.invoice') }} #{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>

        <!-- Invoice Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        {{ __('master.invoice') }} #{{ $invoice->invoice_number }}
                    </h5>
                    <small class="text-muted">{{ __('master.created') }}: {{ $invoice->created_at->format('M d, Y H:i') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> {{ __('master.edit') }}
                    </a>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('master.back') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Invoice Information -->
            <div class="col-md-8">
                <!-- Invoice Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('master.invoice_information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.invoice_number') }}</label>
                                    <p class="mb-0">#{{ $invoice->invoice_number }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.status') }}</label>
                                    <p class="mb-0">
                                        <span class="badge bg-label-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'pending' ? 'warning' : ($invoice->status === 'overdue' ? 'danger' : 'secondary')) }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.total_amount') }}</label>
                                    <p class="mb-0">
                                        <strong class="text-success">Tnd {{ number_format($invoice->total_amount, 2) }}</strong>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.created_date') }}</label>
                                    <p class="mb-0">{{ $invoice->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.due_date') }}</label>
                                    <p class="mb-0">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'Not set' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.payment_method') }}</label>
                                    <p class="mb-0">{{ $invoice->payment_method ?? 'Not specified' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Case Information -->
                @if($invoice->case)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-folder-open me-2"></i>
                            {{ __('master.case_information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.case_id') }}</label>
                                    <p class="mb-0">
                                        <a href="{{ route('admin.cases.show', $invoice->case->id) }}" class="text-decoration-none">
                                            #{{ $invoice->case->case_id }}
                                        </a>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.treatment_type') }}</label>
                                    <p class="mb-0">{{ $invoice->case->treatment_type ?? 'Not specified' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.case_status') }}</label>
                                    <p class="mb-0">
                                        <span class="badge bg-label-{{ $invoice->case->status === 'pending' ? 'warning' : ($invoice->case->status === 'draft' ? 'secondary' : ($invoice->case->status === 'in_planning' ? 'info' : ($invoice->case->status === 'approval' ? 'primary' : ($invoice->case->status === 'in_production' ? 'success' : ($invoice->case->status === 'shipped' ? 'success' : ($invoice->case->status === 'rejected' ? 'danger' : 'secondary')))))) }}">
                                            {{ ucfirst(str_replace('_', ' ', $invoice->case->status)) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.case_created') }}</label>
                                    <p class="mb-0">{{ $invoice->case->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar Information -->
            <div class="col-md-4">
                <!-- Patient Information -->
                @if($invoice->case && $invoice->case->patient)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            {{ __('master.patient_information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.name') }}</label>
                            <p class="mb-0">{{ $invoice->case->patient->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.email') }}</label>
                            <p class="mb-0">{{ $invoice->case->patient->email ?? 'Not provided' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.phone') }}</label>
                            <p class="mb-0">{{ $invoice->case->patient->phone ?? 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Doctor Information -->
                @if($invoice->case && $invoice->case->doctor)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-user-md me-2"></i>
                            {{ __('master.doctor_information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.name') }}</label>
                            <p class="mb-0">{{ $invoice->case->doctor->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.email') }}</label>
                            <p class="mb-0">{{ $invoice->case->doctor->email }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.status') }}</label>
                            <p class="mb-0">
                                <span class="badge bg-label-{{ $invoice->case->doctor->status === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($invoice->case->doctor->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            {{ __('master.quick_actions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> {{ __('master.edit_invoice') }}
                            </a>
                            
                            @if($invoice->case)
                            <a href="{{ route('admin.cases.show', $invoice->case->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-folder-open me-1"></i> {{ __('master.view_case') }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

.badge {
    font-size: 0.875rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endpush

</x-app-layout>
