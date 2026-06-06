<x-app-layout>
    <!-- Content -->
    @include('partials.case_detail_compact')

    <div class="container-xxl flex-grow-1 container-p-y case-detail-compact">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">
                                <i class="icon-base ti tabler-file me-2"></i>
                                Case Details
                            </h4>
                            <p class="text-muted mb-0">Case ID: {{ $case->case_id }}</p>
                        </div>
                        <a href="{{ route('commercial.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="icon-base ti tabler-arrow-left me-1"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Case Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Case Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Doctor Information</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ $case->doctor->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $case->doctor->email ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $case->doctor->phone ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Patient Information</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ $case->patient->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $case->patient->email ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $case->patient->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Case Details</h6>
                                <p class="mb-1"><strong>Case ID:</strong> {{ $case->case_id ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Treatment Type:</strong> {{ $case->treatment_type ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Status:</strong> 
                                    @if($case->status)
                                        <span class="badge bg-label-{{ $case->status === 'completed' ? 'success' : ($case->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </p>
                                <p class="mb-1"><strong>Price:</strong> 
                                    @if($case->price)
                                        <strong class="text-success">Tnd {{ number_format($case->price, 2) }}</strong>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Dates</h6>
                                <p class="mb-1"><strong>Created:</strong> {{ $case->created_at->format('Y-m-d H:i') }}</p>
                                <p class="mb-1"><strong>Updated:</strong> {{ $case->updated_at->format('Y-m-d H:i') }}</p>
                                @if($case->accepted_date)
                                    <p class="mb-1"><strong>Accepted:</strong> {{ \Carbon\Carbon::parse($case->accepted_date)->format('Y-m-d H:i') }}</p>
                                @endif
                                @if($case->rejected_date)
                                    <p class="mb-1"><strong>Rejected:</strong> {{ \Carbon\Carbon::parse($case->rejected_date)->format('Y-m-d H:i') }}</p>
                                @endif
                            </div>
                        </div>

                        @if($case->doctor_instruction)
                        <hr>
                        <h6>Doctor Instructions</h6>
                        <p class="text-muted">{{ $case->doctor_instruction }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice & Payment Information -->
            <div class="col-md-4">
                @if($invoice)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Invoice Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Invoice Number:</span>
                            <strong>{{ $invoice->invoice_number }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Amount:</span>
                            <strong>Tnd {{ number_format($invoice->total_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Advance Payment:</span>
                            <strong>Tnd {{ number_format($invoice->advance_payment, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Remaining Balance:</span>
                            <strong class="text-{{ $invoice->remaining_balance > 0 ? 'danger' : 'success' }}">
                                Tnd {{ number_format($invoice->remaining_balance, 2) }}
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Status:</span>
                            <span class="badge bg-label-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Due Date:</span>
                            <span>{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A' }}</span>
                        </div>
                        <hr>
                        <div class="text-center">
                            <a href="{{ route('commercial.invoices.show', $invoice->id) }}" class="btn btn-primary btn-sm">
                                <i class="icon-base ti tabler-eye me-1"></i>
                                View Invoice
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                @if($payments && $payments->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payment History</h5>
                    </div>
                    <div class="card-body">
                        @foreach($payments as $payment)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <small class="text-muted">{{ $payment->created_at->format('Y-m-d H:i') }}</small>
                                <p class="mb-0">{{ $payment->payment_method ?? 'Payment' }}</p>
                                @if($payment->notes)
                                    <small class="text-muted">{{ $payment->notes }}</small>
                                @endif
                            </div>
                            <strong>Tnd {{ number_format($payment->amount, 2) }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @else
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Invoice Status</h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="icon-base ti tabler-file-x icon-lg text-muted mb-3"></i>
                        <p class="text-muted">No invoice created for this case yet.</p>
                        @if($case->price)
                            <span class="badge bg-label-warning">Waiting for invoice creation</span>
                        @else
                            <span class="badge bg-label-secondary">Case not priced yet</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
