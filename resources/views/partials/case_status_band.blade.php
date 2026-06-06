@php
    $totalCases = $totalCases ?? 0;
    $tiles = [
        ['secondary', 'tabler-alert-triangle',   $status_draft ?? 0,         __('master.draft')],
        ['info',      'tabler-cell-signal-1',     $status_pending ?? 0,       __('master.pending_waiting')],
        ['warning',   'tabler-cell-signal-2',     $status_in_planning ?? 0,   __('master.in_planning')],
        ['primary',   'tabler-cell-signal-3',     $status_approval ?? 0,      __('master.approval')],
        ['success',   'tabler-building-factory',  $status_in_production ?? 0, __('master.in_production')],
        ['success',   'tabler-cube-send',         $status_shipped ?? 0,       __('master.shipped')],
        ['danger',    'tabler-ban',               $status_rejected ?? 0,      __('master.rejected')],
    ];
@endphp
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between pb-0">
                <div class="card-title mb-0">
                    <h5 class="mb-1">{{ __('master.case_status') }}</h5>
                    <p class="card-subtitle mb-0">{{ $totalCases }} {{ __('master.cases') }}</p>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    @foreach($tiles as $tile)
                        <div class="col-6 col-sm-4 col-xl">
                            <div class="d-flex flex-column align-items-center p-3 rounded bg-label-{{ $tile[0] }} h-100">
                                <i class="icon-base ti {{ $tile[1] }} icon-md mb-2"></i>
                                <h4 class="mb-0">{{ $tile[2] }}</h4>
                                <small>{{ $tile[3] }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
