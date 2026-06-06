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
        Schema::create('we_transfer_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id');
            $table->unsignedBigInteger('technician_id');
            $table->unsignedBigInteger('laboratory_id');
            $table->text('wetransfer_link');
            $table->text('message');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('case_patients')->onDelete('cascade');
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('laboratory_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['case_id', 'technician_id']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('we_transfer_notifications');
    }
};
