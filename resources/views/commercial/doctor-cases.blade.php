<x-app-layout>
    @push('styles')
    <style>
        /* Commercial Doctor Cases Custom Styles */
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
        
        .stats-card.info::before {
            background: linear-gradient(90deg, #4facfe, #00f2fe);
        }
        
        .cases-table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
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
        
        .icon-modern.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
           color: white;
        }
        
        .page-subtitle {
            font-size: 1.1rem;
            color: white;
            margin-bottom: 0;
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
    </style>
    @endpush

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Modern Header -->
        <div class="commercial-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title mb-2">{{ $doctor->name }} - Cases & Invoices</h1>
                    <p class="page-subtitle mb-0">{{ $doctor->email }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="icon-modern primary mx-auto ms-md-auto">
                        <i class="icon-base ti tabler-user icon-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctor Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="icon-modern primary mb-3">
                                <i class="icon-base ti tabler-files icon-lg"></i>
                            </div>
                            <h6 class="mb-1 text-muted">Total Cases</h6>
                            <h3 class="mb-0 text-primary">{{ $doctorStats['total_cases'] }}</h3>
                            <small class="text-muted">All cases</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stats-card info">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="icon-modern info mb-3">
                                <i class="icon-base ti tabler-currency-dollar icon-lg"></i>
                            </div>
                            <h6 class="mb-1 text-muted">Cases with Price</h6>
                            <h3 class="mb-0 text-info">{{ $doctorStats['cases_with_price'] }}</h3>
                            <small class="text-muted">Priced cases</small>
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
                            <h3 class="mb-0 text-success">{{ $doctorStats['paid_invoices'] }}</h3>
                            <small class="text-muted">Tnd {{ number_format($doctorStats['paid_amount'], 2) }}</small>
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
                            <h3 class="mb-0 text-warning">{{ $doctorStats['pending_invoices'] }}</h3>
                            <small class="text-muted">Tnd {{ number_format($doctorStats['pending_amount'], 2) }}</small>
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
                        <i class="icon-base ti tabler-files me-2 text-primary"></i>
                        Cases with Prices
                    </h5>
                    <small class="text-muted">View all cases with pricing information and payment status</small>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-outline-primary btn-modern" onclick="refreshCasesTable()">
                            <i class="icon-base ti tabler-refresh me-1"></i>
                            Refresh
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-modern dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="icon-base ti tabler-filter me-1"></i>
                                Filter Cases
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="filterCases('all')">All Cases</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterCases('with_invoice')">With Invoice</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterCases('paid')">Paid</a></li>
                                <li><a class="dropdown-item" href="#" onclick="filterCases('pending')">Pending Payment</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cases Table -->
        <div class="row">
            <div class="col-12">
                <div class="cases-table-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern" id="cases-table">
                                <thead>
                                    <tr>
                                        <th>Case ID</th>
                                        <th>Patient</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                        <th>Invoice Status</th>
                                        <th>Created Date</th>
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
            // Initialize DataTable for cases
            $('#cases-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("commercial.doctors.cases.data", $doctor->id) }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'case_id', name: 'case_id' },
                    { data: 'patient_name', name: 'patient_name' },
                    { data: 'price', name: 'price' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'invoice_status', name: 'invoice_status' },
                    { data: 'created_at', name: 'created_at' },
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

        function refreshCasesTable() {
            $('#cases-table').DataTable().ajax.reload();
        }

        function filterCases(filter) {
            // Implementation for filtering cases
            console.log('Filtering cases by:', filter);
            // You can implement AJAX filtering here
        }
    </script>
    @endpush
</x-app-layout>
