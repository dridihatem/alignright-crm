<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('master.invoice') }} #{{ $invoice->invoice_number }}</title>
    @php
        $brand = '#01b9c6';
        $dark = '#0f172a';
        $muted = '#7c8694';
        $statusColors = [
            'paid'    => ['#16a34a', '#e7f7ee'],
            'pending' => ['#b45309', '#fdf3e3'],
            'overdue' => ['#dc2626', '#fdeaea'],
        ];
        $sc = $statusColors[$invoice->status] ?? ['#475569', '#eef2f6'];
        $siteName = \App\Helpers\SettingsHelper::getSiteName();
        $siteSub = \App\Helpers\SettingsHelper::get('site_description');
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 34px 40px;
            color: {{ $dark }};
            font-size: 13px;
            line-height: 1.5;
        }
        .text-muted { color: {{ $muted }}; }
        .text-brand { color: {{ $brand }}; }
        .text-right { text-align: right; }
        .uppercase { text-transform: uppercase; letter-spacing: .5px; }

        /* Header */
        .head-table { width: 100%; border-collapse: collapse; margin-bottom: 26px; }
        .head-table td { vertical-align: top; }
        .brand-name { font-size: 22px; font-weight: bold; color: {{ $brand }}; margin: 0 0 4px; }
        .brand-sub { font-size: 11px; color: {{ $muted }}; }
        .doc-title { font-size: 30px; font-weight: bold; color: {{ $dark }}; margin: 0; letter-spacing: 1px; }
        .doc-num { font-size: 13px; color: {{ $muted }}; margin-top: 4px; }

        .accent-bar { height: 4px; background: {{ $brand }}; border-radius: 4px; margin-bottom: 26px; }

        /* Info blocks */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
        .info-table > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }
        .block-label { font-size: 10px; font-weight: bold; color: {{ $brand }}; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 8px; }
        .kv { width: 100%; border-collapse: collapse; }
        .kv td { padding: 3px 0; font-size: 12.5px; }
        .kv td.k { color: {{ $muted }}; width: 46%; }
        .kv td.v { color: {{ $dark }}; font-weight: bold; }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: {{ $sc[0] }};
            background: {{ $sc[1] }};
        }

        /* Amount table */
        .amount-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .amount-table th {
            background: {{ $dark }};
            color: #fff;
            text-align: left;
            padding: 11px 14px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .amount-table th.num, .amount-table td.num { text-align: right; }
        .amount-table td { padding: 11px 14px; border-bottom: 1px solid #eef0f2; font-size: 13px; }

        .totals { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .totals td { padding: 6px 14px; font-size: 13px; }
        .totals td.lab { text-align: right; color: {{ $muted }}; }
        .totals td.val { text-align: right; width: 150px; font-weight: bold; }
        .totals tr.grand td { font-size: 16px; color: {{ $brand }}; border-top: 2px solid {{ $brand }}; padding-top: 10px; }

        .notes-box {
            margin-top: 26px;
            background: #f7f9fb;
            border-left: 4px solid {{ $brand }};
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 12.5px;
        }

        .footer {
            margin-top: 40px;
            border-top: 1px solid #e6e9ed;
            padding-top: 14px;
            text-align: center;
            font-size: 11px;
            color: {{ $muted }};
        }
        .footer .ty { color: {{ $dark }}; font-weight: bold; font-size: 12.5px; margin-bottom: 4px; }
    </style>
</head>
<body>

    <table class="head-table">
        <tr>
            <td>
                <div class="brand-name">{{ $siteName }}</div>
                @if($siteSub)<div class="brand-sub">{{ $siteSub }}</div>@endif
            </td>
            <td class="text-right">
                <div class="doc-title uppercase">{{ __('master.invoice') }}</div>
                <div class="doc-num">#{{ $invoice->invoice_number }}</div>
            </td>
        </tr>
    </table>

    <div class="accent-bar"></div>

    <table class="info-table">
        <tr>
            <td style="padding-right: 20px;">
                <div class="block-label">{{ __('master.bill_to') }}</div>
                <table class="kv">
                    <tr><td class="k">{{ __('master.patient_name') }}</td><td class="v">{{ $invoice->case->patient->name ?? 'N/A' }} {{ $invoice->case->patient->surname ?? '' }}</td></tr>
                    <tr><td class="k">{{ __('master.doctor') }}</td><td class="v">{{ $invoice->case->doctor->name ?? 'N/A' }} {{ $invoice->case->doctor->surname ?? '' }}</td></tr>
                    <tr><td class="k">{{ __('master.case_id') }}</td><td class="v">{{ $invoice->case->case_id ?? 'N/A' }}</td></tr>
                    <tr><td class="k">{{ __('master.treatment_type') }}</td><td class="v">{{ $invoice->case->treatment_type ? ucfirst($invoice->case->treatment_type) : 'N/A' }}</td></tr>
                </table>
            </td>
            <td style="padding-left: 20px;">
                <div class="block-label">{{ __('master.invoice_information') }}</div>
                <table class="kv">
                    <tr><td class="k">{{ __('master.invoice_number') }}</td><td class="v">{{ $invoice->invoice_number }}</td></tr>
                    <tr><td class="k">{{ __('master.issue_date') }}</td><td class="v">{{ $invoice->created_at ? date('d-m-Y', strtotime($invoice->created_at)) : 'N/A' }}</td></tr>
                    <tr><td class="k">{{ __('master.due_date') }}</td><td class="v">{{ $invoice->due_date ? date('d-m-Y', strtotime($invoice->due_date)) : 'N/A' }}</td></tr>
                    <tr><td class="k">{{ __('master.status') }}</td><td class="v"><span class="status-badge">{{ __('master.' . $invoice->status) }}</span></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="amount-table">
        <thead>
            <tr>
                <th>{{ __('master.description') }}</th>
                <th class="num">{{ __('master.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ __('master.treatment_type') }}: {{ $invoice->case->treatment_type ? ucfirst($invoice->case->treatment_type) : __('master.invoice') }}
                    <br><span class="text-muted" style="font-size:11px;">{{ __('master.case_id') }}: {{ $invoice->case->case_id ?? 'N/A' }}</span>
                </td>
                <td class="num">Tnd {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="lab">{{ __('master.total_amount') }}</td>
            <td class="val">Tnd {{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        @if($invoice->advance_payment > 0)
        <tr>
            <td class="lab">{{ __('master.advance_payment') }}</td>
            <td class="val">- Tnd {{ number_format($invoice->advance_payment, 2) }}</td>
        </tr>
        @endif
        <tr class="grand">
            <td class="lab">{{ __('master.remaining_balance') }}</td>
            <td class="val">Tnd {{ number_format($invoice->remaining_balance, 2) }}</td>
        </tr>
    </table>

    @if($invoice->notes)
    <div class="notes-box">
        <strong>{{ __('master.notes') }}:</strong> {{ $invoice->notes }}
    </div>
    @endif

    <div class="footer">
        <div class="ty">{{ __('master.thank_you_business') }}</div>
        <div>{{ __('master.computer_generated_invoice') }}</div>
        <div>{{ __('master.generated_on') }} {{ date('d-m-Y H:i') }}</div>
    </div>

</body>
</html>
