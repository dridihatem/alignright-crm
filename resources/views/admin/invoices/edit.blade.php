<x-app-layout>
    <x-slot name="title">{{ __('master.edit_invoice') }} #{{ $invoice->invoice_number }} - {{ __('master.admin') }}</x-slot>
    
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
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.invoices.show', $invoice->id) }}">{{ __('master.invoice') }} #{{ $invoice->invoice_number }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.edit') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('master.edit_invoice') }} #{{ $invoice->invoice_number }}
                    </h5>
                    <small class="text-muted">{{ __('master.update_invoice_information') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('master.back') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Edit Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-edit me-2"></i>
                            {{ __('master.invoice_details') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.invoices.update', $invoice->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="invoice_number" class="form-label">{{ __('master.invoice_number') }}</label>
                                        <input type="text" class="form-control" id="invoice_number" 
                                               value="#{{ $invoice->invoice_number }}" readonly>
                                        <small class="text-muted">{{ __('master.invoice_number_cannot_be_changed') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">{{ __('master.status') }}</label>
                                        <input type="text" class="form-control" id="status" 
                                               value="{{ ucfirst($invoice->status) }}" readonly>
                                        <small class="text-muted">{{ __('master.status_updated_automatically') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="total_amount" class="form-label">{{ __('master.total_amount') }}</label>
                                        <input type="text" class="form-control" id="total_amount" 
                                               value="Tnd {{ number_format($invoice->total_amount, 2) }}" readonly>
                                        <small class="text-muted">{{ __('master.total_amount_cannot_be_changed') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="due_date" class="form-label">{{ __('master.due_date') }}</label>
                                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                               id="due_date" name="due_date" 
                                               value="{{ old('due_date', $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '') }}">
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $error }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">{{ __('master.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="4" 
                                          placeholder="{{ __('master.enter_invoice_notes') }}">{{ old('notes', $invoice->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $error }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> {{ __('master.update_invoice') }}
                                </button>
                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> {{ __('master.cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="col-md-4">
                <!-- Current Invoice Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('master.current_information') }}
                        </h6>
                    </div>
                    <div class="card-body">
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
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.paid_amount') }}</label>
                            <p class="mb-0">
                                <strong class="text-info">Tnd {{ number_format($invoice->paid_amount ?? 0, 2) }}</strong>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.remaining_balance') }}</label>
                            <p class="mb-0">
                                <strong class="text-warning">Tnd {{ number_format(($invoice->total_amount - ($invoice->paid_amount ?? 0)), 2) }}</strong>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.created_date') }}</label>
                            <p class="mb-0">{{ $invoice->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        @if($invoice->due_date)
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.current_due_date') }}</label>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Case Information -->
                @if($invoice->case)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-folder-open me-2"></i>
                            {{ __('master.related_case') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.case_id') }}</label>
                            <p class="mb-0">
                                <a href="{{ route('admin.cases.show', $invoice->case->id) }}" class="text-decoration-none">
                                    #{{ $invoice->case->case_id }}
                                </a>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.patient') }}</label>
                            <p class="mb-0">{{ $invoice->case->patient->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.doctor') }}</label>
                            <p class="mb-0">{{ $invoice->case->doctor->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('master.treatment_type') }}</label>
                            <p class="mb-0">{{ $invoice->case->treatment_type ?? 'Not specified' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Payment History -->
                @if($invoice->payments && $invoice->payments->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            {{ __('master.payment_history') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('master.date') }}</th>
                                        <th>{{ __('master.amount') }}</th>
                                        <th>{{ __('master.method') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->payments->take(5) as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d') : 'N/A' }}</td>
                                        <td>
                                            <strong class="text-success">Tnd {{ number_format($payment->amount, 2) }}</strong>
                                        </td>
                                        <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($invoice->payments->count() > 5)
                        <small class="text-muted">{{ __('master.showing_last_5_payments') }}</small>
                        @endif
                    </div>
                </div>
                @endif
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

.table-sm th,
.table-sm td {
    padding: 0.25rem;
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-save form data to localStorage
    $('form input, form textarea').on('input', function() {
        const formData = $('form').serialize();
        localStorage.setItem('invoice_edit_form', formData);
    });

    // Restore form data from localStorage
    const savedFormData = localStorage.getItem('invoice_edit_form');
    if (savedFormData) {
        const form = $('form');
        const inputs = form.find('input, textarea');
        
        // Parse the saved data and populate form fields
        const params = new URLSearchParams(savedFormData);
        params.forEach((value, key) => {
            const input = form.find(`[name="${key}"]`);
            if (input.length && !input.is('[readonly]')) {
                input.val(value);
            }
        });
    }

    // Clear localStorage on successful form submission
    $('form').on('submit', function() {
        localStorage.removeItem('invoice_edit_form');
    });
});
</script>
@endpush

</x-app-layout>
