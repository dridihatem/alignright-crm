<x-app-layout>
    <x-slot name="title">{{ __('master.add_price_to_case') }}</x-slot>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="icon-base ti tabler-currency-dollar me-2"></i>
                            {{ __('master.add_price_to_case') }}
                        </h5>
                        <a href="{{ route('admin.price_manager.index') }}" class="btn btn-secondary">
                            <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('master.back_to_price_manager') }}
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Case Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-user me-1"></i>
                                            {{ __('master.case_information') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.case_id') }}:</label>
                                            <p class="mb-0">{{ $case->case_id }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.patient') }}:</label>
                                            <p class="mb-0">{{ $case->patient->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.doctor') }}:</label>
                                            <p class="mb-0">{{ $case->doctor->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.technician') }}:</label>
                                            <p class="mb-0">{{ $case->technician->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.treatment_type') }}:</label>
                                            <p class="mb-0">{{ $case->treatment_type ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.current_status') }}:</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-warning">{{ ucfirst($case->status) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Treatment Plans Summary -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-file-text me-1"></i>
                                            {{ __('master.treatment_plans_summary') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.total_treatment_plans') }}:</label>
                                            <p class="mb-0">{{ $case->treatmentType->count() }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.accepted_plans') }}:</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-success">{{ $case->treatmentType->where('status', 'accepted')->count() }} {{ __('master.accepted') }}</span>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">{{ __('master.status') }}:</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-success">{{ __('master.all_plans_accepted') }}</span>
                                            </p>
                                        </div>
                                        @if($case->treatmentType->count() > 0)
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">{{ __('master.treatment_plans_label') }}:</label>
                                                <div class="list-group list-group-flush">
                                                    @foreach($case->treatmentType as $treatmentPlan)
                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>{{ $treatmentPlan->name }}</strong>
                                                                @if($treatmentPlan->description)
                                                                    <br><small class="text-muted">{{ Str::limit($treatmentPlan->description, 50) }}</small>
                                                                @endif
                                                            </div>
                                                            <span class="badge bg-label-success">{{ ucfirst($treatmentPlan->status) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Form -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="icon-base ti tabler-currency-dollar me-1"></i>
                                            {{ __('master.set_treatment_price') }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('admin.price_manager.add_price', $case->id) }}" method="POST">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="price" class="form-label">{{ __('master.treatment_price_tnd') }} *</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Tnd</span>
                                                            <input type="number" 
                                                                   class="form-control @error('price') is-invalid @enderror" 
                                                                   id="price" 
                                                                   name="price" 
                                                                   value="{{ old('price') }}" 
                                                                   step="any"
                                                                   min="0" 
                                                                   required
                                                                   placeholder="0.00">
                                                        </div>
                                                        @error('price')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <small class="form-text text-muted">
                                                            {{ __('master.total_price_help') }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="advance_payment" class="form-label">{{ __('master.advance_payment_tnd') }}</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">Tnd</span>
                                                            <input type="number" 
                                                                   class="form-control @error('advance_payment') is-invalid @enderror" 
                                                                   id="advance_payment" 
                                                                   name="advance_payment" 
                                                                   value="{{ old('advance_payment') }}"
                                                                   step="any"
                                                                   min="0"
                                                                   placeholder="0.00">
                                                        </div>
                                                        @error('advance_payment')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <small class="form-text text-muted">
                                                            {{ __('master.advance_payment_help') }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="estimated_completion_date" class="form-label">{{ __('master.estimated_completion_date') }}</label>
                                                        <input type="date" 
                                                               class="form-control @error('estimated_completion_date') is-invalid @enderror" 
                                                               id="estimated_completion_date" 
                                                               name="estimated_completion_date" 
                                                               value="{{ old('estimated_completion_date') }}">
                                                        @error('estimated_completion_date')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <small class="form-text text-muted">
                                                            {{ __('master.estimated_completion_date_help') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between">
                                                        <a href="{{ route('admin.price_manager.index') }}" 
                                                           class="btn btn-secondary">
                                                            <i class="icon-base ti tabler-x me-1"></i>{{ __('master.cancel') }}
                                                        </a>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="icon-base ti tabler-check me-1"></i>
                                                            {{ __('master.add_price_move_production') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info" role="alert">
                                    <h6 class="alert-heading">
                                        <i class="icon-base ti tabler-info-circle me-1"></i>
                                        {{ __('master.important_information') }}
                                    </h6>
                                    <ul class="mb-0">
                                        <li>{{ __('master.add_price_note_1') }}</li>
                                        <li>{{ __('master.add_price_note_2') }}</li>
                                        <li>{{ __('master.add_price_note_3') }}</li>
                                        <li>{{ __('master.add_price_note_4') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Add price formatting
           /* $('#price, #advance_payment').on('input', function() {
                let value = $(this).val();
                if (value && !isNaN(value)) {
                    $(this).val(parseFloat(value).toFixed(2));
                }
            });*/

            // Form validation
            $('form').on('submit', function(e) {
                let price = $('#price').val();
                let advancePayment = $('#advance_payment').val();

                if (!price || price <= 0) {
                    e.preventDefault();
                    alert('{{ __('master.please_enter_valid_price') }}');
                    return false;
                }

                if (advancePayment && parseFloat(advancePayment) > parseFloat(price)) {
                    e.preventDefault();
                    alert('{{ __('master.advance_cannot_exceed_price') }}');
                    return false;
                }

                // Confirm action
                if (!confirm('{{ __('master.confirm_add_price') }}')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
