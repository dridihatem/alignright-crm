<x-app-layout>
    <x-slot name="title">{{ __('master.new_pricing_workflow_admin_dashboard') }}</x-slot>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="icon-base ti tabler-currency-dollar me-2"></i>
                            {{ __('master.new_pricing_workflow') }}
                        </h5>
                        <p class="card-subtitle text-muted">
                            {{ __('master.new_pricing_workflow_subtitle') }}
                        </p>
                    </div>
                    <div class="card-body">
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

                        <!-- Plans de Traitement en Attente de Tarification -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-clock me-1"></i>
                                            {{ __('master.treatment_plans_waiting_pricing') }} ({{ $pendingPricing->count() }})
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($pendingPricing->count() > 0)
                                            <div class="table-responsive mb-4">
                                                <table class="table table-sm">
                                                    <thead class="border-top">
                                                        <tr>
                                                            <th>{{ __('master.case_id') }}</th>
                                                            <th>{{ __('master.patient') }}</th>
                                                            <th>{{ __('master.doctor') }}</th>
                                                            <th>{{ __('master.technician') }}</th>
                                                            <th>{{ __('master.treatment_plan') }}</th>
                                                            <th>{{ __('master.uploaded') }}</th>
                                                            <th>{{ __('master.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($pendingPricing as $treatmentPlan)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $treatmentPlan->case->case_id ?? 'N/A' }}</strong>
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->case->patient->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->case->doctor->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->case->technician->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <strong>{{ $treatmentPlan->name }}</strong>
                                                                    @if($treatmentPlan->description)
                                                                        <br><small class="text-muted">{{ $treatmentPlan->description }}</small>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">{{ $treatmentPlan->created_at->format('d-m-Y H:i') }}</small>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group" role="group">
                                                                        <a href="{{ route('admin.new_pricing.show_add_price', $treatmentPlan->id) }}" 
                                                                           class="btn btn-success btn-sm">
                                                                            <i class="icon-base ti tabler-plus me-1"></i>{{ __('master.set_price') }}
                                                                        </a>
                                                                        <a href="{{ route('admin.new_pricing.show', $treatmentPlan->id) }}" 
                                                                           class="btn btn-info btn-sm">
                                                                            <i class="icon-base ti tabler-eye me-1"></i>{{ __('master.view') }}
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="icon-base ti tabler-circle-check text-success" style="font-size: 7rem;"></i>
                                                <p class="text-muted mt-2">{{ __('master.no_treatment_plans_waiting_pricing') }}</p>
                                                <small class="text-muted">{{ __('master.all_treatment_plans_priced') }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plans de Traitement Tarifés en Attente du Médecin -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-currency-dollar me-1"></i>
                                            {{ __('master.priced_treatment_plans_waiting_doctor') }} ({{ $pricedWaitingAcceptance->count() }})
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($pricedWaitingAcceptance->count() > 0)
                                            <div class="table-responsive mb-4">
                                                <table class="table table-sm">
                                                    <thead class="border-top">
                                                        <tr>
                                                            <th>{{ __('master.case_id') }}</th>
                                                            <th>{{ __('master.patient') }}</th>
                                                            <th>{{ __('master.doctor') }}</th>
                                                            <th>{{ __('master.treatment_plan') }}</th>
                                                            <th>{{ __('master.price') }}</th>
                                                            <th>{{ __('master.advance_payment') }}</th>
                                                            <th>{{ __('master.remaining_balance') }}</th>
                                                            <th>{{ __('master.price_set_by') }}</th>
                                                            <th>{{ __('master.price_set_date') }}</th>
                                                            <th>{{ __('master.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($pricedWaitingAcceptance as $treatmentPlan)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $treatmentPlan->case->case_id ?? 'N/A' }}</strong>
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->case->patient->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->case->doctor->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <strong>{{ $treatmentPlan->name }}</strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-label-success">Tnd {{ number_format($treatmentPlan->price, 2) }}</span>
                                                                </td>
                                                                <td>
                                                                    @if($treatmentPlan->advance_payment)
                                                                        <span class="badge bg-label-info">Tnd {{ number_format($treatmentPlan->advance_payment, 2) }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($treatmentPlan->remaining_balance)
                                                                        <span class="badge bg-label-warning">Tnd {{ number_format($treatmentPlan->remaining_balance, 2) }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->admin->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->price_added_at ? $treatmentPlan->price_added_at->format('d-m-Y H:i') : 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('admin.new_pricing.show', $treatmentPlan->id) }}" 
                                                                       class="btn btn-info btn-sm">
                                                                        <i class="icon-base ti tabler-eye me-1"></i>{{ __('master.view') }}
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="icon-base ti tabler-clock text-muted" style="font-size: 7rem;"></i>
                                                <p class="text-muted mt-2">{{ __('master.no_priced_treatment_plans_waiting_doctor') }}</p>
                                                <small class="text-muted">{{ __('master.doctors_will_see_these') }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plans de Traitement Acceptés -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-check me-1"></i>
                                            {{ __('master.accepted_treatment_plans') }} ({{ $acceptedPlans->count() }})
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($acceptedPlans->count() > 0)
                                            <div class="table-responsive mb-4">
                                                <table class="table table-sm">
                                                    <thead class="border-top">
                                                        <tr>
                                                            <th>{{ __('master.case_id') }}</th>
                                                            <th>{{ __('master.patient') }}</th>
                                                            <th>{{ __('master.doctor') }}</th>
                                                            <th>{{ __('master.treatment_plan') }}</th>
                                                            <th>{{ __('master.price') }}</th>
                                                            <th>{{ __('master.accepted_by') }}</th>
                                                            <th>{{ __('master.accepted_date') }}</th>
                                                            <th>{{ __('master.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($acceptedPlans as $treatmentPlan)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $treatmentPlan->case->case_id ?? 'N/A' }}</strong>
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->case->patient->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->case->doctor->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <strong>{{ $treatmentPlan->name }}</strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-label-success">Tnd {{ number_format($treatmentPlan->price, 2) }}</span>
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->doctor->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->accepted_at ? $treatmentPlan->accepted_at->format('d-m-Y H:i') : 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('admin.new_pricing.show', $treatmentPlan->id) }}" 
                                                                       class="btn btn-info btn-sm">
                                                                        <i class="icon-base ti tabler-eye me-1"></i>{{ __('master.view') }}
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="icon-base ti tabler-circle-check text-muted" style="font-size: 7rem;"></i>
                                                <p class="text-muted mt-2">{{ __('master.no_accepted_treatment_plans_yet') }}</p>
                                                <small class="text-muted">{{ __('master.accepted_plans_will_appear_here') }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
