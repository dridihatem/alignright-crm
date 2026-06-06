<x-app-layout>
    @push('head')
    <meta name="case-id" content="{{ $case->id }}">
    @endpush
    
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

  /* Clickable photo slots */
  .photo-slot {
      text-align: center;
  }
  .photo-slot label {
      display: block;
      cursor: pointer;
      margin-bottom: .25rem;
  }
  .photo-slot .slot-img {
      width: 100%;
      max-width: 150px;
      height: 120px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px dashed var(--bs-primary);
      transition: transform .2s ease, border-color .2s ease;
      background: #f8f9fa;
  }
  .photo-slot label:hover .slot-img {
      transform: scale(1.03);
      border-color: var(--bs-success);
  }
  .photo-slot.uploaded .slot-img {
      border-style: solid;
      border-color: var(--bs-success);
  }
  .photo-slot .slot-caption {
      font-size: 12px;
      font-weight: 500;
  }
  .photo-slot .slot-status {
      font-size: 11px;
      min-height: 16px;
  }
  .photo-slot input[type="file"] { display: none; }

  .file-upload-progress {
      margin-top: 10px;
      display: none;
  }

  .file-upload-progress .progress {
      height: 8px;
      border-radius: 4px;
  }

  .upload-status {
      font-size: 12px;
      color: #6c757d;
      margin-top: 5px;
  }

  .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
      display: none;
      justify-content: center;
      align-items: center;
  }

  .loading-content {
      text-align: center;
      color: white;
  }

  .loading-spinner {
      margin-bottom: 20px;
  }

  .upload-progress-container {
      width: 300px;
      margin: 0 auto;
  }

  /* Enhanced Uppy Styling */
  .uppy-Dashboard {
      border-radius: 12px !important;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
      border: 2px dashed #e9ecef !important;
      transition: all 0.3s ease !important;
  }

  .uppy-Dashboard:hover {
      border-color: var(--bs-primary) !important;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
  }

  .uppy-Dashboard-inner {
      background: #f8f9fa !important;
      border-radius: 10px !important;
  }

  .card.border-primary .uppy-Dashboard {
      border-color: var(--bs-primary) !important;
  }

  .card.border-success .uppy-Dashboard {
      border-color: var(--bs-success) !important;
  }

  .card.border-warning .uppy-Dashboard {
      border-color: var(--bs-warning) !important;
  }

  .card.border-info .uppy-Dashboard {
      border-color: var(--bs-info) !important;
  }

  /* Beautiful card animations */
  .card {
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }

  .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  }

  /* Progress bars styling */
  .uppy-ProgressBar {
      background: linear-gradient(90deg, #007bff, #28a745) !important;
      border-radius: 10px !important;
  }
</style>
@endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
            <div class="col-md-12 col-xxl-12 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">{{ __('master.file_upload') }} - {{ __('master.case') }} {{ $case->case_id }}</h5>
                            <small class="text-muted">{{ __('master.upload_files_for_case') }}</small>
                        </div>
                        <a href="{{ route('doctor.cases.show', $case->id) }}" class="btn btn-outline-secondary">
                            <i class="icon-base ti tabler-arrow-left me-2"></i>
                            {{ __('master.back_to_case') }}
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Background Upload Info Alert -->
                        <div class="alert alert-success alert-dismissible mb-4" role="alert">
                            <h5 class="alert-heading">
                                <i class="icon-base ti tabler-rocket text-success me-2"></i>
                                {{ __('master.smart_file_upload_system') }}
                            </h5>
                        
                        </div>

                        <form action="{{ route('doctor.cases.upload-files', $case->id) }}" method="post" enctype="multipart/form-data" id="upload-form">
                            @csrf
                            @method('PUT')

                            <div class="bs-stepper wizard-numbered mt-2">
                                <div class="bs-stepper-header">
                                    <div class="step" data-target="#stl-step">
                                        <button type="button" class="step-trigger">
                                            <span class="bs-stepper-circle">1</span>
                                            <span class="bs-stepper-label mt-1">
                                                <span class="bs-stepper-title">{{ __('master.stl_files') }}</span>
                                                <span class="bs-stepper-subtitle">{{ __('master.upper_lower_bite_scans') }}</span>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="line"><i class="icon-base ti tabler-chevron-right icon-md"></i></div>
                                    <div class="step" data-target="#clinical-step">
                                        <button type="button" class="step-trigger">
                                            <span class="bs-stepper-circle">2</span>
                                            <span class="bs-stepper-label mt-1">
                                                <span class="bs-stepper-title">{{ __('master.clinical_photos') }}</span>
                                                <span class="bs-stepper-subtitle">{{ __('master.files_clinical') }}</span>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="line"><i class="icon-base ti tabler-chevron-right icon-md"></i></div>
                                    <div class="step" data-target="#radiographs-step">
                                        <button type="button" class="step-trigger">
                                            <span class="bs-stepper-circle">3</span>
                                            <span class="bs-stepper-label mt-1">
                                                <span class="bs-stepper-title">{{ __('master.radiographs') }}</span>
                                                <span class="bs-stepper-subtitle">{{ __('master.radiograph_images') }}</span>
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <div class="bs-stepper-content">
                                <!-- Step 1: STL Files -->
                                <div id="stl-step" class="content">
                <!-- STL Files -->
                <div class="mb-4">
                    <div id="scan-fields">
                        <!-- STL Files Section -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="icon-base ti tabler-file-3d me-2"></i>
                                    {{ __('master.upper_scan') }}, {{ __('master.lower_scan') }}, {{ __('master.bite_scan') }} - {{ __('master.stl_files') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- STL Method Selection -->
                                <div class="mb-4">
                                    <h6 class="mb-3">{{ __('master.how_do_you_want_to_provide_stl_files') }}?</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="stl_method" id="stl_files" value="files" checked>
                                                <label class="form-check-label" for="stl_files">
                                                    <i class="icon-base ti tabler-upload me-1"></i>
                                                    {{ __('master.i_have_files_to_upload') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="stl_method" id="stl_links" value="links">
                                                <label class="form-check-label" for="stl_links">
                                                    <i class="icon-base ti tabler-link me-1"></i>
                                                    {{ __('master.i_have_external_links') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STL Upload Interface -->
                                <div id="stl-upload-section">
                                    <p class="text-muted mb-3">
                                        <i class="icon-base ti tabler-upload me-1"></i>
                                        {{ __('master.drag_and_drop_your_stl_scan_files_below') }}
                                    </p>
                                    <div id="uppy-stl-dashboard"></div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="icon-base ti tabler-info-circle me-1"></i>
                                            <strong>{{ __('master.supported') }}:</strong> {{ __('master.stl_files_only') }} • <strong>{{ __('master.max_size') }}:</strong> 500MB per file • <strong>{{ __('master.files') }}:</strong> {{ __('master.upper_lower_bite_scans') }}
                                        </small>
                                    </div>
                                </div>

                                <!-- STL Links Interface -->
                                <div id="stl-links-section" class="d-none">
                                    <p class="text-muted mb-3">
                                        <i class="icon-base ti tabler-link me-1"></i>
                                        {{ __('master.paste_your_stl_file_links_below') }}
                                    </p>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('master.upper_scan') }}</label>
                                            <input type="text" name="stl_upper_name" class="form-control mb-2" 
                                                   placeholder="{{ __('master.file_name') }}" value="">
                                            <input type="url" name="stl_upper_url" class="form-control" 
                                                   placeholder="{{ __('master.paste_upper_scan_link_here') }}" value="">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('master.lower_scan') }}</label>
                                            <input type="text" name="stl_lower_name" class="form-control mb-2" 
                                                   placeholder="{{ __('master.file_name') }}" value="">
                                            <input type="url" name="stl_lower_url" class="form-control" 
                                                   placeholder="{{ __('master.paste_lower_scan_link_here') }}" value="">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('master.bite_scan') }}</label>
                                            <input type="text" name="stl_bite_name" class="form-control mb-2" 
                                                   placeholder="{{ __('master.file_name') }}" value="">
                                            <input type="url" name="stl_bite_url" class="form-control" 
                                                   placeholder="{{ __('master.paste_bite_scan_link_here') }}" value="">
                                        </div>
                                    </div>
                                    <small class="text-muted mt-2">
                                        <i class="icon-base ti tabler-info-circle me-1"></i>
                                        {{ __('master.wetransfer_google_drive_dropbox_etc') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <small class="text-primary">
                            <i class="icon-base ti tabler-info-circle me-1"></i>
                            {{ __('master.notification_bite_scan') }}
                        </small>
                    </div>
                </div>
            </div>

                                    <!-- Step 1 navigation -->
                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="{{ route('doctor.cases.show', $case->id) }}" class="btn btn-label-secondary">
                                            <i class="icon-base ti tabler-arrow-left icon-md"></i>
                                            <span class="align-middle d-sm-inline-block d-none">{{ __('master.back_to_case') }}</span>
                                        </a>
                                        <button type="button" class="btn btn-primary btn-next">
                                            <span class="align-middle d-sm-inline-block d-none me-sm-2 text-white">{{ __('master.next') }}</span>
                                            <i class="icon-base ti tabler-chevron-right icon-md text-white"></i>
                                        </button>
                                    </div>
                                </div> <!-- /#stl-step -->

                                <!-- Step 2: Clinical Photos -->
                                <div id="clinical-step" class="bs-stepper-pane">
                            <!-- Clinical Photos -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="icon-base ti tabler-camera me-2"></i>
                                        {{ __('master.files_clinical') }} - {{ __('master.clinical_photos') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-4">
                                        <i class="icon-base ti tabler-info-circle me-1"></i>
                                        {{ __('master.click_image_to_upload') }}
                                    </p>
                                    @php
                                        $clinicalSlots = [
                                            ['img' => 'clinical01.webp', 'label' => __('master.photo_face')],
                                            ['img' => 'clinical02.webp', 'label' => __('master.photo_profile')],
                                            ['img' => 'clinical03.webp', 'label' => __('master.photo_smile')],
                                            ['img' => 'clinical04.webp', 'label' => __('master.photo_intraoral_front')],
                                            ['img' => 'clinical05.webp', 'label' => __('master.photo_lateral_right')],
                                            ['img' => 'clinical06.webp', 'label' => __('master.photo_lateral_left')],
                                            ['img' => 'clinical07.webp', 'label' => __('master.photo_occlusal_upper')],
                                            ['img' => 'clinical08.webp', 'label' => __('master.photo_occlusal_lower')],
                                        ];
                                    @endphp
                                    <div class="row g-4 justify-content-center" id="clinical-photo-grid">
                                        @foreach($clinicalSlots as $slot)
                                            <div class="col-6 col-md-3 photo-slot" data-file-type="clinical_photo">
                                                <label>
                                                    <img src="{{ asset('assets/img/photos_clinic/'.$slot['img']) }}" class="slot-img" alt="{{ $slot['label'] }}">
                                                    <input type="file" accept="image/*" class="slot-input" data-label="{{ $slot['label'] }}">
                                                </label>
                                                <div class="slot-caption">{{ $slot['label'] }}</div>
                                                <div class="slot-status text-muted"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block mt-3">
                                        <i class="icon-base ti tabler-info-circle me-1"></i>
                                        <strong>{{ __('master.supported') }}:</strong> {{ __('master.jpg_jpeg_png_gif_webp') }} • <strong>{{ __('master.max_size') }}:</strong> 50MB
                                    </small>
                                </div>
                            </div>

                                    <!-- Step 2 navigation -->
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-label-secondary btn-prev">
                                            <i class="icon-base ti tabler-chevron-left icon-md"></i>
                                            <span class="align-middle d-sm-inline-block d-none">{{ __('master.previous') }}</span>
                                        </button>
                                        <button type="button" class="btn btn-primary btn-next">
                                            <span class="align-middle d-sm-inline-block d-none me-sm-2 text-white">{{ __('master.next') }}</span>
                                            <i class="icon-base ti tabler-chevron-right icon-md text-white"></i>
                                        </button>
                                    </div>
                                </div> <!-- /#clinical-step -->

                                <!-- Step 3: Radiographs -->
                                <div id="radiographs-step" class="bs-stepper-pane">
                            <!-- Radiographs -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="icon-base ti tabler-radioactive me-2"></i>
                                        {{ __('master.files_radiographs') }} - {{ __('master.radiograph_images') }} ({{ __('master.jpg_jpeg_png_gif_webp') }})
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Radiographs Method Selection -->
                                    <div class="mb-4">
                                        <h6 class="mb-3">{{ __('master.how_do_you_want_to_provide_radiographs') }}?</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="radiographs_method" id="radiographs_files" value="files" checked>
                                                    <label class="form-check-label" for="radiographs_files">
                                                        <i class="icon-base ti tabler-upload me-1"></i>
                                                        {{ __('master.i_have_files_to_upload') }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="radiographs_method" id="radiographs_links" value="links">
                                                    <label class="form-check-label" for="radiographs_links">
                                                        <i class="icon-base ti tabler-link me-1"></i>
                                                        {{ __('master.i_have_external_links') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Radiographs Upload Interface (two sections) -->
                                    <div id="radiographs-upload-section">
                                        <!-- Section 1: Panoramique (multiple files) -->
                                        <div class="border rounded p-3 mb-4">
                                            <h6 class="fw-semibold mb-1">
                                                <i class="icon-base ti tabler-radioactive me-1 text-primary"></i>
                                                {{ __('master.radiograph_panoramic') }}
                                            </h6>
                                            <p class="text-muted mb-3"><small>{{ __('master.upload_your_radiograph_images_below') }}</small></p>
                                            <div id="uppy-panoramic-dashboard"></div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="icon-base ti tabler-info-circle me-1"></i>
                                                    <strong>{{ __('master.supported') }}:</strong> {{ __('master.jpg_jpeg_png_gif_webp') }} • <strong>{{ __('master.max_size') }}:</strong> 50MB • <strong>{{ __('master.max_files') }}:</strong> 20
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Section 2: Téléradiographie de profil -->
                                        <div class="border rounded p-3">
                                            <h6 class="fw-semibold mb-1">
                                                <i class="icon-base ti tabler-radioactive me-1 text-primary"></i>
                                                {{ __('master.radiograph_teleradiography_profile') }}
                                            </h6>
                                            <p class="text-muted mb-3"><small>{{ __('master.upload_your_radiograph_images_below') }}</small></p>
                                            <div id="uppy-teleradiography-dashboard"></div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="icon-base ti tabler-info-circle me-1"></i>
                                                    <strong>{{ __('master.supported') }}:</strong> {{ __('master.jpg_jpeg_png_gif_webp') }} • <strong>{{ __('master.max_size') }}:</strong> 50MB • <strong>{{ __('master.max_files') }}:</strong> 20
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Radiographs Links Interface -->
                                    <div id="radiographs-links-section" class="d-none">
                                        <p class="text-muted mb-3">
                                            <i class="icon-base ti tabler-link me-1"></i>
                                            {{ __('master.paste_your_radiograph_links_below') }}
                                        </p>
                                        <div id="radiographs-links-container">
                                            <div class="row g-3 mb-3 radiographs-link-row">
                                                <div class="col-md-5">
                                                    <input type="text" name="radiographs_names[]" class="form-control" 
                                                           placeholder="{{ __('master.file_name') }}" value="">
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="url" name="radiographs_urls[]" class="form-control" 
                                                           placeholder="{{ __('master.paste_radiograph_link_here') }}" value="">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-outline-primary btn-sm add-radiographs-link">
                                                        <i class="icon-base ti tabler-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class="icon-base ti tabler-info-circle me-1"></i>
                                            {{ __('master.you_can_add_multiple_radiograph_links') }} ({{ __('master.max_20_images') }})
                                        </small>
                                    </div>
                                </div>
                            </div>

                                    <!-- Step 3 navigation (last step) -->
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-label-secondary btn-prev">
                                            <i class="icon-base ti tabler-chevron-left icon-md"></i>
                                            <span class="align-middle d-sm-inline-block d-none">{{ __('master.previous') }}</span>
                                        </button>
                                        <button type="submit" class="btn btn-success" id="submit-upload-btn">
                                            <i class="icon-base ti tabler-device-floppy icon-md"></i>
                                            <span class="align-middle d-sm-inline-block d-none">{{ __('master.save_and_upload_files') }}</span>
                                        </button>
                                    </div>
                                </div> <!-- /#radiographs-step -->
                                </div> <!-- /.bs-stepper-content -->
                            </div> <!-- /.bs-stepper -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>


@push('scripts')
<!-- Multi-step wizard -->
<script src="{{ asset('assets/vendor/libs/bs-stepper/bs-stepper.js') }}"></script>
<script src="{{ asset('assets/js/form-wizard-numbered.js') }}"></script>
<!-- Uppy File Upload System -->
<script src="https://releases.transloadit.com/uppy/v3.15.0/uppy.min.js"></script>
<script>
    // Set case ID for uploads
    window.caseId = {{ $case->id }};
    console.log('Case ID set to:', window.caseId);
    
    document.addEventListener('DOMContentLoaded', function() {
        // Debug what's available in Uppy
        console.log('Uppy object:', window.Uppy);
        console.log('Available plugins:', Object.keys(window.Uppy || {}));
        
        // Get storage setting from backend
        const storagePreference = '{{ App\Models\Setting::getValue("default_upload_storage", "local") }}';
        const googleDriveEnabled = {{ App\Models\Setting::getValue('google_drive_enabled', '0') == '1' ? 'true' : 'false' }};
        
        console.log('Storage preference:', storagePreference);
        console.log('Google Drive enabled:', googleDriveEnabled);
        
        // Test if the Uppy upload endpoint is accessible
        fetch('/doctor/uppy/test', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Uppy route test successful:', data);
        })
        .catch(error => {
            console.error('Uppy route test failed:', error);
        });

        // Debug function to check what's being sent to server
        window.debugUppyRequest = function(fileType) {
            console.log('Debug: Testing file upload with type:', fileType);
            const testData = new FormData();
            testData.append('_token', '{{ csrf_token() }}');
            
            fetch('/doctor/uppy/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Case-ID': '{{ $case->id }}',
                    'X-File-Type': fileType,
                    'X-Storage-Preference': storagePreference,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: testData
            })
            .then(response => {
                console.log('Test response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Test response data:', data);
            })
            .catch(error => {
                console.error('Test request failed:', error);
            });
        };

        // Debug function to show status of all Uppy instances
        window.debugUppyInstances = function() {
            console.log('=== UPPY INSTANCES DEBUG ===');
            console.log('STL Uppy:', {
                id: stlUppy?.id,
                files: stlUppy?.getFiles()?.length || 0,
                plugins: stlUppy?.getPlugins?.() || 'N/A',
                state: stlUppy?.getState?.()
            });
            console.log('Panoramic Uppy:', {
                id: panoramicUppy?.id,
                files: panoramicUppy?.getFiles()?.length || 0,
                plugins: panoramicUppy?.getPlugins?.() || 'N/A',
                state: panoramicUppy?.getState?.()
            });
            console.log('Teleradiography Uppy:', {
                id: teleradiographyUppy?.id,
                files: teleradiographyUppy?.getFiles()?.length || 0,
                plugins: teleradiographyUppy?.getPlugins?.() || 'N/A',
                state: teleradiographyUppy?.getState?.()
            });
            console.log('=== END DEBUG ===');
        };
        
        // Common Uppy configuration
        function createUppy(restrictions, dashboardTarget, note) {
        const uppy = new window.Uppy.Uppy({
            debug: true,
            autoProceed: false,
            allowMultipleUploadBatches: true,
            restrictions: restrictions,
            onBeforeFileAdded: (currentFile, files) => {
                console.log('Adding file:', currentFile.name, 'Type:', currentFile.type, 'Size:', currentFile.size);
                console.log('Target dashboard:', dashboardTarget);
                return true;
            },
            onBeforeUpload: (files) => {
                console.log('About to upload files:', Object.keys(files));
                console.log('Files details:', files);
                return true;
            }
        })
        .use(window.Uppy.Dashboard, {
                target: dashboardTarget,
            inline: true,
            width: '100%',
                height: 400,
            showProgressDetails: true,
                note: note,
            proudlyDisplayPoweredByUppy: false,
            theme: 'auto',
                plugins: googleDriveEnabled ? ['GoogleDrive'] : []
            });
            
            // Add Google Drive plugin if enabled
            if (googleDriveEnabled) {
                uppy.use(window.Uppy.GoogleDrive, {
            companionUrl: 'https://companion.uppy.io'
        });
            }
            
            return uppy;
        }
        
        // Initialize STL Files Uppy
        const stlUppy = createUppy({
            maxFileSize: 500 * 1024 * 1024, // 500MB
            allowedFileTypes: ['.stl'],
            maxNumberOfFiles: 3
        }, '#uppy-stl-dashboard', '{{ __("master.stl_files_only_for_scans_up_to_500mb_each") }}');
        
        // Clinical Photos now use the clickable image grid (see clinical photo slots script)

        // Initialize Panoramique Radiographs Uppy
        const panoramicUppy = createUppy({
            maxFileSize: 50 * 1024 * 1024, // 50MB
            allowedFileTypes: ['.jpg', '.jpeg', '.png', '.gif', '.webp'],
            maxNumberOfFiles: 20
        }, '#uppy-panoramic-dashboard', '{{ __("master.radiograph_images_only_up_to_50mb_each") }}');

        // Initialize Téléradiographie de profil Uppy
        const teleradiographyUppy = createUppy({
            maxFileSize: 50 * 1024 * 1024, // 50MB
            allowedFileTypes: ['.jpg', '.jpeg', '.png', '.gif', '.webp'],
            maxNumberOfFiles: 20
        }, '#uppy-teleradiography-dashboard', '{{ __("master.radiograph_images_only_up_to_50mb_each") }}');

        // Function to setup upload plugins for each Uppy instance
        function setupUppyPlugins(uppyInstance, fileType) {
            console.log('Setting up plugins for file type:', fileType);
            console.log('Current storage preference:', storagePreference);
            console.log('Google Drive enabled:', googleDriveEnabled);
            
            // Add upload strategy based on file size
            uppyInstance.on('file-added', (file) => {
                console.log(`File added to ${fileType}:`, file.name, 'Size:', file.size);
                console.log('File object details:', {
                    id: file.id,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    meta: file.meta
                });
                
                // Add file type metadata
                file.meta.fileType = fileType;
                
                // Debug logging for STL files
                if (fileType === 'stl_scan') {
                    console.warn('STL FILE DETECTED:', {
                        fileName: file.name,
                        fileType: fileType,
                        meta: file.meta
                    });
                }
            
            // For files > 5MB, use TUS chunked upload
            if (file.size > 5 * 1024 * 1024) {
                console.log('Large file detected, will use chunked upload');
                file.meta.useChunked = true;
            } else {
                console.log('Small file detected, will use regular upload');
                file.meta.useChunked = false;
            }
        });

            // Use TUS for large files
            if (!uppyInstance.getPlugin('Tus')) {
                uppyInstance.use(window.Uppy.Tus, {
                endpoint: '/doctor/uppy/upload',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-Case-ID': '{{ $case->id }}',
                    'X-File-Type': fileType,
                    'X-Storage-Preference': storagePreference
                },
                onBeforeRequest: (xhr, options) => {
                    console.log('TUS Headers being sent:', {
                        'X-File-Type': fileType,
                        'X-Case-ID': '{{ $case->id }}',
                        fileType: fileType
                    });
                },
                chunkSize: 1 * 1024 * 1024, // 1MB chunks
                timeout: 300000, // 5 minutes timeout for TUS
                metadata: {
                    case_id: '{{ $case->id }}',
                    user_id: '{{ auth()->id() }}',
                    file_type: fileType,
                    storage_preference: storagePreference,
                    _token: '{{ csrf_token() }}'
                },
                removeFingerprintOnSuccess: true,
                retryDelays: [0, 1000, 3000]
            });
        }
        
            if (!uppyInstance.getPlugin('XHRUpload')) {
                console.log('Setting up XHR Upload for file type:', fileType);
                uppyInstance.use(window.Uppy.XHRUpload, {
                endpoint: '/doctor/uppy/upload',
                method: 'POST',
                headers: (file) => {
                    console.log('XHR Upload headers for file:', file?.name, 'type:', fileType);
                    return {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-Case-ID': '{{ $case->id }}',
                        'X-File-Type': fileType,
                        'X-Storage-Preference': storagePreference
                    };
                },
                fieldName: 'file',
                formData: true,
                bundle: false,
                metadata: {
                    case_id: '{{ $case->id }}',
                    user_id: '{{ auth()->id() }}',
                    file_type: fileType,
                    storage_preference: storagePreference,
                    _token: '{{ csrf_token() }}'
                },
                limit: 1,
                timeout: 300000, // 5 minutes timeout
                getResponseData: (responseText, response) => {
                    console.log('XHR Upload response:', response.status, responseText);
                    console.log('Response headers:', response.getAllResponseHeaders());
                    try {
                        const data = JSON.parse(responseText);
                        console.log('Parsed response data:', data);
                        return data;
                    } catch (e) {
                        console.error('Failed to parse response:', responseText);
                        return { error: 'Invalid response format' };
                    }
                },
                onBeforeRequest: (xhr, options) => {
                    console.log('About to send XHR request:', options);
                    console.log('XHR headers:', xhr.getAllRequestHeaders());
                },
                onAfterResponse: (xhr, options) => {
                    console.log('XHR request completed:', xhr.status, xhr.statusText);
                    console.log('Response text:', xhr.responseText);
                }
            });
            }
        }
        
        // Setup plugins for each Uppy instance
        setupUppyPlugins(stlUppy, 'stl_scan');
        setupUppyPlugins(panoramicUppy, 'radiograph_panoramic');
        setupUppyPlugins(teleradiographyUppy, 'radiograph_teleradiography');

        // Function to setup event handlers for each Uppy instance
        function setupUppyEvents(uppyInstance, instanceName) {
        // Handle upload start
            uppyInstance.on('upload', (data) => {
                console.log(`Upload started in ${instanceName}:`, data);
                console.log('Files being uploaded:', Object.keys(data.fileIDs).length);
            });

        // Handle upload success
            uppyInstance.on('upload-success', (file, response) => {
                console.log(`File uploaded successfully in ${instanceName}:`, file.name);
                console.log('Upload response:', response);
                console.log('File details:', file);
            if (window.Flasher) {
                window.Flasher.success(`${file.name} uploaded successfully!`);
            } else {
                toastr.success(`${file.name} uploaded successfully!`);
            }
            
            // Update file list or UI as needed
            updateFileList();
        });

        // Handle upload error
            uppyInstance.on('upload-error', (file, error, response) => {
                console.error(`Upload failed in ${instanceName}:`, {
                    file: file.name,
                    error: error,
                    response: response,
                    status: response?.status,
                    body: response?.body
                });
                
                let errorMessage = `Failed to upload ${file.name}`;
                if (response?.body?.error) {
                    errorMessage += `: ${response.body.error}`;
                } else if (error?.message) {
                    errorMessage += `: ${error.message}`;
                } else if (response?.status) {
                    errorMessage += ` (Status: ${response.status})`;
                }
                
            if (window.Flasher) {
                window.Flasher.error(errorMessage);
            } else {
                toastr.error(errorMessage);
            }
        });

        // Handle complete upload batch
            uppyInstance.on('complete', (result) => {
                console.log(`Upload batch complete in ${instanceName}:`, result);
            if (result.successful.length > 0) {
                if (window.Flasher) {
                    window.Flasher.success(`Successfully uploaded ${result.successful.length} file(s) in ${instanceName}`);
                } else {
                    toastr.success(`Successfully uploaded ${result.successful.length} file(s) in ${instanceName}`);
                }
            }
            if (result.failed.length > 0) {
                if (window.Flasher) {
                    window.Flasher.error(`Failed to upload ${result.failed.length} file(s) in ${instanceName}`);
                } else {
                    toastr.error(`Failed to upload ${result.failed.length} file(s) in ${instanceName}`);
                }
                }
            });
        }
        
        // Setup event handlers for all Uppy instances
        setupUppyEvents(stlUppy, '{{ __("master.stl_scanner") }}');
        setupUppyEvents(panoramicUppy, '{{ __("master.radiograph_panoramic") }}');
        setupUppyEvents(teleradiographyUppy, '{{ __("master.radiograph_teleradiography_profile") }}');

        // Function to update file list (you can customize this)
        function updateFileList() {
            // Reload the page or update the file list via AJAX
            // For now, we'll just show a message
            console.log('File list should be updated here');
        }

        // Handle form submission (impression type + links)
        const form = document.querySelector('#upload-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Create FormData and collect all form data
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                
                // Add impression type if selected
                const impressionType = document.querySelector('input[name="impression_type"]:checked');
                if (impressionType && impressionType.value) {
                    formData.append('impression_type', impressionType.value);
                }
                
                // Collect STL links
                const stlUpperName = document.querySelector('input[name="stl_upper_name"]');
                const stlUpperUrl = document.querySelector('input[name="stl_upper_url"]');
                const stlLowerName = document.querySelector('input[name="stl_lower_name"]');
                const stlLowerUrl = document.querySelector('input[name="stl_lower_url"]');
                const stlBiteName = document.querySelector('input[name="stl_bite_name"]');
                const stlBiteUrl = document.querySelector('input[name="stl_bite_url"]');
                
                if (stlUpperName && stlUpperName.value) formData.append('stl_upper_name', stlUpperName.value);
                if (stlUpperUrl && stlUpperUrl.value) formData.append('stl_upper_url', stlUpperUrl.value);
                if (stlLowerName && stlLowerName.value) formData.append('stl_lower_name', stlLowerName.value);
                if (stlLowerUrl && stlLowerUrl.value) formData.append('stl_lower_url', stlLowerUrl.value);
                if (stlBiteName && stlBiteName.value) formData.append('stl_bite_name', stlBiteName.value);
                if (stlBiteUrl && stlBiteUrl.value) formData.append('stl_bite_url', stlBiteUrl.value);
                
                // Collect Clinical Photos links
                const clinicalNames = document.querySelectorAll('input[name="clinical_names[]"]');
                const clinicalUrls = document.querySelectorAll('input[name="clinical_urls[]"]');
                clinicalNames.forEach((input, index) => {
                    if (input.value && clinicalUrls[index] && clinicalUrls[index].value) {
                        formData.append('clinical_names[]', input.value);
                    }
                });
                clinicalUrls.forEach((input, index) => {
                    if (input.value && clinicalNames[index] && clinicalNames[index].value) {
                        formData.append('clinical_urls[]', input.value);
                    }
                });
                
                // Collect Radiographs links
                const radiographsNames = document.querySelectorAll('input[name="radiographs_names[]"]');
                const radiographsUrls = document.querySelectorAll('input[name="radiographs_urls[]"]');
                radiographsNames.forEach((input, index) => {
                    if (input.value && radiographsUrls[index] && radiographsUrls[index].value) {
                        formData.append('radiographs_names[]', input.value);
                    }
                });
                radiographsUrls.forEach((input, index) => {
                    if (input.value && radiographsNames[index] && radiographsNames[index].value) {
                        formData.append('radiographs_urls[]', input.value);
                    }
                });
                
                console.log('Submitting form with data:', {
                    impression_type: impressionType?.value,
                    formDataKeys: Array.from(formData.keys())
                });
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    console.log('Response status:', response.status);
                    if (response.ok) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            console.error('Error response:', text);
                            throw new Error('HTTP ' + response.status + ': ' + text);
                        });
                    }
                }).then(data => {
                    console.log('Form submission response:', data);
                    if (data.success) {
                        if (window.Flasher) {
                            window.Flasher.success(data.message || '{{ __("master.files_and_links_saved_successfully") }}');
                        } else {
                            toastr.success('{{ __("master.files_and_links_saved_successfully") }}');
                        }
                        
                        // Redirect to case view after successful save
                        setTimeout(() => {
                            window.location.href = '{{ route("doctor.cases.show", $case->id) }}';
                        }, 1500);
                    } else {
                        throw new Error(data.message || '{{ __("master.unknown_error_occurred") }}');
                    }
                }).catch(error => {
                    console.error('Error saving form:', error);
                    if (window.Flasher) {
                        window.Flasher.error('{{ __("master.failed_to_save_form") }}: ' + error.message);
                    } else {
                        toastr.error('{{ __("master.failed_to_save_form") }}: ' + error.message);
                    }
                });
            });
        }
        
        // Auto-save impression type when selection changes
        const impressionInputs = document.querySelectorAll('input[name="impression_type"]');
        impressionInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.checked) {
                    const formData = new FormData();
                    formData.append('impression_type', this.value);
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'PUT');
                    
                    console.log('Auto-saving impression type:', this.value);
                    
                    fetch('{{ route("doctor.cases.upload-files", $case->id) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    }).then(response => {
                        console.log('Auto-save response status:', response.status);
                        if (response.ok) {
                            return response.json();
                        } else {
                            throw new Error('HTTP ' + response.status);
                        }
                    }).then(data => {
                        console.log('Impression type auto-saved successfully:', data);
                        if (data.success) {
                            // Optionally show a subtle success indicator
                            console.log('Impression type saved:', data.type_of_scan);
                        }
                    }).catch(error => {
                        console.error('Error auto-saving impression type:', error);
                    });
                }
            });
        });

        // Make uppy instances globally available for debugging
        window.stlUppy = stlUppy;
        window.panoramicUppy = panoramicUppy;
        window.teleradiographyUppy = teleradiographyUppy;
        
        // Handle individual section upload/links toggle
        function toggleSectionMode(sectionName, mode) {
            const uploadSection = document.getElementById(`${sectionName}-upload-section`);
            const linksSection = document.getElementById(`${sectionName}-links-section`);
            
            if (mode === 'files') {
                uploadSection.classList.remove('d-none');
                linksSection.classList.add('d-none');
            } else if (mode === 'links') {
                uploadSection.classList.add('d-none');
                linksSection.classList.remove('d-none');
            }
        }
        
        // Add event listeners for each section
        ['stl', 'radiographs'].forEach(section => {
            const radios = document.querySelectorAll(`input[name="${section}_method"]`);
            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        toggleSectionMode(section, this.value);
                    }
                });
            });
        });
        
        // Function to add more link input rows
        function addLinkRow(container, sectionName, maxRows) {
            const currentRows = container.querySelectorAll('.row.g-3');
            if (currentRows.length >= maxRows) {
                alert(`{{ __('master.maximum_files_reached') }}: ${maxRows}`);
                return;
            }
            
            const newRow = document.createElement('div');
            newRow.className = 'row g-3 mb-3 ' + sectionName + '-link-row';
            newRow.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="${sectionName}_names[]" class="form-control" 
                           placeholder="{{ __('master.file_name') }}" value="">
                </div>
                <div class="col-md-6">
                    <input type="url" name="${sectionName}_urls[]" class="form-control" 
                           placeholder="{{ __('master.paste_link_here') }}" value="">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-link">
                        <i class="icon-base ti tabler-minus"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(newRow);
            
            // Add remove functionality to the new row
            newRow.querySelector('.remove-link').addEventListener('click', function() {
                newRow.remove();
            });
        }
        
        // Add event listeners for add link buttons
        var addRadiographsLinkBtn = document.querySelector('.add-radiographs-link');
        if (addRadiographsLinkBtn) {
            addRadiographsLinkBtn.addEventListener('click', function() {
                addLinkRow(document.getElementById('radiographs-links-container'), 'radiographs', 20);
            });
        }
    });
</script>

<!-- Clickable photo slots (clinical photos + named radiographs) -->
<script>
    (function() {
        const caseId = {{ $case->id }};
        const csrf = '{{ csrf_token() }}';
        const storagePref = '{{ App\Models\Setting::getValue("default_upload_storage", "local") }}';

        function notify(type, msg) {
            if (window.Flasher) { window.Flasher[type] && window.Flasher[type](msg); }
            else if (window.toastr) { toastr[type] && toastr[type](msg); }
        }

        function uploadSlotFile(file, fileType, label, statusEl, onSuccess, onError) {
            const fd = new FormData();
            fd.append('file', file);
            fd.append('_token', csrf);
            if (label) fd.append('label', label);

            statusEl.textContent = '{{ __('master.uploading') }}…';
            statusEl.className = 'slot-status text-warning';

            fetch('/doctor/uppy/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-Case-ID': caseId,
                    'X-File-Type': fileType,
                    'X-Storage-Preference': storagePref
                },
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    statusEl.textContent = '✓ {{ __('master.uploaded') }}';
                    statusEl.className = 'slot-status text-success';
                    notify('success', (label ? label + ' — ' : '') + '{{ __('master.uploaded') }}');
                    onSuccess && onSuccess(data);
                } else {
                    throw new Error(data && data.error ? data.error : 'Upload error');
                }
            })
            .catch(err => {
                statusEl.textContent = '✗ ' + (err.message || 'error');
                statusEl.className = 'slot-status text-danger';
                notify('error', (label ? label + ' — ' : '') + (err.message || 'Upload error'));
                onError && onError(err);
            });
        }

        // Clickable slots (clinical photos + named radiographs): replace preview in place
        document.querySelectorAll('.photo-slot .slot-input').forEach(function(input) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                const slot = this.closest('.photo-slot');
                const img = slot.querySelector('.slot-img');
                const status = slot.querySelector('.slot-status');
                const fileType = slot.getAttribute('data-file-type');
                const label = this.getAttribute('data-label') || '';

                if (img && img.tagName === 'IMG') {
                    const reader = new FileReader();
                    reader.onload = e => { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                }

                uploadSlotFile(file, fileType, label, status, function() {
                    slot.classList.add('uploaded');
                });
            });
        });
    })();
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://releases.transloadit.com/uppy/v3.15.0/uppy.min.css">
<link rel="stylesheet" href="{{ asset('css/upload-styles.css') }}">
<style>
.uppy-Root {
    --uppy-c-primary: #007bff;
    --uppy-c-primary-dark: #0056b3;
}
.uppy-Dashboard {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>
@endpush

</x-app-layout>
