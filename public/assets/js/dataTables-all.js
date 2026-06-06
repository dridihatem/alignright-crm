
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
     
  })
$(function () {

    // dashboard admin
    var cases_table_dashboard_admin = $('#cases_table_dashboard_admin').DataTable({ 
        processing: true,
        serverSide: true,
        orderable: false, 
        ajax: "/admin/getcases",
        columns: [
            {data: 'case_id', name: 'case_id',orderable: false},
            {data: 'patient_id', name: 'patient_id',orderable: false},
            {data: 'treatment_type', name: 'treatment_type',orderable: false},
            {data: 'date', name: 'date',orderable: false},
            {data: 'status', name: 'status',orderable: false},
            {
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: true
            },
        ]
    });
    // doctor list table
    var doctor_list_table = $('#doctor_list_table').DataTable({
        processing: true,
        serverSide: true,
        orderable: false, 
        ajax: "/admin/getdoctors",
        columns: [
            {data: 'doctor_name', name: 'doctor_name',orderable: false},
            {data: 'doctor_email', name: 'doctor_email',orderable: false},
            {data: 'doctor_count_cases', name: 'doctor_count_cases',orderable: false},
            {data: 'doctor_photo', name: 'doctor_photo',orderable: false},
            {data: 'doctor_status', name: 'doctor_status',orderable: false},
            {
                data: 'action', 
                name: 'action', 
                orderable: false, 
                searchable: true
            },
        ]

    });
   
   



  var cases_table_dashboard = $('#latest_cases_table_dashboard').DataTable({
     processing: true,
      serverSide: true,
      orderable: false, 
      ajax: "/doctor/getcases",
      columns: [
          {data: 'case_id', name: 'case_id',orderable: false},
          {data: 'patient_id', name: 'patient_id',orderable: false},
          {data: 'treatment_type', name: 'treatment_type',orderable: false},
          {data: 'date', name: 'date',orderable: false},
          {data: 'status', name: 'status',orderable: false},
          {
              data: 'action', 
              name: 'action', 
              orderable: false, 
              searchable: true
          },
      ]
   
     
  });

  var cases_table = $('#latest_cases_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
     ajax: {
        url: "/doctor/getcases",
     data:function(d){
        d.case_id = $('#case_id').val();
        d.patient_id = $('#patient_id').val();
        d.treatment_type = $('#treatment_type').val();
        d.status = $('#status').val();
    }
},
     columns: [
         {data: 'case_id', name: 'case_id',orderable: false},
         {data: 'patient_id', name: 'patient_id',orderable: false},
         {data: 'date', name: 'date',orderable: false},
         {data: 'status', name: 'status',orderable: false},
         {data: 'treatment_type', name: 'treatment_type',orderable: false},
         {data: 'accepted_date', name: 'accepted_date',orderable: false},
         {data: 'rejected_date', name: 'rejected_date',orderable: false},
         {
             data: 'action', 
             name: 'action', 
             orderable: false, 
             searchable: true
         },
     ]
  
    
 });
  // Handle search button click
  $('#searchBtn').click(function() {
  
    cases_table.ajax.reload();
});

// Handle reset button click
$('#resetBtn').click(function() {
    $('#searchForm')[0].reset();
    cases_table.ajax.reload();
});

$('#export-pdf').click(function() {
    const case_id = $('#case_id').val();
    const patient_id = $('#patient_id').val();
    const treatment_type = $('#treatment_type').val();
    const status = $('#status').val();

   let url = "/doctor/cases/exportPdf?" +
       "case_id=" + encodeURIComponent(case_id) +
       "&patient_id=" + encodeURIComponent(patient_id) +
       "&treatment_type=" + encodeURIComponent(treatment_type) +
       "&status=" + encodeURIComponent(status);
window.open(url, '_blank');

});



var technician_list_table = $('#technician_list_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
        ajax: "/doctor/gettechnicians",
     columns: [
         {data: 'technician_name', name: 'technician_name',orderable: false},
         {data: 'technician_email', name: 'technician_email',orderable: false},
         {data: 'technician_count_cases', name: 'technician_count_cases',orderable: false},
         {data: 'technician_photo', name: 'technician_photo',orderable: false},
         {data: 'technician_status', name: 'technician_status',orderable: false},
         {
             data: 'action', 
             name: 'action', 
             orderable: false, 
             searchable: true
         },
     ]
  
    
 });

 var technician_cases_table = $('#technician_cases_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
     searchable: true,
        ajax: "/doctor/technicians_cases/" + $('#technician_id').val(),
     columns: [
         {data: 'case_number', name: 'case_number',orderable: false},
         {data: 'patient_name', name: 'patient_name',orderable: false},
         {data: 'status', name: 'status',orderable: false},
         
         
     ]
  
    
 });



 var laboratory_list_table = $('#laboratory_list_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
        ajax: "/doctor/getlaboratories",
     columns: [
         {data: 'laboratory_name', name: 'laboratory_name',orderable: false},
         {data: 'laboratory_email', name: 'laboratory_email',orderable: false},
         {data: 'laboratory_count_cases', name: 'laboratory_count_cases',orderable: false},
         {data: 'laboratory_photo', name: 'laboratory_photo',orderable: false},
         {data: 'laboratory_status', name: 'laboratory_status',orderable: false},
         {
             data: 'action', 
             name: 'action', 
             orderable: false, 
             searchable: true
         },
     ]
  
    
 });


 var laboratory_cases_table = $('#laboratory_cases_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
     searchable: true,
        ajax: "/doctor/laboratory_cases/" + $('#laboratory_id').val(),
     columns: [
         {data: 'case_number', name: 'case_number',orderable: false},
         {data: 'patient_name', name: 'patient_name',orderable: false},
         {data: 'status', name: 'status',orderable: false},     

         
     ]
  
    
 });    


 var patient_list_table = $('#patient_list_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
     ajax: "/doctor/getpatients",
     columns: [
         {data: 'patient_reference', name: 'patient_reference',orderable: false},  
         {data:'case_id', name:'case_id',orderable: false}, 
         {data: 'patient_name', name: 'patient_name',orderable: false},
         {data: 'patient_gender', name: 'patient_gender',orderable: false},
         {data: 'patient_phone', name: 'patient_phone',orderable: false},
         {data: 'patient_email', name: 'patient_email',orderable: false},   
         {data: 'patient_country', name: 'patient_country',orderable: false},
         {data: 'patient_address', name: 'patient_address',orderable: false},
         {data: 'patient_birthday', name: 'patient_birthday',orderable: false},
         {data: 'created_at', name: 'created_at',orderable: false},
         {
             data: 'action', 
             name: 'action',    
             orderable: false, 
             searchable: true
         },
     ]
  
    
 });

 var tickets_list_table = $('#tickets_list_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
     ajax: "/doctor/tickets/get_tickets",
     columns: [
         {data: 'name', name: 'name',orderable: false},
         {data: 'subject', name: 'subject',orderable: false},
         {data: 'status', name: 'status',orderable: false},
         {data: 'assigned_to', name: 'assigned_to',orderable: false},
         {data: 'priority', name: 'priority',orderable: false},
         {data: 'created_at', name: 'created_at',orderable: false},
         {
             data: 'action', 
             name: 'action', 
             orderable: false, 
             searchable: true
         },
     ]
  
    
 });


 var treatment_types_table = $('#treatment_types_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
     ajax: "/doctor/treatment_types_list/" + $('#case_id').val(),
     columns: [
         {data: 'name', name: 'name',orderable: false},
         {data: 'status', name: 'status',orderable: false},
         {data: 'type_file', name: 'type_file',orderable: false},
         {data: 'action', name: 'action',orderable: false},
     ]
  
    
 });


 

});
