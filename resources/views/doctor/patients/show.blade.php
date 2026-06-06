<x-app-layout>
    @push('styles')
    @endpush
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row g-6">
            <div class="col-md-12 col-xxl-12 mb-6">
                
                   
                        <div class="row">
                            <div class="col-md-4">  
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-1">{{ __('master.patient_record_sheet') }}</h5>
                                    </div>
                                <div class="card-body">
                                <div class="mb-3">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <td>{{ __('master.patient_reference') }}</td>
                                                <td>{{ $patient->reference }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.patient_name') }}</td>
                                                <td>{{ $patient->name }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.patient_surname') }}</td>
                                                <td>{{ $patient->surname }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.patient_gender') }}</td>
                                                <td>{{ ucfirst($patient->gender) }}</td>
                                            </tr>
                                           
                                            <tr>
                                                <td>{{ __('master.patient_birthday') }}</td>
                                                <td>{{ $patient->birthday }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.patient_phone') }}</td>
                                                <td>{{ $patient->phone }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.patient_email') }}</td>
                                                <td>{{ $patient->email }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.patient_address') }}</td>
                                                <td>{{ $patient->address }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.patient_city') }}</td>
                                                <td>{{ $patient->city }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.patient_country') }}</td>
                                                <td>{{ $patient->country }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.created_at') }}</td>
                                                <td>{{ $patient->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td>{{ __('master.updated_at') }}</td>
                                                <td>{{ $patient->updated_at->format('d/m/Y H:i') }}</td>
                                            </tr>

                                            <tr>
                                                <td colspan="2" class="text-center">
                                                    <a href="{{ route('doctor.patients.edit', $patient->reference) }}" class="btn btn-primary">{{ __('master.edit_patient') }}</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>


                               
                                
                                </div>
                                </div>
                            </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-1">{{ __('master.patient_cases') }}</h5>
                                    </div>
                                <div class="card-body">
                                <div class="mb-3">
                                   <table class="table table-bordered ">
                                    <thead>
                                        <tr>
                                            <th>{{ __('master.case_id') }}</th>
                                            <th>{{ __('master.case_date') }}</th>
                                            <th>{{ __('master.doctor_name') }}</th>
                                            <th>{{ __('master.case_status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($patient->cases as $case)
                                            <tr>
                                                <td><a href="{{ route('doctor.cases.show', $case->id) }}">{{ $case->case_id }}</a></td>

                                                <td>{{ $case->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $case->doctor->name }}</td>
                                                <td> @if($case->status == 'pending')
                                                <span class="badge bg-label-warning">{{ __('master.pending') }}</span>
                                                @elseif($case->status == 'draft')
                                                <span class="badge bg-label-secondary">{{ __('master.draft') }}</span>
                                                @elseif($case->status == 'in_planning')
                                                <span class="badge bg-label-info">{{ __('master.in_planning') }}</span>
                                                @elseif($case->status == 'approval')
                                                <span class="badge bg-label-success">{{ __('master.approval') }}</span>
                                                @elseif($case->status == 'in_production')
                                                <span class="badge bg-label-success">{{ __('master.in_production') }}</span>
                                                @elseif($case->status == 'shipped')
                                                <span class="badge bg-label-success">{{ __('master.shipped') }}</span>
                                                @elseif($case->status == 'rejected')
                                                <span class="badge bg-label-danger">{{ __('master.rejected') }}</span>
                                                
                                                @endif
                                              </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                   </table>
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
