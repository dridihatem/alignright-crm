<x-app-layout>
    @push('styles')
    @endpush
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
            <div class="col-md-12 col-xxl-12 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between">
                        <div class="card-title mb-0">
                            <h5 class="mb-1">{{ __('master.update_laboratory') }}</h5>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('doctor.laboratory.update', $laboratory->id) }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">{{ __('master.laboratory_name') }}</label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="{{ __('master.laboratory_name') }}" value="{{old('name', $laboratory->name) }}">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">{{ __('master.laboratory_email') }}</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="{{ __('master.laboratory_email') }}" value="{{old('email', $laboratory->email) }}">
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">{{ __('master.laboratory_password') }}</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="{{ __('master.laboratory_password') }}" value="">
                                        
                                        <span  style="font-size: 11px;color: var(--bs-primary);;">{{ __('master.laboratory_password_confirmation_hint') }}</span>
                                        @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">{{ __('master.laboratory_password_confirmation') }}</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="{{ __('master.laboratory_password_confirmation') }}" value="">
                                     
                                        @error('password_confirmation')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">{{ __('master.update_laboratory') }}</button>
                                </div>

                                
                                
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

