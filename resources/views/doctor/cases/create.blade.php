<x-app-layout>
    @push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
@endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
            <div class="col-md-12 col-xxl-12 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">{{ __('master.create_case') }}</h5>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <!-- Validation Wizard -->
                        <div class="row">
                            <div class="col-12 mb-6">
                                <!-- Create form wizard -->
                                <div class="bs-stepper wizard-numbered mt-2">
                                    <div class="bs-stepper-header">
                                        <div class="step" data-target="#case-details">
                                            <button type="button" class="step-trigger">
                                                <span class="bs-stepper-circle">1</span>
                                                <span class="bs-stepper-label mt-1">
                                                    <span class="bs-stepper-title">{{ __('master.case_details') }}</span>
                                                    <span class="bs-stepper-subtitle">{{ __('master.basic_info') }}</span>
                                                </span>
                                            </button>
                                        </div>
                                        <div class="line">
                                            <i class="icon-base ti tabler-chevron-right icon-md"></i>
                                        </div>
                                        <div class="step" data-target="#patient-info">
                                            <button type="button" class="step-trigger">
                                                <span class="bs-stepper-circle">2</span>
                                                <span class="bs-stepper-label mt-1">
                                                    <span class="bs-stepper-title">{{ __('master.patient_info') }}</span>
                                                    <span class="bs-stepper-subtitle">{{ __('master.patient_details') }}</span>
                                                </span>
                                            </button>
                                        </div>
                                        <div class="line">
                                            <i class="icon-base ti tabler-chevron-right icon-md"></i>
                                        </div>
                                        <div class="step" data-target="#treatment-details">
                                            <button type="button" class="step-trigger">
                                                <span class="bs-stepper-circle">3</span>
                                                <span class="bs-stepper-label mt-1">
                                                    <span class="bs-stepper-title">{{ __('master.treatment_details') }}</span>
                                                    <span class="bs-stepper-subtitle">{{ __('master.treatment_info') }}</span>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="bs-stepper-content">
                                        <form action="{{ route('doctor.cases.store') }}" method="post">
                                            <input type="hidden" name="technician_id" value="">
                                            <input type="hidden" name="laboratory_id" value="">
                                            @csrf
                                            
                                            <!-- Case Details -->
                                            <div id="case-details" class="content">
                                                <div class="content-header mb-4">
                                                    <h6 class="mb-0">{{ __('master.case_details') }}</h6>
                                                    <small>{{ __('master.enter_case_details') }}</small>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label" for="case_id">{{ __('master.case_id') }}</label>
                                                        <input type="text" id="case_id" name="case_id" class="form-control" value="{{ $generatedCaseId ?? ('AR-' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT)) }}" readonly />
                                                    </div>
                                                </div>
                                                <div class="col-12 d-flex justify-content-between mt-4">
                                                    <a class="btn btn-label-secondary btn-prev" disabled>
                                                        <i class="icon-base ti tabler-chevron-left icon-md"></i>
                                                        <span class="align-middle d-sm-inline-block d-none">{{ __('master.previous') }}</span>
                                                    </a>
                                                    <a class="btn btn-primary btn-next">
                                                        <span class="align-middle d-sm-inline-block d-none me-sm-2 text-white">{{ __('master.next') }}</span>
                                                        <i class="icon-base ti tabler-chevron-right icon-md text-white"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Patient Info -->
                                            <div id="patient-info" class="bs-stepper-pane">
                                                <div class="content-header mb-3">
                                                    <h6 class="mb-0">{{ __('master.patient_info') }}</h6>
                                                    <small>{{ __('master.enter_patient_details') }}</small>
                                                </div>

                                                <!-- Patient Selection Type -->
                                                <div class="row mb-4">
                                                    <div class="col-md mb-md-0 mb-5">
                                                        <div class="form-check custom-option custom-option-icon">
                                                            <label class="form-check-label custom-option-content" for="existing_patient">
                                                              <span class="custom-option-body">
                                                                <i class="icon-base ti tabler-user"></i>
                                                                <span class="custom-option-title">{{ __('master.existing_patient') }}</span>
                                                              </span>
                                                              <input name="patient_type" class="form-check-input" type="radio" value="existing" id="existing_patient" checked="">
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md mb-md-0 mb-5">
                                                        <div class="form-check custom-option custom-option-icon">
                                                            <label class="form-check-label custom-option-content" for="new_patient">
                                                              <span class="custom-option-body">
                                                                <i class="icon-base ti tabler-users-plus"></i>
                                                                <span class="custom-option-title">{{ __('master.new_patient') }}</span>
                                                              </span>
                                                              <input name="patient_type" class="form-check-input" type="radio" value="new" id="new_patient">
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Existing Patient Selection -->
                                                <div id="existing_patient_section" class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label" for="patient_id">{{ __('master.select_patient') }} <span class="text-danger">*</span></label>
                                                        <select class="form-select select2" id="patient_id" name="patient_id" data-placeholder="{{ __('master.select_patient') }}" required>
                                                            <option value="">{{ __('master.select_patient') }}</option>
                                                            @foreach($patients as $patient)
                                                                <option value="{{ $patient->id }}">{{ $patient->name }} {{ $patient->surname }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- New Patient Form -->
                                                <div id="new_patient_section" class="row g-3" style="display: none;">
                                                    <div class="row">
                                                        <div class="col-md-6 mt-4">
                                                            <label class="form-label" for="reference">{{ __('master.reference') }} <span class="text-danger">*</span>:</label>
                                                            <input type="text" class="form-control" id="reference" name="reference" value="{{ $generatedReference ?? ('PT-' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT)) }}" readonly required>
                                                        </div>
                                                        <div class="col-md-6 mt-4">
                                                            <label class="form-label" for="name">{{ __('master.name') }} <span class="text-danger">*</span>:</label>
                                                            <input type="text" class="form-control" id="name" name="name" required>
                                                        </div>
                                                        <div class="col-md-6 mt-4">
                                                            <label class="form-label" for="surname">{{ __('master.surname') }} <span class="text-danger">*</span>:</label>
                                                            <input type="text" class="form-control" id="surname" name="surname" required>
                                                        </div>
                                                        <div class="col-md-6 mt-10">
                                                            <label class="form-label">{{ __('master.gender') }} *:</label>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" checked >
                                                                <label class="form-check-label" for="gender_male">{{ __('master.male') }}</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female">
                                                                <label class="form-check-label" for="gender_female">{{ __('master.female') }}</label>
                                                            </div>
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="radio" name="gender" id="gender_other" value="other">
                                                                <label class="form-check-label" for="gender_other">{{ __('master.other') }}</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 mt-4">
                                                            <label class="form-label" for="email">{{ __('master.email') }} {{ __('master.optional') }}:</label>
                                                            <input type="email" class="form-control" id="email" name="email" >
                                                        </div>
                                                        <div class="col-md-6 mt-4">
                                                            <label class="form-label" for="state">{{ __('master.state') }} {{ __('master.optional') }}</label>
                                                            <input type="text" class="form-control" id="state" name="state" >
                                                        </div>
                                                    </div>  
                                                </div>

                                                <!-- Navigation Buttons -->
                                                <div class="d-flex justify-content-between mt-4">
                                                    <a type="button" class="btn btn-label-secondary btn-prev">
                                                        <i class="icon-base ti tabler-chevron-left icon-md"></i>
                                                        <span class="align-middle d-sm-inline-block d-none">{{ __('master.previous') }}</span>
                                                    </a>
                                                    <a type="button" class="btn btn-primary btn-next">
                                                        <span class="align-middle d-sm-inline-block d-none me-sm-1 text-white">{{ __('master.next') }}</span>
                                                        <i class="icon-base ti tabler-chevron-right icon-md text-white"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Treatment Details -->
                                            <div id="treatment-details" class="bs-stepper-pane">
                                                <div class="content-header mb-3">
                                                    <h6 class="mb-0">{{ __('master.treatment_details') }}</h6>
                                                    <small>{{ __('master.enter_treatment_info') }}</small>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <h5 class="card-title mb-3">{{ __('master.tooth_selection') }}</h5>
                                                        
                                                        <!-- Upper Teeth -->
                                                        <div class="row mb-4">
                                                            <div class="col-12">
                                                                <h6 class="mb-3">{{ __('master.upper_teeth') }}</h6>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    @foreach([18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28] as $tooth)
                                                                    <div class="tooth-checkbox">
                                                                        <input type="checkbox" class="btn-check" id="tooth_{{ $tooth }}" name="tooth_numbers[]" value="{{ $tooth }}">
                                                                        <label class="btn btn-outline-primary" for="tooth_{{ $tooth }}">{{ $tooth }}</label>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                            
                                                        <!-- Lower Teeth -->
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <h6 class="mb-3">{{ __('master.lower_teeth') }}</h6>
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    @foreach([48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38] as $tooth)
                                                                    <div class="tooth-checkbox">
                                                                        <input type="checkbox" class="btn-check" id="tooth_{{ $tooth }}" name="tooth_numbers[]" value="{{ $tooth }}">
                                                                        <label class="btn btn-outline-primary" for="tooth_{{ $tooth }}">{{ $tooth }}</label>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                            
                                                        <!-- Selected Teeth Problems -->
                                                        <div class="row mt-4">
                                                            <div class="col-12 card">
                                                                <div class="card-body" id="selected_teeth_problems"></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label" for="doctor_instruction">{{ __('master.doctor_instruction') }}</label>
                                                        <textarea class="form-control" id="doctor_instruction" name="doctor_instruction" rows="3"></textarea>
                                                    </div>

                                                    <h5 class="mb-3 fw-bold">{{ __('master.treatment_request') }}</h5>
                                                    
                                                    <!-- Treatment Type -->
                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_type') }}</h6>
                                                        <div class="row mb-3">
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_type_3_3_social_smile">
                                                                    <input name="treatment_type" class="form-check-input" type="radio" value="3-3 social smile" id="treatment_type_3_3_social_smile" required>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.3_3_social_smile') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_type_5_5_pre_molar_to_pre_molar">
                                                                    <input name="treatment_type" class="form-check-input" type="radio" value="5-5 pre molar to pre molar" id="treatment_type_5_5_pre_molar_to_pre_molar">
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.5_5_pre_molar_to_pre_molar') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic checked">
                                                                  <label class="form-check-label custom-option-content" for="treatment_type_7_7_full_treatment">
                                                                    <input name="treatment_type" class="form-check-input" type="radio" value="7-7 full treatment" id="treatment_type_7_7_full_treatment" checked>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.7_7_full_treatment') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_type_as_recommended">
                                                                    <input name="treatment_type" class="form-check-input" type="radio" value="As recommended" id="treatment_type_as_recommended">
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.as_recommended') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Treatment Overjet -->
                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_overjet') }}</h6>
                                                        <div class="row mb-3">
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_overjet_maintain">
                                                                    <input name="treatment_overjet" class="form-check-input" type="radio" value="Maintain" id="treatment_overjet_maintain" required>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.maintain') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic checked">
                                                                  <label class="form-check-label custom-option-content" for="treatment_overjet_improve">
                                                                    <input name="treatment_overjet" class="form-check-input" type="radio" value="Improve" id="treatment_overjet_improve" checked>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.improve') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Treatment Overbite -->
                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_overbite') }}</h6>
                                                        <div class="row mb-3">
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_overbite_maintain">
                                                                    <input name="treatment_overbite" class="form-check-input" type="radio" value="Maintain" id="treatment_overbite_maintain" required>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.maintain') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic checked">
                                                                  <label class="form-check-label custom-option-content" for="treatment_overbite_improve">
                                                                    <input name="treatment_overbite" class="form-check-input" type="radio" value="Improve" id="treatment_overbite_improve" checked>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.improve') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Treatment Midline -->
                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_midline') }}</h6>
                                                        <div class="row mb-3">
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_midline_maintain">
                                                                    <input name="treatment_midline" class="form-check-input" type="radio" value="Maintain" id="treatment_midline_maintain" required>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.maintain') }}</span>  
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic checked">
                                                                  <label class="form-check-label custom-option-content" for="treatment_midline_improve">
                                                                    <input name="treatment_midline" class="form-check-input" type="radio" value="Improve" id="treatment_midline_improve" checked>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.improve') }}</span>   
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Treatment IPR -->
                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_IPR') }}</h6>  
                                                        <div class="row mb-3">
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_IPR_yes">
                                                                    <input name="treatment_irp" class="form-check-input" type="radio" value="Yes" id="treatment_IPR_yes" required>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.yes') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_IPR_no">
                                                                    <input name="treatment_irp" class="form-check-input" type="radio" value="No" id="treatment_IPR_no">
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.no') }}</span>
                                                                    </span> 
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic checked">
                                                                  <label class="form-check-label custom-option-content" for="treatment_IPR_as_recommended">
                                                                    <input name="treatment_irp" class="form-check-input" type="radio" value="As recommended" id="treatment_IPR_as_recommended" checked>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.as_recommended') }}</span>
                                                                    </span> 
                                                                  </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Treatment Attachments -->
                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_attachments') }}</h6>  
                                                        <div class="row mb-3">
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_attachments_yes">
                                                                    <input name="treatment_attachments" class="form-check-input" type="radio" value="Yes" id="treatment_attachments_yes" required>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.yes') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="treatment_attachments_no">
                                                                    <input name="treatment_attachments" class="form-check-input" type="radio" value="No" id="treatment_attachments_no">
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.no') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic checked">
                                                                  <label class="form-check-label custom-option-content" for="treatment_attachments_as_recommended">
                                                                    <input name="treatment_attachments" class="form-check-input" type="radio" value="As recommended" id="treatment_attachments_as_recommended" checked>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.as_recommended') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 mb-3">
                                                        <label class="form-label" for="patient_chief_complaint">{{ __('master.patient_chief_complaint') }}</label>
                                                        <textarea class="form-control" id="patient_chief_complaint" name="patient_chief_complaint" rows="3"></textarea>
                                                    </div>  

                                                    <!-- Type of Scan -->
                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.impression_type') }} <span class="text-danger">*</span></h6>  
                                                        <div class="row mb-3">
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic checked">
                                                                  <label class="form-check-label custom-option-content" for="impression_intraoral">
                                                                    <input name="type_of_scan" class="form-check-input" type="radio" value="intraoral" id="impression_intraoral" checked>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.intraoral_scan') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="impression_desktop">
                                                                    <input name="type_of_scan" class="form-check-input" type="radio" value="desktop" id="impression_desktop">
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.desktop_scan') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic">
                                                                  <label class="form-check-label custom-option-content" for="impression_silicone">
                                                                    <input name="type_of_scan" class="form-check-input" type="radio" value="silicone" id="impression_silicone">
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.silicone_impression') }}</span>
                                                                    </span>
                                                                  </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between mt-4">
                                                    <a type="button" class="btn btn-label-secondary btn-prev">
                                                        <i class="icon-base ti tabler-chevron-left icon-md"></i>
                                                        <span class="align-middle d-sm-inline-block d-none">{{ __('master.previous') }}</span>
                                                    </a>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="icon-base ti tabler-check icon-md"></i>
                                                        <span class="align-middle d-sm-inline-block d-none">{{ __('master.create_case') }}</span>
                                                    </button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Validation Wizard -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tooth Problem Modal -->
    <div class="modal fade" id="toothProblemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('master.select_tooth_problem') }} - <span id="selected_tooth_number"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('master.tooth_problem') }}</label>
                        <select class="form-select" id="tooth_problem_select">
                            <option value="">{{ __('master.select_problem') }}</option>
                            @foreach($toothProblems as $problem)
                                <option value="{{ $problem->id }}">{{ $problem->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('master.notes') }}</label>
                        <textarea class="form-control" id="tooth_problem_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('master.close') }}</button>
                    <button type="button" class="btn btn-primary" id="save_tooth_problem">{{ __('master.save') }}</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<!-- Vendors JS -->
<script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js') }}"></script>
<script src="{{ asset('assets/js/forms-pickers.js') }}"></script>
<script src="{{ asset('assets/js/dataTables-all.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/js/form-wizard-numbered.js') }}"></script>

<!-- Custom JS -->
<script>
    // Patient type handling
    function handlePatientTypeChange() {
        const patientTypeRadios = document.querySelectorAll('input[name="patient_type"]');
        const patientSelect = document.getElementById('patient_id');
        const existingSection = document.getElementById('existing_patient_section');
        const newSection = document.getElementById('new_patient_section');
        
        patientTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'existing') {   
                    patientSelect.disabled = false;
                    existingSection.style.display = 'block';
                    newSection.style.display = 'none';
                    newSection.querySelectorAll('input[required]').forEach(input => {
                        input.removeAttribute('required');
                        if (!input.readOnly && input.type !== 'radio') {
                            input.value = '';
                        }
                    });
                    newSection.querySelectorAll('input:not([required]):not([readonly]):not([type="radio"]), textarea, select').forEach(input => {
                        input.value = '';
                    });
                } else {  
                    patientSelect.disabled = true;
                    existingSection.style.display = 'none';
                    newSection.style.display = 'block';
                    newSection.querySelectorAll('input[required]').forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                    const genderRadios = newSection.querySelectorAll('input[name="gender"]');
                    const checkedGender = newSection.querySelector('input[name="gender"]:checked');
                    if (!checkedGender && genderRadios.length > 0) {
                        genderRadios[0].checked = true;
                    }
                }
            });
        });
        
        const checkedRadio = document.querySelector('input[name="patient_type"]:checked');
        if (checkedRadio) {
            checkedRadio.dispatchEvent(new Event('change'));
        }
        
        const genderRadios = document.querySelectorAll('input[name="gender"]');
        const checkedGender = document.querySelector('input[name="gender"]:checked');
        if (!checkedGender && genderRadios.length > 0) {
            genderRadios[0].checked = true;
        }
    }
    
    document.addEventListener('DOMContentLoaded', handlePatientTypeChange);

    // Tooth selection functionality
    document.addEventListener('DOMContentLoaded', function () {
        let selectedTeethProblems = {};
        const toothProblemModal = new bootstrap.Modal(document.getElementById('toothProblemModal'));
        
        document.querySelectorAll('input[name="tooth_numbers[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    const toothNumber = this.value;
                    document.getElementById('selected_tooth_number').textContent = toothNumber;
                    document.getElementById('tooth_problem_select').value = '';
                    document.getElementById('tooth_problem_notes').value = '';
                    toothProblemModal.show();
                    window.currentToothCheckbox = this;
                } else {
                    const toothNumber = this.value;
                    delete selectedTeethProblems[toothNumber];
                    updateSelectedProblemsDisplay();
                }
            });
        });
        
        document.getElementById('save_tooth_problem').addEventListener('click', function() {
            const toothNumber = window.currentToothCheckbox.value;
            const problemId = document.getElementById('tooth_problem_select').value;
            const notes = document.getElementById('tooth_problem_notes').value;
            const problemText = document.getElementById('tooth_problem_select').options[document.getElementById('tooth_problem_select').selectedIndex].text;
            
            if (problemId) {
                selectedTeethProblems[toothNumber] = {
                    problem_id: problemId,
                    problem_text: problemText,
                    notes: notes
                };
                updateSelectedProblemsDisplay();
                toothProblemModal.hide();
            } else {
                window.currentToothCheckbox.checked = false;
            }
        });
        
        document.getElementById('toothProblemModal').addEventListener('hidden.bs.modal', function () {
            if (!document.getElementById('tooth_problem_select').value) {
                window.currentToothCheckbox.checked = false;
            }
        });
        
        function updateSelectedProblemsDisplay() {
            const container = document.getElementById('selected_teeth_problems');
            container.innerHTML = '';
            let hiddenInputs = '';
            
            Object.entries(selectedTeethProblems).forEach(([tooth, data]) => {
                container.innerHTML += `
                    <div class="tooth-problem-item">
                        <strong>Tooth ${tooth}:</strong> ${data.problem_text}
                        ${data.notes ? `<br><small>${data.notes}</small>` : ''}
                    </div>
                `;
                hiddenInputs += `
                 <input type="hidden" name="tooth_numbers[${tooth}][tooth_number]" value="${tooth}">
                    <input type="hidden" name="tooth_problems[${tooth}][problem_id]" value="${data.problem_id}">
                    <input type="hidden" name="tooth_notes[${tooth}][notes]" value="${data.notes}">
                `;
            });
            container.innerHTML += hiddenInputs;
        }
    });
</script>
@endpush

</x-app-layout>
