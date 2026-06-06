<x-app-layout>
    <x-slot name="title">{{ __('master.treatment_plans_doctor_dashboard') }}</x-slot>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="icon-base ti tabler-file-text me-2"></i>
                            {{ __('master.treatment_plans_with_prices') }}
                        </h5>
                        <p class="card-subtitle text-muted">
                            {{ __('master.review_accept_treatment_plans') }}
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

                        <!-- Treatment Plans with Prices Waiting for Acceptance -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-currency-dollar me-1"></i>
                                            {{ __('master.priced_treatment_plans_waiting_acceptance') }} ({{ $pricedWaitingAcceptance->count() }})
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
                                                            <th>{{ __('master.technician') }}</th>
                                                            <th>{{ __('master.treatment_plan') }}</th>
                                                            <th>{{ __('master.price') }}</th>
                                                            <th>{{ __('master.advance_payment') }}</th>
                                                            <th>{{ __('master.remaining_balance') }}</th>
                                                            <th>{{ __('master.estimated_completion') }}</th>
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
                                                                    <br><small class="text-muted">{{ $treatmentPlan->case->patient->surname ?? 'N/A' }}</small>
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
                                                                    @if($treatmentPlan->estimated_completion_date)
                                                                        <small class="text-muted">{{ $treatmentPlan->estimated_completion_date->format('d-m-Y') }}</small>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group" role="group">
                                                                        <form action="{{ route('doctor.new_treatment_plans.accept', $treatmentPlan->id) }}" method="POST" class="d-inline">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-success btn-sm" 
                                                                                    onclick="return confirm('{{ __('master.are_you_sure_reject') }} {{ __('master.treatment_plan') }}?')">
                                                                                <i class="icon-base ti tabler-check me-1"></i>{{ __('master.accept') }}
                                                                            </button>
                                                                        </form>
                                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                                onclick="showRejectModal({{ $treatmentPlan->id }}, '{{ $treatmentPlan->name }}')">
                                                                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.reject') }}
                                                                        </button>
                                                                        <a href="{{ route('doctor.new_treatment_plans.show', $treatmentPlan->id) }}" 
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
                                                <i class="icon-base ti tabler-clock text-muted" style="font-size: 7rem;"></i>
                                                <p class="text-muted mt-2">{{ __('master.no_priced_treatment_plans_waiting_acceptance') }}</p>
                                                <small class="text-muted">{{ __('master.all_treatment_plans_reviewed') }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accepted Treatment Plans Section -->
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
                                                            <th>{{ __('master.technician') }}</th>
                                                            <th>{{ __('master.treatment_plan') }}</th>
                                                            <th>{{ __('master.price') }}</th>
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
                                                                    <br><small class="text-muted">{{ $treatmentPlan->case->patient->surname ?? 'N/A' }}</small>
                                                                </td>
                                                                <td>
                                                                    {{ $treatmentPlan->case->technician->name ?? 'N/A' }}
                                                                </td>
                                                                <td>
                                                                    <strong>{{ $treatmentPlan->name }}</strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-label-success">Tnd {{ number_format($treatmentPlan->price, 2) }}</span>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">{{ $treatmentPlan->accepted_at ? $treatmentPlan->accepted_at->format('d-m-Y H:i') : 'N/A' }}</small>
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('doctor.new_treatment_plans.show', $treatmentPlan->id) }}" 
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

    <!-- Reject Treatment Plan Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">{{ __('master.reject_treatment_plan') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>{{ __('master.are_you_sure_reject') }} <strong id="treatmentPlanName"></strong>?</p>
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">{{ __('master.rejection_reason_optional') }}</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                      placeholder="{{ __('master.provide_rejection_reason') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('master.cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('master.reject_treatment_plan_button') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showRejectModal(treatmentPlanId, treatmentPlanName) {
            document.getElementById('treatmentPlanName').textContent = treatmentPlanName;
            document.getElementById('rejectForm').action = `/doctor/new-treatment-plans/${treatmentPlanId}/reject`;
            
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }
    </script>
</x-app-layout>
