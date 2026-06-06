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
            $table->unsignedBigInteger('uploaded_by')->nullable()->after('type_file');
            $table->decimal('price', 10, 2)->nullable()->after('uploaded_by');
            $table->unsignedBigInteger('accepted_by')->nullable()->after('price');
            $table->timestamp('accepted_at')->nullable()->after('accepted_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('accepted_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->unsignedBigInteger('price_added_by')->nullable()->after('rejection_reason');
            $table->timestamp('price_added_at')->nullable()->after('price_added_by');

            // Add foreign key constraints
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('accepted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('price_added_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatment_types', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropForeign(['accepted_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['price_added_by']);
            
            $table->dropColumn([
                'uploaded_by',
                'price',
                'accepted_by',
                'accepted_at',
                'rejected_by',
                'rejected_at',
                'rejection_reason',
                'price_added_by',
                'price_added_at'
            ]);
        });
    }
};
