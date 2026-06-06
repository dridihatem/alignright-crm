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
                        <label for="name" class="form-label">{{ __('master.name') }}</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                    <div class="mb-3">
                        <label for="type_file" class="form-label">{{ __('master.type_file') }}</label>
                        <select class="form-control select2" id="type_file" name="type_file">
                            <option value="">{{ __('master.select_type_file') }}</option>
                            <option value="pdf">{{ __('master.pdf') }}</option>
                            <option value="link">{{ __('master.link') }}</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="file_div" style="display: none;">
                        <label for="file" class="form-label">{{ __('master.file') }}</label>
                        <input type="file" class="form-control" id="file" name="file">
                    </div>
                    <div class="mb-3" id="link_div" style="display: none;">
                        <label for="link" class="form-label">{{ __('master.link') }}</label>
                        <input type="text" class="form-control" id="link" name="link">
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">{{ __('master.add') }}</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>