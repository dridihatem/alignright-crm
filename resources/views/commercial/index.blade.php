<x-app-layout>
    @push('styles')
    <style>
        /* Commercial Dashboard Custom Styles */
        .commercial-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .stats-card.success::before {
            background: linear-gradient(90deg, #11998e, #38ef7d);
        }
        
        .stats-card.warning::before {
            background: linear-gradient(90deg, #f093fb, #f5576c);
        }
        
        .stats-card.danger::before {
            background: linear-gradient(90deg, #ff9a9e, #fecfef);
        }
        
        .stats-card.info::before {
            background: linear-gradient(90deg, #4facfe, #00f2fe);
        }
        
        .doctor-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .doctor-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .doctor-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .doctor-card:hover::before {
            opacity: 1;
        }
        
        .invoice-table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
            padding: 1.5rem;
        }
        
       
        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-modern {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .icon-modern {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .icon-modern.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .icon-modern.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .icon-modern.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .icon-modern.danger {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            color: white;
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .page-title {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
           
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            color: #ffffff;
            margin-bottom: 0;
        }
        #doctors-grid {
           padding-left: 10px;
           padding-right: 10px;
        }
    </style>
    @endpush

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Modern Header -->
        <div class="commercial-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title mb-2">Commercial Dashboard</h1>
                    <p class="page-subtitle mb-0">Invoice and payment tracking overview</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="icon-modern primary mx-auto ms-md-auto">
                        <i class="icon-base ti tabler-chart-line icon-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="icon-modern primary mb-3">
                                <i class="icon-base ti tabler-file-invoice icon-lg"></i>
                            </div>
                            <h6 class="mb-1 text-muted">Total Invoices</h6>
                            <h3 class="mb-0 text-primary">{{ $invoiceStats['total'] }}</h3>
                            <small class="text-muted">All invoices</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stats-card success">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="icon-modern success mb-3">
                                <i class="icon-base ti tabler-check icon-lg"></i>
                            </div>
                            <h6 class="mb-1 text-muted">Paid Invoices</h6>
                            <h3 class="mb-0 text-success">{{ $invoiceStats['paid'] }}</h3>
                            <small class="text-muted">Tnd {{ number_format($invoiceStats['paid_amount'], 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stats-card warning">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="icon-modern warning mb-3">
                                <i class="icon-base ti tabler-clock icon-lg"></i>
                            </div>
                            <h6 class="mb-1 text-muted">Pending Invoices</h6>
                            <h3 class="mb-0 text-warning">{{ $invoiceStats['pending'] }}</h3>
                            <small class="text-muted">Tnd {{ number_format($invoiceStats['pending_amount'], 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stats-card danger">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="icon-modern danger mb-3">
                                <i class="icon-base ti tabler-alert-triangle icon-lg"></i>
                            </div>
                            <h6 class="mb-1 text-muted">Overdue Invoices</h6>
                            <h3 class="mb-0 text-danger">{{ $invoiceStats['overdue'] }}</h3>
                            <small class="text-muted">Need attention</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="icon-base ti tabler-users me-2 text-primary"></i>
                        Doctors Overview
                    </h5>
                    <small class="text-muted">Click on any doctor to view their cases and invoices</small>
                </div>
                <div class="col-md-6 text-end">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-modern dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-base ti tabler-filter me-1"></i>
                            Filter Doctors
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="filterDoctors('all')">All Doctors</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterDoctors('with_pending')">With Pending Invoices</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterDoctors('with_overdue')">With Overdue Invoices</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctors Grid -->
        <div class="row" id="doctors-grid">
            @foreach($doctors as $doctor)
                <div class="col-md-4 col-lg-3 mb-4 doctor-card" data-doctor-id="{{ $doctor->id }}">
                    <div class="text-center" onclick="viewDoctorCases({{ $doctor->id }})">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->name }}" class="rounded-circle">
                        </div>
                        <h6 class="mb-1">{{ $doctor->name }}</h6>
                        <p class="text-muted small mb-3">{{ $doctor->email }}</p>
                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge badge-modern bg-label-primary">{{ $doctor->cases->count() }} Cases</span>
                            <span class="badge badge-modern bg-label-success">{{ $doctor->cases->whereNotNull('price')->count() }} Priced</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Recent Invoices Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="invoice-table-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="icon-base ti tabler-file-invoice me-2 text-primary"></i>
                                Recent Invoices
                            </h5>
                            <small class="text-muted">Latest invoice activity across all doctors</small>
                        </div>
                        <button class="btn btn-primary btn-modern" onclick="refreshInvoicesTable()">
                            <i class="icon-base ti tabler-refresh me-1"></i>
                            Refresh
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern" id="invoices-table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Doctor</th>
                                        <th>Patient</th>
                                        <th>Case ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via DataTables -->
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
            // Initialize DataTable for invoices
            $('#invoices-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("commercial.invoices.data") }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'invoice_number', name: 'invoice_number' },
                    { data: 'doctor_name', name: 'doctor_name' },
                    { data: 'patient_name', name: 'patient_name' },
                    { data: 'case_id', name: 'case_id' },
                    { data: 'amount', name: 'amount' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'due_date', name: 'due_date' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                }
            });
        });

        function viewDoctorCases(doctorId) {
            window.location.href = '{{ route("commercial.doctors.cases", ":id") }}'.replace(':id', doctorId);
        }

        function filterDoctors(filter) {
            // Implementation for filtering doctors
            console.log('Filtering doctors by:', filter);
            // You can implement AJAX filtering here
        }

        function refreshInvoicesTable() {
            $('#invoices-table').DataTable().ajax.reload();
        }
    </script>
    @endpush
</x-app-layout>
