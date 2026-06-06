<x-app-layout>
    <x-slot name="title">{{ __('master.edit_laboratory') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.laboratories.list') }}">{{ __('master.laboratory_list') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.laboratories.show', $laboratory->id) }}">{{ __('master.laboratory_details') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.edit_laboratory') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('master.edit_laboratory') }}
                </h5>
                <small class="text-muted">{{ __('master.update_laboratory_information') }}</small>
            </div>
        </div>

        <!-- Edit Laboratory Form -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.laboratories.update', $laboratory->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('master.basic_information') }}</h6>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('master.full_name') }} *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $laboratory->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('master.email') }} *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $laboratory->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">{{ __('master.phone') }}</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $laboratory->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">{{ __('master.address') }}</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="3">{{ old('address', $laboratory->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">{{ __('master.status') }} *</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="">{{ __('master.select_status') }}</option>
                                    <option value="active" {{ old('status', $laboratory->status) == 'active' ? 'selected' : '' }}>{{ __('master.active') }}</option>
                                    <option value="inactive" {{ old('status', $laboratory->status) == 'inactive' ? 'selected' : '' }}>{{ __('master.inactive') }}</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('master.account_information') }}</h6>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('master.new_password') }}</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                <small class="form-text text-muted">{{ __('master.leave_blank_to_keep_current') }}</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">{{ __('master.confirm_new_password') }}</label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" name="password_confirmation">
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="send_credentials" name="send_credentials">
                                    <label class="form-check-label" for="send_credentials">
                                        {{ __('master.send_new_credentials_email') }}
                                    </label>
                                </div>
                            </div>

                            <input type="hidden" name="role_id" value="4"> <!-- Laboratory role -->

                            <!-- Professional Information -->
                            <h6 class="mb-3 mt-4">{{ __('master.professional_information') }}</h6>
                            
                            <div class="mb-3">
                                <label for="specialization" class="form-label">{{ __('master.specialization') }}</label>
                                <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                       id="specialization" name="specialization" value="{{ old('specialization', $laboratory->specialization) }}">
                                @error('specialization')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="license_number" class="form-label">{{ __('master.license_number') }}</label>
                                <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                       id="license_number" name="license_number" value="{{ old('license_number', $laboratory->license_number) }}">
                                @error('license_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label">{{ __('master.biography') }}</label>
                                <textarea class="form-control @error('bio') is-invalid @enderror" 
                                          id="bio" name="bio" rows="3">{{ old('bio', $laboratory->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Profile Photo -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="mb-3">{{ __('master.profile_photo') }}</h6>
                            
                            @if($laboratory->photo)
                                <div class="mb-3">
                                    <label class="form-label">{{ __('master.current_photo') }}</label>
                                    <div>
                                        <img src="{{ $laboratory->photo_url }}" 
                                             alt="{{ $laboratory->name }}" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px;">
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <label for="photo" class="form-label">{{ __('master.photo_requirements') }}</label>
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                       id="photo" name="photo" accept="image/*">
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="photoPreview" class="d-none">
                                <label class="form-label">{{ __('master.new_photo_preview') }}</label>
                                <div>
                                    <img id="previewImage" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> {{ __('master.update_laboratory') }}
                                </button>
                                <a href="{{ route('admin.laboratories.show', $laboratory->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> {{ __('master.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Photo preview
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('photoPreview').classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                document.getElementById('photoPreview').classList.add('d-none');
            }
        });

        // Password confirmation validation
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmation = this.value;
            
            if (password && confirmation && password !== confirmation) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
    @endpush
</x-app-layout>
