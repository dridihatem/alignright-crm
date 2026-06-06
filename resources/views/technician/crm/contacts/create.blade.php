<x-app-layout>
    @push('styles')
    @endpush

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('master.create_contact') }}</h5>
                        <a href="{{ route('technician.crm.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> {{ __('master.back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('technician.crm.contacts.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">{{ __('master.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">{{ __('master.email') }}</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">{{ __('master.phone') }}</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="company" class="form-label">{{ __('master.company') }}</label>
                                    <input type="text" class="form-control @error('company') is-invalid @enderror" 
                                           id="company" name="company" value="{{ old('company') }}">
                                    @error('company')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="position" class="form-label">{{ __('master.position') }}</label>
                                    <input type="text" class="form-control @error('position') is-invalid @enderror" 
                                           id="position" name="position" value="{{ old('position') }}">
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">{{ __('master.status') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="">{{ __('master.select_status') }}</option>
                                        <option value="prospect" {{ old('status') == 'prospect' ? 'selected' : '' }}>{{ __('master.prospect') }}</option>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>{{ __('master.active') }}</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ __('master.inactive') }}</option>
                                        <option value="customer" {{ old('status') == 'customer' ? 'selected' : '' }}>{{ __('master.customer') }}</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="source" class="form-label">{{ __('master.source') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('source') is-invalid @enderror" id="source" name="source" required>
                                        <option value="">{{ __('master.select_source') }}</option>
                                        <option value="website" {{ old('source') == 'website' ? 'selected' : '' }}>{{ __('master.website') }}</option>
                                        <option value="referral" {{ old('source') == 'referral' ? 'selected' : '' }}>{{ __('master.referral') }}</option>
                                        <option value="cold_call" {{ old('source') == 'cold_call' ? 'selected' : '' }}>{{ __('master.cold_call') }}</option>
                                        <option value="email" {{ old('source') == 'email' ? 'selected' : '' }}>{{ __('master.email') }}</option>
                                        <option value="social_media" {{ old('source') == 'social_media' ? 'selected' : '' }}>{{ __('master.social_media') }}</option>
                                        <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>{{ __('master.other') }}</option>
                                    </select>
                                    @error('source')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="assigned_to" class="form-label">{{ __('master.assigned_to') }}</label>
                                    <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                                        <option value="">{{ __('master.select_user') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">{{ __('master.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('technician.crm.index') }}" class="btn btn-secondary me-2">
                                    {{ __('master.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i> {{ __('master.create_contact') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @endpush
</x-app-layout>
