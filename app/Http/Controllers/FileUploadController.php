<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use App\Providers\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileUploadController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    public function index()
    {
        $user = Auth::user();
        
        if (!$user->google_access_token) {
            return view('doctor.google_connect');
        }

        $this->driveService->setAccessToken(json_decode($user->google_access_token, true));
        $files = $this->driveService->listFiles();

        return view('doctor.files.index', compact('files'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'case_id' => 'required|exists:case_patients,id',
            'description' => 'nullable|string'
        ]);

        $user = Auth::user();
        $this->driveService->setAccessToken(json_decode($user->google_access_token, true));

        // Upload to Google Drive
        $uploadedFile = $this->driveService->uploadFile($request->file('file'));

        // Save to database
        $fileUpload = FileUpload::create([
            'name' => $uploadedFile['name'],
            'path' => $uploadedFile['id'],
            'type' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'url' => $uploadedFile['url'],
            'case_id' => $request->case_id,
            'patient_id' => $request->patient_id
        ]);

        return redirect()->back()
            ->with('success', 'File uploaded successfully');
    }

    public function destroy($id)
    {
        $file = FileUpload::findOrFail($id);
        $user = Auth::user();
        
        $this->driveService->setAccessToken(json_decode($user->google_access_token, true));
        $this->driveService->deleteFile($file->path);
        
        $file->delete();

        return redirect()->back()
            ->with('success', 'File deleted successfully');
    }
} 