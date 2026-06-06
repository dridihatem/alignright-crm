{{-- Align Right simple email layout: centered, shadowed white card, single accent (#01b9c6).
     Optional vars: $title, $subtitle --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Align Right' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#eef2f4;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f4; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
        <tr>
            <td align="center" style="padding:32px 12px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:100%; background-color:#ffffff; border-radius:16px; box-shadow:0 12px 30px rgba(15,23,42,0.12); overflow:hidden;">
                    {{-- Brand --}}
                    <tr>
                        <td align="center" style="padding:32px 32px 0;">
                            <img src="{{ asset('assets/img/logo_align.png') }}" alt="Align Right" width="54" style="height:54px; width:auto; display:inline-block; border:0; outline:none; text-decoration:none;">
                            <div style="margin-top:8px; font-size:20px; font-weight:700; letter-spacing:.5px; color:#01b9c6;">Align Right</div>
                        </td>
                    </tr>
                    @if(!empty($title))
                    <tr>
                        <td align="center" style="padding:20px 32px 0;">
                            <h1 style="margin:0; font-size:20px; font-weight:600; color:#0f172a;">{{ $title }}</h1>
                            @if(!empty($subtitle))
                            <p style="margin:8px 0 0; font-size:14px; color:#64748b; line-height:1.5;">{{ $subtitle }}</p>
                            @endif
                            <div style="margin:16px auto 0; width:56px; height:3px; border-radius:3px; background-color:#01b9c6;"></div>
                        </td>
                    </tr>
                    @endif
                    {{-- Content --}}
                    <tr>
                        <td style="padding:24px 32px 8px; color:#334155; font-size:15px; line-height:1.6;">
