<x-app-layout>
    @push('styles')
    <style>
        /* Commercial Invoice Show Custom Styles */
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .invoice-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }
        
        .invoice-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1.5rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.paid {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .status-badge.pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .status-badge.overdue {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            color: white;
        }
        
        .btn-modern {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: white;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            color: white;
            margin-bottom: 0;
        }
        
        .info-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        .info-section h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .amount-display {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
    </style>
    @endpush

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Modern Header -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title mb-2">Invoice Details</h1>
                    <p class="page-subtitle mb-0">Invoice #{{ $invoice->invoice_number }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('commercial.invoices.print', $invoice->id) }}" class="btn btn-light btn-modern" target="_blank">
                            <i class="icon-base ti tabler-printer me-1"></i>
                            Print
                        </a>
                        <a href="{{ route('commercial.dashboard') }}" class="btn btn-outline-light btn-modern">
                            <i class="icon-base ti tabler-arrow-left me-1"></i>
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Invoice Information -->
            <div class="col-md-8">
                <div class="invoice-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="icon-base ti tabler-file-invoice me-2"></i>
                            Invoice Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-section">
                                    <h6>Doctor Information</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $invoice->case->doctor->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $invoice->case->doctor->email ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $invoice->case->doctor->phone ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-section">
                                    <h6>Patient Information</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $invoice->case->patient->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $invoice->case->patient->email ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td>{{ $invoice->case->patient->phone ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-section">
                                    <h6>Case Information</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Case ID:</strong></td>
                                            <td>{{ $invoice->case->case_id ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Treatment Type:</strong></td>
                                            <td>{{ $invoice->case->treatment_type ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if($invoice->case->status)
                                                    <span class="status-badge {{ $invoice->case->status === 'completed' ? 'paid' : 'pending' }}">
                                                        {{ ucfirst($invoice->case->status) }}
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-section">
                                    <h6>Invoice Details</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Invoice Number:</strong></td>
                                            <td>{{ $invoice->invoice_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Amount:</strong></td>
                                            <td>Tnd {{ number_format($invoice->total_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Due Date:</strong></td>
                                            <td>{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="status-badge {{ strtolower($invoice->status) }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if($invoice->notes)
                        <div class="info-section">
                            <h6>Notes</h6>
                            <p class="text-muted">{{ $invoice->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="col-md-4">
                <div class="invoice-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="icon-base ti tabler-calculator me-2"></i>
                            Payment Summary
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="amount-display mb-3">
                            Tnd {{ number_format($invoice->total_amount, 2) }}
                        </div>
                        <div class="mb-3">
                            <span class="status-badge {{ strtolower($invoice->status) }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('commercial.invoices.print', $invoice->id) }}" class="btn btn-primary btn-modern" target="_blank">
                                <i class="icon-base ti tabler-printer me-1"></i>
                                Print Invoice
                            </a>
                            <a href="{{ route('commercial.cases.show', $invoice->case_id) }}" class="btn btn-outline-primary btn-modern">
                                <i class="icon-base ti tabler-eye me-1"></i>
                                View Case
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="invoice-card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="icon-base ti tabler-credit-card me-2"></i>
                            Payment Details
                        </h5>
                    </div>
                    <div class="card-body">
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
                        <hr>
                        <div class="text-center">
                            @if($invoice->remaining_balance <= 0)
                                <span class="status-badge paid">Fully Paid</span>
                            @elseif($invoice->advance_payment > 0)
                                <span class="status-badge pending">Partial Payment</span>
                            @else
                                <span class="status-badge pending">Waiting Payment</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                @if($invoice->payments && $invoice->payments->count() > 0)
                <div class="invoice-card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="icon-base ti tabler-history me-2"></i>
                            Payment History
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($invoice->payments as $payment)
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2" style="background: #f8f9ff; border-radius: 10px;">
                            <div>
                                <small class="text-muted">{{ $payment->created_at->format('Y-m-d') }}</small>
                                <p class="mb-0">{{ $payment->payment_method ?? 'Payment' }}</p>
                            </div>
                            <strong class="text-success">Tnd {{ number_format($payment->amount, 2) }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
