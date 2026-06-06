<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fileuploads', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('fileuploads', 'status')) {
                $table->string('status', 20)->default('pending')->after('storage_type');
            }
            
            // Add error message column
            if (!Schema::hasColumn('fileuploads', 'error_message')) {
                $table->text('error_message')->nullable()->after('status');
            }
            
            // Add Google Drive specific fields
            if (!Schema::hasColumn('fileuploads', 'google_drive_id')) {
                $table->string('google_drive_id')->nullable()->after('error_message');
            }
            
            if (!Schema::hasColumn('fileuploads', 'google_drive_link')) {
                $table->text('google_drive_link')->nullable()->after('google_drive_id');
            }
            
            if (!Schema::hasColumn('fileuploads', 'file_path')) {
                $table->text('file_path')->nullable()->after('google_drive_link');
            }
            
            if (!Schema::hasColumn('fileuploads', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            
            if (!Schema::hasColumn('fileuploads', 'temp_filename')) {
                $table->string('temp_filename')->nullable()->after('file_name');
            }
            
            if (!Schema::hasColumn('fileuploads', 'uploaded_at')) {
                $table->timestamp('uploaded_at')->nullable()->after('temp_filename');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fileuploads', function (Blueprint $table) {
            // Drop the columns in reverse order
            $table->dropColumn([
                'uploaded_at',
                'temp_filename', 
                'file_name',
                'file_path',
                'google_drive_link',
                'google_drive_id',
                'error_message',
                'status'
            ]);
        });
    }
};
