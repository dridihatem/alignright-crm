<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Collection;

trait GroupsCasesByPatient
{
    /**
     * Group a collection of cases by patient and build a summary
     * (patient name, number of cases, latest status / case id / date)
     * plus the nested list of cases for the collapsible child rows.
     *
     * @param  \Illuminate\Support\Collection  $cases   Collection of CasePatient (with `patient` relation loaded)
     * @param  array  $routes  Optional route names: ['show' => ..., 'edit' => ..., 'delete' => ...]
     * @return \Illuminate\Support\Collection
     */
    protected function buildPatientGroups(Collection $cases, array $routes = []): Collection
    {
        return $cases->groupBy('patient_id')->map(function ($group) use ($routes) {
            $latest = $group->sortByDesc('created_at')->first();

            return (object) [
                'patient_name'      => $latest->patient
                                        ? trim($latest->patient->name . ' ' . ($latest->patient->surname ?? ''))
                                        : __('master.no_patient'),
                'count'             => $group->count(),
                'latest_status'     => $latest->status,
                'latest_case_id'    => $latest->case_id,
                'latest_case_db_id' => $latest->id,
                'latest_date'       => $latest->created_at,
                'cases'             => $group->sortByDesc('created_at')->map(function ($c) use ($routes) {
                    $row = [
                        'case_id'        => $c->case_id,
                        'status'         => $c->status,
                        'status_html'    => $this->caseStatusBadge($c->status),
                        'treatment_type' => $c->treatment_type ?: '-',
                        'date'           => $c->created_at ? $c->created_at->format('d/m/Y') : '-',
                    ];

                    if (!empty($routes['show'])) {
                        $row['url'] = route($routes['show'], $c->id);
                    }
                    if (!empty($routes['edit'])) {
                        $row['edit_url'] = route($routes['edit'], $c->id);
                    }
                    if (!empty($routes['delete'])) {
                        $row['delete_url'] = route($routes['delete'], $c->id);
                    }

                    return $row;
                })->values(),
            ];
        })->sortByDesc('count')->values();
    }

    /**
     * Build a status badge HTML snippet.
     */
    protected function caseStatusBadge($status): string
    {
        $map = [
            'draft'         => ['bg-label-secondary', __('master.draft')],
            'pending'       => ['bg-label-warning',   __('master.pending')],
            'in_planning'   => ['bg-label-info',      __('master.in_planning')],
            'approval'      => ['bg-label-success',    __('master.approval')],
            'in_production' => ['bg-label-primary',    __('master.in_production')],
            'shipped'       => ['bg-label-success',    __('master.shipped')],
            'rejected'      => ['bg-label-danger',     __('master.rejected')],
        ];

        [$class, $label] = $map[$status] ?? ['bg-label-secondary', ucfirst((string) $status)];

        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }
}
