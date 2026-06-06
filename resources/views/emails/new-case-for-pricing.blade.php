@include('emails.partials.header', [
    'title' => __('master.new_case_requires_pricing'),
    'subtitle' => __('master.new_case_requires_pricing_subtitle'),
])

<p style="margin:0 0 8px;">{{ __('master.please_review_and_set_price') }}</p>

@include('emails.partials.panel', [
    'heading' => __('master.case_details'),
    'rows' => [
        __('master.case_id') => '#' . $case->case_id,
        __('master.patient') => $case->patient->name ?? 'N/A',
        __('master.doctor') => $doctor->name,
        __('master.treatment_type') => $case->treatment_type ?? 'N/A',
        __('master.submitted') => $case->created_at->format('d-m-Y H:i'),
        __('master.status') => ucfirst($case->status),
    ],
])

@include('emails.partials.button', [
    'url' => route('admin.price_manager.show_add_price', $case->id),
    'label' => __('master.set_price_for_this_case'),
])

@include('emails.partials.footer')
