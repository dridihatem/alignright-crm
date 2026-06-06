@include('emails.partials.header', [
    'title' => __('master.treatment_plan_ready_for_review'),
    'subtitle' => __('master.treatment_plan_ready_subtitle'),
])

@include('emails.partials.panel', [
    'heading' => __('master.treatment_plan_information'),
    'rows' => [
        __('master.created_by') => $technician->name,
        __('master.created_on') => now()->format('d-m-Y H:i'),
        __('master.status') => __('master.pending_doctor_review'),
    ],
])

@include('emails.partials.panel', [
    'heading' => __('master.case_details'),
    'rows' => [
        __('master.case_id') => $case->case_id,
        __('master.patient') => $case->patient->name ?? 'N/A',
        __('master.treatment_type') => $case->treatment_type ?? 'N/A',
        __('master.status') => ucfirst($case->status),
    ],
])

<p style="margin:0 0 8px;">{{ __('master.please_review_treatment_plan_note') }}</p>

@include('emails.partials.button', [
    'url' => route('doctor.cases.show_treatment_plan_acceptance', $case->id),
    'label' => __('master.review_treatment_plan'),
])

@include('emails.partials.footer')
