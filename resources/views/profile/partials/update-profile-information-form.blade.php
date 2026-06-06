<div class="col-md-6 col-xxl-6 mb-6">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1"> <b>{{ __('Profile Information') }}</b></h5>
            <p class="card-subtitle"> {{ __("Update your account's profile information and email address.") }}</p>
          </div>
        </div>
        <div class="card-body">
            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>
        
            <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                @csrf
                @method('patch')
        
                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                        <div class="card-body">
                        <div class="mb-3">
                            <label for="photo" class="form-label"><b>{{ __('Profile Photo') }}</b></label>
                            <div class="d-flex flex-column align-items-center">
                                <div class="mb-2">
                                    <img src="{{ $user->photo_url }}" alt="Profile Photo" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                <div class="mt-2">
                                    <small class="text-muted">{{ __('Allowed file types: JPG, PNG, GIF. Max size: 2MB') }}</small>
                                </div>
                                <div class="file-preview mt-2" style="display: none;">
                                    <img src="" alt="Preview" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                            </div>
                        </div>
                        </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        @if(auth()->user()->isDoctor())  
                        <div class="mb-3">
                            <label for="name" class="form-label"><b>{{ __('master.code') }}</b></label>
                            <input type="text" name="code_parrent" class="form-control" placeholder="{{ __('Code') }}" value="{{ old('code_parrent', $user->code_parrent) }}" readonly>
                            <x-input-error class="mt-2" :messages="$errors->get('code_parrent')" />
                        </div>
                        @endif
                        <div class="mb-3">
                            <label for="name" class="form-label"><b>{{ __('master.name') }}</b></label>
                            <input type="text" name="name" class="form-control" placeholder="{{ __('master.name') }}" value="{{ old('name', $user->name) }}">
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                
                        <div class="mb-3">
                            <label for="email" class="form-label"><b>{{ __('master.email') }}</b></label>
                            <input type="email" name="email" class="form-control" placeholder="{{ __('master.email') }}" value="{{ old('email', $user->email) }}">
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                        <div class="mb-3">
                                <button type="submit" class="btn btn-primary">{{ __('master.save') }}</button>
        
                                @if (session('status') === 'profile-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600"
                                    >{{ __('Saved.') }}</p>
                                @endif
                           
                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div>
                                    <p class="text-sm mt-2 text-gray-800">
                                        {{ __('Your email address is unverified.') }}
                
                                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ __('Click here to re-send the verification email.') }}
                                        </button>
                                    </p>
                
                                    @if (session('status') === 'verification-link-sent')
                                        <p class="mt-2 font-medium text-sm text-green-600">
                                            {{ __('A new verification link has been sent to your email address.') }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
        
              
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const previewContainer = document.querySelector('.file-preview');
    const previewImage = previewContainer.querySelector('img');

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                toastr()->error('File size must be less than 2MB');
                this.value = '';
                return;
            }

            // Check file type
            if (!file.type.match('image.*')) {
                toastr()->error('Please select an image file');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    });
});
</script>
@endpush
            
            
            
            
           