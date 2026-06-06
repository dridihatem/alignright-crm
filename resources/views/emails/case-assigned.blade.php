@include('emails.partials.header', [
    'title' => __('master.new_case_assignment'),
    'subtitle' => __('master.new_case_assignment_subtitle'),
])

<p style="margin:0 0 12px;">{{ __('master.hello') }} {{ $assignedUser->name }},</p>
<p style="margin:0 0 8px;">{!! __('master.you_have_been_assigned_as', ['role' => '<strong>' . ucfirst($assignedRole) . '</strong>']) !!}</p>

@include('emails.partials.panel', [
    'heading' => __('master.case_information'),
    'rows' => array_filter([
        __('master.case_id') => '#' . $case->case_id,
        __('master.patient') => $case->patient->name ?? 'N/A',
        __('master.doctor') => $case->doctor->name ?? 'N/A',
        __('master.treatment_type') => $case->treatment_type ?? 'N/A',
        __('master.status') => ucfirst($case->status ?? 'Pending'),
        __('master.created') => $case->created_at ? $case->created_at->format('d-m-Y H:i') : 'N/A',
        __('master.description') => $case->description ?: null,
    ]),
])

@include('emails.partials.button', [
    'url' => route('admin.cases.show', $case->id),
    'label' => __('master.view_case_details'),
])

@include('emails.partials.footer')
