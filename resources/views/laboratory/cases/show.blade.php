<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
        }
        .select2-container {
        z-index: 9999 !important;
    }
    .select2-dropdown {
        z-index: 99999 !important;
    }
    .files-table-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #01b9c6; margin-bottom: .6rem; padding-bottom: .4rem; border-bottom: 1px solid #e9eef3; display: flex; align-items: center; }
    @media (min-width: 992px) { .cd-sidebar { position: sticky; top: 1.5rem; } }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/lightbox/css/lightbox.min.css') }}">
    @endpush
    @include('partials.case_detail_compact')

    <div class="container-xxl flex-grow-1 container-p-y case-detail-compact">
        <div class="row g-6">
            @if($case->status == 'in_production')
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('master.case_actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('laboratory.cases.updateStatus', ['id' => $case->id, 'status' => 'shipped']) }}" 
                           class="btn btn-warning waves-effect waves-light"
                           onclick="return confirm('{{ __('master.are_you_sure_mark_shipped') }}')">
                            <i class="icon-base ti tabler-truck"></i> {{ __('master.mark_as_shipped') }}
                        </a>
                        <small class="text-muted d-block mt-2">{{ __('master.mark_shipped_description') }}</small>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-lg-4 cd-sidebar">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('master.case_details') }}  <br><b>{{ __('master.case_id') }}</b> : #<span class="text-body-primary"> {{ $case->case_id }}</span>  <br><span class="badge bg-label-primary">{{ __('master.case_status') }} : {{ ucfirst($case->status) }}</span></h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                
                                <div class="mb-3">  
                                    <b>{{ __('master.patient_name') }} :</b> <span class="text-body-primary"> {{ $case->patient->name }} {{ $case->patient->surname }}</span>
                                </div>
                                <div class="mb-3">  
                                    <b>{{ __('master.patient_gender') }} :</b> <span class="text-body-primary"> {{ ucfirst($case->patient->gender) }}</span>
                                </div>
                                <div class="mb-3">  
                                    <b>{{ __('master.patient_phone') }} :</b> <span class="text-body-primary"> {{ $case->patient->phone ? $case->patient->phone : __('master.not_available') }}</span>
                                </div>
                                <div class="mb-3">  
                                    <b>{{ __('master.patient_email') }} :</b> <span class="text-body-primary"> {{ $case->patient->email ? $case->patient->email : __('master.not_available') }}</span>
                                </div>
                                <div class="mb-3">  
                                    <b>{{ __('master.patient_address') }} :</b> <span class="text-body-primary"> {{ $case->patient->address ? $case->patient->address : __('master.not_available') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">  
                                    <b>{{ __('master.doctor_name') }} :</b> <span class="text-body-primary"> {{ $case->doctor->name }} {{ $case->doctor->surname }}</span>
                                </div>
                                <div class="mb-3">  
                                    <b>{{ __('master.doctor_email') }} :</b> <span class="text-body-primary"> {{ $case->doctor->email ? $case->doctor->email : __('master.not_available') }}</span>
                                </div>
                                <div class="mb-3">  
                                    <b>{{ __('master.case_status') }} :</b> <span class="text-body-primary"> 
                                        @if($case->status == 'pending') <span class="badge bg-label-warning">{{ __('master.pending') }}</span> 
                                        @elseif($case->status == 'draft') <span class="badge bg-label-secondary">{{ __('master.draft') }}</span> 
                                        @elseif($case->status == 'in_planning') <span class="badge bg-label-secondary">{{ __('master.in_planning') }}</span> 
                                        @elseif($case->status == 'approval') <span class="badge bg-label-secondary">{{ __('master.approval') }}</span> 
                                        @elseif($case->status == 'in_production') <span class="badge bg-label-success">{{ __('master.in_production') }}</span> 
                                        @elseif($case->status == 'shipped') <span class="badge bg-label-success">{{ __('master.shipped') }}</span> 
                                        @else <span class="badge bg-label-danger">{{ __('master.rejected') }}</span> 
                                        @endif</span>
                                </div>
                                <div class="mb-3">  
                                    <b>{{ __('master.case_date') }} :</b> <span class="text-body-primary"> {{ $case->created_at->format('d-m-Y') }}</span>
                                </div>
                                


                            </div>
                        </div>
                    </div>
                  
                </div>{{-- /.card case details --}}

                {{-- Chat discussion on the first side --}}
                @include('partials.case_chat', ['case' => $case])
            </div>{{-- /.col-lg-4 sidebar --}}

            {{-- MAIN COLUMN --}}
            <div class="col-lg-8">
                <div class="row g-6">

                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title">{{ __('master.assigned_to') }}</h5>
                        </div>
                        <div class="card-body"> 
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="badge bg-label-warning">{{ __('master.technician') }}</h6>
                                    @if($case->technician_id)
                                    <p><b>{{ __('master.technician_name') }} :</b> <span class="text-body-primary"> {{ $case->technician->name }}</span></p>
                            <p><b>{{ __('master.technician_email') }} :</b> <span class="text-body-primary"> {{ $case->technician->email }}</span></p>
                            @else
                            <p>{{ __('master.not_assigned') }}</p>
                                    @endif
                                </div>
                                <div class="col-md-12">
                                    <h6 class="badge bg-label-success">{{ __('master.laboratory') }}</h6>
                                    @if($case->laboratory_id)
                                    <p><b>{{ __('master.laboratory_name') }} :</b> <span class="text-body-primary"> {{ $case->laboratory->name }}</span></p>
                                    <p><b>{{ __('master.laboratory_email') }} :</b> <span class="text-body-primary"> {{ $case->laboratory->email }}</span></p>
                                    @else
                                    <p>{{ __('master.not_assigned') }}</p>
                                    @endif
                                    @if(!empty($case->laboratory_comment))
                                    <div class="alert alert-info d-flex mt-2 mb-0" role="alert">
                                        <i class="fas fa-comment-dots me-2 mt-1"></i>
                                        <div>
                                            <div class="fw-bold small mb-1">{{ __('master.comment_from_admin') }}</div>
                                            <div class="small">{{ $case->laboratory_comment }}</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
            @php
            $weTransferNotifications = $case->weTransferNotifications->sortByDesc('sent_at');
        @endphp
        @if($weTransferNotifications && $weTransferNotifications->count() > 0)
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ __('master.wetransfer_information') }} ({{ $weTransferNotifications->count() }})</h5>
                </div>
                <div class="card-body">
                    @foreach($weTransferNotifications as $index => $weTransferNotification)
                    <div class="wetransfer-item mb-4 p-3 border rounded {{ $index === 0 ? 'border-primary' : '' }}">
                        @if($index === 0)
                        <div class="mb-2">
                            <span class="badge bg-label-success">{{ __('master.latest') }}</span>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ __('master.wetransfer_link') }}:</strong></p>
                                <a href="{{ $weTransferNotification->wetransfer_link }}" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="icon-base ti tabler-download"></i> {{ __('master.download_files') }}
                                </a>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __('master.sent_by') }}:</strong> {{ $weTransferNotification->technician->name }}</p>
                                <p><strong>{{ __('master.sent_date') }}:</strong> {{ $weTransferNotification->sent_at->format('d/m/Y H:i') }}</p>
                                @if($weTransferNotification->message)
                                <p><strong>{{ __('master.message') }}:</strong> {{ $weTransferNotification->message }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
                                 <!-- Treatment Types -->
                <div class="col-12 order-1">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ __('master.treatment_plan') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($treatmentTypes && $treatmentTypes->count() > 0)
                                @foreach($treatmentTypes as $treatmentPlan)
                                    <div class="treatment-plan-item mb-4 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="mb-2">{{ $treatmentPlan->name }}</h6>
                                                <p class="text-muted mb-2">
                                                    <strong>{{ __('master.created_at') }}:</strong> 
                                                    {{ $treatmentPlan->created_at ? $treatmentPlan->created_at->format('d-m-Y H:i') : 'N/A' }}
                                                </p>
                                                @if($treatmentPlan->description)
                                                    <p class="mb-2"><strong>{{ __('master.description') }}:</strong> {{ $treatmentPlan->description }}</p>
                                                @endif
                                                
                                                <!-- IRP File -->
                                                @if($treatmentPlan->irp_file)
                                                    <div class="mb-2">
                                                        <strong>{{ __('master.irp_file_pdf') }}:</strong>
                                                        <a href="{{ ensure_https_url($treatmentPlan->irp_file) }}" target="_blank" class="btn btn-outline-primary btn-sm ms-2">
                                                            <i class="icon-base ti tabler-file-pdf me-1"></i>{{ __('master.view_irp_file') }}
                                                        </a>
                                                    </div>
                                                @endif
                                                
                                                <!-- 3D Viewer Link -->
                                                @if($treatmentPlan->link_viewer)
                                                    <div class="mb-2">
                                                        <strong>{{ __('master.3d_viewer') }}:</strong>
                                                        <a href="{{ ensure_https_url($treatmentPlan->link_viewer) }}" target="_blank" class="btn btn-outline-success btn-sm ms-2">
                                                            <i class="icon-base ti tabler-3d-cube-sphere me-1"></i>{{ __('master.open_3d_viewer') }}
                                                        </a>
                                                    </div>
                                                @endif
                                                
                                                <!-- Estimated Completion Date -->
                                                <div class="mb-2">
                                                    <strong>{{ __('master.estimated_completion') }}:</strong>
                                                    @if($treatmentPlan->estimated_completion_date)
                                                        <span class="badge bg-label-info">{{ $treatmentPlan->estimated_completion_date->format('d-m-Y H:i') }}</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">{{ __('master.not_set') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-flex flex-column gap-2">

                                                    <!-- Status Badge -->
                                                    <div class="mb-2">
                                                        @if($treatmentPlan->status === 'pending')
                                                            <span class="badge bg-label-warning">{{ __('master.pending') }}</span>
                                                        @elseif($treatmentPlan->status === 'in_progress')
                                                            <span class="badge bg-label-info">{{ __('master.in_progress') }}</span>
                                                        @elseif($treatmentPlan->status === 'completed')
                                                            <span class="badge bg-label-success">{{ __('master.completed') }}</span>
                                                        @elseif($treatmentPlan->status === 'accepted')
                                                            <span class="badge bg-label-success">{{ __('master.accepted') }}</span>
                                                        @elseif($treatmentPlan->status === 'rejected')
                                                            <span class="badge bg-label-danger">{{ __('master.rejected') }}</span>
                                                        @endif
                                                    </div>

                                                    <!-- Status Info -->
                                                    @if($treatmentPlan->accepted_at)
                                                        <small class="text-success">
                                                            <i class="icon-base ti tabler-check me-1"></i>{{ __('master.accepted_at') }}: {{ $treatmentPlan->accepted_at->format('d-m-Y H:i') }}
                                                        </small>
                                                    @endif
                                                    @if($treatmentPlan->treatment_plan_uploaded_at)
                                                        <small class="text-info">
                                                            <i class="icon-base ti tabler-upload me-1"></i>{{ __('master.completed_at') }}: {{ $treatmentPlan->treatment_plan_uploaded_at->format('d-m-Y H:i') }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="ti ti-file-text text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">{{ __('master.no_treatment_plans_available') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- FINITION -->
                <div class="col-12 order-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-1">{{ __('master.finition') }}</h5>
                            <small class="text-muted">{{ __('master.finition_files_description') }}</small>
                        </div>
                        <div class="card-body">
                            @include('partials.finition_content', ['case' => $case])
                        </div>
                    </div>
                </div>

                <!-- Treatment Plan -->
                <div class="col-12 order-4">
                    <div class="card h-100">
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
                                    <td>{{ ucfirst($case->doctor_instructions) }}</td>
                                    <td>{{ ucfirst($case->treatment_treat) }}</td>
                                    <td>{{ ucfirst($case->treatment_type) }}</td>
                                    <td>{{ ucfirst($case->treatment_overjet) }}</td>
                                    <td>{{ ucfirst($case->treatment_overbite) }}</td>
                                    <td>{{ ucfirst($case->treatment_midline) }}</td>
                                    <td>{{ ucfirst($case->treatment_irp) }}</td>
                                    <td>{{ ucfirst($case->treatment_attachements) }}</td>
                                    <td>{{ ucfirst($case->patient_chief_complaint) }}</td>
                                    
                                </tr>
                                </tbody>
                            </table>
                            </div>
                            

                        </div>
                    </div>
                </div>
                <div class="col-12 order-2">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title">{{ __('master.case_comments') }}</h5>
                        </div>
                        <div class="card-body" id="comments_container">
                            
                            <form id="add_comment_form">
                                
                                <input type="hidden" name="case_id" value="{{ $case->id }}">
                                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3 form-group">
                                            <label for="comment" class="form-label">{{ __('master.add_comment') }}</label>
                                            <textarea name="comment" id="comment" rows="3" class="form-control" placeholder="{{ __('master.add_comment') }}"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <button type="button" id="add_comment" class="btn btn-primary waves-effect waves-light">{{ __('master.add_comment') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                                        <div id="comments" class="mt-3">
            @foreach($comments as $comment)
            <div class="comment mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-1">
                        <img src="{{ $comment->user->photo_url }}" alt="User Photo" class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    </div>
                    <div class="col-md-11"> 
                        <p class="mb-1">
                            <span class="badge bg-label-primary">{{ $comment->user->name }} - {{ ucfirst($comment->user->role->name) }}</span> 
                            <small class="text-body-secondary">{{ __('master.date') }}: {{ $comment->created_at->format('d-m-Y H:i:s') }}</small>
                        </p>
                        <p class="mb-0"><strong>{{ __('master.comment') }}:</strong> {{ $comment->comment }}</p> 
                    </div>
                </div>
            </div>
            @endforeach
            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="col-12 order-3">
                    <div class="card h-100">
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
                                <!-- STL Scans Section -->
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
                                                                        : 'Unknown'
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
                                                                        : 'Unknown'
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
                                                                    <i class="icon-base ti tabler-x-ray me-1"></i>{{ $file->name }}
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
                                                                        : 'Unknown'
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
                                                                        : 'Unknown'
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
                </div>
                </div>{{-- /.row main column --}}
            </div>{{-- /.col-lg-8 main column --}}
            </div>
        </div>
        @include('laboratory.cases.treatment_request.modal')
    @push('scripts')
    <script src="{{ asset('assets/js/dataTables-all-laboratory.js') }}"></script>
   <script src="{{ asset('assets/lightbox/js/lightbox.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(document).on('change', '#type_file', function() {
            if ($(this).val() == 'link') {
                $('#file_div').hide();
                $('#link_div').show();
            } else if ($(this).val() == 'pdf') {
                $('#file_div').show();  
                $('#link_div').hide();
            }
            else{
                $('#file_div').hide();
                $('#link_div').hide();
            }
        });
        

        function share_link(id){
            console.log(id);
            $('#share-link('+id+')').on('click', function(e) {
                console.log('clicked');
                e.preventDefault();
                var link = $(this).data('link');
                $('#share-link').val(link);
            });
        }


        $(document).ready(function() {
            $('.select2').select2();
            $(document).on('click', '.share-link', function(e) {
            e.preventDefault();
            var link = $(this).data('link');
            $('#share-link').val(link);
        });
            $('#copy-link').click(function(e) {
                e.preventDefault();
                var link = $('#share-link').val();
                navigator.clipboard.writeText(link);
                toastr.success("{{ __('master.treatment_type_share_link_copied_successfully') }}");
            });

            var token = "{{ csrf_token() }}";
            $('#add_comment').click(function(e) {
                e.preventDefault();
                
                // Validate comment input
                var commentText = $('#comment').val().trim();
                if (!commentText) {
                    toastr.error("{{ __('master.please_enter_comment') }}");
                    return;
                }
                
                // Disable button during submission
                var $btn = $(this);
                $btn.prop('disabled', true).text("{{ __('master.adding_comment') }}...");
                
                $.ajax({
                    url: "{{ route('laboratory.cases.add_comment') }}",
                    type: "POST",
                    data: {
                        _token: token,
                        comment: commentText,
                        case_id: {{ $case->id }},
                        user_id: {{ auth()->user()->id }}
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#comment').val('');
                            $('#comments').prepend(
                                '<div class="comment mb-3 p-3 border rounded">' +
                                    '<div class="row">' +
                                        '<div class="col-md-1">' +
                                            '<img src="' + response.user_photo + '" alt="User Photo" class="img-fluid rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">' +
                                        '</div>' +
                                        '<div class="col-md-11">' +
                                            '<p class="mb-1">' +
                                                '<span class="badge bg-label-primary">' + response.user + ' - ' + (response.user_role ? response.user_role.charAt(0).toUpperCase() + response.user_role.slice(1) : 'Laboratory') + '</span> ' +
                                                '<small class="text-body-secondary">{{ __("master.date") }}: ' + response.date + '</small>' +
                                            '</p>' +
                                            '<p class="mb-0"><strong>{{ __("master.comment") }}:</strong> ' + response.comment + '</p>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>'
                            );
                            toastr.success("{{ __('master.comment_added_successfully') }}");
                        } else {
                            toastr.error("{{ __('master.error_adding_comment') }}");
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = "{{ __('master.error_adding_comment') }}";
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = [];
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errors.push(value[0]);
                            });
                            errorMessage = errors.join(', ');
                        }
                        
                        toastr.error(errorMessage);
                    },
                    complete: function() {
                        // Re-enable button
                        $btn.prop('disabled', false).text("{{ __('master.add_comment') }}");
                    }
                });
            });

            
        });
                  
    </script>
    @endpush
</x-app-layout>


