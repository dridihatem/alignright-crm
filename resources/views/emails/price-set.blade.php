@include('emails.partials.header', [
    'title' => __('master.price_set_for_your_case'),
    'subtitle' => __('master.price_set_subtitle'),
])

@php
    $priceRows = [
        __('master.total_price') => 'TND ' . number_format($price, 2),
    ];
    if (!empty($advancePayment)) {
        $priceRows[__('master.advance_payment')] = 'TND ' . number_format($advancePayment, 2);
        $priceRows[__('master.remaining_balance')] = 'TND ' . number_format($price - $advancePayment, 2);
    }
    $priceRows[__('master.set_by')] = $admin->name;
    $priceRows[__('master.set_on')] = now()->format('d-m-Y H:i');
@endphp

@include('emails.partials.panel', [
    'heading' => __('master.pricing_information'),
    'rows' => $priceRows,
])

@include('emails.partials.panel', [
    'heading' => __('master.case_details'),
    'rows' => [
        __('master.case_id') => '#' . $case->case_id,
        __('master.patient') => $case->patient->name ?? 'N/A',
        __('master.treatment_type') => $case->treatment_type ?? 'N/A',
    ],
])

<p style="margin:0 0 8px;">{{ __('master.please_review_and_accept_price') }}</p>

@include('emails.partials.button', [
    'url' => route('doctor.cases.show_price_acceptance', $case->id),
    'label' => __('master.review_and_accept_price'),
])

@include('emails.partials.footer')
