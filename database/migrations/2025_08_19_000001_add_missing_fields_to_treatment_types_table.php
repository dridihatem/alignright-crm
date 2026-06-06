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
        Schema::table('treatment_types', function (Blueprint $table) {
            // Add missing fields that should be in treatment_types instead of case_patients
            $table->string('treatment_plan_file')->nullable()->after('description');
            $table->string('treatment_plan_google_drive_id')->nullable()->after('treatment_plan_file');
            $table->decimal('advance_payment', 10, 2)->nullable()->after('price');
            $table->decimal('remaining_balance', 10, 2)->nullable()->after('advance_payment');
            $table->string('wetransfer_link')->nullable()->after('remaining_balance');
            $table->timestamp('treatment_plan_uploaded_at')->nullable()->after('wetransfer_link');
            $table->timestamp('doctor_approved_at')->nullable()->after('treatment_plan_uploaded_at');
            $table->timestamp('estimated_completion_date')->nullable()->after('doctor_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_types', function (Blueprint $table) {
            $table->dropColumn([
                'treatment_plan_file',
                'treatment_plan_google_drive_id',
                'advance_payment',
                'remaining_balance',
                'wetransfer_link',
                'treatment_plan_uploaded_at',
                'doctor_approved_at',
                'estimated_completion_date'
            ]);
        });
    }
};

















