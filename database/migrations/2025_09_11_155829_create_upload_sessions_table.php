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
        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 36)->unique()->index(); // UUID length
            $table->unsignedBigInteger('case_id');
            $table->unsignedBigInteger('user_id');
            $table->string('original_filename');
            $table->string('file_type');
            $table->string('mime_type');
            $table->bigInteger('total_size');
            $table->integer('total_chunks');
            $table->integer('chunk_size')->default(2097152); // 2MB default
            $table->integer('uploaded_chunks')->default(0);
            $table->string('status')->default('pending'); // pending, uploading, completed, failed, cancelled
            $table->string('file_category'); // upper_scan, lower_scan, bite_scan, photo_clinic_01, etc.
            $table->text('metadata')->nullable(); // JSON metadata
            $table->string('final_file_path')->nullable();
            $table->string('temp_directory')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('case_id')->references('id')->on('case_patients')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for performance
            $table->index(['case_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_sessions');
    }
};