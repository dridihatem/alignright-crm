'use strict';

document.addEventListener('DOMContentLoaded', function () {
 
  (function () {
    const calendarEl = document.getElementById('calendar');
    const inlineCalendar = document.querySelector('.inline-calendar');
    
    const eventStartDate = document.getElementById('eventStartDate');
    const eventEndDate = document.getElementById('eventEndDate');
    const eventStartDate1 = document.getElementById('eventStartDate1');
    const eventEndDate1 = document.getElementById('eventEndDate1');

    // Calendar Colors
    const calendarColors = {
      primary: 'primary',
      secondary: 'secondary',
      success: 'success',
      danger: 'danger',
      warning: 'warning',
      info: 'info',
      dark: 'dark',
      doctor: 'primary',
      technician: 'success',
      laboratory: 'warning'
    };

    // Init FullCalendar
    // ------------------------------------------------
    let calendar = new Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '/doctor/calendar/events',
        plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
        editable: true,
        dragScroll: true,
        dayMaxEvents: 2,
        eventResizableFromStart: true,
      
        headerToolbar: {
          start: 'sidebarToggle, prev,next, title',
          end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        initialDate: new Date(),
        navLinks: true, // can click day/week names to navigate views
        eventClassNames: function ({ event: calendarEvent }) {
          // Get color from event or use default
          const colorName = calendarEvent.extendedProps.color || calendarEvent.color || 'primary';
          return ['bg-label-' + colorName];
        },
        eventClick: function(info) {
          // Prevent default URL navigation
          info.jsEvent.preventDefault();
          
          // Populate modal with event data
          $('#eventTitle').val(info.event.title);
          $('#eventDescription').val(info.event.extendedProps.description || '');
          $('#eventStartDate').val(info.event.start.toLocaleString() ? info.event.start.toISOString().slice(0, 16).replace('T', ' ') : '');
          $('#eventEndDate').val(info.event.end.toLocaleString() ? info.event.end.toISOString().slice(0, 16).replace('T', ' ') : '');
          $('#eventColor').val(info.event.extendedProps.color || info.event.color || 'primary').trigger('change');
          $('#eventUrl').val(info.event.extendedProps.event_url || '');
          $('#eventId').val(info.event.id);
          
          // Show modal
          $('#eventModal').modal('show');
        }
      });
  
      // Render calendar
      calendar.render();

      // Initialize select2 for color dropdown
      $('#eventColor').select2({
        dropdownParent: $('#eventModal')
      });

      // Handle form submission
      $('#eventForm').on('submit', function(e) {
        e.preventDefault();
        const eventId = $('#eventId').val();
        const eventData = {
          title: $('#eventTitle1').val(),
          description: $('#eventDescription1').val(),
          start: $('#eventStartDate1').val(),
          end: $('#eventEndDate1').val(),
          color: $('#eventColor1').val(),
          event_url: $('#eventUrl1').val()
        };

        if (eventId) {
          // Update existing event
          $.ajax({
            url: '/doctor/calendar/update/' + eventId,
            method: 'PUT',
            data: eventData,
            success: function(response) {
              calendar.refetchEvents();
              $('#eventModal').modal('hide');
              toastr.success(__('master.calendar_updated'));
            },
            error: function(error) {
              toastr.error(__('master.calendar_updated_error'));
            }
          });
        } else {
          // Create new event
          $.ajax({
            url: '/doctor/calendar/store',
            method: 'POST',
            data: eventData,
            success: function(response) {
              calendar.refetchEvents();
              $('#eventModal').modal('hide');
              toastr.success(__('master.calendar_created'));
            },
            error: function(error) {
              toastr.error(__('master.calendar_created_error'));
            }
          });
        }
      });

      // Handle delete button
      $('#deleteEvent').on('click', function() {
        const eventId = $('#eventId').val();
        if (eventId) {
          if (confirm(window.translations.calendar_deleted_confirm)) {
            $.ajax({
              url: '/doctor/calendar/delete/' + eventId,
              method: 'GET',
              success: function(response) {
                calendar.refetchEvents();
                $('#eventModal').modal('hide');
                toastr.success(__('master.calendar_deleted'));
              },
              error: function(error) {
                toastr.error(__('master.calendar_deleted_error'));
              }
            });
          }
        }
      });

      if (inlineCalendar) {
        inlineCalendar.flatpickr({
          monthSelectorType: 'static',
          static: true,
          inline: true
        });
      }

      if (eventStartDate) {
        var start = eventStartDate.flatpickr({
          monthSelectorType: 'static',
          static: true,
          enableTime: true,
          altFormat: 'Y-m-d H:i:S',
          onReady: function (selectedDates, dateStr, instance) {
            if (instance.isMobile) {
              instance.mobileInput.setAttribute('step', null);
            }
          }
        });
      }
  
      // Event end (flatpicker)
      if (eventEndDate) {
        var end = eventEndDate.flatpickr({
          monthSelectorType: 'static',
          static: true,
          enableTime: true,
          altFormat: 'Y-m-d H:i:S',
          onReady: function (selectedDates, dateStr, instance) {
            if (instance.isMobile) {
              instance.mobileInput.setAttribute('step', null);
            }
          }
        });
      }

      if (eventStartDate1) {
        var start = eventStartDate1.flatpickr({
          monthSelectorType: 'static',
          static: true,
          enableTime: true,
          altFormat: 'Y-m-d H:i:S',
          onReady: function (selectedDates, dateStr, instance) {
            if (instance.isMobile) {
              instance.mobileInput.setAttribute('step', null);
            }
          }
        });
      }
  
      // Event end (flatpicker)
      if (eventEndDate1) {
        var end = eventEndDate1.flatpickr({
          monthSelectorType: 'static',
          static: true,
          enableTime: true,
          altFormat: 'Y-m-d H:i:S',
          onReady: function (selectedDates, dateStr, instance) {
            if (instance.isMobile) {
              instance.mobileInput.setAttribute('step', null);
            }
          }
        });
      }




  })(); 
});