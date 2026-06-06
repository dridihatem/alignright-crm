<x-app-layout>
    <x-slot name="title">{{ __('master.commercial_details') }} - {{ __('master.admin') }}</x-slot>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.commercial.list') }}">{{ __('master.commercial_list') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.commercial_details') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="icon-base ti tabler-user me-2"></i>
                        {{ __('master.commercial_details') }}
                    </h5>
                    <small class="text-muted">{{ $user->name }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.commercial.edit', $user->id) }}" class="btn btn-primary btn-sm">
                        <i class="icon-base ti tabler-edit me-1"></i>
                        {{ __('master.edit') }}
                    </a>
                    <a href="{{ route('admin.commercial.list') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="icon-base ti tabler-arrow-left me-1"></i>
                        {{ __('master.back') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- User Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('master.user_information') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">{{ __('master.name') }}</label>
                                    <p class="mb-0">{{ $user->name }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted">{{ __('master.email') }}</label>
                                    <p class="mb-0">{{ $user->email }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted">{{ __('master.phone') }}</label>
                                    <p class="mb-0">{{ $user->phone ?? __('master.not_provided') }}</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">{{ __('master.role') }}</label>
                                    <p class="mb-0">
                                        <span class="badge bg-label-primary">{{ $user->role->name ?? __('master.commercial') }}</span>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted">{{ __('master.status') }}</label>
                                    <p class="mb-0">
                                        @if($user->status === 'active')
                                            <span class="badge bg-label-success">{{ __('master.active') }}</span>
                                        @elseif($user->status === 'inactive')
                                            <span class="badge bg-label-secondary">{{ __('master.inactive') }}</span>
                                        @elseif($user->status === 'suspended')
                                            <span class="badge bg-label-danger">{{ __('master.suspended') }}</span>
                                        @elseif($user->status === 'pending')
                                            <span class="badge bg-label-warning">{{ __('master.pending') }}</span>
                                        @else
                                            <span class="badge bg-label-secondary">{{ $user->status }}</span>
                                        @endif
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted">{{ __('master.created_at') }}</label>
                                    <p class="mb-0">{{ $user->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($user->address)
                        <hr>
                        <div class="mb-3">
                            <label class="form-label text-muted">{{ __('master.address') }}</label>
                            <p class="mb-0">{{ $user->address }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- User Photo & Actions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('master.profile_photo') }}</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="avatar avatar-xxl mx-auto mb-3">
                            <img src="{{ $user->photo_url }}" alt="{{ $user->name }}" class="rounded-circle">
                        </div>
                        <h6 class="mb-1">{{ $user->name }}</h6>
                        <p class="text-muted mb-3">{{ $user->email }}</p>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.commercial.edit', $user->id) }}" class="btn btn-primary">
                                <i class="icon-base ti tabler-edit me-1"></i>
                                {{ __('master.edit_profile') }}
                            </a>
                            
                            @if($user->status === 'active')
                                <form action="{{ route('admin.commercial.update', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="inactive">
                                    <button type="submit" class="btn btn-outline-warning w-100" 
                                            onclick="return confirm('{{ __('master.confirm_deactivate') }}')">
                                        <i class="icon-base ti tabler-user-off me-1"></i>
                                        {{ __('master.deactivate') }}
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.commercial.update', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-outline-success w-100" 
                                            onclick="return confirm('{{ __('master.confirm_activate') }}')">
                                        <i class="icon-base ti tabler-user-check me-1"></i>
                                        {{ __('master.activate') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Account Statistics -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('master.account_statistics') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('master.member_since') }}:</span>
                            <strong>{{ $user->created_at->format('M Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('master.last_updated') }}:</span>
                            <strong>{{ $user->updated_at->format('M Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('master.account_age') }}:</span>
                            <strong>{{ $user->created_at->diffForHumans() }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

