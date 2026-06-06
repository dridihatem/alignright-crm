@include('emails.partials.header', [
    'title' => __('master.welcome_to_align_right'),
    'subtitle' => __('master.account_created_successfully', ['role' => ucfirst($role)]),
])

<p style="margin:0 0 12px;">{{ __('master.hello') }} {{ $user->name }},</p>
<p style="margin:0 0 8px;">{{ __('master.account_created_intro') }}</p>

@include('emails.partials.panel', [
    'heading' => __('master.your_login_credentials'),
    'rows' => [
        __('master.email') => $user->email,
        __('master.password') => $password,
        __('master.role') => ucfirst($role),
    ],
])

@include('emails.partials.button', [
    'url' => route('login'),
    'label' => __('master.login_to_your_account'),
])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0; background-color:#fff7ed; border-left:4px solid #01b9c6; border-radius:8px;">
    <tr>
        <td style="padding:16px 18px;">
            <div style="margin:0 0 6px; font-size:15px; font-weight:600; color:#0f172a;">{{ __('master.security_reminder') }}</div>
            <p style="margin:0; font-size:14px; color:#64748b; line-height:1.6;">{{ __('master.security_reminder_text') }}</p>
        </td>
    </tr>
</table>

@include('emails.partials.footer')
