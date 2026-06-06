<x-app-layout>
    <x-slot name="title">{{ __('master.price_management') }} - {{ __('master.admin') }}</x-slot>
    @include('partials.case_detail_compact')

    <div class="container-xxl flex-grow-1 container-p-y case-detail-compact">
        <!-- Page header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h4 class="mb-0"><i class="icon-base ti tabler-currency-dollar me-2 text-primary"></i>{{ __('master.price_management') }}</h4>
                <small class="text-muted">{{ __('master.system_configuration') }}</small>
            </div>
            <form action="{{ route('admin.price_manager.cleanup_orphaned') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm"
                        onclick="return confirm('{{ __('master.cleanup_orphaned_confirm') }}')">
                    <i class="icon-base ti tabler-trash me-1"></i>{{ __('master.cleanup_orphaned') }}
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- KPIs -->
        <div class="row g-3 mb-2">
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                    <div><h4 class="mb-0">{{ $pendingPricing->total() }}</h4><small class="text-muted">{{ __('master.cases_waiting_for_price') }}</small></div>
                    <span class="badge bg-label-warning rounded p-2"><i class="icon-base ti tabler-clock fs-4"></i></span>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                    <div><h4 class="mb-0">{{ $pricedCases->total() }}</h4><small class="text-muted">{{ __('master.cases_waiting_doctor_acceptance') }}</small></div>
                    <span class="badge bg-label-info rounded p-2"><i class="icon-base ti tabler-hourglass fs-4"></i></span>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
                    <div><h4 class="mb-0">{{ $historicalPricedCases->total() }}</h4><small class="text-muted">{{ __('master.historical_priced_cases') }}</small></div>
                    <span class="badge bg-label-success rounded p-2"><i class="icon-base ti tabler-history fs-4"></i></span>
                </div></div>
            </div>
        </div>

        <!-- Pending Pricing -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="icon-base ti tabler-clock me-1"></i>
                    {{ __('master.cases_waiting_for_price') }} ({{ $pendingPricing->total() }})
                </h6>
            </div>
            <div class="card-body">
                @if($pendingPricing->total() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('master.case_id') }}</th>
                                    <th>{{ __('master.patient') }}</th>
                                    <th>{{ __('master.doctor') }}</th>
                                    <th>{{ __('master.technician') }}</th>
                                    <th>{{ __('master.status') }}</th>
                                    <th class="text-end">{{ __('master.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingPricing as $case)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.cases.show', $case->id) }}" class="fw-semibold text-decoration-none">{{ $case->case_id ?? 'N/A' }}</a>
                                        </td>
                                        <td>{{ $case->patient->name ?? 'N/A' }}</td>
                                        <td>{{ $case->doctor->name ?? 'N/A' }}</td>
                                        <td>{{ $case->technician->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-label-warning">{{ ucfirst(str_replace('_',' ',$case->status)) }}</span></td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.price_manager.show_add_price', $case->id) }}" class="btn btn-success btn-sm">
                                                    <i class="icon-base ti tabler-plus me-1"></i>{{ __('master.add_price') }}
                                                </a>
                                                <a href="{{ route('admin.price_manager.show', $case->id) }}" class="btn btn-label-secondary btn-sm">
                                                    <i class="icon-base ti tabler-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($pendingPricing->hasPages())
                        <div class="mt-3">{{ $pendingPricing->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="icon-base ti tabler-circle-check text-success" style="font-size: 5rem;"></i>
                        <p class="text-muted mt-2 mb-0">{{ __('master.no_cases_pending_pricing') }}</p>
                        <small class="text-muted">{{ __('master.all_cases_accepted_priced_desc') }}</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Waiting for Doctor Acceptance -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="icon-base ti tabler-hourglass me-1"></i>
                    {{ __('master.cases_waiting_doctor_acceptance') }} ({{ $pricedCases->total() }})
                </h6>
            </div>
            <div class="card-body">
                @if($pricedCases->total() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('master.case_id') }}</th>
                                    <th>{{ __('master.patient') }}</th>
                                    <th>{{ __('master.doctor') }}</th>
                                    <th>{{ __('master.price') }}</th>
                                    <th>{{ __('master.advance_payment') }}</th>
                                    <th>{{ __('master.remaining_balance') }}</th>
                                    <th>{{ __('master.price_added_by') }}</th>
                                    <th>{{ __('master.price_added_date') }}</th>
                                    <th class="text-end">{{ __('master.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pricedCases as $case)
                                    <tr>
                                        <td><a href="{{ route('admin.cases.show', $case->id) }}" class="fw-semibold text-decoration-none">{{ $case->case_id ?? 'N/A' }}</a></td>
                                        <td>{{ $case->patient->name ?? 'N/A' }}</td>
                                        <td>{{ $case->doctor->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-label-success">Tnd {{ number_format($case->price, 2) }}</span></td>
                                        <td>
                                            @forelse($case->invoices as $invoice)
                                                @if($invoice->advance_payment)<span class="badge bg-label-info">Tnd {{ number_format($invoice->advance_payment, 2) }}</span>@endif
                                            @empty <span class="text-muted">-</span> @endforelse
                                        </td>
                                        <td>
                                            @forelse($case->invoices as $invoice)
                                                @if($invoice->remaining_balance)<span class="badge bg-label-warning">Tnd {{ number_format($invoice->remaining_balance, 2) }}</span>@endif
                                            @empty <span class="text-muted">-</span> @endforelse
                                        </td>
                                        <td>{{ $case->admin->name ?? 'N/A' }}</td>
                                        <td><small class="text-muted">{{ $case->price_added_at ? $case->price_added_at->format('d-m-Y H:i') : 'N/A' }}</small></td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.price_manager.show', $case->id) }}" class="btn btn-label-secondary btn-sm">
                                                <i class="icon-base ti tabler-eye me-1"></i>{{ __('master.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($pricedCases->hasPages())
                        <div class="mt-3">{{ $pricedCases->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="icon-base ti tabler-check text-success" style="font-size:5rem;"></i>
                        <p class="text-muted mt-2 mb-0">{{ __('master.no_cases_waiting_doctor_acceptance') }}</p>
                        <small class="text-muted">{{ __('master.all_priced_cases_reviewed_desc') }}</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Historical Priced Cases -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="icon-base ti tabler-history me-1"></i>
                    {{ __('master.historical_priced_cases') }} ({{ $historicalPricedCases->total() }})
                </h6>
            </div>
            <div class="card-body">
                @if($historicalPricedCases->total() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ __('master.case_id') }}</th>
                                    <th>{{ __('master.patient') }}</th>
                                    <th>{{ __('master.doctor') }}</th>
                                    <th>{{ __('master.price') }}</th>
                                    <th>{{ __('master.advance_payment') }}</th>
                                    <th>{{ __('master.remaining_balance') }}</th>
                                    <th>{{ __('master.status') }}</th>
                                    <th>{{ __('master.price_added_date') }}</th>
                                    <th>{{ __('master.price_accepted_date') }}</th>
                                    <th class="text-end">{{ __('master.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historicalPricedCases as $case)
                                    <tr>
                                        <td><a href="{{ route('admin.cases.show', $case->id) }}" class="fw-semibold text-decoration-none">{{ $case->case_id ?? 'N/A' }}</a></td>
                                        <td>{{ $case->patient->name ?? 'N/A' }}</td>
                                        <td>{{ $case->doctor->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-label-success">Tnd {{ number_format($case->price, 2) }}</span></td>
                                        <td>
                                            @forelse($case->invoices as $invoice)
                                                @if($invoice->advance_payment)<span class="badge bg-label-info">Tnd {{ number_format($invoice->advance_payment, 2) }}</span>@endif
                                            @empty <span class="text-muted">-</span> @endforelse
                                        </td>
                                        <td>
                                            @forelse($case->invoices as $invoice)
                                                @if($invoice->remaining_balance)<span class="badge bg-label-warning">Tnd {{ number_format($invoice->remaining_balance, 2) }}</span>@endif
                                            @empty <span class="text-muted">-</span> @endforelse
                                        </td>
                                        <td>
                                            @if($case->status == 'approval')
                                                <span class="badge bg-label-secondary">{{ __('master.approval') }}</span>
                                            @elseif($case->status == 'in_production')
                                                <span class="badge bg-label-success">{{ __('master.in_production') }}</span>
                                            @elseif($case->status == 'shipped')
                                                <span class="badge bg-label-primary">{{ __('master.shipped') }}</span>
                                            @else
                                                <span class="badge bg-label-secondary">{{ ucfirst(str_replace('_',' ',$case->status)) }}</span>
                                            @endif
                                        </td>
                                        <td><small class="text-muted">{{ $case->price_added_at ? $case->price_added_at->format('d-m-Y H:i') : 'N/A' }}</small></td>
                                        <td><small class="text-muted">{{ $case->price_accepted_at ? $case->price_accepted_at->format('d-m-Y H:i') : 'N/A' }}</small></td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.price_manager.show', $case->id) }}" class="btn btn-label-secondary btn-sm">
                                                <i class="icon-base ti tabler-eye me-1"></i>{{ __('master.view') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($historicalPricedCases->hasPages())
                        <div class="mt-3">{{ $historicalPricedCases->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="icon-base ti tabler-history text-muted" style="font-size:5rem;"></i>
                        <p class="text-muted mt-2 mb-0">{{ __('master.no_historical_priced_cases') }}</p>
                        <small class="text-muted">{{ __('master.historical_priced_cases_desc') }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
