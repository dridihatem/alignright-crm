<x-app-layout>
    @push('styles')
    
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-email.css') }}" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
        }
        .select2-container {
        z-index: 9999 !important;
    }
    .select2-dropdown {
        z-index: 99999 !important;
    }
    </style>
    
    @endpush
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="app-email card">
            <div class="row g-0">
              <!-- Email Sidebar -->
              <div class="col app-email-sidebar border-end flex-grow-0" id="app-email-sidebar">
               
                <!-- Email Filters -->
                <div class="email-filters pt-4 pb-2 ps ps--active-y">
                  <!-- Email Filters: Folder -->
                  <ul class="email-filter-folders list-unstyled">
                    <li class="active d-flex justify-content-between align-items-center mb-1" data-target="inbox">
                      <a href="javascript:void(0);" class="d-flex flex-wrap align-items-center">
                        <i class="icon-base ti tabler-mail"></i>
                        <span class="align-middle ms-2">{{ __('master.inbox') }}</span>
                      </a>
                      <div class="badge bg-label-primary rounded-pill">{{ $tickets->count() }}</div>
                    </li>
                 
                   
                   
                  </ul>
                  <!-- Email Filters: Labels -->
                  
                  <!--/ Email Filters -->
                <div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 0px; height: 622px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 621px;"></div></div></div>
              </div>
              <!--/ Email Sidebar -->

              <!-- Emails List -->
              <div class="col app-emails-list">
                <div class="card shadow-none border-0 rounded-0">
                 
                  <!-- Email List: Items -->
                  <div class="email-list pt-0 ps ps--active-y">
                      @if($tickets->count() > 0)
                    <ul class="list-unstyled m-0">
                       
                        @foreach ($tickets as $ticket)
                        <li class="email-list-item email-marked-read d-flex align-items-center" data-sent="true" data-bs-toggle="sidebar" data-target="#app-email-view">
                            <div class="d-flex align-items-center w-100">
                            
                              <span class="ms-sm-3 me-4 d-sm-inline-block d-none"><i class="email-list-item-bookmark icon-base ti tabler-star icon-md cursor-pointer ms-1" style="color: {{ $ticket->priority == 'high' ? '#ff0000' : '' }}"></i></span>
                              <img src="{{ $ticket->user->photo_url }}" alt="user-avatar" class="d-block flex-shrink-0 rounded-circle me-sm-2 me-0" height="32" width="32">
                              <div class="email-list-item-content ms-2 ms-sm-0 me-2">
                                <span class="email-list-item-username me-2 h6">{{ $ticket->user->name }}</span>
                                <span class="email-list-item-subject d-xl-inline-block d-block">
                                  {{ __('master.subject') }} : {{ $ticket->subject }}</span>
                                  <span class="email-list-item-subject d-xl-inline-block d-block">
                                   (<i class="icon-base ti tabler-message-circle"></i> {{ $ticket_replies }})</span>

                                 
                              </div>
                              <div class="email-list-item-meta ms-auto d-flex align-items-center " >
                                @php
                                   $assigned = explode('-', $ticket->assigned_to);
                                   $assignedUser = \App\Models\User::find($assigned[0]);
                                @endphp
                                @if($assignedUser)
                                <ul class="list-unstyled m-0 avatar-group d-flex align-items-center"
                                <li class="avatar avatar-xs pull-up">
                                    <img src="{{ $assignedUser->photo_url }}" alt="user-avatar" class="rounded-circle" height="20" width="20">
                                </li>
                                <span class="email-list-item-username me-2"><small>{{ $assignedUser->name }}</small> - <small class="badge bg-label-primary">{{ $assignedUser->role->name }}</small></span>
                                
                                @endif
                                 </div>

                              <div class="email-list-item-meta ms-auto d-flex align-items-center">
                                @if($ticket->status == 'open')
                                <span class="email-list-item-label badge badge-dot bg-primary d-none d-md-inline-block me-2" data-label="important" style="background-color: {{ $ticket->status == 'open' ? '#8acf04!important' : '#ff0000!important' }}"></span>
                                @else
                                <span class="email-list-item-label badge badge-dot bg-danger d-none d-md-inline-block me-2" data-label="private" style="background-color: {{ $ticket->priority == 'low' ? '#000000!important' : '#ff0000!important' }}"></span>
                                @endif
                                <small class="email-list-item-time text-body-secondary">{{ $ticket->created_at->format('d M Y') }}</small>
                                <ul class="list-inline email-list-item-actions">
                                  <li class="list-inline-item email-unread btn btn-icon btn-text-secondary rounded-pill waves-effect" onclick="open_ticket({{ $ticket->id }})">
                                    <i class="icon-base ti tabler-mail icon-md"></i>
                                  </li>
                                 
                                 
                                </ul>
                              </div>
                            </div>
                          </li>
<!-- Compose Email -->
<div class="modal fade" id="modalEmail-details{{ $ticket->id }}" tabindex="-1" aria-labelledby="modalEmail" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('master.subject') }} : {{ $ticket->subject }}</h5>
          <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              
          
         <form action="{{ route('technician.tickets.reply', $ticket->id) }}" method="post">
            @csrf
            <input type="hidden" name="user_id" value="{{ $ticket->user_id }}">
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
            <div class="row">
              
              
              
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="mb-3">
                  <label for="message" class="form-label">{{ __('master.message') }}</label>
                  <textarea name="message" class="form-control" id="message" placeholder="{{ __('master.message') }}" readonly>{{ $ticket->message }}</textarea>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="mb-3">
                  <label for="message" class="form-label">{{ __('master.reply_message') }}</label>
                  <textarea name="reply_message" class="form-control" id="reply_message" placeholder="{{ __('master.reply_message') }}"></textarea>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('master.send') }}</button>
            </form>
         
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <h5>{{ __('master.ticket_replies') }}</h5>
                <ul class="list-unstyled m-0">
                  @foreach ($ticket_replies_list as $reply)
                  <li class="email-list-item email-marked-read " data-sent="true" data-bs-toggle="sidebar" data-target="#app-email-view">
                    <div class="email-list-item-content ms-2 ms-sm-0 me-2">
                      <span class="email-list-item-username me-2 h6">{{ $reply->user->name }}</span>
                      <span class="email-list-item-subject d-xl-inline-block d-block">
                        {{ $reply->message }}
                      </span>
                    </div>
                    <small class="email-list-item-time text-body-secondary">{{ $reply->created_at->format('d M Y') }}</small>
                  </li>
                  @endforeach
                </ul>
              </div>
            </div>
            </div>
          
        </div>
      </div>
    </div>
  </div>
  <!-- /Compose Email -->
                            
                        @endforeach

                        {{ $tickets->links() }}
                          
                   
                      
                   
                    </ul>
                    @else
                    <ul class="list-unstyled m-0">
                      <li class="email-list-empty text-center d-none">{{ __('master.no_items_found') }}</li>
                    </ul>
                    @endif
                  <div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 0px; height: 588px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 493px;"></div></div><div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 0px; height: 588px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 493px;"></div></div></div>
                </div>
                <div class="app-overlay"></div>
              </div>
              <!-- /Emails List -->

              <!-- Email View -->
            </div>

          </div>

            <!-- Compose Email -->
            

      
    </div>
    @push('scripts')
  
   <script src="{{ asset('assets/js/dataTables-all.js') }}"></script>
   <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
        <script>
            $(document).ready(function(){
                $('.select2').select2();
            });
       
        function open_ticket(id){
            $('#modalEmail-details'+id).modal('show');
        }
       
    </script>
    @endpush
</x-app-layout>
