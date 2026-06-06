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
            // Treatment Plan Fields
            $table->string('treatment_plan_file')->nullable()->after('treatment_treat');
            $table->string('treatment_plan_link')->nullable()->after('treatment_plan_file');
            $table->string('treatment_plan_google_drive_id')->nullable()->after('treatment_plan_link');
            
            // Financial Fields
            $table->decimal('price', 10, 2)->nullable()->after('treatment_plan_google_drive_id');
            $table->decimal('advance_payment', 10, 2)->nullable()->after('price');
            $table->decimal('remaining_balance', 10, 2)->nullable()->after('advance_payment');
            
            // WeTransfer Integration
            $table->string('wetransfer_link')->nullable()->after('remaining_balance');
            
            // Status tracking for workflow
            $table->timestamp('treatment_plan_uploaded_at')->nullable()->after('wetransfer_link');
            $table->timestamp('price_added_at')->nullable()->after('treatment_plan_uploaded_at');
            $table->timestamp('doctor_approved_at')->nullable()->after('price_added_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_patients', function (Blueprint $table) {
            $table->dropColumn([
                'treatment_plan_file',
                'treatment_plan_link', 
                'treatment_plan_google_drive_id',
                'price',
                'advance_payment',
                'remaining_balance',
                'wetransfer_link',
                'treatment_plan_uploaded_at',
                'price_added_at',
                'doctor_approved_at'
            ]);
        });
    }
};
