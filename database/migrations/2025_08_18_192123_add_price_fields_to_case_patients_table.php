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
            // Add price-related fields to case_patients table
            $table->decimal('price', 10, 2)->nullable()->after('priority');
            $table->decimal('advance_payment', 10, 2)->nullable()->after('price');
            $table->decimal('remaining_balance', 10, 2)->nullable()->after('advance_payment');
            $table->unsignedBigInteger('price_added_by')->nullable()->after('remaining_balance');
            $table->timestamp('price_added_at')->nullable()->after('price_added_by');
            $table->date('estimated_completion_date')->nullable()->after('price_added_at');
            
            // Add foreign key constraint
            $table->foreign('price_added_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_patients', function (Blueprint $table) {
            $table->dropForeign(['price_added_by']);
            $table->dropColumn([
                'price',
                'advance_payment', 
                'remaining_balance',
                'price_added_by',
                'price_added_at',
                'estimated_completion_date'
            ]);
        });
    }
};
