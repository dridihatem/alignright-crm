{{-- Shared FINITION content: request status, technician description and uploaded finition files.
     Expects: $case (CasePatient) --}}
@php
    $finitionFiles = $case->finitionFiles ?? collect();
    $showRequest = $showRequest ?? true;
@endphp

@if($showRequest && $case->finition_requested_at)
    <div class="alert alert-info d-flex align-items-start mb-3" role="alert">
        <i class="icon-base ti tabler-flag-2 me-2 mt-1"></i>
        <div>
            <strong>{{ __('master.finition_requested') }}</strong>
            <span class="text-muted">— {{ $case->finition_requested_at->format('M d, Y H:i') }}</span>
            @if($case->finition_request_note)
                <p class="mb-0 mt-1">{{ $case->finition_request_note }}</p>
            @endif
        </div>
    </div>
@endif

@if($case->finition_description)
    <div class="mb-3">
        <label class="form-label fw-bold mb-1">{{ __('master.description') }}</label>
        <p class="mb-0">{{ $case->finition_description }}</p>
    </div>
@endif

@if($finitionFiles->count() > 0)
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('master.file') }}</th>
                    <th>{{ __('master.type_file') }}</th>
                    <th>{{ __('master.size_file') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($finitionFiles as $file)
                    <tr>
                        <td>
                            <a href="{{ $file->storage_type == 'google_drive' ? google_drive_image_url($file->url) : $file->url }}" target="_blank" class="text-decoration-none">
                                <i class="icon-base ti tabler-file me-1"></i>{{ $file->name }}
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
                                : '—'
                        }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif(!$case->finition_description && !$case->finition_requested_at)
    <div class="text-center text-muted py-4">
        <i class="icon-base ti tabler-checkup-list" style="font-size: 3rem;"></i>
        <p class="mb-0 mt-2">{{ __('master.no_finition_available') }}</p>
    </div>
@endif
