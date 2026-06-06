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
        Schema::create('case_patients', function (Blueprint $table) {
            $table->id();
            $table->string('case_id')->nullable();
            $table->string('patient_id')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('laboratory_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('date')->nullable();
            $table->string('time')->nullable();
            $table->enum('status', ['draft','pending', 'in_planning', 'approval', 'rejected', 'in_production', 'shipped'])->default('draft');
            $table->string('doctor_instruction')->nullable();
            $table->string('treatment_plan')->nullable();
            $table->string('treatment_type')->nullable();
            $table->string('treatment_overjet')->nullable();
            $table->string('treatment_overbite')->nullable();
            $table->string('treatment_midline')->nullable();
            $table->string('treatment_irp')->nullable();
            $table->string('treatment_attachments')->nullable();
            $table->string('patient_chief_complaint')->nullable();
            $table->dateTime('accepted_date')->nullable();
            $table->dateTime('rejected_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_patients');
    }
};
