<x-app-layout>
    <x-slot name="title">{{ __('master.invoice_details') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('doctor.cases') }}">{{ __('master.cases') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.invoice_details') }}</li>
            </ol>
        </nav>

        <!-- Invoice Details -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt me-2"></i>
                            {{ __('master.invoice') }} #{{ $invoice->invoice_number }}
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('doctor.invoices.print', $invoice->id) }}" 
                               class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-print me-1"></i>{{ __('master.print') }}
                            </a>
                            <a href="{{ route('doctor.cases') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>{{ __('master.back') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Invoice Information -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">{{ __('master.invoice_information') }}</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __('master.invoice_number') }}:</strong></td>
                                        <td>{{ $invoice->invoice_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('master.issue_date') }}:</strong></td>
                                        <td>{{ $invoice->created_at ? date('Y-m-d', strtotime($invoice->created_at)) : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('master.due_date') }}:</strong></td>
                                        <td>{{ $invoice->due_date ? date('Y-m-d', strtotime($invoice->due_date)) : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('master.status') }}:</strong></td>
                                        <td>{!! $invoice->status === 'paid' ? '<span class="badge bg-success">Paid</span>' : 
                                               ($invoice->status === 'pending' ? '<span class="badge bg-warning">Pending</span>' : 
                                               '<span class="badge bg-label-danger">Overdue</span>') !!}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('master.amount') }}:</strong></td>
                                        <td><strong class="text-primary">Tnd {{ number_format($invoice->total_amount, 2) }}</strong></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Case Information -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">{{ __('master.case_information') }}</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __('master.case_id') }}:</strong></td>
                                        <td>{{ $invoice->case->case_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('master.patient_name') }}:</strong></td>
                                        <td>{{ $invoice->case->patient->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('master.treatment_type') }}:</strong></td>
                                        <td>{{ $invoice->case->treatment_type ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('master.doctor') }}:</strong></td>
                                        <td>{{ $invoice->case->doctor->name ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                     

                        <!-- Notes -->
                        @if($invoice->notes)
                        <div class="mt-4">
                            <h6 class="text-primary mb-3">{{ __('master.notes') }}</h6>
                            <div class="alert alert-info">
                                {{ $invoice->notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
