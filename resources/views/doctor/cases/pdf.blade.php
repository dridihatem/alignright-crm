<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cases Export - {{ date('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-draft { background-color: #6c757d; color: white; }
        .status-pending { background-color: #ffc107; color: black; }
        .status-in_planning { background-color: #17a2b8; color: white; }
        .status-approval { background-color: #007bff; color: white; }
        .status-rejected { background-color: #dc3545; color: white; }
        .status-in_production { background-color: #28a745; color: white; }
        .status-shipped { background-color: #343a40; color: white; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cases Export Report</h1>
        <p>Generated on: {{ date('F j, Y \a\t g:i A') }}</p>
        <p>Doctor: {{ auth()->user()->name ?? 'N/A' }}</p>
        <p>Total Cases: {{ $cases->count() }}</p>
    </div>

    @if($cases->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Patient Name</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Treatment Type</th>
                    <th>Accepted Date</th>
                    <th>Rejected Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cases as $case)
                    <tr>
                        <td>{{ $case->case_id ?? 'N/A' }}</td>
                        <td>{{ $case->patient->name ?? 'N/A' }} {{ $case->patient->surname ?? '' }}</td>
                        <td>{{ $case->date ? date('Y-m-d', strtotime($case->date)) : 'N/A' }}</td>
                        <td>
                            <span class="status-badge status-{{ $case->status }}">
                                {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                            </span>
                        </td>
                        <td>{{ $case->treatment_type ?? 'N/A' }}</td>
                        <td>{{ $case->accepted_date ? date('Y-m-d', strtotime($case->accepted_date)) : 'N/A' }}</td>
                        <td>{{ $case->rejected_date ? date('Y-m-d', strtotime($case->rejected_date)) : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; margin-top: 50px; color: #666;">
            <h3>No cases found</h3>
            <p>There are no cases to export based on the current filters.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically from the Doctor Cases Management System.</p>
        <p>© {{ date('Y') }} Doctor Cases System. All rights reserved.</p>
    </div>
</body>
</html>
