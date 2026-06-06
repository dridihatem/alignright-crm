<x-app-layout>
    <x-slot name="title">{{ __('master.edit_commercial') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.commercial.list') }}">{{ __('master.commercial_list') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.edit_commercial') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="icon-base ti tabler-user-edit me-2"></i>
                    {{ __('master.edit_commercial') }}
                </h5>
                <small class="text-muted">{{ __('master.edit_commercial_user') }}: {{ $user->name }}</small>
            </div>
        </div>

        <!-- Form -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.commercial.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('master.basic_information') }}</h6>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('master.name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ __('master.email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">{{ __('master.phone') }}</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Security & Status -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('master.security_status') }}</h6>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('master.password') }}</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                <small class="form-text text-muted">{{ __('master.leave_blank_to_keep_current') }}</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">{{ __('master.confirm_password') }}</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">{{ __('master.status') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="">{{ __('master.select_status') }}</option>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>{{ __('master.active') }}</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>{{ __('master.inactive') }}</option>
                                    <option value="pending" {{ old('status', $user->status) == 'pending' ? 'selected' : '' }}>{{ __('master.pending') }}</option>
                                    <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>{{ __('master.suspended') }}</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">{{ __('master.additional_information') }}</h6>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">{{ __('master.address') }}</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.commercial.list') }}" class="btn btn-outline-secondary">
                            <i class="icon-base ti tabler-arrow-left me-1"></i>
                            {{ __('master.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-check me-1"></i>
                            {{ __('master.update_commercial') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

