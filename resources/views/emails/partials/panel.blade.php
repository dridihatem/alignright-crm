{{-- Light info panel with single accent left border. Vars: $heading (optional), $rows (assoc label => value) --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0; background-color:#f1fbfc; border-left:4px solid #01b9c6; border-radius:8px;">
    <tr>
        <td style="padding:16px 18px;">
            @if(!empty($heading))
            <div style="margin:0 0 10px; font-size:15px; font-weight:600; color:#0f172a;">{{ $heading }}</div>
            @endif
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                @foreach($rows as $label => $value)
                <tr>
                    <td style="padding:6px 0; font-size:14px; color:#64748b; width:45%; vertical-align:top;">{{ $label }}</td>
                    <td style="padding:6px 0; font-size:14px; color:#0f172a; font-weight:600; text-align:right; vertical-align:top;">{{ $value }}</td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
