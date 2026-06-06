<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\CompleteTreatmentRequest;
use App\Http\Requests\Technician\UpdateEstimatedCompletionRequest;
use App\Services\TechnicianService;
use App\Models\TreatmentType;
use App\Models\CasePatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class TechnicianTreatmentController extends Controller
{
    protected $technicianService;

    public function __construct(TechnicianService $technicianService)
    {
        $this->technicianService = $technicianService;
    }

    /**
     * Get treatment types for a case
     */
    public function getTreatmentTypes(Request $request, $caseId)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Invalid request'], 400);
            }

            // Verify technician has access to this case
            CasePatient::where('id', $caseId)
                      ->where('technician_id', auth()->user()->id)
                      ->firstOrFail();

            $treatmentTypes = TreatmentType::where('case_id', $caseId)->latest()->get();

            return DataTables::of($treatmentTypes)
                ->addIndexColumn()
                ->addColumn('irp_file', function($row) {
                    return $row->irp_file;
                })
                ->addColumn('link_viewer', function($row) {
                    return $row->link_viewer;
                })
              
                ->addColumn('type_file', function($row) {
                    return $this->getFileTypeIcon($row->type_file);
                })
                ->addColumn('status', function($row) {
                    return $this->getStatusBadge($row->status);
                })
                ->addColumn('estimated_completion', function($row) {
                    return $row->estimated_completion_date 
                        ? $row->estimated_completion_date->format('d-m-Y H:i')
                        : 'Not set';
                })
                ->addColumn('action', function($row) {
                    return $this->getActionButtons($row);
                })
                ->rawColumns(['type_file', 'status', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting treatment types: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load treatment types'], 500);
        }
    }

    /**
     * Show treatment type details
     */
    public function show($id)
    {
        try {
            $treatmentType = TreatmentType::findOrFail($id);
            
            // Verify technician has access
            CasePatient::where('id', $treatmentType->case_id)
                      ->where('technician_id', auth()->user()->id)
                      ->firstOrFail();

            return view('technician.cases.treatment_request.show', compact('treatmentType'));
        } catch (Exception $e) {
            Log::error('Error showing treatment type: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Treatment type not found');
        }
    }

    /**
     * Accept treatment type
     */
    public function accept($id)
    {
        try {
            $technicianId = auth()->user()->id;
            $treatmentType = $this->technicianService->acceptTreatmentType($id, $technicianId);
            
            $case = CasePatient::find($treatmentType->case_id);
            $case->status = 'in_production';
            $case->save();
            return response()->json([
                'success' => true,
                'message' => 'Treatment type accepted successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error accepting treatment type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept treatment type'
            ], 500);
        }
    }

    /**
     * Reject treatment type
     */
    public function reject($id, Request $request)
    {
        try {
            $treatmentType = TreatmentType::findOrFail($id);
            
            // Verify technician has access
            CasePatient::where('id', $treatmentType->case_id)
                      ->where('technician_id', auth()->user()->id)
                      ->firstOrFail();

            $treatmentType->update([
                'status' => 'rejected',
                'rejected_by' => auth()->user()->id,
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Treatment type rejected successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error rejecting treatment type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject treatment type'
            ], 500);
        }
    }

    /**
     * Complete treatment type
     */
    public function complete(CompleteTreatmentRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $technicianId = auth()->user()->id;
            
            $treatmentType = $this->technicianService->completeTreatmentType(
                $id,
                $technicianId,
                $validated['wetransfer_link'],
                $validated['completion_notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Treatment type completed successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error completing treatment type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete treatment type'
            ], 500);
        }
    }

    /**
     * Update estimated completion date
     */
    public function updateEstimatedCompletion(UpdateEstimatedCompletionRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $technicianId = auth()->user()->id;
            
            $treatmentType = $this->technicianService->updateEstimatedCompletion(
                $id,
                $technicianId,
                $validated['estimated_completion_date']
            );

            return response()->json([
                'success' => true,
                'message' => 'Estimated completion date updated successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error updating estimated completion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update estimated completion date'
            ], 500);
        }
    }

    /**
     * Store new treatment type
     */
    public function store(Request $request, $caseId)
    {
        try {
            $request->validate([
                'irp_file' => 'required|string|max:255',
                'link_viewer' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'type_file' => 'required|in:pdf,link',
                'treatment_plan_file' => 'required_if:type_file,pdf|file|mimes:pdf|max:10240'
            ]);

            // Verify technician has access to this case
            $case = CasePatient::where('id', $caseId)
                              ->where('technician_id', auth()->user()->id)
                              ->firstOrFail();

            $treatmentTypeData = [
                'irp_file' => $request->irp_file,
                'link_viewer' => $request->link_viewer,
                'description' => $request->description,
                'case_id' => $caseId,
                'type_file' => $request->type_file,
                'status' => 'pending',
                'uploaded_by' => auth()->user()->id,
                'treatment_plan_uploaded_at' => now()
            ];

          
                // Handle file upload logic here
                if($request->hasFile('irp_file')){
                    $file = $request->file('irp_file');
                    $filename = time().'-'.$file->getClientOriginalName();
                    $caseFolder = 'case_files/'.$caseId;
                    $path = $file->storeAs($caseFolder, $filename, 'public');
                    $treatmentTypeData['irp_file'] = Storage::disk('public')->url($path);
                }
                // You'll need to integrate with your file upload service
               
           

            $treatmentType = TreatmentType::create($treatmentTypeData);

            return response()->json([
                'success' => true,
                'message' => 'Treatment type created successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error creating treatment type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create treatment type'
            ], 500);
        }
    }

    /**
     * Get file type icon
     */
    private function getFileTypeIcon($typeFile)
    {
        if ($typeFile === 'pdf') {
            return '<i class="icon-base ti tabler-file-text icon-md text-body-secondary"></i>';
        } elseif ($typeFile === 'link') {
            return '<i class="icon-base ti tabler-link icon-md text-body-secondary"></i>';
        }
        return '<i class="icon-base ti tabler-file icon-md text-body-secondary"></i>';
    }

    /**
     * Get status badge
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="badge bg-label-warning">Pending</span>',
            'in_progress' => '<span class="badge bg-label-info">In Progress</span>',
            'completed' => '<span class="badge bg-label-success">Completed</span>',
            'accepted' => '<span class="badge bg-label-success">Accepted</span>',
            'rejected' => '<span class="badge bg-label-danger">Rejected</span>'
        ];

        return $badges[$status] ?? '<span class="badge bg-label-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get action buttons
     */
    private function getActionButtons($treatmentType)
    {
        $buttons = '<div class="dropdown">
            <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1 waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a href="' . route('technician.treatment_types.show', $treatmentType->id) . '" target="_blank" class="dropdown-item waves-effect">View</a></li>';

        if ($treatmentType->status === 'pending') {
            $buttons .= '<li><a href="#" onclick="acceptTreatmentType(' . $treatmentType->id . ')" class="dropdown-item waves-effect text-success">Accept</a></li>';
            $buttons .= '<li><a href="#" onclick="rejectTreatmentType(' . $treatmentType->id . ')" class="dropdown-item waves-effect text-danger">Reject</a></li>';
        } elseif ($treatmentType->status === 'in_progress') {
            $buttons .= '<li><a href="#" onclick="completeTreatmentType(' . $treatmentType->id . ')" class="dropdown-item waves-effect text-primary">Complete</a></li>';
        }

        if (in_array($treatmentType->status, ['pending', 'in_progress'])) {
            $buttons .= '<li><a href="#" onclick="setEstimatedCompletion(' . $treatmentType->id . ')" class="dropdown-item waves-effect">Set Estimate</a></li>';
        }

        $buttons .= '</ul></div>';

        return $buttons;
    }
}

