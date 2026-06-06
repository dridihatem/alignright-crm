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

        <!-- Forgot Password -->
        <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 pt-5">
                <h4 class="mb-1">Forgot Password? 🔒</h4>
                <p class="mb-6">Enter your email and we'll send you instructions to reset your password</p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="mb-6" id="formAuthentication">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-6 form-control-validation">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" class="form-control" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <button class="btn btn-primary d-grid w-100">Send Reset Link</button>
                </form>

                <p class="text-center">
                    <span>Back to</span>
                    <a href="{{ route('login') }}">
                        <span>Sign in</span>
                    </a>
                </p>
            </div>
        </div>
        <!-- /Forgot Password -->
    </div>
</x-guest-layout>
