<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-info {
            margin-bottom: 30px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info, .client-info {
            flex: 1;
        }
        .invoice-info {
            margin-right: 20px;
        }
        .client-info {
            margin-left: 20px;
        }
        .section-title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .total-label {
            font-weight: bold;
        }
        .total-amount {
            font-weight: bold;
            font-size: 18px;
            color: #007bff;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <h2>{{ $invoice->invoice_number }}</h2>
        </div>

        <!-- Company Information -->
        <div class="company-info">
            <h3>Alignrightlab/</h3>
            <p>Professional Dental Services</p>
            <p>Email: info@alignrightlab.com</p>
            <p>Phone: +1 41 529 75 699</p>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-info">
                <div class="section-title">Invoice Information</div>
                <div class="info-item"><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</div>
                <div class="info-item"><strong>Issue Date:</strong> {{ $invoice->created_at->format('Y-m-d') }}</div>
                <div class="info-item"><strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A' }}</div>
                <div class="info-item"><strong>Status:</strong> {{ ucfirst($invoice->status) }}</div>
            </div>
            
            <div class="client-info">
                <div class="section-title">Doctor Information</div>
                <div class="info-item"><strong>Name:</strong> {{ $invoice->case->doctor->name ?? 'N/A' }}</div>
                <div class="info-item"><strong>Email:</strong> {{ $invoice->case->doctor->email ?? 'N/A' }}</div>
                <div class="info-item"><strong>Phone:</strong> {{ $invoice->case->doctor->phone ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Case Information -->
        <div class="section-title">Case Information</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Patient Name</th>
                    <th>Treatment Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->case->case_id ?? 'N/A' }}</td>
                    <td>{{ $invoice->case->patient->name ?? 'N/A' }}</td>
                    <td>{{ $invoice->case->treatment_type ?? 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $invoice->case->status ?? 'N/A')) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Payment Summary -->
        <div class="section-title">Payment Summary</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Treatment Services</td>
                    <td>Tnd {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="total-section">
            <div class="total-row">
                <span class="total-label">Total Amount:</span>
                <span>Tnd {{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Advance Payment:</span>
                <span>Tnd {{ number_format($invoice->advance_payment, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Remaining Balance:</span>
                <span class="total-amount">Tnd {{ number_format($invoice->remaining_balance, 2) }}</span>
            </div>
        </div>

        <!-- Payment History -->
        @if($invoice->payments && $invoice->payments->count() > 0)
        <div class="section-title">Payment History</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                    <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                    <td>Tnd {{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->notes ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
        <div class="section-title">Notes</div>
        <p>{{ $invoice->notes }}</p>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This invoice was generated on {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
