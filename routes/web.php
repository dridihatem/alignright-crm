<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\DoctorsController;
use App\Http\Controllers\TechniciansController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TreatmentPlanController;

use App\Http\Controllers\WeTransferController;
use App\Http\Controllers\AdminInvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\CommercialController;
use App\Http\Controllers\Admin\AdminCommercialController;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        elseif ($user->isDoctor()) {
            return redirect()->route('doctor.dashboard');

        } elseif ($user->isTechnician()) {
            return redirect()->route('technician.dashboard');
        } elseif ($user->isLaboratory()) {
            return redirect()->route('laboratory.dashboard');
        } elseif ($user->isCommercial()) {
            return redirect()->route('commercial.dashboard');
        }
        
    }
    return redirect()->route('login');
})->name('home');

Route::get('/check/code/doctor', [DoctorsController::class, 'check_code_doctor'])->name('check.code.doctor');

Route::middleware(['auth'])->group(function () {
   /* Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');*/

    // Per-case chat between roles (admin/doctor/technician/laboratory)
    Route::get('/case-chat/{case}/{channel}', [\App\Http\Controllers\CaseChatController::class, 'messages'])->name('case.chat.messages');
    Route::post('/case-chat/{case}/{channel}', [\App\Http\Controllers\CaseChatController::class, 'send'])->name('case.chat.send');

    // Messenger-style inbox (all roles)
    Route::get('/messages', [\App\Http\Controllers\MessagesController::class, 'index'])->name('messages.index');
    Route::get('/messages/conversations', [\App\Http\Controllers\MessagesController::class, 'conversations'])->name('messages.conversations');

    // Admin routes
    Route::middleware([CheckRole::class . ':admin'])->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/cases/list',[AdminController::class, 'cases'])->name('admin.cases.list');
        Route::get('/admin/getcases',[AdminController::class, 'getcases'])->name('admin.getcases');
        Route::get('/admin/cases/table',[AdminController::class, 'cases_table'])->name('admin.cases.table');
        Route::get('/admin/cases/create',[AdminController::class, 'cases_create'])->name('admin.cases.create');
        Route::get('/admin/cases/show/{id}',[AdminController::class, 'cases_show'])->name('admin.cases.show');
        Route::post('/admin/cases/{id}/add-comment',[AdminController::class, 'addCaseComment'])->name('admin.cases.add_comment');
        Route::get('/admin/cases/edit/{id}',[AdminController::class, 'cases_edit'])->name('admin.cases.edit');
        Route::get('/admin/cases/delete/{id}',[AdminController::class, 'cases_delete'])->name('admin.cases.delete');
        Route::post('/admin/cases/store',[AdminController::class, 'cases_store'])->name('admin.cases.store');
        Route::put('/admin/cases/update/{id}',[AdminController::class, 'cases_update'])->name('admin.cases.update');
        Route::get('/admin/cases/change_status/{id}/{status}',[AdminController::class, 'cases_change_status'])->name('admin.cases.change_status');
        Route::get('/admin/cases/change_priority/{id}/{priority}',[AdminController::class, 'cases_change_priority'])->name('admin.cases.change_priority');
        Route::post('/admin/cases/{id}/assign-technician',[AdminController::class, 'assign_technician'])->name('admin.cases.assign-technician');
        Route::post('/admin/cases/{id}/assign-laboratory',[AdminController::class, 'assign_laboratory'])->name('admin.cases.assign-laboratory');
        Route::post('/admin/cases/mass-delete',[AdminController::class, 'cases_mass_delete'])->name('admin.cases.mass-delete');


       
        Route::get('/admin/settings',[AdminController::class, 'settings'])->name('admin.settings');
        Route::get('/admin/doctors/list',[AdminController::class, 'doctors'])->name('admin.doctors.list');
        Route::get('/admin/getdoctors',[AdminController::class, 'getdoctors'])->name('admin.getdoctors');
        Route::get('/admin/doctors/create',[AdminController::class, 'doctors_create'])->name('admin.doctors.create');
        Route::post('/admin/doctors',[AdminController::class, 'doctors_store'])->name('admin.doctors.store');
        Route::put('/admin/doctors/{id}',[AdminController::class, 'doctors_update'])->name('admin.doctors.update');
        Route::get('/admin/doctors/edit/{id}',[AdminController::class, 'doctors_edit'])->name('admin.doctors.edit');
        Route::get('/admin/doctors/delete/{id}',[AdminController::class, 'doctors_delete'])->name('admin.doctors.delete');
        Route::get('/admin/doctors/show/{id}',[AdminController::class, 'doctors_show'])->name('admin.doctors.show');
        Route::get('/admin/doctors/{id}/cases',[AdminController::class, 'doctors_cases'])->name('admin.doctors.cases');
        Route::get('/admin/doctors/{id}/cases/export',[AdminController::class, 'doctors_cases_export'])->name('admin.doctors.cases.export');

        // Laboratory routes
        Route::get('/admin/laboratories', [AdminController::class, 'laboratories'])->name('admin.laboratories.list');
        Route::get('/admin/laboratories/getlaboratories', [AdminController::class, 'getlaboratories'])->name('admin.laboratories.getlaboratories');
        Route::get('/admin/laboratories/create', [AdminController::class, 'laboratories_create'])->name('admin.laboratories.create');
        Route::post('/admin/laboratories/store', [AdminController::class, 'laboratories_store'])->name('admin.laboratories.store');
        Route::get('/admin/laboratories/{id}', [AdminController::class, 'laboratories_show'])->name('admin.laboratories.show');
        Route::get('/admin/laboratories/{id}/edit', [AdminController::class, 'laboratories_edit'])->name('admin.laboratories.edit');
        Route::put('/admin/laboratories/{id}/update', [AdminController::class, 'laboratories_update'])->name('admin.laboratories.update');
        Route::delete('/admin/laboratories/{id}/delete', [AdminController::class, 'laboratories_delete'])->name('admin.laboratories.delete');

        // Commercial Management Routes
        Route::get('/admin/commercial', [AdminCommercialController::class, 'index'])->name('admin.commercial.list');
        Route::get('/admin/commercial/getcommercial', [AdminCommercialController::class, 'getCommercialUsers'])->name('admin.commercial.getcommercial');
        Route::get('/admin/commercial/create', [AdminCommercialController::class, 'create'])->name('admin.commercial.create');
        Route::post('/admin/commercial/store', [AdminCommercialController::class, 'store'])->name('admin.commercial.store');
        Route::get('/admin/commercial/{id}', [AdminCommercialController::class, 'show'])->name('admin.commercial.show');
        Route::get('/admin/commercial/{id}/edit', [AdminCommercialController::class, 'edit'])->name('admin.commercial.edit');
        Route::put('/admin/commercial/{id}/update', [AdminCommercialController::class, 'update'])->name('admin.commercial.update');
        Route::delete('/admin/commercial/{id}/delete', [AdminCommercialController::class, 'destroy'])->name('admin.commercial.delete');

        // Technician routes
        Route::get('/admin/technicians', [AdminController::class, 'technicians'])->name('admin.technicians.list');
        Route::get('/admin/technicians/gettechnicians', [AdminController::class, 'gettechnicians'])->name('admin.technicians.gettechnicians');
        Route::get('/admin/technicians/create', [AdminController::class, 'technicians_create'])->name('admin.technicians.create');
        Route::post('/admin/technicians/store', [AdminController::class, 'technicians_store'])->name('admin.technicians.store');
        Route::get('/admin/technicians/{id}', [AdminController::class, 'technicians_show'])->name('admin.technicians.show');
        Route::get('/admin/technicians/{id}/edit', [AdminController::class, 'technicians_edit'])->name('admin.technicians.edit');
        Route::put('/admin/technicians/{id}/update', [AdminController::class, 'technicians_update'])->name('admin.technicians.update');
        Route::delete('/admin/technicians/{id}/delete', [AdminController::class, 'technicians_delete'])->name('admin.technicians.delete');

        // Admin Price Manager Routes (Case-based Pricing)
        Route::get('/admin/price-manager', [App\Http\Controllers\Admin\AdminPriceManagerController::class, 'index'])->name('admin.price_manager.index');
        Route::get('/admin/price-manager/case/{id}/add-price', [App\Http\Controllers\Admin\AdminPriceManagerController::class, 'showAddPrice'])->name('admin.price_manager.show_add_price');
        Route::post('/admin/price-manager/case/{id}/add-price', [App\Http\Controllers\Admin\AdminPriceManagerController::class, 'addPrice'])->name('admin.price_manager.add_price');
        Route::get('/admin/price-manager/case/{id}', [App\Http\Controllers\Admin\AdminPriceManagerController::class, 'show'])->name('admin.price_manager.show');
        Route::post('/admin/price-manager/cleanup-orphaned', [App\Http\Controllers\Admin\AdminPriceManagerController::class, 'cleanupOrphaned'])->name('admin.price_manager.cleanup_orphaned');
        Route::get('/admin/price-manager/stats', [App\Http\Controllers\Admin\AdminPriceManagerController::class, 'getPricingStats'])->name('admin.price_manager.stats');

        // Treatment Plan Routes (Admin)
        Route::post('/admin/treatment-plan/add-price', [TreatmentPlanController::class, 'addPrice'])->name('admin.treatment-plan.add-price');


        Route::get('/admin/treatment-plans/pending', [TreatmentPlanController::class, 'pendingPlans'])->name('admin.treatment-plans.pending');

        // Invoice Management Routes
        Route::get('/admin/invoices', [AdminInvoiceController::class, 'index'])->name('admin.invoices.index');
        Route::get('/admin/invoices/export', [AdminInvoiceController::class, 'export'])->name('admin.invoices.export');
        Route::get('/admin/invoices/stats', [AdminInvoiceController::class, 'getStats'])->name('admin.invoices.stats');
        Route::get('/admin/invoices/{id}', [AdminInvoiceController::class, 'show'])->name('admin.invoices.show');
        Route::get('/admin/invoices/{id}/edit', [AdminInvoiceController::class, 'edit'])->name('admin.invoices.edit');
        Route::post('/admin/invoices/{id}/payments', [AdminInvoiceController::class, 'addPayment'])->name('admin.invoices.add-payment');
        Route::put('/admin/invoices/{id}', [AdminInvoiceController::class, 'update'])->name('admin.invoices.update');
        Route::delete('/admin/invoices/{invoiceId}/payments/{paymentId}', [AdminInvoiceController::class, 'deletePayment'])->name('admin.invoices.delete-payment');
        
        // Settings Routes
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::put('/admin/settings/general', [AdminController::class, 'updateGeneralSettings'])->name('admin.settings.general.update');
        Route::put('/admin/settings/email', [AdminController::class, 'updateEmailSettings'])->name('admin.settings.email.update');
        Route::put('/admin/settings/google-drive', [AdminController::class, 'updateGoogleDriveSettings'])->name('admin.settings.google-drive.update');
        Route::put('/admin/settings/system', [AdminController::class, 'updateSystemSettings'])->name('admin.settings.system.update');
        Route::put('/admin/settings/appearance', [AdminController::class, 'updateAppearanceSettings'])->name('admin.settings.appearance.update');
        Route::post('/admin/settings/test-email', [AdminController::class, 'testEmail'])->name('admin.settings.test-email');
        Route::post('/admin/settings/regenerate-identifiers', [AdminController::class, 'regenerateIdentifiers'])->name('admin.settings.regenerate-identifiers');
        Route::get('/admin/search', [AdminController::class, 'globalSearch'])->name('admin.search');
        Route::post('/admin/settings/test-google-drive', [AdminController::class, 'testGoogleDrive'])->name('admin.settings.test-google-drive');
        Route::get('/admin/settings/test-backup', [AdminController::class, 'testBackupSystem'])->name('admin.settings.test-backup');
        Route::post('/admin/settings/create-backup', [AdminController::class, 'createBackup'])->name('admin.settings.create-backup');
        Route::post('/admin/settings/restore-backup', [AdminController::class, 'restoreBackup'])->name('admin.settings.restore-backup');
        Route::get('/admin/settings/backup-history', [AdminController::class, 'getBackupHistory'])->name('admin.settings.backup-history');
        Route::get('/admin/settings/download-backup/{filename}', [AdminController::class, 'downloadBackup'])->name('admin.settings.download-backup');
        Route::delete('/admin/settings/delete-backup/{filename}', [AdminController::class, 'deleteBackup'])->name('admin.settings.delete-backup');
        
    });

    // Doctor routes
    Route::middleware([CheckRole::class . ':doctor'])->group(function () {
        Route::get('/doctor', [DoctorsController::class, 'index'])->name('doctor.dashboard');
        Route::get('/doctor/getcases', [DoctorsController::class, 'latest_cases'])->name('doctor.latest_cases');
        //page cases
       
        Route::get('/doctor/cases/list', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'index'])->name('doctor.cases');   
        Route::get('/doctor/cases/create', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'create'])->name('doctor.cases.create');  
        Route::post('/doctor/cases/store', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'store'])->name('doctor.cases.store');

// File upload routes for chunked uploads
Route::post('/doctor/files/upload', [App\Http\Controllers\Doctor\DoctorFileController::class, 'upload'])->name('doctor.files.upload');
Route::get('/doctor/files/test', [App\Http\Controllers\Doctor\DoctorFileController::class, 'test'])->name('doctor.files.test');
Route::post('/doctor/files/upload-chunk', [App\Http\Controllers\Doctor\DoctorFileController::class, 'uploadChunk'])->name('doctor.files.upload-chunk');
Route::post('/doctor/files/combine-chunks', [App\Http\Controllers\Doctor\DoctorFileController::class, 'combineChunks'])->name('doctor.files.combine-chunks');
Route::delete('/doctor/files/remove/{id}', [App\Http\Controllers\Doctor\DoctorFileController::class, 'removeFile'])->name('doctor.files.remove');   
        Route::get('/doctor/cases/show/{id}', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'show'])->name('doctor.cases.show');   
        Route::get('/doctor/search', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'search'])->name('doctor.search');
        Route::post('/doctor/cases/{id}/request-finition', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'requestFinition'])->name('doctor.cases.request_finition');
        Route::get('/doctor/cases/edit/{id}', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'edit'])->name('doctor.cases.edit');   
        Route::put('/doctor/cases/update/{id}', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'update'])->name('doctor.cases.update');   
        Route::get('/doctor/cases/delete/{id}', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'destroy'])->name('doctor.cases.delete');
        
        // File upload routes
        Route::get('/doctor/cases/{id}/upload-files', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'showUploadFiles'])->name('doctor.cases.upload-files');
        Route::post('/doctor/cases/{id}/upload-files', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'uploadFiles'])->name('doctor.cases.upload-files');
        Route::put('/doctor/cases/{id}/upload-files', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'uploadFiles'])->name('doctor.cases.upload-files.put');
        
        
         // Uppy TUS Upload routes
        Route::prefix('doctor/uppy')->name('doctor.uppy.')->group(function () {
            // Debug routes
            Route::get('/test', function() {
                return response()->json([
                    'message' => 'Uppy routes working', 
                    'user' => auth()->user()->name ?? 'guest',
                    'timestamp' => now()
                ]);
            })->name('test');
            
            Route::get('/debug/{uploadId}', function($uploadId) {
                $uploadData = cache()->get("tus_upload_{$uploadId}");
                return response()->json([
                    'upload_id' => $uploadId,
                    'upload_data' => $uploadData,
                    'cache_exists' => $uploadData !== null
                ]);
            })->name('debug');
            
            Route::options('/upload', [App\Http\Controllers\Doctor\UppyUploadController::class, 'options'])->name('options');
            Route::post('/upload', [App\Http\Controllers\Doctor\UppyUploadController::class, 'create'])->name('create');
            Route::match(['HEAD'], '/upload/{uploadId}', [App\Http\Controllers\Doctor\UppyUploadController::class, 'head'])->name('head');
            Route::patch('/upload/{uploadId}', [App\Http\Controllers\Doctor\UppyUploadController::class, 'patch'])->name('patch');
            Route::delete('/upload/{uploadId}', [App\Http\Controllers\Doctor\UppyUploadController::class, 'delete'])->name('delete');
        });

        // Legacy Chunked Upload API routes (keep for now)
        Route::prefix('doctor/chunked-upload')->name('doctor.chunked-upload.')->group(function () {
        Route::get('/test', function() {
            return response()->json(['success' => true, 'message' => 'Chunked upload routes working', 'user' => auth()->user()->name]);
        })->name('test');
        
        Route::post('/simple-test', function() {
            return response()->json(['success' => true, 'message' => 'Simple POST test working']);
        })->name('simple-test');
            Route::get('/debug', [App\Http\Controllers\Doctor\ChunkedUploadController::class, 'debug'])->name('debug');
            Route::post('/initialize', [App\Http\Controllers\Doctor\ChunkedUploadController::class, 'initializeUpload'])->name('initialize');
            Route::post('/upload-chunk', [App\Http\Controllers\Doctor\ChunkedUploadController::class, 'uploadChunk'])->name('upload-chunk');
            Route::get('/status/{sessionId}', [App\Http\Controllers\Doctor\ChunkedUploadController::class, 'getStatus'])->name('status');
            Route::post('/complete/{sessionId}', [App\Http\Controllers\Doctor\ChunkedUploadController::class, 'completeUpload'])->name('complete');
            Route::delete('/cancel/{sessionId}', [App\Http\Controllers\Doctor\ChunkedUploadController::class, 'cancelUpload'])->name('cancel');
        });
        
        Route::get('/doctor/cases/getCases', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'getCases'])->name('doctor.cases.getCases');   
        Route::get('/doctor/cases/exportPdf', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'exportPdf'])->name('doctor.cases.exportPdf');   
        Route::get('/doctor/cases/upload-progress/{id}', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'getUploadProgress'])->name('doctor.cases.upload_progress');
        
        // Doctor Comment Routes
        Route::post('/doctor/cases/add_comment', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'addComment'])->name('doctor.cases.add_comment');
        Route::get('/doctor/cases/get_comments/{id}', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'getComments'])->name('doctor.cases.get_comments');
        
        // Doctor Price Management Routes
        Route::post('/doctor/cases/{id}/accept-price', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'acceptPrice'])->name('doctor.cases.accept_price');
        Route::post('/doctor/cases/{id}/reject-price', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'rejectPrice'])->name('doctor.cases.reject_price');
        Route::get('/doctor/cases/{id}/accept-treatment-plan', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'acceptTreatmentPlan'])->name('doctor.cases.accept_treatment_plan');
        Route::get('/doctor/cases/{id}/price-acceptance', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'showPriceAcceptance'])->name('doctor.cases.show_price_acceptance');
        Route::get('/doctor/cases/{id}/treatment-plan-acceptance', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'showTreatmentPlanAcceptance'])->name('doctor.cases.show_treatment_plan_acceptance');
        Route::get('/doctor/cases/waiting-for-price-acceptance', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'getCasesWaitingForPriceAcceptance'])->name('doctor.cases.waiting_for_price_acceptance');
        Route::get('/doctor/cases/waiting-for-treatment-plan-acceptance', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'getCasesWaitingForTreatmentPlanAcceptance'])->name('doctor.cases.waiting_for_treatment_plan_acceptance');
        
        // Doctor Invoice Routes
        Route::get('/doctor/invoices/getInvoices', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'getInvoices'])->name('doctor.invoices.getInvoices');
        Route::get('/doctor/invoices/{id}', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'showInvoice'])->name('doctor.invoices.show');
        Route::get('/doctor/invoices/{id}/print', [App\Http\Controllers\Doctor\DoctorCaseController::class, 'printInvoice'])->name('doctor.invoices.print');   

        

        //change status
        Route::get('/doctor/cases/change_status/{id}/{status}', [DoctorsController::class, 'change_status'])->name('doctor.cases.change_status');   
        Route::get('/doctor/cases/{id}/affected-users', [DoctorsController::class, 'get_affected_users'])->name('doctor.cases.affected-users');
        Route::post('/doctor/cases/{id}/change-status/{status}', [DoctorsController::class, 'change_status'])->name('doctor.cases.change-status');
        Route::get('/doctor/cases/{id}/send-notification', [DoctorsController::class, 'send_notification'])->name('doctor.cases.send_notification');
        
       
        //comments
        Route::post('/doctor/cases/add_comment', [DoctorsController::class, 'add_comment'])->name('doctor.cases.add_comment');
        Route::get('/doctor/cases/get_comments/{id}', [DoctorsController::class, 'get_comments'])->name('doctor.cases.get_comments');

        // Treatment Plan Routes (Doctor)
Route::post('/doctor/treatment-plan/accept', [TreatmentPlanController::class, 'accept'])->name('doctor.treatment-plan.accept');
Route::post('/doctor/treatment-plan/reject', [TreatmentPlanController::class, 'reject'])->name('doctor.treatment-plan.reject');
Route::get('/doctor/cases/{id}/treatment-plan/view', [TreatmentPlanController::class, 'view'])->name('doctor.treatment-plan.view');

       
        //page patients
        Route::get('/doctor/patients/list', [DoctorsController::class, 'patients_list'])->name('doctor.patients');   
        Route::get('/doctor/getpatients', [DoctorsController::class, 'getpatients'])->name('doctor.getpatients');
        Route::get('/doctor/patients/create', [DoctorsController::class, 'patients_create'])->name('doctor.patients.create');  
        Route::get('/doctor/patients/show/{reference}', [DoctorsController::class, 'patients_show'])->name('doctor.patients.show'); 
        Route::get('/doctor/patients/edit/{reference}', [DoctorsController::class, 'patients_edit'])->name('doctor.patients.edit');   
        Route::get('/doctor/patients/delete/{reference}', [DoctorsController::class, 'patients_delete'])->name('doctor.patients.delete');   
        Route::post('/doctor/patients/store', [DoctorsController::class, 'patients_store'])->name('doctor.patients.store');    
        Route::put('/doctor/patients/update/{reference}', [DoctorsController::class, 'patients_update'])->name('doctor.patients.update');   
        // tickets
        Route::get('/doctor/tickets/list', [DoctorsController::class, 'tickets'])->name('doctor.tickets.index');
        Route::get('/doctor/tickets/get_tickets', [DoctorsController::class, 'get_tickets'])->name('doctor.tickets.get_tickets');
        Route::get('/doctor/tickets/create', [DoctorsController::class, 'tickets_create'])->name('doctor.tickets.create');
        Route::post('/doctor/tickets/store', [DoctorsController::class, 'tickets_store'])->name('doctor.tickets.store');
        Route::get('/doctor/tickets/edit/{id}', [DoctorsController::class, 'tickets_edit'])->name('doctor.tickets.edit');
        Route::get('/doctor/tickets/show/{id}', [DoctorsController::class, 'tickets_show'])->name('doctor.tickets.show');
        Route::get('/doctor/tickets/delete/{id}', [DoctorsController::class, 'tickets_delete'])->name('doctor.tickets.delete');
        Route::get('/doctor/tickets/change_status/{id}/{status}', [DoctorsController::class, 'tickets_change_status'])->name('doctor.tickets.change_status');
        Route::get('/doctor/tickets/change_priority/{id}/{priority}', [DoctorsController::class, 'tickets_change_priority'])->name('doctor.tickets.change_priority');
        //calendar
        Route::get('/doctor/calendar', [DoctorsController::class, 'calendar'])->name('doctor.calendar.index');
        Route::get('/doctor/calendar/events', [DoctorsController::class, 'calendar_events'])->name('doctor.calendar.events');
        Route::post('/doctor/calendar/store', [DoctorsController::class, 'calendar_store'])->name('doctor.calendar.store');
        Route::get('/doctor/calendar/create', [DoctorsController::class, 'calendar_create'])->name('doctor.calendar.create');
        Route::get('/doctor/calendar/edit/{id}', [DoctorsController::class, 'calendar_edit'])->name('doctor.calendar.edit');
        Route::get('/doctor/calendar/delete/{id}', [DoctorsController::class, 'calendar_delete'])->name('doctor.calendar.delete');
        Route::put('/doctor/calendar/update/{id}', [DoctorsController::class, 'calendar_update'])->name('doctor.calendar.update');


        Route::get('/doctor/notifications/delete/{id}', [DoctorsController::class, 'notifications_delete'])->name('doctor.notifications.delete');


        Route::get('/doctor/treatment_types_list/{id}', [DoctorsController::class, 'treatment_types_list'])->name('doctor.treatment_types_list');
        Route::get('/doctor/treatment_types/accept/{id}', [DoctorsController::class, 'treatment_types_accept'])->name('doctor.treatment_types.accept');
        Route::get('/doctor/treatment_types/show/{id}', [DoctorsController::class, 'treatment_types_show'])->name('doctor.treatment_types.show');
        Route::get('/doctor/treatment_types/reject/{id}', [DoctorsController::class, 'treatment_types_reject'])->name('doctor.treatment_types.reject');
        Route::post('/doctor/treatment_types/store/{id}', [DoctorsController::class, 'treatment_types_store'])->name('doctor.treatment_types.store');
        Route::get('/doctor/cases/treatment_type/{id}', [DoctorsController::class, 'treatment_type'])->name('doctor.treatment_type');
        Route::get('/doctor/cases/treatment_type/share/{id}', [DoctorsController::class, 'treatment_type_share'])->name('doctor.treatment_type.share');
        Route::get('/doctor/cases/treatment_type/delete/{id}', [DoctorsController::class, 'treatment_types_delete'])->name('doctor.treatment_types.delete');


        //google drive
        Route::get('/doctor/google/drive', [DoctorsController::class, 'google_drive'])->name('doctor.google.drive');
        Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.auth');
        Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

    });


    // Technician routes
    Route::middleware([CheckRole::class . ':technician'])->group(function () {
        Route::get('/technician', [TechniciansController::class, 'index'])->name('technician.dashboard');

        // Technician Cases
        Route::get('/technician/cases/list', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'index'])->name('technician.cases.index');
        Route::get('/technician/getcases', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'getCasesData'])->name('technician.latest_cases');
        Route::get('/technician/cases/show/{id}', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'show'])->name('technician.cases.show');
        Route::get('/technician/search', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'search'])->name('technician.search');
        Route::post('/technician/cases/{id}/finition', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'storeFinition'])->name('technician.cases.store_finition');
        Route::put('/technician/cases/update/{id}', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'addComment'])->name('technician.cases.update');


        Route::get('/technician/cases/updateStatus/{id}/{status}', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'updateStatus'])->name('technician.cases.updateStatus');  
        Route::post('/technician/cases/add_comment', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'addComment'])->name('technician.cases.add_comment');
        Route::get('/technician/cases/get_comments/{id}', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'getComments'])->name('technician.cases.get_comments');
        
        // WeTransfer Laboratory Notification Routes
        Route::post('/technician/cases/send-wetransfer-notification', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'sendWeTransferNotification'])->name('technician.cases.send_wetransfer_notification');
        Route::get('/technician/cases/{id}/recent-wetransfer-links', [\App\Http\Controllers\Technician\TechnicianCaseController::class, 'getRecentWeTransferLinks'])->name('technician.cases.recent_wetransfer_links');

        Route::get('/technician/calendar', [TechniciansController::class, 'calendar'])->name('technician.calendar.index');


            
        Route::get('/technician/treatment_types_list/{id}', [TechniciansController::class, 'treatment_types_list'])->name('technician.treatment_types_list');
        Route::get('/technician/treatment_types/accept/{id}', [TechniciansController::class, 'treatment_types_accept'])->name('technician.treatment_types.accept');
        Route::get('/technician/treatment_types/show/{id}', [TechniciansController::class, 'treatment_types_show'])->name('technician.treatment_types.show');
        Route::get('/technician/treatment_types/reject/{id}', [TechniciansController::class, 'treatment_types_reject'])->name('technician.treatment_types.reject');
        Route::post('/technician/treatment_types/store/{id}', [TechniciansController::class, 'treatment_types_store'])->name('technician.treatment_types.store');
        Route::delete('/technician/treatment_types/remove-file/{id}', [TechniciansController::class, 'removeTreatmentFile'])->name('technician.treatment-types.remove-file');
        
        // Enhanced technician functionality - Treatment Types
        Route::put('/technician/treatment_types/{id}/estimated-completion', [\App\Http\Controllers\Technician\TechnicianTreatmentController::class, 'updateEstimatedCompletion'])->name('technician.treatment_types.update_estimated_completion');
        Route::post('/technician/treatment_types/{id}/complete', [\App\Http\Controllers\Technician\TechnicianTreatmentController::class, 'complete'])->name('technician.treatment_types.complete');
        Route::post('/technician/treatment_types/{id}/accept', [\App\Http\Controllers\Technician\TechnicianTreatmentController::class, 'accept'])->name('technician.treatment_types.accept_new');
        
        Route::get('/technician/cases/treatment_type/{id}', [TechniciansController::class, 'treatment_type'])->name('technician.treatment_type');
        Route::get('/technician/cases/treatment_type/share/{id}', [TechniciansController::class, 'treatment_type_share'])->name('technician.treatment_type.share');


        //comments
        Route::post('/technician/cases/add_comment', [TechniciansController::class, 'add_comment'])->name('technician.cases.add_comment');
        Route::get('/technician/cases/get_comments/{id}', [TechniciansController::class, 'get_comments'])->name('technician.cases.get_comments');

        // Treatment Plan Routes (Technician)
Route::post('/technician/treatment-plan/upload', [TreatmentPlanController::class, 'upload'])->name('technician.treatment-plan.upload');
Route::get('/technician/cases/{id}/treatment-plan/view', [TreatmentPlanController::class, 'view'])->name('technician.treatment-plan.view');
Route::delete('/technician/treatment-plan/delete/{id}', [TreatmentPlanController::class, 'delete'])->name('technician.treatment-plan.delete');

        // WeTransfer Routes
        Route::post('/technician/cases/{id}/wetransfer/add', [WeTransferController::class, 'addLink'])->name('technician.wetransfer.add');
        Route::put('/technician/cases/{id}/wetransfer/update', [WeTransferController::class, 'updateLink'])->name('technician.wetransfer.update');

        // CRM Routes
        Route::get('/technician/crm', [\App\Http\Controllers\Technician\CrmController::class, 'index'])->name('technician.crm.index');
        Route::get('/technician/crm/contacts/data', [\App\Http\Controllers\Technician\CrmController::class, 'getContacts'])->name('technician.crm.contacts.data');
        Route::get('/technician/crm/contacts/create', [\App\Http\Controllers\Technician\CrmController::class, 'create'])->name('technician.crm.contacts.create');
        Route::post('/technician/crm/contacts', [\App\Http\Controllers\Technician\CrmController::class, 'store'])->name('technician.crm.contacts.store');
        Route::get('/technician/crm/contacts/{id}', [\App\Http\Controllers\Technician\CrmController::class, 'show'])->name('technician.crm.contacts.show');
        Route::get('/technician/crm/contacts/{id}/edit', [\App\Http\Controllers\Technician\CrmController::class, 'edit'])->name('technician.crm.contacts.edit');
        Route::put('/technician/crm/contacts/{id}', [\App\Http\Controllers\Technician\CrmController::class, 'update'])->name('technician.crm.contacts.update');
        Route::delete('/technician/crm/contacts/{id}', [\App\Http\Controllers\Technician\CrmController::class, 'destroy'])->name('technician.crm.contacts.destroy');
        Route::get('/technician/crm/contacts/{id}/interactions', [\App\Http\Controllers\Technician\CrmController::class, 'interactions'])->name('technician.crm.contacts.interactions');
        Route::post('/technician/crm/contacts/{id}/interactions', [\App\Http\Controllers\Technician\CrmController::class, 'storeInteraction'])->name('technician.crm.contacts.interactions.store');
        Route::put('/technician/crm/interactions/{id}/status', [\App\Http\Controllers\Technician\CrmController::class, 'updateInteractionStatus'])->name('technician.crm.interactions.status');

        // FAQ Routes
        Route::get('/technician/faq', [\App\Http\Controllers\Technician\FaqController::class, 'index'])->name('technician.faq.index');
        Route::get('/technician/faq/{id}', [\App\Http\Controllers\Technician\FaqController::class, 'show'])->name('technician.faq.show');
        Route::get('/technician/faq/category/{slug}', [\App\Http\Controllers\Technician\FaqController::class, 'category'])->name('technician.faq.category');
        Route::get('/technician/faq/search', [\App\Http\Controllers\Technician\FaqController::class, 'search'])->name('technician.faq.search');
        Route::post('/technician/faq/{id}/helpful', [\App\Http\Controllers\Technician\FaqController::class, 'markHelpful'])->name('technician.faq.helpful');
        Route::post('/technician/faq/{id}/not-helpful', [\App\Http\Controllers\Technician\FaqController::class, 'markNotHelpful'])->name('technician.faq.not-helpful');

    });

    // Laboratory routes
    Route::middleware([CheckRole::class . ':laboratory'])->group(function () {
        // Dashboard routes
        Route::get('/laboratory', [\App\Http\Controllers\Laboratory\LaboratoryDashboardController::class, 'index'])->name('laboratory.dashboard');
        Route::get('/laboratory/getcases', [\App\Http\Controllers\Laboratory\LaboratoryDashboardController::class, 'getLatestCases'])->name('laboratory.latest_cases');
        
        // Case routes
        Route::get('/laboratory/cases/list', [\App\Http\Controllers\Laboratory\LaboratoryCaseController::class, 'index'])->name('laboratory.cases.index');
        Route::get('/laboratory/cases/data', [\App\Http\Controllers\Laboratory\LaboratoryCaseController::class, 'getCasesData'])->name('laboratory.cases.data');
        Route::get('/laboratory/cases/show/{id}', [\App\Http\Controllers\Laboratory\LaboratoryCaseController::class, 'show'])->name('laboratory.cases.show');
        Route::get('/laboratory/search', [\App\Http\Controllers\Laboratory\LaboratoryCaseController::class, 'search'])->name('laboratory.search');
        Route::get('/laboratory/cases/updateStatus/{id}/{status}', [\App\Http\Controllers\Laboratory\LaboratoryCaseController::class, 'updateStatus'])->name('laboratory.cases.updateStatus');
        Route::post('/laboratory/cases/add_comment', [\App\Http\Controllers\Laboratory\LaboratoryCaseController::class, 'addComment'])->name('laboratory.cases.add_comment');

        //tickets (keeping old controller for now)
        Route::get('/laboratory/tickets/list', [LaboratoryController::class, 'tickets'])->name('laboratory.tickets.index');
        Route::get('/laboratory/tickets/create', [LaboratoryController::class, 'tickets_create'])->name('laboratory.tickets.create');
        Route::get('/laboratory/tickets/show/{id}', [LaboratoryController::class, 'tickets_show'])->name('laboratory.tickets.show');
        Route::post('/laboratory/tickets/reply/{id}', [LaboratoryController::class, 'tickets_reply'])->name('laboratory.tickets.reply');
        Route::get('/laboratory/calendar', [LaboratoryController::class, 'calendar'])->name('laboratory.calendar.index');

        //treatment types (keeping old controller for now)
        Route::get('/laboratory/treatment_types_list/{id}', [LaboratoryController::class, 'treatment_types_list'])->name('laboratory.treatment_types_list');
        Route::get('/laboratory/treatment_types/accept/{id}', [LaboratoryController::class, 'treatment_types_accept'])->name('laboratory.treatment_types.accept');
        Route::get('/laboratory/treatment_types/show/{id}', [LaboratoryController::class, 'treatment_types_show'])->name('laboratory.treatment_types.show');
        Route::get('/laboratory/treatment_types/reject/{id}', [LaboratoryController::class, 'treatment_types_reject'])->name('laboratory.treatment_types.reject');
        Route::post('/laboratory/treatment_types/store/{id}', [LaboratoryController::class, 'treatment_types_store'])->name('laboratory.treatment_types.store');
        Route::get('/laboratory/cases/treatment_type/{id}', [LaboratoryController::class, 'treatment_type'])->name('laboratory.treatment_type');
        Route::get('/laboratory/cases/treatment_type/share/{id}', [LaboratoryController::class, 'treatment_type_share'])->name('laboratory.treatment_type.share');
        Route::get('/laboratory/cases/treatment_type/delete/{id}', [LaboratoryController::class, 'treatment_types_delete'])->name('laboratory.treatment_types.delete');

        //legacy routes (keeping old controller for compatibility)
        Route::get('/laboratory/cases/get_comments/{id}', [LaboratoryController::class, 'get_comments'])->name('laboratory.cases.get_comments');

        // Treatment Plan Routes (View Only)
        Route::get('/laboratory/cases/{id}/treatment-plan/view', [TreatmentPlanController::class, 'view'])->name('laboratory.treatment-plan.view');

        // WeTransfer Routes
        Route::get('/laboratory/cases/{id}/wetransfer/view', [WeTransferController::class, 'viewLink'])->name('laboratory.wetransfer.view');
        Route::post('/laboratory/cases/{id}/wetransfer/ship', [WeTransferController::class, 'markShipped'])->name('laboratory.wetransfer.ship');

        // CRM Routes
        Route::get('/laboratory/crm', [\App\Http\Controllers\Laboratory\CrmController::class, 'index'])->name('laboratory.crm.index');
        Route::get('/laboratory/crm/contacts/data', [\App\Http\Controllers\Laboratory\CrmController::class, 'getContacts'])->name('laboratory.crm.contacts.data');
        Route::get('/laboratory/crm/contacts/create', [\App\Http\Controllers\Laboratory\CrmController::class, 'create'])->name('laboratory.crm.contacts.create');
        Route::post('/laboratory/crm/contacts', [\App\Http\Controllers\Laboratory\CrmController::class, 'store'])->name('laboratory.crm.contacts.store');
        Route::get('/laboratory/crm/contacts/{id}', [\App\Http\Controllers\Laboratory\CrmController::class, 'show'])->name('laboratory.crm.contacts.show');
        Route::get('/laboratory/crm/contacts/{id}/edit', [\App\Http\Controllers\Laboratory\CrmController::class, 'edit'])->name('laboratory.crm.contacts.edit');
        Route::put('/laboratory/crm/contacts/{id}', [\App\Http\Controllers\Laboratory\CrmController::class, 'update'])->name('laboratory.crm.contacts.update');
        Route::delete('/laboratory/crm/contacts/{id}', [\App\Http\Controllers\Laboratory\CrmController::class, 'destroy'])->name('laboratory.crm.contacts.destroy');
        Route::get('/laboratory/crm/contacts/{id}/interactions', [\App\Http\Controllers\Laboratory\CrmController::class, 'interactions'])->name('laboratory.crm.contacts.interactions');
        Route::post('/laboratory/crm/contacts/{id}/interactions', [\App\Http\Controllers\Laboratory\CrmController::class, 'storeInteraction'])->name('laboratory.crm.contacts.interactions.store');
        Route::put('/laboratory/crm/interactions/{id}/status', [\App\Http\Controllers\Laboratory\CrmController::class, 'updateInteractionStatus'])->name('laboratory.crm.interactions.status');

        // FAQ Routes
        Route::get('/laboratory/faq', [\App\Http\Controllers\Laboratory\FaqController::class, 'index'])->name('laboratory.faq.index');
        Route::get('/laboratory/faq/{id}', [\App\Http\Controllers\Laboratory\FaqController::class, 'show'])->name('laboratory.faq.show');
        Route::get('/laboratory/faq/category/{slug}', [\App\Http\Controllers\Laboratory\FaqController::class, 'category'])->name('laboratory.faq.category');
        Route::get('/laboratory/faq/search', [\App\Http\Controllers\Laboratory\FaqController::class, 'search'])->name('laboratory.faq.search');
        Route::post('/laboratory/faq/{id}/helpful', [\App\Http\Controllers\Laboratory\FaqController::class, 'markHelpful'])->name('laboratory.faq.helpful');
        Route::post('/laboratory/faq/{id}/not-helpful', [\App\Http\Controllers\Laboratory\FaqController::class, 'markNotHelpful'])->name('laboratory.faq.not-helpful');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Admin Settings Routes
    Route::middleware([CheckRole::class . ':admin'])->group(function () {
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::put('/admin/settings/general/update', [AdminController::class, 'updateGeneralSettings'])->name('admin.settings.general.update');
        Route::put('/admin/settings/email/update', [AdminController::class, 'updateEmailSettings'])->name('admin.settings.email.update');
        Route::put('/admin/settings/google-drive/update', [AdminController::class, 'updateGoogleDriveSettings'])->name('admin.settings.google-drive.update');
        Route::put('/admin/settings/system/update', [AdminController::class, 'updateSystemSettings'])->name('admin.settings.system.update');
        Route::post('/admin/settings/backup/create', [AdminController::class, 'createBackup'])->name('admin.settings.backup.create');
        Route::post('/admin/settings/backup/restore', [AdminController::class, 'restoreBackup'])->name('admin.settings.backup.restore');
    });

    // New Pricing Workflow Routes (Admin sets price first, then doctor accepts)
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/new-pricing', [App\Http\Controllers\Admin\AdminNewPricingController::class, 'index'])->name('new_pricing.index');
        Route::get('/new-pricing/{id}/add-price', [App\Http\Controllers\Admin\AdminNewPricingController::class, 'showAddPrice'])->name('new_pricing.show_add_price');
        Route::post('/new-pricing/{id}/add-price', [App\Http\Controllers\Admin\AdminNewPricingController::class, 'addPrice'])->name('new_pricing.add_price');
        Route::get('/new-pricing/{id}', [App\Http\Controllers\Admin\AdminNewPricingController::class, 'show'])->name('new_pricing.show');
    });

    Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/new-treatment-plans', [App\Http\Controllers\Doctor\DoctorNewTreatmentPlanController::class, 'index'])->name('new_treatment_plans.index');
        Route::post('/new-treatment-plans/{id}/accept', [App\Http\Controllers\Doctor\DoctorNewTreatmentPlanController::class, 'accept'])->name('new_treatment_plans.accept');
        Route::post('/new-treatment-plans/{id}/reject', [App\Http\Controllers\Doctor\DoctorNewTreatmentPlanController::class, 'reject'])->name('new_treatment_plans.reject');
        Route::get('/new-treatment-plans/{id}', [App\Http\Controllers\Doctor\DoctorNewTreatmentPlanController::class, 'show'])->name('new_treatment_plans.show');
    });


    // Language switching route
    Route::get('language/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');

    // Google Drive Routes
    Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {

       
        
        Route::get('files', [FileUploadController::class, 'index'])->name('files.index');
Route::post('files', [FileUploadController::class, 'store'])->name('files.store');
Route::delete('files/{id}', [FileUploadController::class, 'destroy'])->name('files.destroy');
    });

    // Commercial Routes
    Route::middleware(['auth', 'role:commercial'])->prefix('commercial')->name('commercial.')->group(function () {
        Route::get('/', [CommercialController::class, 'index'])->name('dashboard');
        Route::get('/invoices', [CommercialController::class, 'getInvoicesByDoctors'])->name('invoices.data');
        Route::get('/doctors/{doctorId}/cases', [CommercialController::class, 'getDoctorCases'])->name('doctors.cases.data');
        Route::get('/doctors/{doctorId}', [CommercialController::class, 'showDoctorCases'])->name('doctors.cases');
        Route::get('/invoices/{id}', [CommercialController::class, 'showInvoice'])->name('invoices.show');
        Route::get('/invoices/{id}/print', [CommercialController::class, 'printInvoice'])->name('invoices.print');
        Route::get('/cases/{id}', [CommercialController::class, 'showCase'])->name('cases.show');
    });
});

require __DIR__.'/auth.php';
