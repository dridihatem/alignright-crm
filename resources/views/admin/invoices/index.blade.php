<x-app-layout>
    <x-slot name="title">{{ __('master.invoice_management') }} - {{ __('master.admin') }}</x-slot>
    @include('partials.case_detail_compact')
<div class="container-xxl flex-grow-1 container-p-y case-detail-compact">
    <!-- Page header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i>{{ __('master.invoice_management') }}</h4>
            <small class="text-muted">{{ __('master.total_invoices') }}: {{ $invoices->total() }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.invoices.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-download me-1"></i> {{ __('master.export_csv') }}
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i> {{ __('master.refresh') }}
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-2">
        <div class="col-6 col-xl-3">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><h4 class="mb-0" id="totalInvoices">0</h4><small class="text-muted">{{ __('master.total_invoices') }}</small></div>
                <span class="badge bg-label-primary rounded p-2"><i class="fas fa-receipt fa-lg"></i></span>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><h4 class="mb-0" id="pendingInvoices">0</h4><small class="text-muted">{{ __('master.pending') }}</small></div>
                <span class="badge bg-label-warning rounded p-2"><i class="fas fa-clock fa-lg"></i></span>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><h4 class="mb-0" id="paidInvoices">0</h4><small class="text-muted">{{ __('master.paid') }}</small></div>
                <span class="badge bg-label-success rounded p-2"><i class="fas fa-check-circle fa-lg"></i></span>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><h4 class="mb-0" id="overdueInvoices">0</h4><small class="text-muted">{{ __('master.overdue') }}</small></div>
                <span class="badge bg-label-danger rounded p-2"><i class="fas fa-exclamation-triangle fa-lg"></i></span>
            </div></div>
        </div>
    </div>

    <!-- Revenue Cards -->
    <div class="row g-3 mb-2">
        <div class="col-12 col-xl-4">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><h4 class="mb-0" id="totalRevenue">Tnd 0</h4><small class="text-muted">{{ __('master.total_revenue') }}</small></div>
                <span class="badge bg-label-info rounded p-2"><i class="fas fa-coins fa-lg"></i></span>
            </div></div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><h4 class="mb-0" id="totalCollected">Tnd 0</h4><small class="text-muted">{{ __('master.total_collected') }}</small></div>
                <span class="badge bg-label-success rounded p-2"><i class="fas fa-money-bill-wave fa-lg"></i></span>
            </div></div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                <div><h4 class="mb-0" id="totalPending">Tnd 0</h4><small class="text-muted">{{ __('master.total_pending') }}</small></div>
                <span class="badge bg-label-warning rounded p-2"><i class="fas fa-hourglass-half fa-lg"></i></span>
            </div></div>
        </div>
    </div>

    <!-- Filters + Table -->
    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.invoices.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ __('master.search') }}</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('master.invoice_search_placeholder') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">{{ __('master.status') }}</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">{{ __('master.all_statuses') }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('master.pending') }}</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('master.paid') }}</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>{{ __('master.overdue') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">{{ __('master.from_date') }}</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">{{ __('master.to_date') }}</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-search me-1"></i> {{ __('master.filter') }}
                            </button>
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-label-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
                <div class="card-body">
                    <!-- Invoices Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-hover table-sm align-middle" id="invoicesTable">
                            <thead class="table-dark">
                                <tr>
                                        <th>{{ __('master.invoice_number') }}</th>
                                    <th>{{ __('master.case_id') }}</th>
                                    <th>{{ __('master.patient') }}</th>
                                    <th>{{ __('master.doctor') }}</th>
                                    <th>{{ __('master.total_amount') }}</th>
                                    <th>{{ __('master.advance_payment') }}</th>
                                    <th>{{ __('master.remaining_balance') }}</th>
                                    <th>{{ __('master.status') }}</th>
                                    <th>{{ __('master.created') }}</th>
                                    <th>{{ __('master.due_date') }}</th>
                                    <th>{{ __('master.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong>{{ $invoice->invoice_number }}</strong>
                                    </td>
                                    <td>
                                        @if($invoice->case)
                                            <a href="{{ route('admin.cases.show', $invoice->case->id) }}" 
                                               class="text-decoration-none">
                                                {{ $invoice->case->case_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->case && $invoice->case->patient)
                                            {{ $invoice->case->patient->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($invoice->case && $invoice->case->doctor)
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $invoice->case->doctor->photo_url }}" 
                                                     alt="Doctor" 
                                                     class="rounded-circle me-2" 
                                                     width="32" height="32">
                                                <span>{{ $invoice->case->doctor->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>Tnd {{ number_format($invoice->total_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        Tnd {{ number_format($invoice->advance_payment, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-label-{{ $invoice->remaining_balance > 0 ? 'warning' : 'success' }}">
                                            Tnd {{ number_format($invoice->remaining_balance, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-{{ $invoice->status === 'pending' ? 'warning' : ($invoice->status === 'paid' ? 'success' : 'danger') }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $invoice->created_at->format('M d, Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($invoice->due_date)
                                            <small class="text-{{ $invoice->due_date->isPast() ? 'danger' : 'muted' }}">
                                                {{ $invoice->due_date->format('M d, Y') }}
                                            </small>
                                        @else
                                            <small class="text-muted">Not set</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.invoices.show', $invoice->id) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($invoice->remaining_balance > 0)
                                                <button type="button" 
                                                        class="btn btn-sm btn-success" 
                                                        onclick="addPayment({{ $invoice->id }})">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <h5>{{ __('master.no_invoices_found') }}</h5>
                                            <p>{{ __('master.no_invoices_match_filters') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                        <small class="text-muted">
                            {{ __('master.showing') }} {{ $invoices->firstItem() ?? 0 }}-{{ $invoices->lastItem() ?? 0 }}
                            / {{ $invoices->total() }}
                        </small>
                        @if($invoices->hasPages())
                            <div>{{ $invoices->links() }}</div>
                        @endif
                    </div>
                </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('master.add_payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">{{ __('master.amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Tnd</span>
                            <input type="number" 
                                   class="form-control" 
                                   id="payment_amount" 
                                   name="amount" 
                                   step="0.01" 
                                   min="0.01" 
                                   required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">{{ __('master.payment_date') }} <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control" 
                               id="payment_date" 
                               name="payment_date" 
                               value="{{ date('Y-m-d') }}"
                               required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">{{ __('master.payment_method') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">{{ __('master.select_method') }}</option>
                            <option value="cash">{{ __('master.cash') }}</option>
                            <option value="card">{{ __('master.card') }}</option>
                            <option value="bank_transfer">{{ __('master.bank_transfer') }}</option>
                            <option value="check">{{ __('master.check') }}</option>
                            <option value="other">{{ __('master.other') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">{{ __('master.notes') }}</label>
                        <textarea class="form-control" 
                                  id="payment_notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="{{ __('master.add_any_notes_about_this_payment') }}"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('master.cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="submitPayment()">{{ __('master.add_payment') }}</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
let currentInvoiceId = null;

// Load invoice statistics
function loadInvoiceStats() {
    fetch('{{ route("admin.invoices.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalInvoices').textContent = data.data.total_invoices;
                document.getElementById('pendingInvoices').textContent = data.data.pending_invoices;
                document.getElementById('paidInvoices').textContent = data.data.paid_invoices;
                document.getElementById('overdueInvoices').textContent = data.data.overdue_invoices;
                document.getElementById('totalRevenue').textContent = 'Tnd ' + parseFloat(data.data.total_revenue).toFixed(2);
                document.getElementById('totalCollected').textContent = 'Tnd ' + parseFloat(data.data.total_collected).toFixed(2);
                document.getElementById('totalPending').textContent = 'Tnd ' + parseFloat(data.data.total_pending).toFixed(2);
            }
        })
        .catch(error => {
            console.error('Error loading invoice stats:', error);
        });
}

// Add payment modal
function addPayment(invoiceId) {
    currentInvoiceId = invoiceId;
    document.getElementById('paymentForm').reset();
    document.getElementById('payment_date').value = '{{ date("Y-m-d") }}';
    new bootstrap.Modal(document.getElementById('addPaymentModal')).show();
}

// Submit payment
function submitPayment() {
    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#addPaymentModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __('master.adding') }}';
    
    fetch(`/admin/invoices/${currentInvoiceId}/payments`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            bootstrap.Modal.getInstance(document.getElementById('addPaymentModal')).hide();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            toastr.error(data.error || '{{ __('master.failed_to_add_payment') }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('{{ __('master.an_error_occurred_while_adding_the_payment') }}');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Refresh data
function refreshData() {
    location.reload();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadInvoiceStats();
    
    // Auto-refresh stats every 30 seconds
    setInterval(loadInvoiceStats, 30000);
});
</script>
@endpush

@push('styles')

@endpush
</x-app-layout>


