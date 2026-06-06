<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\CasePatient;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class CommercialController extends Controller
{
    /**
     * Display the commercial dashboard
     */
    public function index()
    {
        try {
            // Get all doctors for the dropdown
            $doctors = User::where('role_id', 2)->where('status', 'active')->get();
            
            // Get invoice statistics
            $invoiceStats = $this->getInvoiceStats();
            
            return view('commercial.index', compact('doctors', 'invoiceStats'));
        } catch (Exception $e) {
            Log::error('Error displaying commercial dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load commercial dashboard');
        }
    }

    /**
     * Get invoices grouped by doctors for DataTable
     */
    public function getInvoicesByDoctors(Request $request): JsonResponse
    {
        try {
            $query = Invoice::with(['case.patient', 'case.doctor'])
                           ->whereHas('case', function($q) {
                               $q->whereNotNull('doctor_id');
                           });

            return DataTables::of($query)
                ->addColumn('doctor_name', function ($invoice) {
                    return $invoice->case->doctor->name ?? 'N/A';
                })
                ->addColumn('patient_name', function ($invoice) {
                    return $invoice->case->patient->name ?? 'N/A';
                })
                ->addColumn('case_id', function ($invoice) {
                    return $invoice->case->case_id ?? 'N/A';
                })
                ->addColumn('amount', function ($invoice) {
                    return 'Tnd ' . number_format($invoice->total_amount, 2);
                })
                ->addColumn('due_date', function ($invoice) {
                    return $invoice->due_date ? date('Y-m-d', strtotime($invoice->due_date)) : 'N/A';
                })
                ->addColumn('status_badge', function ($invoice) {
                    return $this->getInvoiceStatusBadge($invoice->status);
                })
                ->addColumn('payment_status', function ($invoice) {
                    return $this->getPaymentStatusBadge($invoice);
                })
                ->addColumn('actions', function ($invoice) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" 
                                id="dropdownMenuButton" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item waves-effect" href="'.route('commercial.invoices.show', $invoice->id).'">'.__('master.view').'</a></li>
                            <li><a class="dropdown-item waves-effect" href="'.route('commercial.invoices.print', $invoice->id).'">'.__('master.print').'</a></li>
                        </ul>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'payment_status', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting invoices by doctors: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve invoices'], 500);
        }
    }

    /**
     * Get cases for a specific doctor
     */
    public function getDoctorCases(Request $request, $doctorId): JsonResponse
    {
        try {
            $query = CasePatient::where('doctor_id', $doctorId)
                               ->with(['patient', 'doctor'])
                               ->whereNotNull('price');

            return DataTables::of($query)
                ->addColumn('patient_name', function ($case) {
                    return $case->patient->name ?? 'N/A';
                })
                ->addColumn('case_id', function ($case) {
                    return $case->case_id ?? 'N/A';
                })
                ->addColumn('price', function ($case) {
                    return $case->price ? 'Tnd ' . number_format($case->price, 2) : 'N/A';
                })
                ->addColumn('status_badge', function ($case) {
                    return $this->getCaseStatusBadge($case->status);
                })
                ->addColumn('payment_status', function ($case) {
                    return $this->getCasePaymentStatus($case);
                })
                ->addColumn('invoice_status', function ($case) {
                    $invoice = Invoice::where('case_id', $case->id)->first();
                    if ($invoice) {
                        return $this->getInvoiceStatusBadge($invoice->status);
                    }
                    return '<span class="badge bg-label-secondary">No Invoice</span>';
                })
                ->addColumn('actions', function ($case) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" 
                                id="dropdownMenuButton" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item waves-effect" href="'.route('commercial.cases.show', $case->id).'">'.__('master.view').'</a></li>
                        </ul>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'payment_status', 'invoice_status', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting doctor cases: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve doctor cases'], 500);
        }
    }

    /**
     * Show doctor cases page
     */
    public function showDoctorCases($doctorId)
    {
        try {
            $doctor = User::where('id', $doctorId)->where('role_id', 2)->firstOrFail();
            
            // Get doctor statistics
            $doctorStats = $this->getDoctorStats($doctorId);
            
            return view('commercial.doctor-cases', compact('doctor', 'doctorStats'));
        } catch (Exception $e) {
            Log::error('Error showing doctor cases: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Doctor not found');
        }
    }

    /**
     * Show invoice details
     */
    public function showInvoice($id)
    {
        try {
            $invoice = Invoice::with(['case.patient', 'case.doctor'])
                             ->findOrFail($id);

            return view('commercial.invoices.show', compact('invoice'));
        } catch (Exception $e) {
            Log::error('Error showing invoice: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load invoice details');
        }
    }

    /**
     * Print invoice
     */
    public function printInvoice($id)
    {
        try {
            $invoice = Invoice::with(['case.patient', 'case.doctor'])
                             ->findOrFail($id);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('commercial.invoices.print', compact('invoice'));
            return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
        } catch (Exception $e) {
            Log::error('Error printing invoice: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate invoice PDF');
        }
    }

    /**
     * Show case details
     */
    public function showCase($id)
    {
        try {
            $case = CasePatient::with(['patient', 'doctor'])->findOrFail($id);
            
            // Get invoice for this case
            $invoice = Invoice::where('case_id', $id)->first();
            
            // Get payments for this case
            $payments = [];
            if ($invoice) {
                $payments = \App\Models\Payment::where('invoice_id', $invoice->id)->get();
            }

            return view('commercial.cases.show', compact('case', 'invoice', 'payments'));
        } catch (Exception $e) {
            Log::error('Error showing case: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Case not found');
        }
    }

    /**
     * Get invoice statistics
     */
    private function getInvoiceStats(): array
    {
        try {
            $invoices = Invoice::whereHas('case', function($q) {
                $q->whereNotNull('doctor_id');
            });

            return [
                'total' => $invoices->count(),
                'paid' => $invoices->where('status', 'paid')->count(),
                'pending' => $invoices->where('status', 'pending')->count(),
                'overdue' => $invoices->where('status', 'overdue')->count(),
                'total_amount' => $invoices->sum('total_amount'),
                'paid_amount' => $invoices->where('status', 'paid')->sum('total_amount'),
                'pending_amount' => $invoices->where('status', 'pending')->sum('total_amount'),
            ];
        } catch (Exception $e) {
            Log::error('Error getting invoice stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'paid' => 0,
                'pending' => 0,
                'overdue' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'pending_amount' => 0,
            ];
        }
    }

    /**
     * Get doctor statistics
     */
    private function getDoctorStats($doctorId): array
    {
        try {
            $cases = CasePatient::where('doctor_id', $doctorId);
            $caseIds = $cases->pluck('id');
            
            $invoices = Invoice::whereIn('case_id', $caseIds);

            return [
                'total_cases' => $cases->count(),
                'cases_with_price' => $cases->whereNotNull('price')->count(),
                'total_invoices' => $invoices->count(),
                'paid_invoices' => $invoices->where('status', 'paid')->count(),
                'pending_invoices' => $invoices->where('status', 'pending')->count(),
                'overdue_invoices' => $invoices->where('status', 'overdue')->count(),
                'total_amount' => $invoices->sum('total_amount'),
                'paid_amount' => $invoices->where('status', 'paid')->sum('total_amount'),
                'pending_amount' => $invoices->where('status', 'pending')->sum('total_amount'),
            ];
        } catch (Exception $e) {
            Log::error('Error getting doctor stats: ' . $e->getMessage());
            return [
                'total_cases' => 0,
                'cases_with_price' => 0,
                'total_invoices' => 0,
                'paid_invoices' => 0,
                'pending_invoices' => 0,
                'overdue_invoices' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'pending_amount' => 0,
            ];
        }
    }

    /**
     * Get invoice status badge HTML
     */
    private function getInvoiceStatusBadge($status): string
    {
        $badges = [
            'paid' => '<span class="badge bg-label-success">Paid</span>',
            'pending' => '<span class="badge bg-label-warning">Pending</span>',
            'overdue' => '<span class="badge bg-label-danger">Overdue</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">Unknown</span>';
    }

    /**
     * Get payment status badge HTML
     */
    private function getPaymentStatusBadge($invoice): string
    {
        $totalPaid = \App\Models\Payment::where('invoice_id', $invoice->id)->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            return '<span class="badge bg-label-success">Payment Complete</span>';
        } elseif ($totalPaid > 0) {
            return '<span class="badge bg-label-info">Partial Payment</span>';
        } else {
            return '<span class="badge bg-label-warning">Waiting Payment</span>';
        }
    }

    /**
     * Get case status badge HTML
     */
    private function getCaseStatusBadge($status): string
    {
        $badges = [
            'draft' => '<span class="badge bg-label-secondary">Draft</span>',
            'pending' => '<span class="badge bg-label-warning">Pending</span>',
            'in_planning' => '<span class="badge bg-label-info">In Planning</span>',
            'approval' => '<span class="badge bg-label-primary">Approval</span>',
            'rejected' => '<span class="badge bg-label-danger">Rejected</span>',
            'in_production' => '<span class="badge bg-label-success">In Production</span>',
            'shipped' => '<span class="badge bg-label-dark">Shipped</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">Unknown</span>';
    }

    /**
     * Get case payment status
     */
    private function getCasePaymentStatus($case): string
    {
        $invoice = Invoice::where('case_id', $case->id)->first();
        
        if (!$invoice) {
            return '<span class="badge bg-label-secondary">No Invoice</span>';
        }
        
        return $this->getPaymentStatusBadge($invoice);
    }
}