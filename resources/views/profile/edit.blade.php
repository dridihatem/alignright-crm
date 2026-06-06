<x-app-layout>
  
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
           

                    @include('profile.partials.update-profile-information-form')
                
                    @include('profile.partials.update-password-form')
                
        </div>
    </div>
</x-app-layout>
