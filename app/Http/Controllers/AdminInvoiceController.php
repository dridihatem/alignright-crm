<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\CasePatient;
use Illuminate\Support\Facades\Log;

class AdminInvoiceController extends Controller
{
    /**
     * Display invoice management dashboard
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['case.doctor', 'case.patient', 'payments']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by invoice number or case ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('case', function($q) use ($search) {
                      $q->where('case_id', 'like', "%{$search}%")
                        ->orWhereHas('patient', function($p) use ($search) {
                            $p->where('name', 'like', "%{$search}%")
                              ->orWhere('surname', 'like', "%{$search}%");
                        });
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::where('status', Invoice::STATUS_PENDING)->count(),
            'paid_invoices' => Invoice::where('status', Invoice::STATUS_PAID)->count(),
            'overdue_invoices' => Invoice::where('status', Invoice::STATUS_OVERDUE)->count(),
            'total_revenue' => Invoice::sum('total_amount'),
            'total_collected' => Invoice::where('status', Invoice::STATUS_PAID)->sum('total_amount'),
            'total_pending' => Invoice::where('status', Invoice::STATUS_PENDING)->sum('remaining_balance'),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Show invoice details
     */
    public function show($id)
    {
        $invoice = Invoice::with(['case.doctor', 'case.patient', 'case.technician', 'payments'])
            ->findOrFail($id);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show invoice edit form
     */
    public function edit($id)
    {
        $invoice = Invoice::with(['case.doctor', 'case.patient', 'case.technician', 'payments'])
            ->findOrFail($id);

        return view('admin.invoices.edit', compact('invoice'));
    }

    /**
     * Add payment to invoice
     */
    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,check,other',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $invoice = Invoice::findOrFail($id);
            
            // Check if payment amount exceeds remaining balance
            if ($request->amount > $invoice->remaining_balance) {
                return response()->json([
                    'error' => 'Payment amount cannot exceed remaining balance'
                ], 400);
            }

            // Create payment
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes
            ]);

            // Update invoice status
            $invoice->updateStatus();

            Log::info('Payment added to invoice', [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount' => $request->amount,
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment added successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'remaining_balance' => $invoice->remaining_balance,
                    'status' => $invoice->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding payment to invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to add payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update invoice
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $invoice = Invoice::findOrFail($id);
            
            $invoice->update([
                'due_date' => $request->due_date,
                'notes' => $request->notes
            ]);

            // Update status based on due date
            $invoice->updateStatus();

            Log::info('Invoice updated', [
                'invoice_id' => $invoice->id,
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.invoices.index')->with('success', 'Invoice updated successfully');
           

        } catch (\Exception $e) {
            Log::error('Error updating invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.invoices.index')->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    /**
     * Delete payment
     */
    public function deletePayment($invoiceId, $paymentId)
    {
        try {
            $payment = Payment::where('invoice_id', $invoiceId)->findOrFail($paymentId);
            $invoice = $payment->invoice;
            
            $payment->delete();
            
            // Update invoice status
            $invoice->updateStatus();

            Log::info('Payment deleted', [
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
                'admin_id' => auth()->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully',
                'data' => [
                    'remaining_balance' => $invoice->remaining_balance,
                    'status' => $invoice->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting payment', [
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to delete payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export invoices to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = Invoice::with(['case.doctor', 'case.patient']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $invoices = $query->orderBy('created_at', 'desc')->get();

            $filename = 'invoices_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($invoices) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'Invoice Number',
                    'Case ID',
                    'Patient',
                    'Doctor',
                    'Total Amount',
                    'Advance Payment',
                    'Remaining Balance',
                    'Status',
                    'Created Date',
                    'Due Date'
                ]);

                // CSV data
                foreach ($invoices as $invoice) {
                    fputcsv($file, [
                        $invoice->invoice_number,
                        $invoice->case->case_id ?? 'N/A',
                        $invoice->case->patient->name ?? 'N/A',
                        $invoice->case->doctor->name ?? 'N/A',
                        $invoice->total_amount,
                        $invoice->advance_payment,
                        $invoice->remaining_balance,
                        $invoice->status,
                        $invoice->created_at->format('Y-m-d'),
                        $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting invoices', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->user()->id
            ]);

            return redirect()->route('admin.invoices.index')
                ->with('error', 'Failed to export invoices: ' . $e->getMessage());
        }
    }

    /**
     * Get invoice statistics for dashboard
     */
    public function getStats()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::where('status', Invoice::STATUS_PENDING)->count(),
            'paid_invoices' => Invoice::where('status', Invoice::STATUS_PAID)->count(),
            'overdue_invoices' => Invoice::where('status', Invoice::STATUS_OVERDUE)->count(),
            'total_revenue' => Invoice::sum('total_amount'),
            'total_collected' => Invoice::where('status', Invoice::STATUS_PAID)->sum('total_amount'),
            'total_pending' => Invoice::where('status', Invoice::STATUS_PENDING)->sum('remaining_balance'),
            'monthly_revenue' => Invoice::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
