<div class="modal fade" id="share-modal" tabindex="-1" aria-labelledby="share-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="share-modal-label">{{ __('master.treatment_type_share') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('master.treatment_type_share_link') }}</p>
                <input type="text" class="form-control" id="share-link" readonly>
                <button type="button" class="btn btn-primary mt-3" id="copy-link">{{ __('master.copy') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_treatment_type_modal" tabindex="-1" aria-labelledby="add_treatment_type_modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_treatment_type_modal-label">{{ __('master.add_treatment_type') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add_treatment_type_form" action="{{ route('technician.treatment_types.store', $case->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('master.description') }}</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="{{ __('master.enter_treatment_plan_description') }}"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="irp_file" class="form-label">
                            <i class="icon-base ti tabler-file-type-pdf me-1"></i>{{ __('master.irp_file_pdf') }}
                        </label>
                        <input type="file" class="form-control" id="irp_file" name="irp_file" accept=".pdf">
                        <div class="form-text">
                            <i class="icon-base ti tabler-info-circle me-1"></i>{{ __('master.upload_irp_pdf_file') }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="link_viewer" class="form-label">
                            <i class="icon-base ti tabler-link me-1"></i>{{ __('master.3d_viewer') }} {{ __('master.url') }}
                        </label>
                        <input type="url" class="form-control" id="link_viewer" name="link_viewer" placeholder="https://example.com/3d-viewer">
                        <div class="form-text">
                            <i class="icon-base ti tabler-info-circle me-1"></i>{{ __('master.enter_3d_viewer_url') }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-plus me-1"></i>{{ __('master.add_treatment_plan') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" data-bs-dismiss="modal">
                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.cancel') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>