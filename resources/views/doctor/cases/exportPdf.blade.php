
<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('master.export_pdf') }}</title>
    <!-- Custom styles for this template-->
  
    <style type="text/css">
      table.blueTable {
  border: 1px solid #01b9c6;
  width: 100%;
  text-align: left;
  border-collapse: collapse;
}
table.blueTable td, table.blueTable th {
  padding: 3px 2px;
}
table.blueTable tbody td {
  font-size: 13px;
}
table.blueTable tr:nth-child(even) {
  background: #D0E4F5;
}
table.blueTable thead {
  background: #01b9c6;
  color: #fff;
  
}
table.blueTable thead th {
  font-size: 14px;
  font-weight: bold;
  color: #FFFFFF;
}
    </style>
</head>
<body>
   <h2 style="text-align: center">
    <strong>
                {{ __('master.case_list') }}
   </strong>
 </h2>
   
 
    <div class="margin-top table-responsive">
        <table class="blueTable">
            <thead>
            <tr>
                <th>{{ __('master.case_id') }}</th>
                <th>{{ __('master.patient_name') }}</th>
                <th>{{ __('master.date') }}</th>
                <th>{{ __('master.status') }}</th>
                <th>{{ __('master.treatment_type') }}</th>
                <th>{{ __('master.accepted_date') }}</th>
                <th>{{ __('master.rejected_date') }}</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $case )
            <tr>
              <td>{{ $case['id'] }}</td>
              <td>{{ $case['patient']['name'] }}</td>
              <td>{{ $case['date'] }}</td>
              <td>{{ $case['status'] }}</td>
              <td>{{ $case['treatment_type'] }}</td>
              <td>{{ $case['accepted_date'] }}</td>
              <td>{{ $case['rejected_date'] }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <script>
        window.print();
       
    </script>
</body>
</html>

