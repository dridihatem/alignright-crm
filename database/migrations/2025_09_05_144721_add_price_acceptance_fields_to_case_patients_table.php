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
            $table->timestamp('price_accepted_at')->nullable()->after('price_added_at');
            $table->timestamp('price_rejected_at')->nullable()->after('price_accepted_at');
            $table->text('price_rejection_reason')->nullable()->after('price_rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_patients', function (Blueprint $table) {
            $table->dropColumn(['price_accepted_at', 'price_rejected_at', 'price_rejection_reason']);
        });
    }
};
