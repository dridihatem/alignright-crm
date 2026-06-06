
<x-app-layout>
  @push('styles')
 @endpush
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
   
   <div class="row g-4 mb-4">
     <!-- New Cases -->
     <div class="col-sm-6 col-lg-3">
       <div class="card h-100">
         <div class="card-body py-4">
           <div class="d-flex align-items-center">
             <div class="avatar flex-shrink-0 me-3">
               <span class="avatar-initial rounded bg-label-primary">
                 <i class="icon-base ti tabler-briefcase icon-24px"></i>
               </span>
             </div>
             <div>
               <p class="mb-1 small text-muted">{{ __('master.new_cases') }}</p>
               <h4 class="mb-0">{{ $new_cases }}</h4>
             </div>
           </div>
         </div>
       </div>
     </div>

     <!-- Doctors -->
     <div class="col-sm-6 col-lg-3">
       <div class="card h-100">
         <div class="card-body py-4">
           <div class="d-flex align-items-center">
             <div class="avatar flex-shrink-0 me-3">
               <span class="avatar-initial rounded bg-label-info">
                 <i class="icon-base ti tabler-stethoscope icon-24px"></i>
               </span>
             </div>
             <div>
               <p class="mb-1 small text-muted">{{ __('master.count_doctor') }}</p>
               <h4 class="mb-0">{{ $total_doctors }}</h4>
             </div>
           </div>
         </div>
       </div>
     </div>

     <!-- Technicians -->
     <div class="col-sm-6 col-lg-3">
       <div class="card h-100">
         <div class="card-body py-4">
           <div class="d-flex align-items-center">
             <div class="avatar flex-shrink-0 me-3">
               <span class="avatar-initial rounded bg-label-warning">
                 <i class="icon-base ti tabler-tool icon-24px"></i>
               </span>
             </div>
             <div>
               <p class="mb-1 small text-muted">{{ __('master.count_technician') }}</p>
               <h4 class="mb-0">{{ $total_technicians }}</h4>
             </div>
           </div>
         </div>
       </div>
     </div>

     <!-- Laboratories -->
     <div class="col-sm-6 col-lg-3">
       <div class="card h-100">
         <div class="card-body py-4">
           <div class="d-flex align-items-center">
             <div class="avatar flex-shrink-0 me-3">
               <span class="avatar-initial rounded bg-label-success">
                 <i class="icon-base ti tabler-microscope icon-24px"></i>
               </span>
             </div>
             <div>
               <p class="mb-1 small text-muted">{{ __('master.count_laboratory') }}</p>
               <h4 class="mb-0">{{ $total_laboratories }}</h4>
             </div>
           </div>
         </div>
       </div>
     </div>
   </div>
     <!-- Case Status (full-width horizontal band) -->
     <div class="row g-4 mb-4">
       <div class="col-12">
         <div class="card">
           <div class="card-header d-flex align-items-center justify-content-between pb-0">
             <div class="card-title mb-0">
               <h5 class="mb-1">{{ __('master.case_status') }}</h5>
               <p class="card-subtitle mb-0">{{ $count_cases }} {{ __('master.cases') }}</p>
             </div>
           </div>
           <div class="card-body">
             <div class="row g-3 text-center">
               <div class="col-6 col-sm-4 col-xl">
                 <div class="d-flex flex-column align-items-center p-3 rounded bg-label-secondary h-100">
                   <i class="icon-base ti tabler-alert-triangle icon-md mb-2"></i>
                   <h4 class="mb-0">{{ $status_draft }}</h4>
                   <small>{{ __('master.draft') }}</small>
                 </div>
               </div>
               <div class="col-6 col-sm-4 col-xl">
                 <div class="d-flex flex-column align-items-center p-3 rounded bg-label-info h-100">
                   <i class="icon-base ti tabler-cell-signal-1 icon-md mb-2"></i>
                   <h4 class="mb-0">{{ $status_pending }}</h4>
                   <small>{{ __('master.pending_waiting') }}</small>
                 </div>
               </div>
               <div class="col-6 col-sm-4 col-xl">
                 <div class="d-flex flex-column align-items-center p-3 rounded bg-label-warning h-100">
                   <i class="icon-base ti tabler-cell-signal-2 icon-md mb-2"></i>
                   <h4 class="mb-0">{{ $status_in_planning }}</h4>
                   <small>{{ __('master.in_planning') }}</small>
                 </div>
               </div>
               <div class="col-6 col-sm-4 col-xl">
                 <div class="d-flex flex-column align-items-center p-3 rounded bg-label-primary h-100">
                   <i class="icon-base ti tabler-cell-signal-3 icon-md mb-2"></i>
                   <h4 class="mb-0">{{ $status_approval }}</h4>
                   <small>{{ __('master.approval') }}</small>
                 </div>
               </div>
               <div class="col-6 col-sm-4 col-xl">
                 <div class="d-flex flex-column align-items-center p-3 rounded bg-label-success h-100">
                   <i class="icon-base ti tabler-building-factory icon-md mb-2"></i>
                   <h4 class="mb-0">{{ $status_in_production }}</h4>
                   <small>{{ __('master.in_production') }}</small>
                 </div>
               </div>
               <div class="col-6 col-sm-4 col-xl">
                 <div class="d-flex flex-column align-items-center p-3 rounded bg-label-success h-100">
                   <i class="icon-base ti tabler-cube-send icon-md mb-2"></i>
                   <h4 class="mb-0">{{ $status_shipped }}</h4>
                   <small>{{ __('master.shipped') }}</small>
                 </div>
               </div>
               <div class="col-6 col-sm-4 col-xl">
                 <div class="d-flex flex-column align-items-center p-3 rounded bg-label-danger h-100">
                   <i class="icon-base ti tabler-ban icon-md mb-2"></i>
                   <h4 class="mb-0">{{ $status_rejected }}</h4>
                   <small>{{ __('master.rejected') }}</small>
                 </div>
               </div>
             </div>
           </div>
         </div>
       </div>
     </div>

     <!-- Cases grouped by patient (full width) -->
     <div class="row g-4 mb-4">
       <div class="col-12">
         <div class="card h-100">
             <div class="card-header d-flex justify-content-between">
               <div class="card-title mb-0">
                 <h5 class="mb-1">{{ __('master.patients') }}</h5>
                 <p class="card-subtitle mb-0">{{ __('master.cases_grouped_by_patient') }}</p>
               </div>
             </div>
             <div class="card-body">
                 @include('admin.cases._patients_table', ['tableId' => 'dashboardPatientsCases'])
             </div>
         </div>
       </div>
     </div>
 
     <div class="row g-4 mb-4">
         <div class="col-md-6 col-xxl-6">
             <div class="card h-100">
                 <div class="card-header d-flex justify-content-between">
                     <div class="card-title mb-0">
                         <h5 class="mb-1">{{ __('master.statistics') }}</h5>  
                     </div>
                 </div>
                 <div class="card-body">
                     <div id="caseStatusChart"></div>
                 </div>
             </div>
         </div>
         <div class="col-md-6 col-xxl-6">
             <div class="card h-100">
                 <div class="card-header d-flex justify-content-between">
                     <div class="card-title mb-0">
                         <h5 class="mb-1">{{ __('master.count_cases_by_month') }}</h5>  
                     </div>
                 </div>
                 <div class="card-body">
                     <div id="count_cases_by_month"></div>
                 </div>
             </div>
         </div>
         
     </div>
 
     
  
 
 
     
 
 
    
 
   
   @push('scripts')
  
 
   <script src="{{ asset('assets/js/dataTables-all.js') }}"></script>
   <script>
   document.addEventListener('DOMContentLoaded', function() {
       var options = {
           series: [{
               name: '{{ __("master.cases") }}',
               data: [
                   {{ $status_draft }},
                   {{ $status_pending }},
                   {{ $status_in_planning }},
                   {{ $status_approval }},
                   {{ $status_in_production }},
                   {{ $status_shipped }},
                   {{ $status_rejected }}
               ]
           }],
           chart: {
               type: 'bar',
               height: 350,
               toolbar: {
                   show: false
               }
           },
           plotOptions: {
               bar: {
                   borderRadius: 4,
                   horizontal: false,
                   columnWidth: '55%',
                   distributed: true,
               }
           },
           dataLabels: {
               enabled: true
           },
           colors: ['#8592a3', '#ffab00', '#00bad1', '#03c3ec', '#71dd37', '#71dd37', '#ff3e1d', '#ff3e1d'],
           xaxis: {
               categories: [
                   '{{ __("master.draft") }}',
                   '{{ __("master.pending") }}',
                   '{{ __("master.in_planning") }}',
                   '{{ __("master.approval") }}',
                   '{{ __("master.in_production") }}',
                   '{{ __("master.shipped") }}',
                   '{{ __("master.rejected") }}'
               ],
               labels: {
                   style: {
                       fontSize: '12px'
                   }
               }
           },
           yaxis: {
               title: {
                   text: '{{ __("master.cases") }}'
               }
           },
           legend: {
               show: false
           },
           tooltip: {
               y: {
                   formatter: function (val) {
                       return val + ' {{ __("master.cases") }}'
                   }
               }
           }
       };
 
       var chart = new ApexCharts(document.querySelector("#caseStatusChart"), options);
       chart.render();
   });
 
 
    // Expenses Radial Bar Chart
   // --------------------------------------------------------------------
   const expensesRadialChartEl = document.querySelector('#expensesChart'),
     expensesRadialChartConfig = {
       chart: {
         height: 145,
         sparkline: {
           enabled: true
         },
         parentHeightOffset: 0,
         type: 'radialBar'
       },
       colors: ['#ff9f43'],
       series: [{{ $case_retarded_percentage }}],
       plotOptions: {
         radialBar: {
           offsetY: 0,
           startAngle: -90,
           endAngle: 90,
           hollow: {
             size: '65%'
           },
           track: {
             strokeWidth: '45%',
             background: '#CCC'
           },
           dataLabels: {
             name: {
               show: false
             },
             value: {
               fontSize: '24px',
               color: '#444050',
               fontWeight: 500,
               offsetY: -5
             }
           }
         }
       },
       grid: {
         show: false,
         padding: {
           bottom: 5
         }
       },
       stroke: {
         lineCap: 'round'
       },
       labels: ['Progress'],
       responsive: [
         {
           breakpoint: 1442,
           options: {
             chart: {
               height: 100
             },
             plotOptions: {
               radialBar: {
                 hollow: {
                   size: '55%'
                 },
                 dataLabels: {
                   value: {
                     fontSize: '16px',
                     offsetY: -1
                   }
                 }
               }
             }
           }
         },
         {
           breakpoint: 1200,
           options: {
             chart: {
               height: 228
             },
             plotOptions: {
               radialBar: {
                 hollow: {
                   size: '75%'
                 },
                 track: {
                   strokeWidth: '50%'
                 },
                 dataLabels: {
                   value: {
                     fontSize: '26px'
                   }
                 }
               }
             }
           }
         },
         {
           breakpoint: 890,
           options: {
             chart: {
               height: 180
             },
             plotOptions: {
               radialBar: {
                 hollow: {
                   size: '70%'
                 }
               }
             }
           }
         },
         {
           breakpoint: 426,
           options: {
             chart: {
               height: 142
             },
             plotOptions: {
               radialBar: {
                 hollow: {
                   size: '70%'
                 },
                 dataLabels: {
                   value: {
                     fontSize: '22px'
                   }
                 }
               }
             }
           }
         },
         {
           breakpoint: 376,
           options: {
             chart: {
               height: 105
             },
             plotOptions: {
               radialBar: {
                 hollow: {
                   size: '60%'
                 },
                 dataLabels: {
                   value: {
                     fontSize: '18px'
                   }
                 }
               }
             }
           }
         }
       ]
     };
   if (typeof expensesRadialChartEl !== undefined && expensesRadialChartEl !== null) {
     const expensesRadialChart = new ApexCharts(expensesRadialChartEl, expensesRadialChartConfig);
     expensesRadialChart.render();
   }
 
 
   // Count Cases by Month
   // --------------------------------------------------------------------
   const countCasesByMonthEl = document.querySelector('#count_cases_by_month'),
     countCasesByMonthConfig = {
       chart: {
         height: 350,
         type: 'bar',
         toolbar: {
           show: false
         }
       },
       plotOptions: { 
         bar: {
           columnWidth: '55%',
           distributed: true,
         }
       },
       dataLabels: {
         enabled: true
       },
       colors: ['#8592a3', '#ffab00', '#00bad1', '#03c3ec', '#71dd37', '#71dd37', '#ff3e1d'],
       series: [{
           name: '{{ __("master.cases") }}',
           data: @json($monthly_totals)
       }],
       xaxis: {
         categories: [
           '{{ __("master.january") }}', 
           '{{ __("master.february") }}',
           '{{ __("master.march") }}',
           '{{ __("master.april") }}',
           '{{ __("master.may") }}',
           '{{ __("master.june") }}',
           '{{ __("master.july") }}',
           '{{ __("master.august") }}',
           '{{ __("master.september") }}',
           '{{ __("master.october") }}',
           '{{ __("master.november") }}',
           '{{ __("master.december") }}'
         ],
         labels: {
           style: {
             fontSize: '12px'
           }
         }
       },
       yaxis: {
         title: {
           text: '{{ __("master.cases") }}'
         }
       },
       legend: {
         show: false
       },
       tooltip: {
         y: {
           formatter: function (val) {
             return val + ' {{ __("master.cases") }}'
           }
         }
       }
     };
   if (typeof countCasesByMonthEl !== undefined && countCasesByMonthEl !== null) {
     const countCasesByMonth = new ApexCharts(countCasesByMonthEl, countCasesByMonthConfig);
     countCasesByMonth.render();
   }
         
         
   
   </script>
   @endpush
 
 </x-app-layout> 
 