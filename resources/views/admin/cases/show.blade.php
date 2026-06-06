<x-app-layout>
    <x-slot name="title">{{ __('master.case_details') }} - {{ $case->case_id }}</x-slot>

    @push('styles')
    <style>
        @media (min-width: 992px) { .cd-sidebar { position: sticky; top: 1.5rem; align-self: flex-start; } }
    </style>
    @endpush

    @include('partials.case_detail_compact')

    <div class="container-xxl flex-grow-1 container-p-y case-detail-compact">
        <div class="row">
            <div class="col-12">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.cases.list') }}">{{ __('master.cases') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $case->case_id }}</li>
                    </ol>
                </nav>

                <!-- Case Header -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-folder-open me-2"></i>
                                {{ __('master.case') }}: {{ $case->case_id }}
                            </h5>
                            <small class="text-muted">{{ __('master.created') }}: {{ $case->created_at->format('M d, Y H:i') }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.cases.edit', $case->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> {{ __('master.edit') }}
                            </a>
                            <a href="{{ route('admin.cases.list') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> {{ __('master.back') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Case Information -->
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
                                            <label class="form-label fw-bold">{{ __('master.case_id') }}</label>
                                            <p class="mb-0">{{ $case->case_id }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.status') }}</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-{{ $case->status === 'pending' ? 'warning' : ($case->status === 'draft' ? 'secondary' : ($case->status === 'in_planning' ? 'info' : ($case->status === 'approval' ? 'primary' : ($case->status === 'in_production' ? 'success' : ($case->status === 'shipped' ? 'success' : ($case->status === 'rejected' ? 'danger' : 'secondary')))))) }}">
                                                    {{ __('master.' . $case->status) }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.treatment_type') }}</label>
                                            <p class="mb-0">{{ $case->treatment_type ?? __('master.not_specified') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.treatment_description') }}</label>
                                            <p class="mb-0">{{ $case->treatment_treat ?? __('master.no_description') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.created_date') }}</label>
                                            <p class="mb-0">{{ $case->created_at->format('M d, Y H:i') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.last_updated') }}</label>
                                            <p class="mb-0">{{ $case->updated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.accepted_date') }}</label>
                                            <p class="mb-0">{{ $case->accepted_date ? \Carbon\Carbon::parse($case->accepted_date)->format('M d, Y H:i') : __('master.not_accepted') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.rejected_date') }}</label>
                                            <p class="mb-0">{{ $case->rejected_date ? \Carbon\Carbon::parse($case->rejected_date)->format('M d, Y H:i') : __('master.not_rejected') }}</p>
                                        </div>
                                        @if($case->price_rejected_at)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.price_rejected_date') }}</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-danger">
                                                    <i class="fas fa-times me-1"></i>
                                                    {{ $case->price_rejected_at->format('M d, Y H:i') }}
                                                </span>
                                            </p>
                                        </div>
                                        @endif
                                        @if($case->price_rejection_reason)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.price_rejection_reason') }}</label>
                                            <div class="alert alert-danger">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $case->price_rejection_reason }}
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        @if($case->price || $case->advance_payment)
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
                                            <label class="form-label fw-bold">{{ __('master.total_price') }}</label>
                                            <p class="mb-0">
                                                @if($case->price)
                                                    <strong class="text-success">Tnd {{ number_format($case->price, 2) }}</strong>
                                                @else
                                                    <span class="text-muted">{{ __('master.not_set') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.advance_payment') }}</label>
                                            <p class="mb-0">
                                                @foreach($case->invoices as $invoice)
                                                    @if($invoice->advance_payment)
                                                        <strong class="text-info">Tnd {{ number_format($invoice->advance_payment, 2) }}</strong>
                                                    @else
                                                        <span class="text-muted">{{ __('master.no_advance_payment') }}</span>
                                                    @endif
                                                @endforeach
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.remaining_balance') }}</label>
                                            <p class="mb-0">
                                                @foreach($case->invoices as $invoice)
                                                    @if($invoice->remaining_balance !== null)
                                                        <strong class="text-warning">Tnd {{ number_format($invoice->remaining_balance, 2) }}</strong>
                                                    @else
                                                        <span class="text-muted">{{ __('master.not_calculated') }}</span>
                                                    @endif
                                                @endforeach
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    {{ __('master.payment_information') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                   <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('master.payment_method') }}</th>
                                                    <th>{{ __('master.amount') }}</th>
                                                    <th>{{ __('master.payment_date') }}</th>
                                                    <th>{{ __('master.notes') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($case->invoices as $invoice)
                                                    @foreach($invoice->payments as $payment)
                                                    <tr>
                                                        <td>{{ $payment->payment_method }}</td>
                                                        <td>{{ $payment->amount }}</td>
                                                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                                        <td>{{ $payment->notes }}</td>
                                                    </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                                 
                                </div>
                        </div>
                        


                        @endif



                        <!-- Treatment Plans -->
                        @if($case->treatmentType && $case->treatmentType->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-medical me-2"></i>
                                    {{ __('master.treatment_plans_label') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($case->treatmentType as $treatmentPlan)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">{{ __('master.treatment_plan') }} #{{ $treatmentPlan->id }}</h6>
                                            <span class="badge bg-{{ $treatmentPlan->status === 'pending' ? 'warning' : ($treatmentPlan->status === 'accepted' ? 'success' : 'danger') }}">
                                                {{ __('master.' . $treatmentPlan->status) }}
                                            </span>
                                        </div>
                                        
                                        @if($treatmentPlan->description)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.description') }}</label>
                                                <p class="mb-0">{{ $treatmentPlan->description }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($treatmentPlan->irp_file)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.irp_file_pdf') }}</label>
                                                <p class="mb-0">
                                                    <a href="{{ ensure_https_url($treatmentPlan->irp_file) }}" 
                                                       target="_blank" 
                                                       class="badge bg-label-primary text-decoration-none">
                                                        <i class="fas fa-file-type-pdf me-1"></i>
                                                        {{ __('master.view_irp_file') }}
                                                    </a>
                                                </p>
                                            </div>
                                        @endif
                                        
                                        @if($treatmentPlan->link_viewer)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.3d_viewer') }}</label>
                                                <p class="mb-0">
                                                    <a href="{{ ensure_https_url($treatmentPlan->link_viewer) }}" 
                                                       target="_blank" 
                                                       class="badge bg-label-success text-decoration-none">
                                                        <i class="fas fa-link me-1"></i>
                                                        {{ __('master.open_3d_viewer') }}
                                                    </a>
                                                </p>
                                            </div>
                                        @endif
                                        
                                        @if($treatmentPlan->price)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.price') }}</label>
                                                <p class="mb-0">Tnd {{ number_format($treatmentPlan->price, 2) }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($treatmentPlan->treatment_plan_uploaded_at)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.uploaded_at') }}</label>
                                                <p class="mb-0">{{ $treatmentPlan->treatment_plan_uploaded_at ? $treatmentPlan->treatment_plan_uploaded_at->format('M d, Y H:i') : __('master.not_available') }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($treatmentPlan->accepted_at)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.accepted_at') }}</label>
                                                <p class="mb-0">{{ $treatmentPlan->accepted_at ? $treatmentPlan->accepted_at->format('M d, Y H:i') : __('master.not_available') }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($treatmentPlan->rejected_at)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.rejected_at') }}</label>
                                                <p class="mb-0">{{ $treatmentPlan->rejected_at ? $treatmentPlan->rejected_at->format('M d, Y H:i') : __('master.not_available') }}</p>
                                            </div>
                                            <div class="mb-2 text-danger">
                                                <label class="form-label fw-bold">{{ __('master.rejection_reason') }}</label>
                                                <p class="mb-0"><i class="icon-base ti tabler-alert-triangle me-1"></i>{{ $treatmentPlan->rejection_reason }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($treatmentPlan->price_added_at)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.price_added_at') }}</label>
                                                <p class="mb-0">{{ $treatmentPlan->price_added_at ? $treatmentPlan->price_added_at->format('M d, Y H:i') : __('master.not_available') }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($treatmentPlan->wetransfer_link)
                                            <div class="mb-2">
                                                <label class="form-label fw-bold">{{ __('master.wetransfer_link') }}</label>
                                                <p class="mb-0">
                                                    <a href="{{ ensure_https_url($treatmentPlan->wetransfer_link) }}" 
                                                       target="_blank" 
                                                       class="badge bg-label-success text-decoration-none">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        {{ __('master.open_wetransfer_link') }}
                                                    </a>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Files Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">{{ __('master.files') }}</h5>
                            </div>
                            <div class="card-body">
                                <h5>{{ __('master.impression_type') }} : <span class="badge bg-label-primary">
                                    @if($case->type_of_scan == 'intraoral')
                                        {{ __('master.intraoral_scan') }}
                                    @elseif($case->type_of_scan == 'desktop')
                                        {{ __('master.desktop_scan') }}
                                    @else
                                        {{ __('master.silicone_impression') }}
                                    @endif
                                </span></h5>
                                
                                <div class="mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary">
                                            <h6 class="mb-0">
                                                <i class="icon-base ti tabler-file-3d me-2"></i>
                                                {{ __('master.stl_files') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Upper Scan -->
                                              
                                                <div class="col-md-12">
                                                    @if($count_stl_files > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('master.rubrique') }}</th>
                                                                        <th>{{ __('master.file') }}</th>
                                                                        <th>{{ __('master.type_file') }}</th>
                                                                        <th>{{ __('master.size_file') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($stl_files as $file)
                                                                    <tr>
                                                                        <td>
                                                                            <span class="badge bg-label-primary">
                                                                                {{ __('master.stl_files') }}
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <a href="{{ $file->storage_type == 'google_drive' ? google_drive_download_url($file->url) : $file->url }}" download class="text-decoration-none">
                                                                                <i class="icon-base ti tabler-file-3d me-1"></i>{{ $file->name }}
                                                                            </a>
                                                                        </td>
                                                                        <td><span class="badge bg-label-primary">{{ $file->type }}</span></td>
                                                                        <td><small class="text-muted">{{ 
                                                                            $file->size 
                                                                            ? ($file->size < 1024 
                                                                                ? $file->size . ' B' 
                                                                                : ($file->size < 1024 * 1024 
                                                                                    ? number_format($file->size / 1024, 1) . ' KB' 
                                                                                    : number_format($file->size / (1024 * 1024), 1) . ' MB')) 
                                                                            : __('master.unknown')
                                                                        }}</small></td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="text-center text-muted py-3">
                                                            <i class="icon-base ti tabler-file-3d" style="font-size: 2rem;"></i>
                                                            <p class="mb-0 mt-2">{{ __('master.not_available') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
    
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
    
                                <!-- Clinical Photos Section -->
                                <div class="mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary ">
                                            <h6 class="mb-0">
                                                <i class="icon-base ti tabler-camera me-2"></i>
                                                {{ __('master.clinical_photos') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($count_clinical_files > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('master.rubrique') }}</th>
                                                                <th>{{ __('master.file') }}</th>
                                                                <th>{{ __('master.type_file') }}</th>
                                                                <th>{{ __('master.size_file') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($files_clinical as $file)
                                                            <tr>
                                                                <td>
                                                                    <span class="badge bg-label-success">
                                                                        {{ __('master.clinical_photos') }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <a href="{{ $file->storage_type == 'google_drive' ? google_drive_image_url($file->url) : $file->url }}" target="_blank" class="text-decoration-none">
                                                                        <i class="icon-base ti tabler-photo me-1"></i>{{ $file->name }}
                                                                    </a>
                                                                </td>
                                                                <td><span class="badge bg-label-info">{{ $file->type }}</span></td>
                                                                <td><small class="text-muted">{{ 
                                                                            $file->size 
                                                                            ? ($file->size < 1024 
                                                                                ? $file->size . ' B' 
                                                                                : ($file->size < 1024 * 1024 
                                                                                    ? number_format($file->size / 1024, 1) . ' KB' 
                                                                                    : number_format($file->size / (1024 * 1024), 1) . ' MB')) 
                                                                            : __('master.unknown')
                                                                        }}</small></td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center text-muted py-3">
                                                    <i class="icon-base ti tabler-photo-off" style="font-size: 2rem;"></i>
                                                    <p class="mb-0 mt-2">{{ __('master.not_available') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
    
                                <!-- Radiographs Section -->
                                <div class="mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary">
                                            <h6 class="mb-0">
                                                <i class="icon-base ti tabler-radioactive me-2"></i>
                                                {{ __('master.files_radiographs') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($count_radiograph_files > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('master.rubrique') }}</th>
                                                                <th>{{ __('master.file') }}</th>
                                                                <th>{{ __('master.type_file') }}</th>
                                                                <th>{{ __('master.size_file') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($files_radiographs as $file)
                                                            <tr>
                                                                <td>
                                                                    <span class="badge bg-label-warning">
                                                                        {{ __('master.files_radiographs') }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <a href="{{ $file->storage_type == 'google_drive' ? google_drive_image_url($file->url) : $file->url }}" target="_blank" class="text-decoration-none">
                                                                        <i class="icon-base ti tabler-radioactive  me-1"></i>{{ $file->name }}
                                                                    </a>
                                                                </td>
                                                                <td><span class="badge bg-label-warning">{{ $file->type }}</span></td>
                                                                <td><small class="text-muted">{{ 
                                                                            $file->size 
                                                                            ? ($file->size < 1024 
                                                                                ? $file->size . ' B' 
                                                                                : ($file->size < 1024 * 1024 
                                                                                    ? number_format($file->size / 1024, 1) . ' KB' 
                                                                                    : number_format($file->size / (1024 * 1024), 1) . ' MB')) 
                                                                            : __('master.unknown')
                                                                        }}</small></td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center text-muted py-3">
                                                    <i class="icon-base ti tabler-radioactive-off" style="font-size: 2rem;"></i>
                                                    <p class="mb-0 mt-2">{{ __('master.not_available') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
    
                                <!-- Other Files Section -->
                                <div class="mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary">
                                            <h6 class="mb-0">
                                                <i class="icon-base ti tabler-file-plus me-2"></i>
                                                {{ __('master.other_files') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($count_other_files > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('master.rubrique') }}</th>
                                                                <th>{{ __('master.file') }}</th>
                                                                <th>{{ __('master.type_file') }}</th>
                                                                <th>{{ __('master.size_file') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($other_files as $file)
                                                            <tr>
                                                                <td>
                                                                    <span class="badge bg-label-secondary">
                                                                        {{ __('master.other_files') }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <a href="{{ $file->storage_type == 'google_drive' ? google_drive_image_url($file->url) : $file->url }}" target="_blank" class="text-decoration-none">
                                                                        <i class="icon-base ti tabler-file me-1"></i>{{ $file->name }}
                                                                    </a>
                                                                </td>
                                                                <td><span class="badge bg-label-secondary">{{ $file->type }}</span></td>
                                                                <td><small class="text-muted">{{ 
                                                                            $file->size 
                                                                            ? ($file->size < 1024 
                                                                                ? $file->size . ' B' 
                                                                                : ($file->size < 1024 * 1024 
                                                                                    ? number_format($file->size / 1024, 1) . ' KB' 
                                                                                    : number_format($file->size / (1024 * 1024), 1) . ' MB')) 
                                                                            : __('master.unknown')
                                                                        }}</small></td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center text-muted py-3">
                                                    <i class="icon-base ti tabler-file-off" style="font-size: 2rem;"></i>
                                                    <p class="mb-0 mt-2">{{ __('master.not_available') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Case Treatment Details (below files list) -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">{{ __('master.case_treatment') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>{{ __('master.tooth_problem') }}</th>
                                                <th>{{ __('master.doctor_instructions') }}</th>
                                                <th>{{ __('master.treatment_treat') }}</th>
                                                <th>{{ __('master.treatment_type') }}</th>
                                                <th>{{ __('master.treatment_overjet') }}</th>
                                                <th>{{ __('master.treatment_overbite') }}</th>
                                                <th>{{ __('master.treatment_midline') }}</th>
                                                <th>{{ __('master.treatment_IPR') }}</th>
                                                <th>{{ __('master.treatment_attachments') }}</th>
                                                <th>{{ __('master.patient_chief_complaint') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    @foreach($toothProblemscase as $toothProblems)
                                                    {{ __('master.tooth') }} {{ $toothProblems->tooth_number }} - {{ $toothProblems->tooth_problem->name }} <br>
                                                     {{ $toothProblems->tooth_notes }}
                                                     @endforeach
                                                </td>
                                                <td>{{ ucfirst($case->doctor_instructions ?? __('master.not_available')) }}</td>
                                                <td>{{ ucfirst($case->treatment_treat ?? __('master.not_available')) }}</td>
                                                <td>{{ ucfirst($case->treatment_type ?? __('master.not_available')) }}</td>
                                                <td>{{ ucfirst($case->treatment_overjet ?? __('master.not_available')) }}</td>
                                                <td>{{ ucfirst($case->treatment_overbite ?? __('master.not_available')) }}</td>
                                                <td>{{ ucfirst($case->treatment_midline ?? __('master.not_available')) }}</td>
                                                <td>{{ ucfirst($case->treatment_irp ?? __('master.not_available')) }}</td>
                                                <td>{{ ucfirst($case->treatment_attachements ?? __('master.not_available')) }}</td>
                                                <td>{{ ucfirst($case->patient_chief_complaint ?? __('master.not_available')) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Comments -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ __('master.case_comments') }}</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.cases.add_comment', $case->id) }}" method="POST" class="mb-4">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="comment" class="form-label">{{ __('master.add_comment') }}</label>
                                        <textarea name="comment" id="comment" rows="3" class="form-control" placeholder="{{ __('master.add_comment') }}" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> {{ __('master.add_comment') }}
                                    </button>
                                </form>

                                <div id="comments">
                                    @forelse($comments as $comment)
                                        <div class="comment mb-3 p-3 border rounded">
                                            <div class="d-flex align-items-start gap-3">
                                                <img src="{{ $comment->user->photo_url }}" alt="User Photo" class="rounded-circle" style="width:42px;height:42px;object-fit:cover;">
                                                <div class="flex-grow-1">
                                                    <p class="mb-1">
                                                        <span class="badge bg-label-primary">{{ $comment->user->name }} - {{ ucfirst($comment->user->role->name ?? '') }}</span>
                                                        <small class="text-body-secondary">{{ __('master.date') }} : {{ $comment->created_at->format('d-m-Y H:i:s') }}</small>
                                                    </p>
                                                    <p class="mb-0"><strong>{{ __('master.comment') }} :</strong> {{ $comment->comment }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-comments" style="font-size: 2rem;"></i>
                                            <p class="mb-0 mt-2">{{ __('master.no_comments_yet') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- FINITION -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-1">
                                    <i class="fas fa-flag-checkered me-2"></i>{{ __('master.finition') }}
                                </h6>
                                <small class="text-muted">{{ __('master.finition_description') }}</small>
                            </div>
                            <div class="card-body">
                                @include('partials.finition_content', ['case' => $case])
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar Information -->
                    <div class="col-md-4 cd-sidebar">
                        <!-- Patient Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user me-2"></i>
                                    {{ __('master.patient_information') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($case->patient)
                                    <div class="text-center mb-3">
                                        <img src="{{ $case->patient->photo_url ?? asset('assets/img/avatars/default.png') }}" 
                                             alt="Patient Photo" 
                                             class="rounded-circle mb-2" 
                                             width="80" height="80"
                                             style="object-fit: cover;">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('master.reference') }}</label>
                                        <p class="mb-0"><span class="badge bg-label-primary">{{ $case->patient->reference ?? __('master.not_provided') }}</span></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('master.name') }}</label>
                                        <p class="mb-0">{{ $case->patient->name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('master.email') }}</label>
                                        <p class="mb-0">{{ $case->patient->email ?? __('master.not_provided') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('master.phone') }}</label>
                                        <p class="mb-0">{{ $case->patient->phone ?? __('master.not_provided') }}</p>
                                    </div>
                                   
                                @else
                                    <p class="text-muted">{{ __('master.no_patient_information') }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Case discussion / chat --}}
                        @include('partials.case_chat', ['case' => $case])

                        <!-- Doctor Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-md me-2"></i>
                                    {{ __('master.doctor_information') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($case->doctor)
                                    <div class="text-center mb-3">
                                        <img src="{{ $case->doctor->photo_url }}" 
                                             alt="Doctor Photo" 
                                             class="rounded-circle mb-2" 
                                             width="80" height="80"
                                             style="object-fit: cover;">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('master.name') }}</label>
                                        <p class="mb-0">{{ $case->doctor->name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('master.email') }}</label>
                                        <p class="mb-0">{{ $case->doctor->email }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ __('master.status') }}</label>
                                        <p class="mb-0">
                                            <span class="badge bg-label-{{ $case->doctor->status === 'active' ? 'success' : 'danger' }}">
                                                {{ __('master.' . $case->doctor->status) }}
                                            </span>
                                        </p>
                                    </div>
                                @else
                                    <p class="text-muted">{{ __('master.no_doctor_information') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Technician Information -->
                        @if($case->technician)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-tools me-2"></i>
                                    {{ __('master.technician_information') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <img src="{{ $case->technician->photo_url }}" 
                                         alt="Technician Photo" 
                                         class="rounded-circle mb-2" 
                                         width="80" height="80"
                                         style="object-fit: cover;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.name') }}</label>
                                    <p class="mb-0">{{ $case->technician->name }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.email') }}</label>
                                    <p class="mb-0">{{ $case->technician->email }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.status') }}</label>
                                    <p class="mb-0">
                                        <span class="badge bg-label-{{ $case->technician->status === 'active' ? 'success' : 'danger' }}">
                                            {{ __('master.' . $case->technician->status) }}
                                        </span>
                                    </p>
                                </div>
                                @if(!empty($case->technician_comment))
                                <div class="alert alert-info d-flex mb-3" role="alert">
                                    <i class="fas fa-comment-dots me-2 mt-1"></i>
                                    <div>
                                        <div class="fw-bold small mb-1">{{ __('master.private_comment') }}</div>
                                        <div class="small">{{ $case->technician_comment }}</div>
                                    </div>
                                </div>
                                @endif
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignTechnicianModal">
                                        <i class="fas fa-edit me-1"></i> {{ __('master.change_technician') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-tools me-2"></i>
                                    {{ __('master.technician_assignment') }}
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-plus fa-3x text-muted"></i>
                                </div>
                                <p class="text-muted mb-3">{{ __('master.no_technician_assigned') }}</p>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignTechnicianModal">
                                    <i class="fas fa-plus me-1"></i> {{ __('master.assign_technician') }}
                                </button>
                            </div>
                        </div>
                        @endif

                        <!-- Laboratory Information -->
                        @if($case->laboratory)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-flask me-2"></i>
                                    {{ __('master.laboratory_information') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <img src="{{ $case->laboratory->photo_url }}" 
                                         alt="Laboratory Photo" 
                                         class="rounded-circle mb-2" 
                                         width="80" height="80"
                                         style="object-fit: cover;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.name') }}</label>
                                    <p class="mb-0">{{ $case->laboratory->name }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.email') }}</label>
                                    <p class="mb-0">{{ $case->laboratory->email }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ __('master.status') }}</label>
                                    <p class="mb-0">
                                        <span class="badge bg-label-{{ $case->laboratory->status === 'active' ? 'success' : 'danger' }}">
                                            {{ __('master.' . $case->laboratory->status) }}
                                        </span>
                                    </p>
                                </div>
                                @if(!empty($case->laboratory_comment))
                                <div class="alert alert-info d-flex mb-3" role="alert">
                                    <i class="fas fa-comment-dots me-2 mt-1"></i>
                                    <div>
                                        <div class="fw-bold small mb-1">{{ __('master.private_comment') }}</div>
                                        <div class="small">{{ $case->laboratory_comment }}</div>
                                    </div>
                                </div>
                                @endif
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignLaboratoryModal">
                                        <i class="fas fa-edit me-1"></i> {{ __('master.change_laboratory') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-flask me-2"></i>
                                    {{ __('master.laboratory_assignment') }}
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-plus fa-3x text-muted"></i>
                                </div>
                                <p class="text-muted mb-3">{{ __('master.no_laboratory_assigned') }}</p>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignLaboratoryModal">
                                    <i class="fas fa-plus me-1"></i> {{ __('master.assign_laboratory') }}
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Technician Modal -->
    <div class="modal fade" id="assignTechnicianModal" tabindex="-1" aria-labelledby="assignTechnicianModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignTechnicianModalLabel">
                        <i class="fas fa-tools me-2"></i>
                        {{ __('master.assign_technician') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignTechnicianForm" action="{{ route('admin.cases.assign-technician', $case->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="technician_id" class="form-label">{{ __('master.select_technician') }}</label>
                            <select class="form-select" id="technician_id" name="technician_id" required>
                                <option value="">{{ __('master.choose_technician') }}</option>
                                @foreach(\App\Models\User::where('role_id', 3)->where('status', 'active')->get() as $technician)
                                    <option value="{{ $technician->id }}" {{ $case->technician_id == $technician->id ? 'selected' : '' }}>
                                        {{ $technician->name }} ({{ $technician->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="technician_comment" class="form-label">{{ __('master.private_comment') }}</label>
                            <textarea class="form-control" id="technician_comment" name="technician_comment" rows="3" placeholder="{{ __('master.comment_visible_to_technician_only') }}">{{ $case->technician_comment }}</textarea>
                            <small class="text-muted">{{ __('master.comment_visible_to_technician_only') }}</small>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="send_notification" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="send_notification" name="send_notification" value="1" checked>
                                <label class="form-check-label" for="send_notification">
                                    {{ __('master.send_notification_to_technician') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('master.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {{ __('master.assign_technician') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Laboratory Modal -->
    <div class="modal fade" id="assignLaboratoryModal" tabindex="-1" aria-labelledby="assignLaboratoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignLaboratoryModalLabel">
                        <i class="fas fa-flask me-2"></i>
                        {{ __('master.laboratory_assignment') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignLaboratoryForm" action="{{ route('admin.cases.assign-laboratory', $case->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="laboratory_id" class="form-label">{{ __('master.select_laboratory') }}</label>
                            <select class="form-select" id="laboratory_id" name="laboratory_id" required>
                                <option value="">{{ __('master.choose_laboratory') }}</option>
                                @foreach(\App\Models\User::where('role_id', 4)->where('status', 'active')->get() as $laboratory)
                                    <option value="{{ $laboratory->id }}" {{ $case->laboratory_id == $laboratory->id ? 'selected' : '' }}>
                                        {{ $laboratory->name }} ({{ $laboratory->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="laboratory_comment" class="form-label">{{ __('master.private_comment') }}</label>
                            <textarea class="form-control" id="laboratory_comment" name="laboratory_comment" rows="3" placeholder="{{ __('master.comment_visible_to_laboratory_only') }}">{{ $case->laboratory_comment }}</textarea>
                            <small class="text-muted">{{ __('master.comment_visible_to_laboratory_only') }}</small>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="send_notification" value="0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="send_notification_lab" name="send_notification" value="1" checked>
                                <label class="form-check-label" for="send_notification_lab">
                                    {{ __('master.send_notification_to_laboratory') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('master.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {{ __('master.assign_laboratory') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
@push('styles')

@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle technician assignment form submission
    $('#assignTechnicianForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> {{ __('master.assigning') }}');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                // Show success message
                toastr.success('{{ __('master.technician_assigned_successfully') }}');
                
                // Close modal
                $('#assignTechnicianModal').modal('hide');
                
                // Reload page to show updated information
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                // Show error message
                const errorMessage = xhr.responseJSON?.message || '{{ __('master.error_assigning_technician') }}';
                toastr.error(errorMessage);
                
                // Re-enable button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Handle laboratory assignment form submission
    $('#assignLaboratoryForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> {{ __('master.assigning') }}');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                // Show success message
                toastr.success('{{ __('master.laboratory_assigned_successfully') }}');
                
                // Close modal
                $('#assignLaboratoryModal').modal('hide');
                
                // Reload page to show updated information
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                // Show error message
                const errorMessage = xhr.responseJSON?.message || '{{ __('master.error_assigning_laboratory') }}';
                toastr.error(errorMessage);
                
                // Re-enable button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush

</x-app-layout>
