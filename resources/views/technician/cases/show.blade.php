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
                                        @if($case->status == 'in_production') 
                                            <span class="badge bg-label-success">{{ __('master.in_production') }}</span> 
                                        @elseif($case->status == 'rejected')
                                            <span class="badge bg-label-danger">{{ __('master.rejected') }}</span>
                                        @else
                                            <span class="badge bg-label-primary">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</span>
                                        @endif
                                    </span>
                                </div>
                                @if($case->price_rejected_at)
                                <div class="mb-3">  
                                    <b>{{ __('master.price_status') }} :</b> 
                                    <span class="badge bg-label-danger">
                                        <i class="icon-base ti tabler-x me-1"></i>{{ __('master.price_rejected') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ __('master.rejected_on') }}: {{ $case->price_rejected_at->format('d-m-Y H:i') }}</small>
                                </div>
                                @elseif($case->price_accepted_at)
                                <div class="mb-3">  
                                    <b>{{ __('master.price_status') }} :</b> 
                                    <span class="badge bg-label-success">
                                        <i class="icon-base ti tabler-check me-1"></i>{{ __('master.price_accepted') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ __('master.accepted_on') }}: {{ $case->price_accepted_at->format('d-m-Y H:i') }}</small>
                                </div>
                                @endif
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
                                    @if(!empty($case->technician_comment))
                                    <div class="alert alert-info d-flex mt-2 mb-0" role="alert">
                                        <i class="fas fa-comment-dots me-2 mt-1"></i>
                                        <div>
                                            <div class="fw-bold small mb-1">{{ __('master.comment_from_admin') }}</div>
                                            <div class="small">{{ $case->technician_comment }}</div>
                                        </div>
                                    </div>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- WeTransfer Card for Laboratory Notification -->
       
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="icon-base ti tabler-link me-2"></i>{{ __('master.wetransfer_laboratory_notification') }}
                        </h5>
                        <span class="badge bg-label-info">{{ __('master.send_to_laboratory') }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form id="wetransfer_form">
                                    <input type="hidden" name="case_id" value="{{ $case->id }}">
                                    <div class="mb-3">
                                        <label for="wetransfer_link" class="form-label">
                                            <i class="icon-base ti tabler-external-link me-1"></i>{{ __('master.wetransfer_link') }}
                                        </label>
                                        <input type="url" 
                                               class="form-control" 
                                               id="wetransfer_link" 
                                               name="wetransfer_link" 
                                               placeholder="https://we.tl/..."
                                               required>
                                        <div class="form-text">
                                            <i class="icon-base ti tabler-info-circle me-1"></i>{{ __('master.wetransfer_link_help') }}
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="notification_message" class="form-label">
                                            <i class="icon-base ti tabler-message me-1"></i>{{ __('master.notification_message') }}
                                        </label>
                                        <textarea class="form-control" 
                                                  id="notification_message" 
                                                  name="notification_message" 
                                                  rows="3" 
                                                  placeholder="{{ __('master.notification_message_placeholder') }}">{{ __('master.default_wetransfer_message') }}</textarea>
                                        <div class="form-text">
                                            {{ __('master.message_will_be_sent_to_laboratory') }}
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" 
                                                id="send_wetransfer_notification" 
                                                class="btn btn-primary">
                                            <i class="icon-base ti tabler-send me-1"></i>{{ __('master.send_notification') }}
                                        </button>
                                        <button type="button" 
                                                id="preview_email" 
                                                class="btn btn-outline-info">
                                            <i class="icon-base ti tabler-eye me-1"></i>{{ __('master.preview_email') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="icon-base ti tabler-info-circle me-1"></i>{{ __('master.laboratory_details') }}
                                    </h6>
                                    @if($case->laboratory)
                                        <p class="mb-1"><strong>{{ __('master.name') }}:</strong> {{ $case->laboratory->name }}</p>
                                        <p class="mb-1"><strong>{{ __('master.email') }}:</strong> {{ $case->laboratory->email }}</p>
                                        <p class="mb-0"><strong>{{ __('master.case_id') }}:</strong> #{{ $case->case_id }}</p>
                                    @else
                                        <p class="mb-0 text-warning">{{ __('master.no_laboratory_assigned') }}</p>
                                    @endif
                                </div>
                                
                                <!-- Recent WeTransfer Links -->
                                <div class="mt-3">
                                    <h6>{{ __('master.recent_wetransfer_links') }}</h6>
                                    <div id="recent_wetransfer_links">
                                        <!-- Will be populated via AJAX -->
                                        <p class="text-muted small">{{ __('master.loading_recent_links') }}...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
                <!-- Treatment Plan Card -->
                <div class="col-12 order-1">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ __('master.treatment_plan') }}</h5>
                           
                            <div class="card-header-actions">
                                @php
                                    $hasAcceptedPlan = $treatmentTypes->where('status', 'accepted')->count() > 0;
                                    $hasPendingPlan = $treatmentTypes->where('status', 'pending')->count() > 0;
                                    $hasRejectedPlan = $treatmentTypes->where('status', 'rejected')->count() > 0;
                                    $canAddPlan = !$hasAcceptedPlan && (!$hasPendingPlan || $hasRejectedPlan);
                                @endphp
                                
                                @if($canAddPlan)
                                    <button class="btn btn-primary" id="add_treatment_type" data-bs-toggle="modal" data-bs-target="#add_treatment_type_modal">
                                        <i class="icon-base ti tabler-plus me-1"></i>{{ __('master.add_treatment_plan') }}
                                    </button>
                                @elseif($hasAcceptedPlan)
                                    <span class="badge bg-label-success">
                                        <i class="icon-base ti tabler-check me-1"></i>{{ __('master.treatment_plan_accepted') }}
                                    </span>
                                @elseif($hasPendingPlan)
                                    <span class="badge bg-label-warning">
                                        <i class="icon-base ti tabler-clock me-1"></i>{{ __('master.treatment_plan_pending') }}
                                    </span>
                                @endif
                            </div>
                          
                        </div>
                        <div class="card-body">
                            @if($treatmentTypes && $treatmentTypes->count() > 0)
                                @foreach($treatmentTypes as $treatmentPlan)
                                    <div class="treatment-plan-item mb-4 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="mb-2">{{ $treatmentPlan->irp_file ? basename($treatmentPlan->irp_file) : __('master.no_irp_file') }}</h6>
                                                <p class="text-muted mb-2">
                                                    <strong>{{ __('master.created_at') }}:</strong> 
                                                    {{ $treatmentPlan->created_at ? $treatmentPlan->created_at->format('d-m-Y H:i') : 'N/A' }}
                                                </p>
                                                @if($treatmentPlan->description)
                                                    <p class="mb-2"><strong>{{ __('master.description') }}:</strong> {{ $treatmentPlan->description }}</p>
                                                @endif
                                                
                                                <!-- Estimated Completion Date -->
                                                <div class="mb-2">
                                                    <strong>{{ __('master.estimated_completion') }}:</strong>
                                                    @if($treatmentPlan->estimated_completion_date)
                                                        <span class="badge bg-label-info">{{ $treatmentPlan->estimated_completion_date->format('d-m-Y H:i') }}</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">{{ __('master.not_set') }}</span>
                                                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="setEstimatedCompletion({{ $treatmentPlan->id }})">
                                                                <i class="icon-base ti tabler-clock me-1"></i>{{ __('master.set_estimate') }}
                                                            </button>
                                                       
                                                    @endif
                                                </div>

                                                
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-flex flex-column gap-2">
                                                    <!-- IRP File Download -->
                                                    @if($treatmentPlan->irp_file)
                                                        <a href="{{ ensure_https_url($treatmentPlan->irp_file) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                            <i class="icon-base ti tabler-file-type-pdf me-1"></i>{{ __('master.view_irp_file') }}
                                                        </a>
                                                    @endif
                                                    
                                                    <!-- 3D Viewer Link -->
                                                    @if($treatmentPlan->link_viewer)
                                                        <a href="{{ ensure_https_url($treatmentPlan->link_viewer) }}" target="_blank" class="btn btn-outline-success btn-sm">
                                                            <i class="icon-base ti tabler-link me-1"></i>{{ __('master.open_3d_viewer') }}
                                                        </a>
                                                    @endif
                                                        
                                                        <!-- Remove File Button (Only for technicians and admins, and only if not accepted/rejected) -->
                                                        @if(in_array(strtolower(auth()->user()->role->name ?? ''), ['technician', 'admin']) && !in_array($treatmentPlan->status, ['accepted', 'rejected']))
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    onclick="confirmRemoveTreatmentFile({{ $treatmentPlan->id }}, '{{ $treatmentPlan->irp_file ? basename($treatmentPlan->irp_file) : __('master.no_irp_file') }}')">
                                                                <i class="icon-base ti tabler-trash me-1"></i>{{ __('master.remove_file') }}
                                                            </button>
                                                        @elseif(in_array(strtolower(auth()->user()->role->name ?? ''), ['technician', 'admin']) && in_array($treatmentPlan->status, ['accepted', 'rejected']))
                                                            <button type="button" class="btn btn-outline-danger btn-sm" disabled 
                                                                    title="{{ $treatmentPlan->status === 'accepted' ? __('master.cannot_remove_accepted_treatment_plan') : __('master.cannot_remove_rejected_treatment_plan') }}">
                                                                <i class="icon-base ti tabler-trash me-1"></i>{{ __('master.remove_file') }}
                                                            </button>
                                                        @else
                                                            <!-- Debug: Show current role -->
                                                            <small class="text-muted">Debug: Role = "{{ auth()->user()->role->name ?? 'null' }}"</small>
                                                        @endif
                                                   

                                               

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
                                                            <span class="badge bg-label-primary"><i class="icon-base ti tabler-alert-triangle me-1"></i>{{ $treatmentPlan->rejection_reason }}</span>
                                                        @endif
                                                    </div>

                                                    <!-- Action Buttons -->
                                                    
                                                   @if($treatmentPlan->status === 'in_production')
                                                        <button class="btn btn-primary btn-sm" onclick="completeTreatmentType({{ $treatmentPlan->id }})">
                                                            <i class="icon-base ti tabler-check me-1"></i>{{ __('master.in_production') }}
                                                        </button>
                                                    @endif

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
                                    <i class="icon-base ti tabler-file-text text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">{{ __('master.no_treatment_plans_available') }}</p>
                                    <small class="text-muted">{{ __('master.treatment_plans_will_appear_here_when_created') }}</small>
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
                            {{-- Doctor's finition demand shown prominently to the technician --}}
                            @if($case->finition_requested_at)
                            <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                                <i class="icon-base ti tabler-flag-2 me-2 mt-1"></i>
                                <div>
                                    <div class="fw-bold">
                                        {{ __('master.finition_requested') }}
                                        <span class="fw-normal text-muted">— {{ $case->finition_requested_at->format('d-m-Y H:i') }}</span>
                                    </div>
                                    <p class="mb-0 mt-1">{{ $case->finition_request_note ?: __('master.finition_request_message') }}</p>
                                </div>
                            </div>
                            @endif

                            {{-- Upload form: technician uploads finition files + description --}}
                            <form action="{{ route('technician.cases.store_finition', $case->id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3 mb-4">
                                @csrf
                                <div class="mb-3">
                                    <label for="finition_description" class="form-label fw-bold">{{ __('master.description') }}</label>
                                    <textarea name="finition_description" id="finition_description" rows="3" class="form-control" placeholder="{{ __('master.finition_description_placeholder') }}">{{ $case->finition_description }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="finition_files" class="form-label fw-bold">{{ __('master.finition_files') }}</label>
                                    <input type="file" name="finition_files[]" id="finition_files" class="form-control" multiple
                                           accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.stl,.zip,.rar,.doc,.docx">
                                    <small class="text-muted">{{ __('master.max_size') }}: 50MB • {{ __('master.max_files') }}: 20</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="icon-base ti tabler-upload me-1"></i>{{ __('master.save_finition') }}
                                </button>
                            </form>

                            @include('partials.finition_content', ['case' => $case, 'showRequest' => false])
                        </div>
                    </div>
                </div>

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
                            <div class="comment">
                                <div class="row">
                                    <div class="col-md-1">
                                        <img src="{{ $comment->user->photo_url }}" alt="User Photo" class="img-fluid rounded-circle">
                                    </div>
                                    <div class="col-md-11"> 
                                        <p class="mb-0"><span class="badge bg-label-primary">{{ $comment->user->name }} - {{ ucfirst($comment->user->role->name) }}</span> <small class="text-body-secondary">{{ __('master.date') }} : {{ $comment->created_at->format('d-m-Y H:i:s') }}</small> </p>
                                        <p><strong>{{ __('master.comment') }} :</strong> {{ $comment->comment }}</p> 
                                       
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

       

        @include('technician.cases.treatment_request.modal')
    @push('styles')
    <style>
        /* WeTransfer Card Styles */
        .wetransfer-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .wetransfer-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        
        .wetransfer-card .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .wetransfer-card .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .wetransfer-card .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        /* Rotating animation for loader */
        @keyframes rotating {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .rotating {
            animation: rotating 1s linear infinite;
        }
        
        /* WeTransfer link history styles */
        .wetransfer-history {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .wetransfer-history::-webkit-scrollbar {
            width: 4px;
        }
        
        .wetransfer-history::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .wetransfer-history::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="{{ asset('assets/js/dataTables-all-technician.js') }}"></script>
   <script src="{{ asset('assets/lightbox/js/lightbox.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        // 3D viewer URL validation
        $(document).on('input', '#link_viewer', function() {
            const url = $(this).val();
            if (url && !url.startsWith('http://') && !url.startsWith('https://')) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // IRP file upload validation
        $(document).on('change', '#irp_file', function() {
            const file = this.files[0];
            if (file) {
                // Check if it's a PDF file
                if (file.type !== 'application/pdf') {
                    $(this).addClass('is-invalid');
                    toastr.error("{{ __('master.please_select_pdf_file') }}");
                } else {
                    $(this).removeClass('is-invalid');
                    toastr.success("{{ __('master.irp_file_selected_successfully') }}");
                }
            }
        });

        // Handle treatment plan form submission
        $(document).on('submit', '#add_treatment_type_form', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const formData = new FormData(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="icon-base ti tabler-loader me-1 rotating"></i>{{ __("master.submitting") }}...');
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Close modal
                    $('#add_treatment_type_modal').modal('hide');
                    
                    // Reset form
                    form[0].reset();
                    
                    // Show success message
                    toastr.success("{{ __('master.treatment_plan_added_successfully') }}");
                    
                    // Reload page to show new treatment plan
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    console.error('Error submitting treatment plan:', xhr.responseText);
                    var errorMessage = "{{ __('master.error_adding_treatment_plan') }}";
                    
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = [];
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors.push(value[0]);
                        });
                        errorMessage = errors.join(', ');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    toastr.error(errorMessage);
                },
                complete: function() {
                    // Reset button state
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
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
                    url: "{{ route('technician.cases.add_comment') }}",
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
                                            '<p class="mb-1"><small class="text-body-secondary">{{ __("master.date") }}: ' + response.date + '</small></p>' +
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
                        console.error('Error adding comment:', xhr.responseText);
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

            // Treatment Type Management Functions
            window.acceptTreatmentType = function(treatmentTypeId) {
                if (confirm("{{ __('master.confirm_accept_treatment_type') }}")) {
                    $.ajax({
                        url: '/technician/treatment_types/' + treatmentTypeId + '/accept',
                        type: "POST",
                        data: {
                            _token: token
                        },
                        success: function(response) {
                            toastr.success("{{ __('master.treatment_type_accepted_successfully') }}");
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || "{{ __('master.error_accepting_treatment_type') }}");
                        }
                    });
                }
            };

            window.rejectTreatmentType = function(treatmentTypeId) {
                if (confirm("{{ __('master.confirm_reject_treatment_type') }}")) {
                    $.ajax({
                        url: '/technician/treatment_types/reject/' + treatmentTypeId,
                        type: "GET",
                        success: function(response) {
                            toastr.success("{{ __('master.treatment_type_rejected_successfully') }}");
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || "{{ __('master.error_rejecting_treatment_type') }}");
                        }
                    });
                }
            };

            window.setEstimatedCompletion = function(treatmentTypeId) {
                // Set current treatment type ID in modal
                $('#currentTreatmentTypeId').val(treatmentTypeId);
                
                // Set default datetime to current date + 7 days
                var defaultDate = new Date();
                defaultDate.setDate(defaultDate.getDate() + 7);
                var formattedDate = defaultDate.toISOString().slice(0, 16);
                $('#estimatedCompletionDatetime').val(formattedDate);
                
                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('estimatedCompletionModal'));
                modal.show();
            };

            // Handle estimated completion form submission
            $('#saveEstimatedCompletion').on('click', function() {
                var treatmentTypeId = $('#currentTreatmentTypeId').val();
                var dateTime = $('#estimatedCompletionDatetime').val();
                
                if (!dateTime) {
                    toastr.error("{{ __('master.please_select_datetime') }}");
                    return;
                }
                
                // Validate that the date is in the future
                var selectedDate = new Date(dateTime);
                var currentDate = new Date();
                if (selectedDate <= currentDate) {
                    toastr.error("{{ __('master.estimated_completion_must_be_future_date') }}");
                    return;
                }
                
                $.ajax({
                    url: '/technician/treatment_types/' + treatmentTypeId + '/estimated-completion',
                    type: "PUT",
                    data: {
                        _token: token,
                        estimated_completion_date: dateTime
                    },
                    success: function(response) {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('estimatedCompletionModal'));
                        modal.hide();
                        toastr.success("{{ __('master.estimated_completion_updated_successfully') }}");
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "{{ __('master.error_updating_estimated_completion') }}");
                    }
                });
            });

            window.completeTreatmentType = function(treatmentTypeId) {
                var wetransferLink = prompt("{{ __('master.enter_wetransfer_link') }}");
                var completionNotes = prompt("{{ __('master.enter_completion_notes_optional') }}");
                
                if (wetransferLink !== null && wetransferLink !== '') {
                    // Basic URL validation
                    if (!wetransferLink.startsWith('http://') && !wetransferLink.startsWith('https://')) {
                        toastr.error("{{ __('master.please_enter_valid_url') }}");
                        return;
                    }
                    
                    $.ajax({
                        url: '/technician/treatment_types/' + treatmentTypeId + '/complete',
                        type: "POST",
                        data: {
                            _token: token,
                            wetransfer_link: wetransferLink,
                            completion_notes: completionNotes || ''
                        },
                        success: function(response) {
                            toastr.success("{{ __('master.treatment_type_completed_successfully') }}");
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || "{{ __('master.error_completing_treatment_type') }}");
                        }
                    });
                }
            };

            // WeTransfer Laboratory Notification Functions
            $('#send_wetransfer_notification').click(function(e) {
                e.preventDefault();
                
                var wetransferLink = $('#wetransfer_link').val().trim();
                var message = $('#notification_message').val().trim();
                
                // Validation
                if (!wetransferLink) {
                    toastr.error("{{ __('master.please_enter_wetransfer_link') }}");
                    return;
                }
                
                if (!message) {
                    toastr.error("{{ __('master.please_enter_notification_message') }}");
                    return;
                }
                
                // Basic URL validation
                if (!wetransferLink.startsWith('http://') && !wetransferLink.startsWith('https://')) {
                    toastr.error("{{ __('master.please_enter_valid_url') }}");
                    return;
                }
                
                // Check if laboratory is assigned
                @if(!$case->laboratory)
                    toastr.error("{{ __('master.no_laboratory_assigned_cannot_send') }}");
                    return;
                @endif
                
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="icon-base ti tabler-loader-2 me-1 rotating"></i>{{ __("master.sending") }}...');
                
                $.ajax({
                    url: '/technician/cases/send-wetransfer-notification',
                    type: 'POST',
                    data: {
                        _token: token,
                        case_id: {{ $case->id }},
                        wetransfer_link: wetransferLink,
                        message: message
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("{{ __('master.wetransfer_notification_sent_successfully') }}");
                            // Clear the form
                            $('#wetransfer_link').val('');
                            $('#notification_message').val("{{ __('master.default_wetransfer_message') }}");
                            // Reload recent links
                            loadRecentWeTransferLinks();
                            
                            // Show notification details if available
                            if (response.notification_id) {
                                console.log('WeTransfer notification saved with ID:', response.notification_id);
                            }
                        } else {
                            toastr.error(response.message || "{{ __('master.error_sending_notification') }}");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error sending WeTransfer notification:', xhr.responseText);
                        var errorMessage = "{{ __('master.error_sending_notification') }}";
                        
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
                        $btn.prop('disabled', false).html('<i class="icon-base ti tabler-send me-1"></i>{{ __("master.send_notification") }}');
                    }
                });
            });

            // Preview Email Function
            $('#preview_email').click(function(e) {
                e.preventDefault();
                
                var wetransferLink = $('#wetransfer_link').val().trim();
                var message = $('#notification_message').val().trim();
                
                if (!wetransferLink || !message) {
                    toastr.warning("{{ __('master.fill_fields_to_preview') }}");
                    return;
                }
                
                // Create preview modal content
                var previewContent = 
                    '<div class="modal fade" id="emailPreviewModal" tabindex="-1">' +
                        '<div class="modal-dialog modal-lg">' +
                            '<div class="modal-content">' +
                                '<div class="modal-header">' +
                                    '<h5 class="modal-title">{{ __("master.email_preview") }}</h5>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                                '</div>' +
                                '<div class="modal-body">' +
                                    '<div class="email-preview">' +
                                        '<p><strong>{{ __("master.to") }}:</strong> {{ $case->laboratory->email ?? "laboratory@example.com" }}</p>' +
                                        '<p><strong>{{ __("master.subject") }}:</strong> {{ __("master.wetransfer_notification_subject") }} #{{ $case->case_id }}</p>' +
                                        '<hr>' +
                                        '<div class="email-body">' +
                                            '<p>{{ __("master.dear") }} {{ $case->laboratory->name ?? "Laboratory" }},</p>' +
                                            '<p>' + message + '</p>' +
                                            '<p><strong>{{ __("master.wetransfer_link") }}:</strong> <a href="' + wetransferLink + '" target="_blank">' + wetransferLink + '</a></p>' +
                                            '<p><strong>{{ __("master.case_details") }}:</strong></p>' +
                                            '<ul>' +
                                                '<li>{{ __("master.case_id") }}: #{{ $case->case_id }}</li>' +
                                                '<li>{{ __("master.patient_name") }}: {{ $case->patient->name ?? "N/A" }}</li>' +
                                                '<li>{{ __("master.technician") }}: {{ auth()->user()->name }}</li>' +
                                                '<li>{{ __("master.date") }}: ' + new Date().toLocaleDateString() + '</li>' +
                                            '</ul>' +
                                            '<p>{{ __("master.best_regards") }},<br>{{ auth()->user()->name }}<br>{{ __("master.technician") }}</p>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("master.close") }}</button>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                
                // Remove existing modal and add new one
                $('#emailPreviewModal').remove();
                $('body').append(previewContent);
                
                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('emailPreviewModal'));
                modal.show();
            });

            // Load Recent WeTransfer Links
            function loadRecentWeTransferLinks() {
                $.ajax({
                    url: '/technician/cases/{{ $case->id }}/recent-wetransfer-links',
                    type: 'GET',
                    success: function(response) {
                        if (response.links && response.links.length > 0) {
                            var linksHtml = '';
                            $.each(response.links, function(index, link) {
                                linksHtml += 
                                    '<div class="small mb-2 p-2 border rounded">' +
                                        '<div class="d-flex justify-content-between">' +
                                            '<span class="text-muted">' + link.created_at + '</span>' +
                                            '<span class="badge bg-label-success">{{ __("master.sent") }}</span>' +
                                        '</div>' +
                                        '<a href="' + link.wetransfer_link + '" target="_blank" class="text-truncate d-block">' + link.wetransfer_link + '</a>' +
                                    '</div>';
                            });
                            $('#recent_wetransfer_links').html(linksHtml);
                        } else {
                            $('#recent_wetransfer_links').html('<p class="text-muted small">{{ __("master.no_recent_links") }}</p>');
                        }
                    },
                    error: function() {
                        $('#recent_wetransfer_links').html('<p class="text-muted small">{{ __("master.error_loading_links") }}</p>');
                    }
                });
            }

            // Load recent links on page load
            loadRecentWeTransferLinks();

            
        });
                  
    </script>
    @endpush

    <!-- Estimated Completion Modal -->
    <div class="modal fade" id="estimatedCompletionModal" tabindex="-1" aria-labelledby="estimatedCompletionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="estimatedCompletionModalLabel">
                        <i class="icon-base ti tabler-clock me-2"></i>{{ __('master.set_estimated_completion') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="estimatedCompletionDatetime" class="form-label">
                            <i class="icon-base ti tabler-calendar-event me-1"></i>{{ __('master.estimated_completion_datetime') }}
                        </label>
                        <input type="datetime-local" 
                               class="form-control" 
                               id="estimatedCompletionDatetime" 
                               required>
                        <input type="hidden" id="currentTreatmentTypeId">
                        <div class="form-text">
                            <i class="icon-base ti tabler-info-circle me-1"></i>{{ __('master.select_when_treatment_will_be_completed') }}
                        </div>
                    </div>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="icon-base ti tabler-bulb me-2"></i>
                        <div>
                            {{ __('master.estimated_completion_help_text') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="icon-base ti tabler-x me-1"></i>{{ __('master.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="saveEstimatedCompletion">
                        <i class="icon-base ti tabler-check me-1"></i>{{ __('master.save_estimate') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove Treatment File Confirmation Modal -->
    <div class="modal fade" id="removeTreatmentFileModal" tabindex="-1" aria-labelledby="removeTreatmentFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="removeTreatmentFileModalLabel">
                        <i class="icon-base ti tabler-trash me-2"></i>{{ __('master.remove_treatment_file') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="icon-base ti tabler-alert-triangle me-2"></i>
                        <div>
                            {{ __('master.remove_file_warning') }}
                        </div>
                    </div>
                    <p>{{ __('master.remove_file_confirm_text') }} <strong id="treatmentFileName"></strong>?</p>
                    <p class="text-muted small">{{ __('master.remove_file_action_irreversible') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="icon-base ti tabler-x me-1"></i>{{ __('master.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveTreatmentFile">
                        <i class="icon-base ti tabler-trash me-1"></i>{{ __('master.remove_file') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let treatmentIdToRemove = null;

        function confirmRemoveTreatmentFile(treatmentId, treatmentName) {
            treatmentIdToRemove = treatmentId;
            document.getElementById('treatmentFileName').textContent = treatmentName;
            
            const modal = new bootstrap.Modal(document.getElementById('removeTreatmentFileModal'));
            modal.show();
        }

        document.getElementById('confirmRemoveTreatmentFile').addEventListener('click', function() {
            if (treatmentIdToRemove) {
                removeTreatmentFile(treatmentIdToRemove);
            }
        });

        function removeTreatmentFile(treatmentId) {
            const removeBtn = document.getElementById('confirmRemoveTreatmentFile');
            const originalText = removeBtn.innerHTML;
            
            // Show loading state
            removeBtn.innerHTML = '<i class="icon-base ti tabler-loader me-1"></i>{{ __("master.removing") }}...';
            removeBtn.disabled = true;

            fetch(`{{ route('technician.treatment-types.remove-file', '') }}/${treatmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('removeTreatmentFileModal'));
                    modal.hide();
                    
                    // Show success message
                    if (window.Flasher) {
                        window.Flasher.success(data.message || '{{ __("master.file_removed_successfully") }}');
                    } else {
                        toastr.success('{{ __("master.file_removed_successfully") }}');
                    }
                    
                    // Reload page to reflect changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || '{{ __("master.unknown_error_occurred") }}');
                }
            })
            .catch(error => {
                console.error('Error removing file:', error);
                if (window.Flasher) {
                    window.Flasher.error('{{ __("master.failed_to_remove_file") }}: ' + error.message);
                } else {
                    toastr.error('{{ __("master.failed_to_remove_file") }}: ' + error.message);
                }
            })
            .finally(() => {
                // Reset button state
                removeBtn.innerHTML = originalText;
                removeBtn.disabled = false;
                treatmentIdToRemove = null;
            });
        }
    </script>

</x-app-layout>


