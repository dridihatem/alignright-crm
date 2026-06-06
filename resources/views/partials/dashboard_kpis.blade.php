@php $kpis = $kpis ?? []; @endphp
<div class="row g-4 mb-4">
    @foreach($kpis as $kpi)
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-{{ $kpi['color'] ?? 'primary' }}">
                                <i class="icon-base ti {{ $kpi['icon'] ?? 'tabler-briefcase' }} icon-24px"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-1 small text-muted">{{ $kpi['label'] }}</p>
                            <h4 class="mb-0">{{ $kpi['value'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
