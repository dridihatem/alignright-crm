<x-guest-layout>
    <a href="{{ route('home') }}" class="app-brand auth-cover-brand">
        <img src="{{ asset('assets/img/logo_align.png') }}" alt="logo" class="img-fluid" style="width: 100px;">
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
        <!-- /Left Text -->
        <div class="d-none d-xl-flex col-xl-8 p-0">
          <div class="auth-cover-bg d-flex justify-content-center align-items-center">
           
            <img
            src="{{ asset('assets/img/aligner_teeth.png') }}"
            alt="auth-login-cover"
            class="my-5 auth-illustration animated-touth" />
        

          
          <img
            src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}"
            alt="auth-login-cover"
            class="platform-bg" />
        </div>
        </div>
        <!-- /Left Text -->

        <!-- Register -->
        <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 pt-5">
                <h4 class="mb-1">Welcome to AlignRight! 👋</h4>
                <p class="mb-6">Please create your account and start the adventure</p>

                <form method="POST" action="{{ route('register') }}" class="mb-6" id="formAuthentication">
                    @csrf
                   
                    <input type="hidden" name="role_id" value="2">
                    <!-- Name -->
                    <div class="mb-6 form-control-validation">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" class="form-control" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div class="mb-6 form-control-validation">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" class="form-control" type="email" name="email" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="mb-6 form-password-toggle form-control-validation">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group input-group-merge">
                            <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                            <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6 form-password-toggle form-control-validation">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div class="input-group input-group-merge">
                            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                            <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button class="btn btn-primary d-grid w-100">Register</button>
                </form>

                <p class="text-center">
                    <span>Already have an account?</span>
                    <a href="{{ route('login') }}">
                        <span>Sign in instead</span>
                    </a>
                </p>
            </div>
        </div>
        <!-- /Register -->
    </div>
    @push('scripts')
      
    @endpush
</x-guest-layout>