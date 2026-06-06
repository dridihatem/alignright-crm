<x-app-layout>
    <x-slot name="title">{{ __('master.edit_doctor') }} - {{ $doctor->name }}</x-slot>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.doctors.list') }}">Doctors</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.doctors.show', $doctor->id) }}">{{ $doctor->name }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('master.edit') }}</li>
                    </ol>
                </nav>

                <!-- Page Header -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-user-edit me-2"></i>
                                {{ __('master.edit_doctor') }}: {{ $doctor->name }}
                            </h5>
                            <small class="text-muted">{{ __('master.update_doctor_information') }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-eye me-1"></i> {{ __('master.view') }}
                            </a>
                            <a href="{{ route('admin.doctors.list') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> {{ __('master.back') }}
                            </a>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.doctors.update', $doctor->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Main Information -->
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        {{ __('master.basic_information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">{{ __('master.full_name') }} <span class="text-danger">*</span></label>
                                                <input type="text" 
                                                       class="form-control @error('name') is-invalid @enderror" 
                                                       id="name" 
                                                       name="name" 
                                                       value="{{ old('name', $doctor->name) }}" 
                                                       required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">{{ __('master.email') }} <span class="text-danger">*</span></label>
                                                <input type="email" 
                                                       class="form-control @error('email') is-invalid @enderror" 
                                                       id="email" 
                                                       name="email" 
                                                       value="{{ old('email', $doctor->email) }}" 
                                                       required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">{{ __('master.phone') }}</label>
                                                <input type="tel" 
                                                       class="form-control @error('phone') is-invalid @enderror" 
                                                       id="phone" 
                                                       name="phone" 
                                                       value="{{ old('phone', $doctor->phone) }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">{{ __('master.status') }}</label>
                                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                                    <option value="active" {{ old('status', $doctor->status) === 'active' ? 'selected' : '' }}>{{ __('master.active') }}</option>
                                                    <option value="inactive" {{ old('status', $doctor->status) === 'inactive' ? 'selected' : '' }}>{{ __('master.inactive') }}</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">{{ __('master.address') }}</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="3">{{ old('address', $doctor->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-lock me-2"></i>
                                        {{ __('master.account_information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password" class="form-label">{{ __('master.new_password') }}</label>
                                                <input type="password" 
                                                       class="form-control @error('password') is-invalid @enderror" 
                                                       id="password" 
                                                       name="password">
                                                <small class="text-muted">{{ __('master.leave_blank_to_keep_current') }}</small>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password_confirmation" class="form-label">{{ __('master.confirm_new_password') }}</label>
                                                <input type="password" 
                                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                                       id="password_confirmation" 
                                                       name="password_confirmation">
                                                @error('password_confirmation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="send_credentials" name="send_credentials" value="1">
                                            <label class="form-check-label" for="send_credentials">
                                                {{ __('master.send_new_credentials_email') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-stethoscope me-2"></i>
                                        {{ __('master.professional_information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="specialization" class="form-label">{{ __('master.specialization') }}</label>
                                                <input type="text" 
                                                       class="form-control @error('specialization') is-invalid @enderror" 
                                                       id="specialization" 
                                                       name="specialization" 
                                                       value="{{ old('specialization', $doctor->specialization) }}">
                                                @error('specialization')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="license_number" class="form-label">{{ __('master.license_number') }}</label>
                                                <input type="text" 
                                                       class="form-control @error('license_number') is-invalid @enderror" 
                                                       id="license_number" 
                                                       name="license_number" 
                                                       value="{{ old('license_number', $doctor->license_number) }}">
                                                @error('license_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="bio" class="form-label">{{ __('master.biography') }}</label>
                                        <textarea class="form-control @error('bio') is-invalid @enderror" 
                                                  id="bio" 
                                                  name="bio" 
                                                  rows="4">{{ old('bio', $doctor->bio) }}</textarea>
                                        @error('bio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="col-md-4">
                            <!-- Profile Photo -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-camera me-2"></i>
                                        {{ __('master.profile_photo') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="position-relative d-inline-block">
                                            <img id="photoPreview" 
                                                 src="{{ $doctor->photo_url }}" 
                                                 alt="Profile Photo" 
                                                 class="rounded-circle mb-3" 
                                                 width="150" height="150"
                                                 style="object-fit: cover;">
                                                                                         <div class="position-absolute bottom-0 end-0">
                                                 <label for="photo" class="btn btn-primary btn-sm rounded-circle">
                                                     <i class="fas fa-image"></i>
                                                 </label>
                                             </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <input type="file" 
                                               class="form-control @error('photo') is-invalid @enderror" 
                                               id="photo" 
                                               name="photo" 
                                               accept="image/*"
                                               onchange="previewPhoto(this)">
                                        @error('photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">
                                        {{ __('master.photo_requirements') }}
                                    </small>
                                </div>
                            </div>

                            <!-- Role Assignment -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-tag me-2"></i>
                                        {{ __('master.role_assignment') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="role_id" class="form-label">{{ __('master.role') }}</label>
                                        <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                            <option value="">{{ __('master.select_role') }}</option>
                                            <option value="2" {{ old('role_id', $doctor->role_id) == 2 ? 'selected' : '' }}>{{ __('master.doctor') }}</option>
                                        </select>
                                        @error('role_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> {{ __('master.update_doctor') }}
                                        </button>
                                        <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i> {{ __('master.cancel') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
@push('scripts')
<script>
// Photo preview functionality
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Password confirmation validation
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password && confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('password_confirmation');
    if (confirmPassword.value) {
        if (this.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.breadcrumb {
    background: none;
    padding: 0;
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.position-relative {
    position: relative;
}

.position-absolute {
    position: absolute;
}

.bottom-0 {
    bottom: 0;
}

.end-0 {
    right: 0;
}
</style>
@endpush

</x-app-layout>
