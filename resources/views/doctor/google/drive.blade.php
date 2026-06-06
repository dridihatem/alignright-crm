<x-app-layout>
 @push('styles')

 
    @endpush
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card app-calendar-wrapper">
            <div class="row g-0">
            

              <!-- Calendar & Modal -->
              <div class="col app-calendar-content">
                <div class="card shadow-none border-0">
                  <div class="card-body pb-0">
                    <!-- FullCalendar -->
                    @if (!auth()->user()->google_access_token)
    <a href="{{ route('google.auth') }}" class="btn btn-primary">{{ __('master.connect_google_drive') }}</a>
        @else
            <p class="text-success">{{ __('master.google_drive_connected') }}</p>
        @endif
                  </div>
                </div>
              
                <!-- FullCalendar Offcanvas -->
                
              </div>
             




                        
                      
              
            </div>
          </div>
</div>
   
   @push('scripts')

@endpush
</x-app-layout>

