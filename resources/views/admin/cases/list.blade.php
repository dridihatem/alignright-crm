<x-app-layout>
    <x-slot name="title">{{ __('master.cases') }} - {{ __('master.admin') }}</x-slot>

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">{{ __('master.dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('master.cases') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="icon-base ti tabler-users me-2"></i>
                        {{ __('master.patients') }}
                    </h5>
                    <small class="text-muted">{{ __('master.cases_grouped_by_patient') }}</small>
                </div>
                <a href="{{ route('admin.cases.create') }}" class="btn btn-primary">
                    <i class="icon-base ti tabler-plus me-1"></i>
                    {{ __('master.create_case') }}
                </a>
            </div>
        </div>

        <!-- Patients (cases grouped by patient) -->
        <div class="card">
            <div class="card-body">
                @include('admin.cases._patients_table', ['tableId' => 'patientsCasesTable'])
            </div>
        </div>
    </div>
</x-app-layout>
