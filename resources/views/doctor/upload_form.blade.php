<x-app-layout>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">{{ __('upload.title') }}</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                            @if(session('file_url'))
                                <div class="mt-2">
                                    <strong>{{ __('upload.files.file_name') }}:</strong>
                                    <span class="d-block">{{ session('file_name') }}</span>
                                    <strong>URL:</strong>
                                    <a href="{{ session('file_url') }}" target="_blank" class="d-block text-break">
                                        {{ session('file_url') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('doctor.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">{{ __('upload.form.select_file') }}</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required>
                            @error('file')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('upload.form.description') }}</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">{{ __('upload.form.upload') }}</button>
                        </div>
                    </form>

                    @if($files->count() > 0)
                        <div class="mt-4">
                            <h4>{{ __('upload.files.title') }}</h4>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('upload.files.file_name') }}</th>
                                            <th>{{ __('upload.files.description') }}</th>
                                            <th>{{ __('upload.files.uploaded_at') }}</th>
                                            <th>{{ __('upload.files.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($files as $file)
                                            <tr>
                                                <td>{{ $file->original_name }}</td>
                                                <td>{{ $file->description ?? __('upload.messages.no_description') }}</td>
                                                <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <a href="{{ $file->url }}" target="_blank" class="btn btn-sm btn-primary">
                                                        {{ __('upload.files.view') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>