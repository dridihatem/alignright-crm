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

    /* Professional case header + info grid */
    .cd-hero { border: 0; background: linear-gradient(135deg, #f7fdfd 0%, #ffffff 60%); box-shadow: 0 2px 10px rgba(15,23,42,.05); }
    .cd-hero .cd-hero-icon { width: 56px; height: 56px; border-radius: 14px; background: #01b9c6; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex: 0 0 auto; box-shadow: 0 6px 16px rgba(1,185,198,.35); }
    .cd-hero .cd-eyebrow { font-size: .72rem; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; font-weight: 600; }
    .cd-hero h4 { color: #0f172a; font-weight: 700; letter-spacing: -.01em; }
    .cd-meta-chip { display: inline-flex; align-items: center; gap: .35rem; font-size: .8rem; color: #475569; background: #f1f5f9; border-radius: 999px; padding: .25rem .7rem; }

    .cd-info-row { display: flex; align-items: center; gap: .75rem; padding: .65rem 0; border-bottom: 1px dashed #e9eef3; }
    .cd-info-row:last-child { border-bottom: 0; }
    .cd-info-row .cd-ic { width: 36px; height: 36px; border-radius: 9px; background: #f0fbfc; color: #01b9c6; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .cd-info-row .cd-label { font-size: .72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; margin: 0; line-height: 1.1; }
    .cd-info-row .cd-value { font-weight: 600; color: #0f172a; margin: 0; word-break: break-word; }
    .cd-subhead { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #01b9c6; margin-bottom: .25rem; }

    /* Title above each file table inside the card body */
    .files-table-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #01b9c6; margin-bottom: .6rem; padding-bottom: .4rem; border-bottom: 1px solid #e9eef3; display: flex; align-items: center; }

    @media (min-width: 992px) {
        .cd-sidebar { position: sticky; top: 1.5rem; }
    }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/lightbox/css/lightbox.min.css') }}">
    @endpush

    @include('partials.case_detail_compact')

    <div class="container-xxl flex-grow-1 container-p-y case-detail-compact">
        <div class="row g-6">

            {{-- ============================================================ --}}
            {{-- 1. CASE & PATIENT INFORMATION                                --}}
            {{-- ============================================================ --}}
            @php
                $statusMap = [
                    'pending' => 'warning', 'draft' => 'secondary', 'in_planning' => 'info',
                    'approval' => 'info', 'in_production' => 'success', 'shipped' => 'success', 'rejected' => 'danger',
                ];
                $statusColor = $statusMap[$case->status] ?? 'secondary';
            @endphp

            {{-- Hero header --}}
            <div class="col-12">
                <div class="card cd-hero">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="cd-hero-icon"><i class="icon-base ti tabler-folder"></i></span>
                                <div>
                                    <div class="cd-eyebrow">{{ __('master.case_id') }}</div>
                                    <h4 class="mb-2">#{{ $case->case_id }}</h4>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge bg-label-{{ $statusColor }}">{{ __('master.' . $case->status) }}</span>
                                        <span class="cd-meta-chip"><i class="icon-base ti tabler-user"></i>{{ $case->patient->name }} {{ $case->patient->surname }}</span>
                                        <span class="cd-meta-chip"><i class="icon-base ti tabler-calendar"></i>{{ $case->created_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('doctor.cases.upload-files', $case->id) }}" class="btn btn-primary">
                                    <i class="icon-base ti tabler-cloud-upload me-2"></i>{{ __('master.upload_files_for_case') }}
                                </a>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="icon-base ti tabler-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('doctor.cases.edit', $case->id) }}"><i class="icon-base ti tabler-edit me-2"></i>{{ __('master.edit') }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- LEFT SIDE: PATIENT & CASE INFORMATION                        --}}
            {{-- ============================================================ --}}
            <div class="col-lg-4">
                <div class="cd-sidebar">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="icon-base ti tabler-user me-2 text-primary"></i>{{ __('master.patient_information') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="cd-info-row">
                                <span class="cd-ic"><i class="icon-base ti tabler-user"></i></span>
                                <div><p class="cd-label">{{ __('master.patient_name') }}</p><p class="cd-value">{{ $case->patient->name }} {{ $case->patient->surname }}</p></div>
                            </div>
                            @if($case->patient->reference)
                            <div class="cd-info-row">
                                <span class="cd-ic"><i class="icon-base ti tabler-id"></i></span>
                                <div><p class="cd-label">{{ __('master.patient_reference') }}</p><p class="cd-value">{{ $case->patient->reference }}</p></div>
                            </div>
                            @endif
                            <div class="cd-info-row">
                                <span class="cd-ic"><i class="icon-base ti tabler-gender-bigender"></i></span>
                                <div><p class="cd-label">{{ __('master.patient_gender') }}</p><p class="cd-value">{{ ucfirst($case->patient->gender) }}</p></div>
                            </div>
                            <div class="cd-info-row">
                                <span class="cd-ic"><i class="icon-base ti tabler-phone"></i></span>
                                <div><p class="cd-label">{{ __('master.patient_phone') }}</p><p class="cd-value">{{ $case->patient->phone ?: __('master.not_available') }}</p></div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="icon-base ti tabler-clipboard-text me-2 text-primary"></i>{{ __('master.case_details') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="cd-info-row">
                                <span class="cd-ic"><i class="icon-base ti tabler-stethoscope"></i></span>
                                <div><p class="cd-label">{{ __('master.doctor_name') }}</p><p class="cd-value">{{ $case->doctor->name }} {{ $case->doctor->surname }}</p></div>
                            </div>
                            <div class="cd-info-row">
                                <span class="cd-ic"><i class="icon-base ti tabler-mail"></i></span>
                                <div><p class="cd-label">{{ __('master.doctor_email') }}</p><p class="cd-value">{{ $case->doctor->email ?: __('master.not_available') }}</p></div>
                            </div>
                            <div class="cd-info-row">
                                <span class="cd-ic"><i class="icon-base ti tabler-progress"></i></span>
                                <div><p class="cd-label">{{ __('master.case_status') }}</p><p class="cd-value"><span class="badge bg-label-{{ $statusColor }}">{{ __('master.' . $case->status) }}</span></p></div>
                            </div>
                            <div class="cd-info-row">
                                <span class="cd-ic"><i class="icon-base ti tabler-calendar"></i></span>
                                <div><p class="cd-label">{{ __('master.case_date') }}</p><p class="cd-value">{{ $case->created_at->format('d-m-Y') }}</p></div>
                            </div>
                        </div>
                    </div>

                    {{-- Chat discussion (admin / technician / laboratory) on the first side --}}
                    @include('partials.case_chat', ['case' => $case])
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- RIGHT SIDE: TREATMENT PLAN, COMMENTS, FILES, FINITION        --}}
            {{-- ============================================================ --}}
            <div class="col-lg-8">
                <div class="row g-6">

            {{-- ============================================================ --}}
            {{-- 3. FILES                                                     --}}
            {{-- ============================================================ --}}
            <div class="col-12 order-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('master.files') }}</h5>
                    </div>
                    <div class="card-body">

                        <!-- Impression Type Badge -->
                        <div class="mb-4">
                            <h6>{{ __('master.impression_type') }} :
                                <span class="badge bg-label-primary">
                                    @if($case->type_of_scan == 'intraoral')
                                    {{ __('master.intraoral_scan') }}
                                    @elseif($case->type_of_scan == 'desktop')
                                    {{ __('master.desktop_scan') }}
                                    @else
                                    {{ __('master.silicone_impression') }}
                                    @endif
                                </span>
                            </h6>
                        </div>

                        <div class="row g-4">
                            <!-- STL Scans Section -->
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary">
                                    <h6 class="mb-0 text-primary">
                                           <i class="icon-base ti tabler-file-3d me-1"></i>{{ __('master.stl_files') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                       
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
                                                                <div class="d-flex align-items-center gap-2">
                                                                <a href="{{ $file->storage_type == 'google_drive' ? google_drive_download_url($file->url) : $file->url }}" download class="text-decoration-none">
                                                                    <i class="icon-base ti tabler-file-3d me-1"></i>{{ $file->name }}
                                                                </a>
                                                                    @if(auth()->user()->role_id == 2 && $case->status !== 'in_production')
                                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                                                onclick="confirmRemoveFile({{ $file->id }}, '{{ $file->name }}', 'stl')">
                                                                            <i class="icon-base ti tabler-trash me-1"></i>
                                                                        </button>
                                                                    @elseif($case->status === 'in_production')
                                                                        <span class="badge bg-label-warning" title="{{ __('master.cannot_remove_files_in_production') }}">
                                                                            <i class="icon-base ti tabler-lock me-1"></i>{{ __('master.locked') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
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

                            <!-- Clinical Photos Section -->
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary ">
                                        <h6 class="mb-0 text-primary">
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
                                                                <div class="d-flex align-items-center gap-2">
                                                                <a href="{{ $file->storage_type == 'google_drive' ? google_drive_image_url($file->url) : $file->url }}" target="_blank" class="text-decoration-none">
                                                                    <i class="icon-base ti tabler-photo me-1"></i>{{ $file->name }}
                                                                </a>
                                                                    @if(auth()->user()->role_id == 2 && $case->status !== 'in_production')
                                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                                                onclick="confirmRemoveFile({{ $file->id }}, '{{ $file->name }}', 'clinical')">
                                                                            <i class="icon-base ti tabler-trash me-1"></i>
                                                                        </button>
                                                                    @elseif($case->status === 'in_production')
                                                                        <span class="badge bg-label-warning" title="{{ __('master.cannot_remove_files_in_production') }}">
                                                                            <i class="icon-base ti tabler-lock me-1"></i>{{ __('master.locked') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
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
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary">
                                        <h6 class="mb-0 text-primary">
                                            <i class="icon-base ti tabler-radioactive me-2"></i>
                                            {{ __('master.files_radiographs') }}
                                        </h6>
                                        <small class="text-white-50">{{ __('master.radiograph_optional_note') }}</small>
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
                                                                <div class="d-flex align-items-center gap-2">
                                                                <a href="{{ $file->storage_type == 'google_drive' ? google_drive_image_url($file->url) : $file->url }}" target="_blank" class="text-decoration-none">
                                                                    <i class="icon-base ti tabler-x-ray me-1"></i>{{ $file->name }}
                                                                </a>
                                                                    @if(auth()->user()->role_id == 2 && $case->status !== 'in_production')
                                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                                                onclick="confirmRemoveFile({{ $file->id }}, '{{ $file->name }}', 'radiograph')">
                                                                            <i class="icon-base ti tabler-trash me-1"></i>
                                                                        </button>
                                                                    @elseif($case->status === 'in_production')
                                                                        <span class="badge bg-label-warning" title="{{ __('master.cannot_remove_files_in_production') }}">
                                                                            <i class="icon-base ti tabler-lock me-1"></i>{{ __('master.locked') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
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
                        </div>

                    </div>
                </div>
            </div>

            {{-- Case treatment details (shown below the files list) --}}
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

            {{-- ============================================================ --}}
            {{-- 1. TREATMENT PLAN                                            --}}
            {{-- ============================================================ --}}
            <div class="col-12 order-1">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('master.treatment_plan') }}</h5>

                        <!-- Price Proposal Section -->
                        @if($case->price)
                            <div class="price-proposal-section">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-start">
                                        <h6 class="mb-1 text-success">{{ __('master.price_proposal') }}</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="h5 mb-0 text-success">TND {{ number_format($case->price, 2) }}</span>
                                            @if($case->advance_payment)
                                                <small class="text-muted">
                                                    ({{ __('master.advance') }}: TND {{ number_format($case->advance_payment, 2) }})
                                                </small>
                                            @endif
                                        </div>
                                        @if($case->remaining_balance && $case->remaining_balance > 0)
                                            <small class="text-warning">
                                                {{ __('master.remaining_balance') }}: TND {{ number_format($case->remaining_balance, 2) }}
                                            </small>
                                        @endif
                                    </div>
                                    <div class="text-center">
                                        @if($case->price_accepted_at)
                                            @php
                                                $latestInvoice = $case->latestInvoice;
                                            @endphp
                                            @if($latestInvoice && $latestInvoice->isFullyPaid())
                                                <span class="badge bg-label-success">{{ __('master.total_paid') }}</span>
                                            @elseif($latestInvoice && $latestInvoice->hasPartialPayment())
                                                <span class="badge bg-label-warning">{{ __('master.partial_payment') }}</span>
                                            @else
                                                <span class="badge bg-label-info">{{ __('master.pending_payment') }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-label-secondary">{{ __('master.proposal_price') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($case->price_added_at)
                                    <small class="text-muted d-block mt-1">
                                        {{ __('master.price_added_by') }} {{ $case->admin ? $case->admin->name : 'Admin' }} on {{ $case->price_added_at->format('d-m-Y H:i') }}
                                    </small>
                                @endif

                                <!-- Price Acceptance Actions -->
                                @if($case->status === 'in_planning' || $case->status === 'approval' && $case->price && !$case->price_accepted_at && !$case->price_rejected_at)
                                    <div class="mt-3">
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-success btn-sm" onclick="acceptPrice({{ $case->id }})">
                                                <i class="icon-base ti tabler-check me-1"></i>{{ __('master.accept_price') }}
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="showRejectPriceModal({{ $case->id }})">
                                                <i class="icon-base ti tabler-x me-1"></i>{{ __('master.reject_price') }}
                                            </button>
                                        </div>
                                    </div>
                                @elseif($case->price_accepted_at)
                                    <div class="mt-2">
                                        <span class="badge bg-label-success">
                                            <i class="icon-base ti tabler-check me-1"></i>{{ __('master.price_accepted') }} on {{ $case->price_accepted_at->format('d-m-Y H:i') }}
                                        </span>
                                    </div>
                                @elseif($case->price_rejected_at)
                                    <div class="mt-2">
                                        <span class="badge bg-label-danger">
                                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.price_rejected') }} on {{ $case->price_rejected_at->format('d-m-Y H:i') }}
                                        </span>
                                        @if($case->price_rejection_reason)
                                            <small class="text-muted d-block mt-1">
                                                <strong>{{ __('master.reason') }}:</strong> {{ $case->price_rejection_reason }}
                                            </small>
                                        @endif
                                    </div>
                                @elseif($case->price && auth()->user()->role_id == 2)
                                    <div class="mt-3">
                                        <div class="alert alert-warning mb-2">
                                            <small><strong>{{ __('master.note') }}:</strong> {{ __('master.price_acceptance_buttons_note') }}</small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-success btn-sm" onclick="acceptPrice({{ $case->id }})">
                                                <i class="icon-base ti tabler-check me-1"></i>{{ __('master.accept_price') }}
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="showRejectPriceModal({{ $case->id }})">
                                                <i class="icon-base ti tabler-x me-1"></i>{{ __('master.reject_price') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="price-proposal-section">
                                <span class="badge bg-label-secondary">{{ __('master.no_price_set') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @php
                            $treatmentPlans = $case->treatmentType()->orderBy('created_at', 'desc')->get();
                        @endphp

                        @if($treatmentPlans->count() > 0)
                            @foreach($treatmentPlans as $treatmentPlan)
                                <div class="treatment-plan-item mb-4 p-3 border rounded">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="mb-2">{{ __('master.treatment_plan') }} #{{ $treatmentPlan->id }}</h6>
                                            <p class="text-muted mb-2">
                                                <strong>{{ __('master.uploaded_by') }}:</strong>
                                                {{ $treatmentPlan->technician ? $treatmentPlan->technician->name : 'N/A' }}
                                            </p>
                                            <p class="text-muted mb-2">
                                                <strong>{{ __('master.uploaded_at') }}:</strong>
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
                                                        <i class="icon-base ti tabler-file-type-pdf me-1"></i>{{ __('master.view_irp_file') }}
                                                    </a>
                                                </div>
                                            @endif

                                            <!-- 3D Viewer Link -->
                                            @if($treatmentPlan->link_viewer)
                                                <div class="mb-2">
                                                    <strong>{{ __('master.3d_viewer') }}:</strong>
                                                    <a href="{{ ensure_https_url($treatmentPlan->link_viewer) }}" target="_blank" class="btn btn-outline-success btn-sm ms-2">
                                                        <i class="icon-base ti tabler-link me-1"></i>{{ __('master.open_3d_viewer') }}
                                                    </a>
                                                </div>
                                            @endif

                                            @if($treatmentPlan->price)
                                                <p class="mb-2">
                                                    <strong>{{ __('master.price') }}:</strong>
                                                    <span class="badge bg-label-success">${{ number_format($treatmentPlan->price, 2) }}</span>
                                                    <small class="text-muted">(Added by {{ $treatmentPlan->admin ? $treatmentPlan->admin->name : 'Admin' }} on {{ $treatmentPlan->price_added_at ? $treatmentPlan->price_added_at->format('d-m-Y H:i') : 'N/A' }})</small>
                                                </p>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex flex-column gap-2">
                                                <!-- Status Badge -->
                                                <div class="mb-2">
                                                    @if($treatmentPlan->isPending())
                                                        <span class="badge bg-label-warning">{{ __('master.pending') }}</span>
                                                    @elseif($treatmentPlan->isAccepted())
                                                        <span class="badge bg-label-success">{{ __('master.accepted') }}</span>
                                                    @elseif($treatmentPlan->isRejected())
                                                        <span class="badge bg-label-danger">{{ __('master.rejected') }}</span>
                                                    @endif
                                                </div>

                                                <!-- Action Buttons for Pending Plans -->
                                                @if($treatmentPlan->isPending() && auth()->user()->role_id == 2)
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-success btn-sm" onclick="acceptTreatmentPlan({{ $treatmentPlan->id }})">
                                                            <i class="icon-base ti tabler-check me-1"></i>{{ __('master.accept') }}
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="showRejectModal({{ $treatmentPlan->id }})">
                                                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.reject') }}
                                                        </button>
                                                    </div>
                                                @endif

                                                <!-- Status Info -->
                                                @if($treatmentPlan->isAccepted())
                                                    <small class="text-success">
                                                        <i class="icon-base ti tabler-check me-1"></i>{{ __('master.accepted_by') }} {{ $treatmentPlan->doctor ? $treatmentPlan->doctor->name : 'Doctor' }} on {{ $treatmentPlan->accepted_at ? $treatmentPlan->accepted_at->format('d-m-Y H:i') : 'N/A' }}
                                                    </small>
                                                @elseif($treatmentPlan->isRejected())
                                                    <small class="text-danger">
                                                        <i class="icon-base ti tabler-x me-1"></i>{{ __('master.rejected_by') }} {{ $treatmentPlan->doctor ? $treatmentPlan->doctor->name : 'Doctor' }} on {{ $treatmentPlan->rejected_at ? $treatmentPlan->rejected_at->format('d-m-Y H:i') : 'N/A' }}
                                                    </small>
                                                    @if($treatmentPlan->rejection_reason)
                                                        <small class="text-muted d-block mt-1">
                                                            <strong>{{ __('master.reason') }}:</strong> {{ $treatmentPlan->rejection_reason }}
                                                        </small>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="icon-base  ti tabler-file-text text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">{{ __('master.no_treatment_plans_available') }}</p>
                                <small class="text-muted">{{ __('master.treatment_plans_will_appear_here_when_uploaded_by_technician') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- 5. FINITION (IRP + 3D) — finition request                    --}}
            {{-- ============================================================ --}}
            <div class="col-12 order-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title mb-1">{{ __('master.finition') }}</h5>
                            <small class="text-muted">{{ __('master.finition_files_description') }}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#demandFinitionForm">
                            <i class="icon-base ti tabler-flag-2 me-1"></i>
                            {{ $case->finition_requested_at ? __('master.request_finition_again') : __('master.demand_finition') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="collapse mb-3" id="demandFinitionForm">
                            <form action="{{ route('doctor.cases.request_finition', $case->id) }}" method="POST" class="border rounded p-3">
                                @csrf
                                <label for="finition_request_note" class="form-label fw-bold">{{ __('master.finition_request_note') }}</label>
                                <textarea name="finition_request_note" id="finition_request_note" rows="3" class="form-control mb-2" placeholder="{{ __('master.finition_request_note_placeholder') }}"></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="icon-base ti tabler-send me-1"></i>{{ __('master.send_request') }}
                                </button>
                            </form>
                        </div>

                        @include('partials.finition_content', ['case' => $case])
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- COMMENTS                                                     --}}
            {{-- ============================================================ --}}
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
                                    <img src="{{ $comment->user->photo_url }}" alt="User Photo" class="img-fluid rounded-circle">
                                </div>
                                <div class="col-md-11">
                                    <p class="mb-1"><span class="badge bg-label-primary">{{ $comment->user->name }} - {{ ucfirst($comment->user->role->name ?? 'Doctor') }}</span> <small class="text-body-secondary">{{ __('master.date') }} : {{ $comment->created_at->format('d-m-Y H:i:s') }}</small> </p>
                                    <p class="mb-0"><strong>{{ __('master.comment') }} :</strong> {{ $comment->comment }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>

                </div>{{-- /.row g-6 (main column inner) --}}
            </div>{{-- /.col-lg-8 (right side) --}}

        </div>

        @include('doctor.cases.treatment_request.modal')

        <!-- Remove File Confirmation Modal -->
        <div class="modal fade" id="removeFileModal" tabindex="-1" aria-labelledby="removeFileModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="removeFileModalLabel">
                            <i class="icon-base ti tabler-trash me-2"></i>{{ __('master.remove_file') }}
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
                        <p>{{ __('master.remove_file_confirm_text') }} <strong id="fileName"></strong>?</p>
                        <p class="text-muted small">{{ __('master.remove_file_action_irreversible') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmRemoveFile">
                            <i class="icon-base ti tabler-trash me-1"></i>{{ __('master.remove_file') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Price Modal -->
        <div class="modal fade" id="rejectPriceModal" tabindex="-1" aria-labelledby="rejectPriceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectPriceModalLabel">
                            <i class="icon-base ti tabler-x me-2"></i>{{ __('master.reject_price') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="icon-base ti tabler-alert-triangle me-2"></i>
                            <div>
                                {{ __('master.reject_price_warning') }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="price_rejection_reason" class="form-label">{{ __('master.rejection_reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="price_rejection_reason" name="price_rejection_reason" rows="4" placeholder="{{ __('master.enter_rejection_reason') }}" required></textarea>
                            <div class="form-text">{{ __('master.rejection_reason_help') }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmRejectPrice">
                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.reject_price') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Treatment Plan Modal -->
        <div class="modal fade" id="rejectTreatmentPlanModal" tabindex="-1" aria-labelledby="rejectTreatmentPlanModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectTreatmentPlanModalLabel">
                            <i class="icon-base ti tabler-x me-2"></i>{{ __('master.reject_treatment_plan') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="icon-base ti tabler-alert-triangle me-2"></i>
                            <div>
                                {{ __('master.reject_treatment_plan_warning') }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">{{ __('master.rejection_reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" placeholder="{{ __('master.enter_rejection_reason') }}" required></textarea>
                            <div class="form-text">{{ __('master.rejection_reason_help') }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmRejectTreatmentPlan">
                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.reject_treatment_plan') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @push('scripts')
    <script src="{{ asset('assets/js/dataTables-all.js') }}"></script>
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
                    url: "{{ route('doctor.cases.add_comment') }}",
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
                                            '<p class="mb-1"><span class="badge bg-label-primary">' + response.user_name + ' - ' + response.user_role + '</span> <small class="text-body-secondary">{{ __("master.date") }}: ' + response.date + '</small></p>' +
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

            // Price Acceptance Functions
            window.acceptPrice = function(caseId) {
                if (confirm("{{ __('master.confirm_accept_price') }}")) {
                    $.ajax({
                        url: "{{ route('doctor.cases.accept_price', ':id') }}".replace(':id', caseId),
                        type: "POST",
                        data: {
                            _token: token
                        },
                        success: function(response) {
                            toastr.success("{{ __('master.price_accepted_successfully') }}");
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || "{{ __('master.error_accepting_price') }}");
                        }
                    });
                }
            };

            // Price Rejection Modal Functions
            let caseIdToReject = null;

            window.showRejectPriceModal = function(caseId) {
                caseIdToReject = caseId;
                $('#price_rejection_reason').val('');
                $('#rejectPriceModal').modal('show');
            };

            window.rejectPrice = function(caseId, reason) {
                $.ajax({
                    url: "{{ route('doctor.cases.reject_price', ':id') }}".replace(':id', caseId),
                    type: "POST",
                    data: {
                        _token: token,
                        reason: reason
                    },
                    success: function(response) {
                        toastr.success("{{ __('master.price_rejected_successfully') }}");
                        $('#rejectPriceModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "{{ __('master.error_rejecting_price') }}");
                    }
                });
            };

            // Treatment Plan Functions
            window.acceptTreatmentPlan = function(treatmentPlanId) {
                if (confirm("{{ __('master.confirm_accept_treatment_plan') }}")) {
                    $.ajax({
                        url: "{{ route('doctor.treatment-plan.accept') }}",
                        type: "POST",
                        data: {
                            _token: token,
                            treatment_plan_id: treatmentPlanId
                        },
                        success: function(response) {
                            toastr.success("{{ __('master.treatment_plan_accepted_successfully') }}");
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || "{{ __('master.error_accepting_treatment_plan') }}");
                        }
                    });
                }
            };

            // Treatment Plan Rejection Modal Functions
            let treatmentPlanIdToReject = null;

            window.showRejectModal = function(treatmentPlanId) {
                treatmentPlanIdToReject = treatmentPlanId;
                $('#rejection_reason').val('');
                $('#rejectTreatmentPlanModal').modal('show');
            };

            window.acceptTreatmentPlan = function(treatmentPlanId) {
                if (confirm("{{ __('master.confirm_accept_treatment_plan') }}")) {
                    $.ajax({
                        url: "{{ route('doctor.treatment-plan.accept') }}",
                        type: "POST",
                        data: {
                            _token: token,
                            treatment_plan_id: treatmentPlanId
                        },
                        success: function(response) {
                            toastr.success("{{ __('master.treatment_plan_accepted_successfully') }}");
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || "{{ __('master.error_accepting_treatment_plan') }}");
                        }
                    });
                }
            };

            // Handle reject treatment plan form submission
            $('#confirmRejectTreatmentPlan').on('click', function() {
                const rejectionReason = $('#rejection_reason').val().trim();
                
                if (!rejectionReason) {
                    toastr.error("{{ __('master.rejection_reason_required') }}");
                    return;
                }

                if (!treatmentPlanIdToReject) {
                    toastr.error("{{ __('master.invalid_treatment_plan_id') }}");
                    return;
                }

                const submitBtn = $(this);
                const originalText = submitBtn.html();
                
                // Disable button and show loading
                submitBtn.prop('disabled', true).html('<i class="icon-base ti tabler-loader me-1"></i>{{ __("master.rejecting") }}...');
                
                $.ajax({
                    url: "{{ route('doctor.treatment-plan.reject') }}",
                    type: "POST",
                    data: {
                        _token: token,
                        treatment_plan_id: treatmentPlanIdToReject,
                        rejection_reason: rejectionReason
                    },
                    success: function(response) {
                        toastr.success("{{ __('master.treatment_plan_rejected_successfully') }}");
                        $('#rejectTreatmentPlanModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "{{ __('master.error_rejecting_treatment_plan') }}");
                    },
                    complete: function() {
                        // Re-enable button
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Handle reject price modal form submission
            $('#confirmRejectPrice').on('click', function() {
                const rejectionReason = $('#price_rejection_reason').val().trim();
                
                if (!rejectionReason) {
                    toastr.error("{{ __('master.rejection_reason_required') }}");
                    return;
                }

                if (!caseIdToReject) {
                    toastr.error("{{ __('master.invalid_case_id') }}");
                    return;
                }

                const submitBtn = $(this);
                const originalText = submitBtn.html();
                
                // Disable button and show loading
                submitBtn.prop('disabled', true).html('<i class="icon-base ti tabler-loader me-1"></i>{{ __("master.rejecting") }}...');
                
                rejectPrice(caseIdToReject, rejectionReason);
                
                // Reset button state after a delay
                setTimeout(function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }, 2000);
            });
            
            // File Removal Functions
            let fileIdToRemove = null;

            window.confirmRemoveFile = function(fileId, fileName, fileType) {
                fileIdToRemove = fileId;
                document.getElementById('fileName').textContent = fileName;
                
                const modal = new bootstrap.Modal(document.getElementById('removeFileModal'));
                modal.show();
            };

            document.getElementById('confirmRemoveFile').addEventListener('click', function() {
                if (fileIdToRemove) {
                    removeFile(fileIdToRemove);
                }
            });

            function removeFile(fileId) {
                const removeBtn = document.getElementById('confirmRemoveFile');
                const originalText = removeBtn.innerHTML;
                
                // Show loading state
                removeBtn.innerHTML = '<i class="icon-base ti tabler-loader me-1"></i>{{ __("master.removing") }}...';
                removeBtn.disabled = true;

                fetch(`{{ route('doctor.files.remove', '') }}/${fileId}`, {
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
                        const modal = bootstrap.Modal.getInstance(document.getElementById('removeFileModal'));
                        modal.hide();
                        
                        // Show success message
                        toastr.success(data.message || '{{ __("master.file_removed_successfully") }}');
                        
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
                    toastr.error('{{ __("master.failed_to_remove_file") }}: ' + error.message);
                })
                .finally(() => {
                    // Reset button state
                    removeBtn.innerHTML = originalText;
                    removeBtn.disabled = false;
                    fileIdToRemove = null;
                });
            }
            
        });

       
                  
    </script>
    @endpush
</x-app-layout>
