<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/fullcalendar/fullcalendar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-calendar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" /> 
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" /> 
    <style>

    .fc-sidebarToggle-button{display:none!important;}
       .fc .fc-view-harness .fc-event {
    border: 0;
    border-radius: 0.25rem;
    font-size: 0.7rem!important;
    font-weight: 400;
    padding-block: 0.25rem;
    padding-inline: 0.75rem;
}

        .select2-container--default .select2-selection--single {
            height: 40px;
        }
        .select2-container {
        z-index: 9999 !important;
    }
    .select2-dropdown {
        z-index: 9999 !important;
    }
    .flatpickr-calendar.open {
    z-index: 999999;
}
    </style>


 
    @endpush
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card app-calendar-wrapper">
            <div class="row g-0">
              <!-- Calendar Sidebar -->
              <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
                <div class="border-bottom p-6 my-sm-0 mb-4">
                  <button
                    class="btn btn-primary btn-toggle-sidebar w-100"
                    data-bs-toggle="modal"
                    data-bs-target="#addEventSidebar"
                    aria-controls="addEventSidebar">
                    <i class="icon-base ti tabler-plus icon-16px me-2"></i>
                    <span class="align-middle">{{ __('master.add_event') }}</span>
                  </button>
                </div>
                <div class="px-3 pt-2">
                  <!-- inline calendar (flatpicker) -->
                  <div class="inline-calendar"></div>
                </div>
              
              </div>
              <!-- /Calendar Sidebar -->

              <!-- Calendar & Modal -->
              <div class="col app-calendar-content">
                <div class="card shadow-none border-0">
                  <div class="card-body pb-0">
                    <!-- FullCalendar -->
                    <div id="calendar"></div>
                  </div>
                </div>
              
                <!-- FullCalendar Offcanvas -->
                
              </div>
             




                        
                      
              
            </div>
          </div>
</div>
 <!-- /Calendar & Modal -->
             <!-- ADD EVENT SIDEBAR -->
             <div class="modal fade" id="addEventSidebar" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addEventSidebarTitle">Add Event</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form method="POST" action="{{ route('doctor.calendar.store') }}">
                        @csrf
                        <div class="row">
                          <div class="col-12 mb-3">
                            <label class="form-label" for="eventTitle1">{{ __('master.event_title') }}</label>
                            <input type="text" name="title" id="eventTitle1" class="form-control" required>
                          </div>
                          <div class="col-12 mb-3">
                            <label class="form-label" for="eventDescription1">{{ __('master.event_description') }}</label>
                            <textarea name="description" id="eventDescription1" class="form-control" rows="3"></textarea>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label" for="eventStartDate1">{{ __('master.event_start_date') }}</label>
                            <input type="text" name="start" id="eventStartDate1" class="form-control" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label" for="eventEndDate1">{{ __('master.event_end_date') }}</label>
                            <input type="text" name="end" id="eventEndDate1" class="form-control " required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label" for="eventColor1">{{ __('master.event_color') }}</label>
                            <select name="color" id="eventColor1" class="form-select select2">
                              <option value="primary">Primary</option>
                              <option value="secondary">Secondary</option>
                              <option value="success">Success</option>
                              <option value="danger">Danger</option>
                              <option value="warning">Warning</option>
                              <option value="info">Info</option>
                              <option value="dark">Dark</option>
                            </select>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label" for="eventUrl1">{{ __('master.event_url') }}</label>
                            <input type="url" name="event_url" id="eventUrl1" class="form-control">
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">{{ __('master.save_changes') }}</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
   
   
                 <!-- Event Modal -->
                 <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
                   <div class="modal-dialog modal-lg" role="document">
                     <div class="modal-content">
                       <div class="modal-header">
                         <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body">
                         <form id="eventForm">
                           <input type="hidden" id="eventId">
                           <div class="row">
                             <div class="col-12 mb-3">
                               <label class="form-label" for="eventTitle">Title</label>
                               <input type="text" id="eventTitle" class="form-control" required>
                             </div>
                             <div class="col-12 mb-3">
                               <label class="form-label" for="eventDescription">Description</label>
                               <textarea id="eventDescription" class="form-control" rows="3"></textarea>
                             </div>
                             <div class="col-md-6 mb-3">
                               <label class="form-label" for="eventStart">Start Date & Time</label>
                               <input type="text" id="eventStartDate" class="form-control" required>
                             </div>
                             <div class="col-md-6 mb-3">
                               <label class="form-label" for="eventEnd">End Date & Time</label>
                               <input type="text" id="eventEndDate" class="form-control " required>
                             </div>
                             <div class="col-md-6 mb-3">
                               <label class="form-label" for="eventColor">Color</label>
                               <select id="eventColor" class="form-select select2">
                                 <option value="primary">Primary</option>
                                 <option value="secondary">Secondary</option>
                                 <option value="success">Success</option>
                                 <option value="danger">Danger</option>
                                 <option value="warning">Warning</option>
                                 <option value="info">Info</option>
                                 <option value="dark">Dark</option>
                               </select>
                             </div>
                             <div class="col-md-6 mb-3">
                               <label class="form-label" for="eventUrl">{{ __('master.event_url') }}</label>
                               <input type="event_url" id="eventUrl" class="form-control">
                             </div>
                           </div>
                           <div class="row">
                             <div class="col-12 text-end">
                               <button type="button" class="btn btn-danger me-2" id="deleteEvent">{{ __('master.delete_event') }}</button>
                               <button type="submit" class="btn btn-primary">{{ __('master.save_changes') }}</button>
                             </div>
                           </div>
                         </form>
                       </div>
                     </div>
                   </div>
                 </div>
@push('scripts')

<script src="{{ asset('assets/vendor/libs/fullcalendar/fullcalendar.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/js/calendar.js') }}"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2();
  });

</script>
<script>
  window.translations = {
      calendar_deleted_confirm: "{{ __('master.calendar_deleted_confirm') }}"
  };
</script>


@endpush
</x-app-layout>

