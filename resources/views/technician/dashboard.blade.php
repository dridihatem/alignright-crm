
<x-app-layout>
    @push('styles')
   @endpush
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
  
     @include('partials.dashboard_kpis', ['kpis' => [
         ['label' => __('master.new_cases'),      'value' => $new_cases,           'icon' => 'tabler-briefcase',        'color' => 'primary'],
         ['label' => __('master.in_production'),  'value' => $status_in_production, 'icon' => 'tabler-building-factory', 'color' => 'success'],
         ['label' => __('master.cases'),          'value' => $count_cases,         'icon' => 'tabler-files',            'color' => 'info'],
         ['label' => __('master.case_retarded'),  'value' => $case_retarded,       'icon' => 'tabler-clock', 'color' => 'warning'],
     ]])

     @include('partials.case_status_band', ['totalCases' => $count_cases])

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
             @include('partials.cases_by_patient', ['patientGroups' => $patientGroups, 'caseShowRoute' => 'technician.cases.show', 'tableId' => 'technicianDashboardPatients'])
           </div>
         </div>
       </div>
     </div>
   
       <div class="row g-6">
           <div class="col-md-6 col-xxl-6 mb-6">
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
           <div class="col-md-6 col-xxl-6 mb-6">
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
    
   
     <script src="{{ asset('assets/js/dataTables-all-technician.js') }}"></script>
     <script>
     document.addEventListener('DOMContentLoaded', function() {
         var options = {
             series: [{
                 name: '{{ __("master.cases") }}',
                 data: [    
                     {{ $status_in_production }}
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
             colors: ['#8592a3', '#00bad1', '#71dd37'],
             xaxis: {
                 categories: [
                    
                     '{{ __("master.in_production") }}'
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
   