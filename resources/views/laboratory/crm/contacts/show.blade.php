<x-app-layout>
    @push('styles')
    @endpush

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ $contact->name }}</h5>
                        <div>
                            <a href="{{ route('laboratory.crm.contacts.edit', $contact->id) }}" class="btn btn-warning me-2">
                                <i class="ti ti-pencil me-1"></i> {{ __('master.edit') }}
                            </a>
                            <a href="{{ route('laboratory.crm.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> {{ __('master.back') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">{{ __('master.contact_information') }}</h6>
                                        <p><strong>{{ __('master.name') }}:</strong> {{ $contact->name }}</p>
                                        @if($contact->email)
                                            <p><strong>{{ __('master.email') }}:</strong> 
                                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                            </p>
                                        @endif
                                        @if($contact->phone)
                                            <p><strong>{{ __('master.phone') }}:</strong> 
                                                <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>
                                            </p>
                                        @endif
                                        @if($contact->company)
                                            <p><strong>{{ __('master.company') }}:</strong> {{ $contact->company }}</p>
                                        @endif
                                        @if($contact->position)
                                            <p><strong>{{ __('master.position') }}:</strong> {{ $contact->position }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">{{ __('master.crm_details') }}</h6>
                                        <p><strong>{{ __('master.status') }}:</strong> 
                                            <span class="badge bg-label-{{ $contact->status_badge }}">{{ ucfirst($contact->status) }}</span>
                                        </p>
                                        <p><strong>{{ __('master.source') }}:</strong> 
                                            <span class="badge bg-label-{{ $contact->source_badge }}">{{ ucfirst(str_replace('_', ' ', $contact->source)) }}</span>
                                        </p>
                                        <p><strong>{{ __('master.assigned_to') }}:</strong> 
                                            {{ $contact->assignedUser ? $contact->assignedUser->name : __('master.unassigned') }}
                                        </p>
                                        <p><strong>{{ __('master.created_by') }}:</strong> {{ $contact->creator->name }}</p>
                                        <p><strong>{{ __('master.created_at') }}:</strong> {{ $contact->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>

                                @if($contact->notes)
                                    <div class="mb-4">
                                        <h6 class="text-muted">{{ __('master.notes') }}</h6>
                                        <div class="border rounded p-3">
                                            {{ $contact->notes }}
                                        </div>
                                    </div>
                                @endif

                                <!-- Recent Interactions -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-muted mb-0">{{ __('master.recent_interactions') }}</h6>
                                        <a href="{{ route('laboratory.crm.contacts.interactions', $contact->id) }}" class="btn btn-sm btn-primary">
                                            {{ __('master.view_all') }}
                                        </a>
                                    </div>
                                    @if($contact->interactions->count() > 0)
                                        <div class="list-group">
                                            @foreach($contact->interactions->take(5) as $interaction)
                                                <div class="list-group-item">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h6 class="mb-1">
                                                            <i class="ti {{ $interaction->type_icon }} me-2"></i>
                                                            {{ $interaction->subject }}
                                                        </h6>
                                                        <small class="text-muted">{{ $interaction->created_at->format('M d, Y') }}</small>
                                                    </div>
                                                    <p class="mb-1">{{ Str::limit($interaction->description, 100) }}</p>
                                                    <small>
                                                        <span class="badge bg-label-{{ $interaction->status_badge }}">{{ ucfirst($interaction->status) }}</span>
                                                        <span class="badge bg-label-{{ $interaction->priority_badge }}">
                                                            {{ $interaction->priority == 1 ? __('master.low') : ($interaction->priority == 2 ? __('master.medium') : __('master.high')) }}
                                                        </span>
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="ti ti-message-circle-off text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">{{ __('master.no_interactions_yet') }}</p>
                                            <a href="{{ route('laboratory.crm.contacts.interactions', $contact->id) }}" class="btn btn-primary">
                                                {{ __('master.add_interaction') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Quick Actions -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('master.quick_actions') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('laboratory.crm.contacts.interactions', $contact->id) }}" class="btn btn-outline-primary">
                                                <i class="ti ti-message-circle me-2"></i> {{ __('master.add_interaction') }}
                                            </a>
                                            @if($contact->email)
                                                <a href="mailto:{{ $contact->email }}" class="btn btn-outline-info">
                                                    <i class="ti ti-mail me-2"></i> {{ __('master.send_email') }}
                                                </a>
                                            @endif
                                            @if($contact->phone)
                                                <a href="tel:{{ $contact->phone }}" class="btn btn-outline-success">
                                                    <i class="ti ti-phone me-2"></i> {{ __('master.call') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistics -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">{{ __('master.statistics') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <h4 class="text-primary">{{ $contact->interactions->count() }}</h4>
                                                <small class="text-muted">{{ __('master.interactions') }}</small>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-success">{{ $contact->interactions->where('status', 'completed')->count() }}</h4>
                                                <small class="text-muted">{{ __('master.completed') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @endpush
</x-app-layout>



