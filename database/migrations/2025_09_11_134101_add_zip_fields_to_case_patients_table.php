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
        Schema::table('case_patients', function (Blueprint $table) {
            $table->string('zip_status')->nullable()->after('status')->comment('Status of ZIP creation: processing, completed, failed, no_files');
            $table->string('zip_google_drive_id')->nullable()->after('zip_status')->comment('Google Drive ID of the ZIP file');
            $table->text('zip_google_drive_link')->nullable()->after('zip_google_drive_id')->comment('Google Drive link to the ZIP file');
            $table->timestamp('zip_created_at')->nullable()->after('zip_google_drive_link')->comment('When the ZIP was created and uploaded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_patients', function (Blueprint $table) {
            $table->dropColumn(['zip_status', 'zip_google_drive_id', 'zip_google_drive_link', 'zip_created_at']);
        });
    }
};
