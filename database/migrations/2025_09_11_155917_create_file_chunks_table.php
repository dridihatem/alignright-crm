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
        Schema::create('file_chunks', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 36)->index(); // UUID length
            $table->integer('chunk_number');
            $table->integer('chunk_size');
            $table->string('chunk_hash')->nullable(); // MD5 or SHA1 hash for integrity
            $table->string('chunk_path');
            $table->string('status')->default('pending'); // pending, uploaded, verified, failed
            $table->timestamp('uploaded_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('session_id')->references('session_id')->on('upload_sessions')->onDelete('cascade');

            // Composite unique constraint to prevent duplicate chunks
            $table->unique(['session_id', 'chunk_number']);

            // Indexes for performance (limit session_id length for MySQL)
            $table->index('session_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_chunks');
    }
};