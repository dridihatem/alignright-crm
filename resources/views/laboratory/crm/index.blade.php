<x-app-layout>
    @push('styles')
    @endpush

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('master.crm_contacts') }}</h5>
                        <a href="{{ route('laboratory.crm.contacts.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> {{ __('master.add_contact') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="crm-contacts-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('master.name') }}</th>
                                        <th>{{ __('master.email') }}</th>
                                        <th>{{ __('master.phone') }}</th>
                                        <th>{{ __('master.company') }}</th>
                                        <th>{{ __('master.status') }}</th>
                                        <th>{{ __('master.source') }}</th>
                                        <th>{{ __('master.assigned_to') }}</th>
                                        <th>{{ __('master.interactions') }}</th>
                                        <th>{{ __('master.last_interaction') }}</th>
                                        <th>{{ __('master.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#crm-contacts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('laboratory.crm.contacts.data') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'company', name: 'company' },
                    { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                    { data: 'source_badge', name: 'source', orderable: false, searchable: false },
                    { data: 'assigned_user', name: 'assigned_to', orderable: false },
                    { data: 'interactions_count', name: 'interactions_count', orderable: false, searchable: false },
                    { data: 'last_interaction', name: 'last_interaction', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/English.json"
                }
            });
        });
    </script>
    @endpush
</x-app-layout>



