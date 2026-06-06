<x-app-layout>
    <x-slot name="title">Case Details - Price Manager</x-slot>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="icon-base ti tabler-eye me-2"></i>
                            Case Details - {{ $case->case_id }}
                        </h5>
                        <a href="{{ route('admin.price_manager.index') }}" class="btn btn-secondary">
                            <i class="icon-base ti tabler-arrow-left me-1"></i>Back to Price Manager
                        </a>
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
                            <!-- Case Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-user me-1"></i>
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
                                            <label class="form-label fw-bold">Doctor:</label>
                                            <p class="mb-0">{{ $case->doctor->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Technician:</label>
                                            <p class="mb-0">{{ $case->technician->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Treatment Type:</label>
                                            <p class="mb-0">{{ $case->treatment_type ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Current Status:</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-{{ $case->status === 'in_production' ? 'success' : ($case->status === 'approval' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Created Date:</label>
                                            <p class="mb-0">{{ $case->created_at ? $case->created_at->format('d-m-Y H:i') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-currency-dollar me-1"></i>
                                            Pricing Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($case->price)
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Total Price:</label>
                                                <p class="mb-0">
                                                    <span class="badge bg-label-success fs-6">Tnd {{ number_format($case->price, 2) }}</span>
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Advance Payment:</label>
                                                <p class="mb-0">
                                                        @foreach($case->invoices as $invoice)
                                                        @if($invoice->advance_payment)
                                                            <span class="badge bg-label-info">Tnd {{ number_format($invoice->advance_payment, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">No advance payment</span>
                                                        @endif
                                                    @endforeach
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Remaining Balance:</label>
                                                <p class="mb-0">
                                                    @foreach($case->invoices as $invoice)
                                                        @if($invoice->remaining_balance)
                                                                <span class="badge bg-label-warning">Tnd {{ number_format($invoice->remaining_balance, 2) }}</span>
                                                        @else
                                                            <span class="text-muted">No remaining balance</span>
                                                        @endif
                                                    @endforeach
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Price Added By:</label>
                                                <p class="mb-0">{{ $case->admin->name ?? 'N/A' }}</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Price Added Date:</label>
                                                <p class="mb-0">{{ $case->price_added_at ? $case->price_added_at->format('d-m-Y H:i') : 'N/A' }}</p>
                                            </div>
                                            @if($case->estimated_completion_date)
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Estimated Completion:</label>
                                                    <p class="mb-0">{{ $case->estimated_completion_date->format('d-m-Y') }}</p>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center py-4">
                                                <i class="icon-base ti tabler-currency-dollar text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted mt-2">No price added yet</p>
                                                <a href="{{ route('admin.price_manager.show_add_price', $case->id) }}" class="btn btn-success btn-sm">
                                                    <i class="icon-base ti tabler-plus me-1"></i>Add Price
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Treatment Plans -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-file-text me-1"></i>
                                            Treatment Plans ({{ $case->treatmentType->count() }})
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($case->treatmentType->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="border-top">
                                                        <tr>
                                                            <th>IRP File</th>
                                                            <th>3D Viewer Link</th>
                                                            <th>Status</th>
                                                            <th>Uploaded By</th>
                                                            <th>Uploaded Date</th>
                                                            <th>Accepted/Rejected By</th>
                                                            <th>Accepted/Rejected Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($case->treatmentType as $treatmentPlan)
                                                            <tr>
                                                                <td>
                                                                    <strong><a href="{{ $treatmentPlan->irp_file }}" target="_blank">{{ Str::limit($treatmentPlan->irp_file, 50) }}</a></strong>
                                                                </td>
                                                                <td>
                                                                    <a href="{{ $treatmentPlan->link_viewer }}" target="_blank">{{ Str::limit($treatmentPlan->link_viewer, 50) }}</a>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-label-{{ $treatmentPlan->status === 'accepted' ? 'success' : ($treatmentPlan->status === 'rejected' ? 'danger' : 'warning') }}">
                                                                        {{ ucfirst($treatmentPlan->status) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->technician->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->created_at ? $treatmentPlan->created_at->format('d-m-Y H:i') : 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    @if($treatmentPlan->status === 'accepted')
                                                                        {{ $treatmentPlan->doctor->name ?? 'N/A' }}
                                                                    @elseif($treatmentPlan->status === 'rejected')
                                                                        {{ $treatmentPlan->doctor->name ?? 'N/A' }}
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($treatmentPlan->status === 'accepted')
                                                                        {{ $treatmentPlan->accepted_at ? $treatmentPlan->accepted_at->format('d-m-Y H:i') : 'N/A' }}
                                                                    @elseif($treatmentPlan->status === 'rejected')
                                                                        {{ $treatmentPlan->rejected_at ? $treatmentPlan->rejected_at->format('d-m-Y H:i') : 'N/A' }}
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                               
                                                            </tr>
                                                            @if($treatmentPlan->status === 'rejected' && $treatmentPlan->rejection_reason)
                                                                <tr>
                                                                    <td colspan="8">
                                                                        <div class="alert alert-danger py-2 mb-0">
                                                                            <strong>Rejection Reason:</strong> {{ $treatmentPlan->rejection_reason }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="icon-base ti tabler-file-text text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted mt-2">No treatment plans uploaded yet</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoices -->
                        @if($case->invoices->count() > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="icon-base ti tabler-receipt me-1"></i>
                                                Invoices ({{ $case->invoices->count() }})
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="border-top">
                                                        <tr>
                                                            <th>Invoice Number</th>
                                                            <th>Total Amount</th>
                                                            <th>Advance Payment</th>
                                                            <th>Remaining Balance</th>
                                                            <th>Status</th>
                                                            <th>Created Date</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($case->invoices as $invoice)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $invoice->invoice_number }}</strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-label-success">Tnd {{ number_format($invoice->total_amount, 2) }}</span>
                                                                </td>
                                                                <td>
                                                                    @if($invoice->advance_payment)
                                                                        <span class="badge bg-label-info">Tnd {{ number_format($invoice->advance_payment, 2) }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-label-warning">Tnd {{ number_format($invoice->remaining_balance, 2) }}</span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-label-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'pending' ? 'warning' : 'secondary') }}">
                                                                        {{ ucfirst($invoice->status) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    {{ $invoice->created_at ? $invoice->created_at->format('d-m-Y H:i') : 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <a href="#" class="btn btn-sm btn-outline-info">
                                                                        <i class="icon-base ti tabler-eye me-1"></i>View
                                                                    </a>
                                                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                                                        <i class="icon-base ti tabler-download me-1"></i>Download
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.price_manager.index') }}" class="btn btn-secondary">
                                        <i class="icon-base ti tabler-arrow-left me-1"></i>Back to Price Manager
                                    </a>
                                    @if(!$case->price)
                                        <a href="{{ route('admin.price_manager.show_add_price', $case->id) }}" class="btn btn-success">
                                            <i class="icon-base ti tabler-plus me-1"></i>Add Price
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
