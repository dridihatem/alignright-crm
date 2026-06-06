<x-app-layout>
    @push('styles')
    @endpush
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
            <div class="col-md-12 col-xxl-12 mb-6">
                <div class="card h-100">
                    <div class="card-header">
                       <a href="{{ route('doctor.cases.show', $treatment_type->case_id) }}" class="btn btn-primary">{{ __('master.back') }}</a>
                    </div>
                    <div class="card-body">
                       <iframe src="{{ google_drive_file_public_url($treatment_type->link) }}" width="100%" height="600px"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    @endpush
</x-app-layout>

