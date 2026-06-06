<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
      
    <style>
      .image-upload > input {
          display: none;
      }
    
      .image-upload img {
          width: 150px;
          cursor: pointer;
          transition: transform 0.3s ease;
          border-radius: 10px;
          border: 2px dashed var(--bs-primary);
      }
    
      .image-upload img:hover {
          transform: scale(1.05);
      }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        
        
    </div>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <div class="row g-6">
            
            <div class="col-md-12 col-xxl-12 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">{{ __('master.edit_case') }}</h5>
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
                                            <form  action="{{ route('doctor.cases.update', $case->id) }}" method="post" enctype="multipart/form-data">
                                             @csrf
                                             @method('PUT')
                                             <input type="hidden" name="technician_id" value="{{ $case->technician_id }}">
                                             <input type="hidden" name="laboratory_id" value="{{ $case->laboratory_id }}">
                                            <!-- Case Details -->
                                            <div id="case-details" class="content">
                                                <div class="content-header mb-4">
                                                    <h6 class="mb-0">{{ __('master.case_details') }}</h6>
                                                    <small>{{ __('master.enter_case_details') }}</small>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label" for="case_id">{{ __('master.case_id') }}</label>
                                                        <input type="text" id="case_id" name="case_id" class="form-control" value="{{ $case->case_id }}" readonly />
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

                                                <!-- Existing Patient Selection -->
                                                <div  class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label" for="patient_id">{{ __('master.select_patient') }}</label>
                                                        <select class="form-select select2" id="patient_id" name="patient_id" data-placeholder="{{ __('master.select_patient') }}">
                                                            <option value="">{{ __('master.select_patient') }}</option>
                                                            @foreach($patients as $patient)
                                                                <option value="{{ $patient->id }}" {{ $case->patient_id == $patient->id ? 'selected' : '' }}>{{ $patient->name }} {{ $patient->surname }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- New Patient Form -->
                                                
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
                                                                                <input type="checkbox" class="btn-check" id="tooth_{{ $tooth }}" name="tooth_numbers[]" value="{{ $tooth }}" {{ in_array($tooth, $toothProblemscase->pluck('tooth_number')->toArray()) ? 'checked' : '' }}>
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
                                                                                <input type="checkbox" class="btn-check" id="tooth_{{ $tooth }}" name="tooth_numbers[]" value="{{ $tooth }}" {{ in_array($tooth, $toothProblemscase->pluck('tooth_number')->toArray()) ? 'checked' : '' }}>
                                                                                <label class="btn btn-outline-primary" for="tooth_{{ $tooth }}">{{ $tooth }}</label>
                                                                            </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                    
                                                                <!-- Selected Teeth Problems -->
                                                                <div class="row mt-4">
                                                                    <div class="col-12 card">
                                                                        <div class="card-body" id="selected_teeth_problems">
                                                                            @foreach($toothProblemscase as $toothProblem)
                                                                                <div class="tooth-problem">
                                                                                    <span class="tooth-number"><strong>{{ __('master.tooth') }} {{ $toothProblem->tooth_number }}:</strong></span>
                                                                                    <span class="problem-description"> {{ $toothProblem->tooth_problem->name }} </br> <small>{{ $toothProblem->tooth_notes }}</small></span>
                                                                                </div>
                                                                            @endforeach
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


                                                    <div class="col-12">
                                                        <label class="form-label" for="doctor_instruction">{{ __('master.doctor_instruction') }}</label>
                                                        <textarea class="form-control" id="doctor_instruction" name="doctor_instruction" rows="3">{{ $case->doctor_instruction }}</textarea>
                                                      
                                                    </div>
                                                    <h5 class="mb-3 fw-bold">{{ __('master.treatment_request') }}</h5>
                                                    <div class="col-12">

                                                          <h6 class="mb-3 fw-bold">{{ __('master.treatment_treat') }}</h6>

                                                          <div class="row">
                                                            <div class="col-md mb-md-0 mb-5">
                                                              <div class="form-check custom-option custom-option-basic {{ $case->treatment_treat == 'both arches' ? 'checked' : '' }}">
                                                                <label class="form-check-label custom-option-content" for="treatment_treat_both_arches">
                                                                  <input name="treatment_treat" class="form-check-input" type="radio" value="both arches" id="treatment_treat_both_arches" {{ $case->treatment_treat == 'both arches' ? 'checked' : '' }}>
                                                                  <span class="custom-option-header">
                                                                    <span class="h6 mb-0">{{ __('master.both_arches') }}</span>
                                                                    
                                                                  </span>
                                                                  
                                                                </label>
                                                              </div>
                                                            </div>
                                                            <div class="col-md">
                                                              <div class="form-check custom-option custom-option-basic {{ $case->treatment_treat == 'upper_arch' ? 'checked' : '' }}">
                                                                <label class="form-check-label custom-option-content" for="treatment_treat_upper_arch">
                                                                  <input name="treatment_treat" class="form-check-input" type="radio" value="upper_arch" id="treatment_treat_upper_arch" {{ $case->treatment_treat == 'upper_arch' ? 'checked' : '' }}>
                                                                  <span class="custom-option-header">
                                                                    <span class="h6 mb-0">{{ __('master.upper_arch') }}</span>
                                                                  </span>
                                                                  
                                                                </label>
                                                              </div>
                                                            </div>

                                                            <div class="col-md">
                                                                <div class="form-check custom-option custom-option-basic {{ $case->treatment_treat == 'lower_arch' ? 'checked' : '' }}">
                                                                  <label class="form-check-label custom-option-content" for="treatment_treat_lower_arch">
                                                                    <input name="treatment_treat" class="form-check-input" type="radio" value="lower_arch" id="treatment_treat_lower_arch" {{ $case->treatment_treat == 'lower_arch' ? 'checked' : '' }}>
                                                                    <span class="custom-option-header">
                                                                      <span class="h6 mb-0">{{ __('master.lower_arch') }}</span>
                                                                    </span>
                                                                    
                                                                  </label>
                                                                </div>
                                                              </div>
                                                          </div>


                                                      
                                                    </div>


                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_type') }}</h6>
                                                        <div class="row mb-3">
                                                        <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_type == '3-3 social smile' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_type_3_3_social_smile">
                                                                <input name="treatment_type" class="form-check-input" type="radio" value="3-3 social smile" id="treatment_type_3_3_social_smile" {{ $case->treatment_type == '3-3 social smile' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.3_3_social_smile') }}</span>
                                                                </span>
                                                                
                                                              </label>
                                                            </div>
                                                          </div>
                                                      
                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_type == '5-5 pre molar to pre molar' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_type_5_5_pre_molar_to_pre_molar">
                                                                <input name="treatment_type" class="form-check-input" type="radio" value="5-5 pre molar to pre molar" id="treatment_type_5_5_pre_molar_to_pre_molar" {{ $case->treatment_type == '5-5 pre molar to pre molar' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.5_5_pre_molar_to_pre_molar') }}</span>
                                                                </span>
                                                                
                                                              </label>
                                                            </div>
                                                          </div>



                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_type == '7-7 full treatment' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_type_7_7_full_treatment">
                                                                <input name="treatment_type" class="form-check-input" type="radio" value="7-7 full treatment" id="treatment_type_7_7_full_treatment" {{ $case->treatment_type == '7-7 full treatment' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.7_7_full_treatment') }}</span>
                                                                </span>
                                                                
                                                              </label>
                                                            </div>
                                                          </div>


                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_type == 'As recommended' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_type_as_recommended">
                                                                <input name="treatment_type" class="form-check-input" type="radio" value="As recommended" id="treatment_type_as_recommended" {{ $case->treatment_type == 'As recommended' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.as_recommended') }}</span>
                                                                </span>
                                                                
                                                              </label>
                                                            </div>
                                                          </div>



                                                    </div>



                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_overjet') }}</h6>
                                                        <div class="row mb-3">
                                                        <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_overjet == 'Maintain' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_overjet_maintain">
                                                                <input name="treatment_overjet" class="form-check-input" type="radio" value="Maintain" id="treatment_overjet_maintain" {{ $case->treatment_overjet == 'Maintain' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.maintain') }}</span>
                                                                </span>
                                                                
                                                              </label>
                                                            </div>
                                                          </div>

                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_overjet == 'Improve' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_overjet_improve">
                                                                <input name="treatment_overjet" class="form-check-input" type="radio" value="Improve" id="treatment_overjet_improve" {{ $case->treatment_overjet == 'Improve' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.improve') }}</span>
                                                                </span>
                                                                
                                                              </label>
                                                            </div>
                                                          </div>
 
                                                          

                                                       
                                                    </div>



                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_overbite') }}</h6>
                                                        <div class="row mb-3">
                                                        <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_overbite == 'Maintain' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_overbite_maintain">
                                                                <input name="treatment_overbite" class="form-check-input" type="radio" value="Maintain" id="treatment_overbite_maintain" {{ $case->treatment_overbite == 'Maintain' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.maintain') }}</span>
                                                                </span>

                                                                
                                                              </label>
                                                            </div>
                                                          </div>
                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_overbite == 'Improve' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_overbite_improve">
                                                                <input name="treatment_overbite" class="form-check-input" type="radio" value="Improve" id="treatment_overbite_improve" {{ $case->treatment_overbite == 'Improve' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.improve') }}</span>
                                                                </span>

                                                                
                                                              </label>
                                                            </div>
                                                          </div>
                                                        </div>

                                                       
                                                    </div>



                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_midline') }}</h6>
                                                        <div class="row mb-3">
                                                        <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_midline == 'Maintain' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_midline_maintain">
                                                                <input name="treatment_midline" class="form-check-input" type="radio" value="Maintain" id="treatment_midline_maintain" {{ $case->treatment_midline == 'Maintain' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.maintain') }}</span>  
                                                                </span>

                                                                
                                                              </label>
                                                            </div>
                                                          </div>

                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_midline == 'Improve' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_midline_improve">
                                                                <input name="treatment_midline" class="form-check-input" type="radio" value="Improve" id="treatment_midline_improve" {{ $case->treatment_midline == 'Improve' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.improve') }}</span>   
                                                                </span>

                                                                
                                                              </label>
                                                            </div>
                                                          </div>
                                                        </div>

                                                       
                                                    </div>


                                                    <div class="col-12 mb-3">
                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_IPR') }}</h6>  
                                                        <div class="row mb-3">
                                                        <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_irp == 'Yes' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_IPR_yes">
                                                                <input name="treatment_irp" class="form-check-input" type="radio" value="Yes" id="treatment_IPR_yes" {{ $case->treatment_irp == 'Yes' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.yes') }}</span>
                                                                </span>

                                                                
                                                              </label>
                                                            </div>
                                                          </div>

                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_irp == 'No' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_IPR_no">
                                                                <input name="treatment_irp" class="form-check-input" type="radio" value="No" id="treatment_IPR_no" {{ $case->treatment_irp == 'No' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.no') }}</span>
                                                                </span> 

                                                              </label>
                                                            </div>
                                                          </div>


                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_irp == 'As recommended' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_IPR_as_recommended">
                                                                <input name="treatment_irp" class="form-check-input" type="radio" value="As recommended" id="treatment_IPR_as_recommended" {{ $case->treatment_irp == 'As recommended' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.as_recommended') }}</span>
                                                                </span> 

                                                              </label>
                                                            </div>
                                                          </div>
                                                        </div>


                                                        

                                                       
                                                    </div>


                                                    <div class="col-12 mb-3">

                                                        <h6 class="mb-3 fw-bold">{{ __('master.treatment_attachments') }}</h6>  
                                                        <div class="row mb-3">
                                                        <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_attachments == 'Yes' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_attachments_yes">
                                                                <input name="treatment_attachments" class="form-check-input" type="radio" value="Yes" id="treatment_attachments_yes" {{ $case->treatment_attachments == 'Yes' ? 'checked' : '' }}>
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.yes') }}</span>
                                                                </span>

                                                                
                                                              </label>
                                                            </div>
                                                          </div>
                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_attachments == 'No' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_attachments_no">
                                                                <input name="treatment_attachments" class="form-check-input" type="radio" value="No" id="treatment_attachments_no" {{ $case->treatment_attachments == 'No' ? 'checked' : '' }}  >
                                                                <span class="custom-option-header">
                                                                  <span class="h6 mb-0">{{ __('master.no') }}</span>
                                                                </span>

                                                                
                                                              </label>
                                                            </div>
                                                          </div>
                                                          <div class="col-md">
                                                            <div class="form-check custom-option custom-option-basic {{ $case->treatment_attachments == 'As recommended' ? 'checked' : '' }}">
                                                              <label class="form-check-label custom-option-content" for="treatment_attachments_as_recommended">
                                                                <input name="treatment_attachments" class="form-check-input" type="radio" value="As recommended" id="treatment_attachments_as_recommended" {{ $case->treatment_attachments == 'As recommended' ? 'checked' : '' }}>
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
                                                        <textarea class="form-control" id="patient_chief_complaint" name="patient_chief_complaint" rows="3">{{ $case->patient_chief_complaint }}</textarea>
                                                    </div>  

                                                   
                                                </div>
                                                <div class="d-flex justify-content-between mt-4">
                                                    <a type="button" class="btn btn-label-secondary btn-prev">
                                                        <i class="icon-base ti tabler-chevron-left icon-md"></i>
                                                        <span class="align-middle d-sm-inline-block d-none">{{ __('master.previous') }}</span>
                                                    </a>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="icon-base ti tabler-check icon-md"></i>
                                                        <span class="align-middle d-sm-inline-block d-none">{{ __('master.submit') }}</span>
                                                    </button>
                                                </div>
                                            </div>
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
   


    // Add this to your existing JavaScript
    $(document).ready(function() {
        $('#country').select2({
            placeholder: '{{ __("master.select_country") }}',
            allowClear: true
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Store selected tooth problems, pre-seeded with the case's existing problems
        let selectedTeethProblems = Object.assign({}, @json($existingToothProblems ?? (object)[]));
        
        // Initialize modal
        const toothProblemModal = new bootstrap.Modal(document.getElementById('toothProblemModal'));
        
        // Handle tooth checkbox click
        document.querySelectorAll('input[name="tooth_numbers[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    const toothNumber = this.value;
                    document.getElementById('selected_tooth_number').textContent = toothNumber;
                    
                    // Reset modal form
                    document.getElementById('tooth_problem_select').value = '';
                    document.getElementById('tooth_problem_notes').value = '';
                    
                    // Show modal
                    toothProblemModal.show();
                    
                    // Store the current checkbox for reference
                    window.currentToothCheckbox = this;
                } else {
                    // Remove tooth problem when unchecked
                    const toothNumber = this.value;
                    delete selectedTeethProblems[toothNumber];
                    updateSelectedProblemsDisplay();
                }
            });
        });
        
        // Handle save tooth problem
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
                // Uncheck the tooth if no problem is selected
                window.currentToothCheckbox.checked = false;
            }
        });
        
        // Handle modal close
        document.getElementById('toothProblemModal').addEventListener('hidden.bs.modal', function () {
            if (!document.getElementById('tooth_problem_select').value) {
                // Uncheck the tooth if modal is closed without selecting a problem
                window.currentToothCheckbox.checked = false;
            }
        });
        
        // Update the display of selected problems
        function updateSelectedProblemsDisplay() {
            const container = document.getElementById('selected_teeth_problems');
            container.innerHTML = '';
            
            // Create hidden inputs for form submission
            let hiddenInputs = '';
            
            Object.entries(selectedTeethProblems).forEach(([tooth, data]) => {
                // Display item
                container.innerHTML += `
                    <div class="tooth-problem-item">
                        <strong>Tooth ${tooth}:</strong> ${data.problem_text}
                        ${data.notes ? `<br><small>${data.notes}</small>` : ''}
                    </div>
                `;
                
                // Create hidden inputs
                hiddenInputs += `
                 <input type="hidden" name="tooth_numbers[${tooth}][tooth_number]" value="${tooth}">
                    <input type="hidden" name="tooth_problems[${tooth}][problem_id]" value="${data.problem_id}">
                    <input type="hidden" name="tooth_notes[${tooth}][notes]" value="${data.notes}">
                `;
            });
            
            // Add hidden inputs to the form
            container.innerHTML += hiddenInputs;
        }

        // Render the pre-existing problems (and their hidden inputs) on load
        updateSelectedProblemsDisplay();
    });

    document.addEventListener('DOMContentLoaded', function() {
        function toggleScanFields() {
            const type = document.querySelector('input[name="impression_type"]:checked').value;
            document.getElementById('scan-fields').style.display = (type === 'silicone') ? 'none' : 'block';
        }
        document.querySelectorAll('input[name="impression_type"]').forEach(el => {
            el.addEventListener('change', toggleScanFields);
        });
        toggleScanFields();
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Select all file inputs with the class 'clinic-file-input'
    document.querySelectorAll('.clinic-file-input').forEach(function(input) {
        input.addEventListener("change", function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    // Find the corresponding img inside the label for this input
                    // The label's 'for' attribute matches the input's id
                    const label = document.querySelector('label[for="' + input.id + '"]');
                    if (label) {
                        const img = label.querySelector('img');
                        if (img) {
                            img.src = e.target.result;
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
});
</script>

@endpush

</x-app-layout>
