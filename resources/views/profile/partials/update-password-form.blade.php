<div class="col-md-6 col-xxl-6 mb-6">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1"> <b>{{ __('Update Password') }}</b></h5>
            <p class="card-subtitle">  {{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
          </div>
         
        </div>
        <div class="card-body">
            <div class="row"> 
                <div class="col-md-12">

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label"><b>{{ __('Current Password') }}</b></label>
            <input type="password" name="current_password" class="form-control" placeholder="{{ __('Current Password') }}">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label"><b>{{ __('New Password') }}</b></label>
            <input type="password" name="password" class="form-control" placeholder="{{ __('New Password') }}">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />

           
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label"><b>{{ __('Confirm Password') }}</b></label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('Confirm Password') }}">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>


        <div class="flex items-center gap-4 mt-4">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
                </div>  
            </div>
        </div>
    </div>
</div>
