
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
     
  })
$(function () {

  var cases_table_dashboard = $('#latest_cases_table_dashboard').DataTable({
     processing: true,
      serverSide: true,
      orderable: false, 
      ajax: "/laboratory/getcases",
      columns: [
          {data: 'case_id', name: 'case_id',orderable: false},
          {data: 'patient_id', name: 'patient_id',orderable: false},
          {data: 'doctor_id', name: 'doctor_id',orderable: false},
          {data: 'treatment_type', name: 'treatment_type',orderable: false},
          {data: 'date', name: 'date',orderable: false},
          {data: 'status', name: 'status',orderable: false},
          {data: 'wetransfer_info', name: 'wetransfer_info',orderable: false},
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
        url: "/laboratory/cases/data",
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
         {data: 'doctor_id', name: 'doctor_id',orderable: false},
         {data: 'wetransfer_info', name: 'wetransfer_info',orderable: false},
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
    const laboratory_id = $('#laboratory_id').val();
    const status = $('#status').val();

   let url = "/laboratory/cases/exportcasesPdf?" +
       "case_id=" + encodeURIComponent(case_id) +
       "&patient_id=" + encodeURIComponent(patient_id) +
       "&treatment_type=" + encodeURIComponent(treatment_type) +
       "&laboratory_id=" + encodeURIComponent(laboratory_id) +
       "&status=" + encodeURIComponent(status);
window.open(url, '_blank');

});





 var tickets_list_table = $('#tickets_list_table').DataTable({
    processing: true,
     serverSide: true,
     orderable: false, 
     ajax: "/laboratory/tickets/get_tickets",
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
     ajax: "/laboratory/treatment_types_list/" + $('#case_id').val(),
     columns: [
         {data: 'name', name: 'name',orderable: false},
         {data: 'status', name: 'status',orderable: false},
         {data: 'type_file', name: 'type_file',orderable: false},
         {data: 'action', name: 'action',orderable: false},
     ]
  
    
 });


 

});
