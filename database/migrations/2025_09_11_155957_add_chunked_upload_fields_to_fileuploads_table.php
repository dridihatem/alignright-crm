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
            // Chunked upload support
            $table->string('session_id')->nullable()->after('included_in_zip');
            $table->boolean('is_chunked_upload')->default(false)->after('session_id');
            $table->bigInteger('original_size')->nullable()->after('is_chunked_upload');
            $table->bigInteger('compressed_size')->nullable()->after('original_size');
            $table->string('file_hash')->nullable()->after('compressed_size'); // File integrity check
            $table->json('processing_metadata')->nullable()->after('file_hash'); // Store processing info
            $table->timestamp('upload_started_at')->nullable()->after('processing_metadata');
            $table->timestamp('upload_completed_at')->nullable()->after('upload_started_at');
            $table->string('optimization_status')->default('pending')->after('upload_completed_at'); // pending, processing, completed, failed
            $table->string('thumbnail_path')->nullable()->after('optimization_status'); // For image previews
            
            // Add index for session_id
            $table->index('session_id');
            $table->index(['is_chunked_upload', 'upload_status']);
            $table->index('optimization_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fileuploads', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropIndex(['is_chunked_upload', 'upload_status']);
            $table->dropIndex(['optimization_status']);
            
            $table->dropColumn([
                'session_id',
                'is_chunked_upload', 
                'original_size',
                'compressed_size',
                'file_hash',
                'processing_metadata',
                'upload_started_at',
                'upload_completed_at',
                'optimization_status',
                'thumbnail_path'
            ]);
        });
    }
};