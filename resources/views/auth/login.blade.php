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

        <!-- Login -->
        <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
          <div class="w-px-400 mx-auto mt-12 pt-5">
            <h4 class="mb-1">Welcome to AlignRight! 👋</h4>
            <p class="mb-6">Please sign-in to your account and start the adventure</p>
            <x-auth-session-status class="mb-4" :status="session('status')" />
            <form method="POST" action="{{ route('login') }}" class="mb-6" id="formAuthentication">
                @csrf
               
           
              <div class="mb-6 form-control-validation">
                <label for="email" class="form-label">Email or Username</label>
                  <input id="email" class="form-control" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                  <x-input-error :messages="$errors->get('email')" class="mt-2" />

              </div>
              <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                    <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                  <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
              </div>
              <div class="my-8">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0 ms-2">
                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember"/>
                    <label class="form-check-label" for="remember_me"> Remember Me </label>
                  </div>
                  <a href="{{ route('password.request') }}">
                    <p class="mb-0">Forgot Password?</p>
                  </a>
                </div>
              </div>
              <button class="btn btn-primary d-grid w-100">Sign in</button>
            </form>

            <p class="text-center">
              <span>New on our platform?</span>
              <a href="{{ route('register') }}">
                <span>Create an account</span>
              </a>
            </p>

            
           
          </div>
        </div>
        <!-- /Login -->
      </div>

    
</x-guest-layout>
