@php
    $tableId = $tableId ?? 'patientsCasesTable';
    $statusMap = [
        'draft'         => ['bg-label-secondary', __('master.draft')],
        'pending'       => ['bg-label-warning',   __('master.pending')],
        'in_planning'   => ['bg-label-info',      __('master.in_planning')],
        'approval'      => ['bg-label-success',    __('master.approval')],
        'in_production' => ['bg-label-primary',    __('master.in_production')],
        'shipped'       => ['bg-label-success',    __('master.shipped')],
        'rejected'      => ['bg-label-danger',     __('master.rejected')],
    ];
@endphp

<div class="table-responsive">
    <table class="table table-hover" id="{{ $tableId }}" width="100%">
        <thead class="border-top">
            <tr>
                <th>{{ __('master.patient') }}</th>
                <th>{{ __('master.latest_status') }}</th>
                <th>{{ __('master.latest_case_id') }}</th>
                <th>{{ __('master.date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patientGroups as $i => $group)
                <tr data-key="{{ $i }}">
                    <td class="patient-toggle" style="cursor: pointer;">
                        <i class="icon-base ti tabler-chevron-right toggle-icon me-1 text-muted"></i>
                        <span class="fw-medium">{{ $group->patient_name }}</span>
                    </td>
                    @php $b = $statusMap[$group->latest_status] ?? ['bg-label-secondary', ucfirst($group->latest_status)]; @endphp
                    <td><span class="badge {{ $b[0] }}">{{ $b[1] }}</span></td>
                    <td><strong>{{ $group->latest_case_id }}</strong></td>
                    <td>{{ $group->latest_date ? $group->latest_date->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">{{ __('master.no_patients_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script src="{{ asset('assets/js/dataTables-all.js') }}"></script>
<script>
    (function () {
        var casesByPatient = @json($patientGroups->pluck('cases'));
        var labels = {
            caseId: @json(__('master.case_id')),
            status: @json(__('master.status')),
            treatment: @json(__('master.treatment_type')),
            date: @json(__('master.date')),
            actions: @json(__('master.action')),
            view: @json(__('master.view')),
            edit: @json(__('master.edit')),
            del: @json(__('master.delete')),
            confirmDelete: @json(__('master.confirm_delete_case'))
        };

        function buildChild(cases) {
            if (!cases || cases.length === 0) {
                return '<div class="p-3 text-muted">' + @json(__('master.no_cases_found')) + '</div>';
            }
            var html = '<table class="table table-sm mb-0">';
            html += '<thead><tr>'
                + '<th>' + labels.caseId + '</th>'
                + '<th>' + labels.status + '</th>'
                + '<th>' + labels.treatment + '</th>'
                + '<th>' + labels.date + '</th>'
                + '<th class="text-end">' + labels.actions + '</th>'
                + '</tr></thead><tbody>';
            cases.forEach(function (c) {
                html += '<tr>'
                    + '<td><a href="' + c.url + '" class="fw-medium text-primary"><strong>' + c.case_id + '</strong></a></td>'
                    + '<td>' + c.status_html + '</td>'
                    + '<td>' + (c.treatment_type || '-') + '</td>'
                    + '<td>' + c.date + '</td>'
                    + '<td class="text-end text-nowrap">'
                        + '<a href="' + c.url + '" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="' + labels.view + '"><i class="icon-base ti tabler-eye"></i></a>'
                        + '<a href="' + c.edit_url + '" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="' + labels.edit + '"><i class="icon-base ti tabler-edit"></i></a>'
                        + '<a href="' + c.delete_url + '" class="btn btn-sm btn-icon btn-text-danger rounded-pill case-delete-btn" title="' + labels.del + '"><i class="icon-base ti tabler-trash"></i></a>'
                    + '</td>'
                    + '</tr>';
            });
            html += '</tbody></table>';
            return html;
        }

        $(function () {
            var table = $('#{{ $tableId }}').DataTable({
                order: [[3, 'desc']],
                language: {
                    search: @json(__('master.search')) + ":",
                    lengthMenu: @json(__('master.show_cases_per_page')) + " _MENU_",
                    info: @json(__('master.showing_cases')) + " _START_ " + @json(__('master.to')) + " _END_ " + @json(__('master.of')) + " _TOTAL_",
                    infoEmpty: @json(__('master.no_patients_found')),
                    emptyTable: @json(__('master.no_patients_found')),
                    paginate: {
                        first: @json(__('master.first')),
                        last: @json(__('master.last')),
                        next: @json(__('master.next')),
                        previous: @json(__('master.previous'))
                    }
                }
            });

            $('#{{ $tableId }} tbody').on('click', '.case-delete-btn', function (e) {
                if (!window.confirm(labels.confirmDelete)) {
                    e.preventDefault();
                }
            });

            $('#{{ $tableId }} tbody').on('click', 'td.patient-toggle', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var icon = $(this).find('.toggle-icon');
                var key = tr.data('key');

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    icon.removeClass('tabler-chevron-down').addClass('tabler-chevron-right');
                } else {
                    row.child(buildChild(casesByPatient[key])).show();
                    tr.addClass('shown');
                    icon.removeClass('tabler-chevron-right').addClass('tabler-chevron-down');
                }
            });
        });
    })();
</script>
@endpush
