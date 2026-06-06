@include('emails.partials.header', [
    'title' => __('master.price_accepted'),
    'subtitle' => __('master.price_accepted_subtitle'),
])

@include('emails.partials.panel', [
    'heading' => __('master.acceptance_confirmed'),
    'rows' => [
        __('master.price_accepted_label') => 'TND ' . number_format($price, 2),
        __('master.accepted_by') => $doctor->name,
        __('master.accepted_on') => now()->format('d-m-Y H:i'),
        __('master.status') => __('master.approval_ready_for_treatment_planning'),
    ],
])

@include('emails.partials.panel', [
    'heading' => __('master.case_details'),
    'rows' => [
        __('master.case_id') => '#' . $case->case_id,
        __('master.patient') => $case->patient->name ?? 'N/A',
        __('master.treatment_type') => $case->treatment_type ?? 'N/A',
    ],
])

<p style="margin:0 0 8px;">{{ __('master.case_ready_for_treatment_planning_note') }}</p>

@include('emails.partials.button', [
    'url' => route('admin.price_manager.index'),
    'label' => __('master.view_admin_dashboard'),
])

@include('emails.partials.footer')
