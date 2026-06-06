@include('emails.partials.header', [
    'title' => __('master.wetransfer_notification_subject'),
    'subtitle' => __('master.case_id') . ': #' . $case->case_id,
])

<p style="margin:0 0 12px;"><strong>{{ __('master.dear') }} {{ $case->laboratory->name }},</strong></p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0; background-color:#f1fbfc; border-left:4px solid #01b9c6; border-radius:8px;">
    <tr>
        <td style="padding:16px 18px;">
            <div style="margin:0 0 8px; font-size:15px; font-weight:600; color:#0f172a;">{{ __('master.notification_message') }}</div>
            <p style="margin:0; font-size:14px; color:#475569; line-height:1.6;">{{ $message }}</p>
        </td>
    </tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0; background-color:#f1fbfc; border-left:4px solid #01b9c6; border-radius:8px;">
    <tr>
        <td style="padding:16px 18px;">
            <div style="margin:0 0 8px; font-size:15px; font-weight:600; color:#0f172a;">{{ __('master.wetransfer_link') }}</div>
            <p style="margin:0; font-size:13px; word-break:break-all;"><a href="{{ $wetransfer_link }}" target="_blank" style="color:#01b9c6; text-decoration:none; font-weight:600;">{{ $wetransfer_link }}</a></p>
        </td>
    </tr>
</table>

@include('emails.partials.button', [
    'url' => $wetransfer_link,
    'label' => __('master.download_files'),
])

@include('emails.partials.panel', [
    'heading' => __('master.case_details'),
    'rows' => [
        __('master.case_id') => '#' . $case->case_id,
        __('master.patient_name') => $case->patient->name ?? 'N/A',
        __('master.technician') => $technician->name,
        __('master.technician_email') => $technician->email,
        __('master.date') => now()->format('d-m-Y H:i'),
        __('master.treatment_type') => $case->treatment_type ?? 'N/A',
    ],
])

<p style="margin:16px 0 0; font-size:13px; color:#94a3b8;">
    {{ __('master.best_regards') }},<br>
    <strong>{{ $technician->name }}</strong> — {{ __('master.technician') }}<br>
    {{ __('master.notification_id') }}: #{{ $notification_id }}
</p>

@include('emails.partials.footer')
