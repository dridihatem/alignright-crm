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
            // Private note written by admin on assignment, visible only to the
            // assigned technician (technician_comment) or laboratory (laboratory_comment).
            if (!Schema::hasColumn('case_patients', 'technician_comment')) {
                $table->text('technician_comment')->nullable()->after('technician_id');
            }
            if (!Schema::hasColumn('case_patients', 'laboratory_comment')) {
                $table->text('laboratory_comment')->nullable()->after('laboratory_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_patients', function (Blueprint $table) {
            if (Schema::hasColumn('case_patients', 'technician_comment')) {
                $table->dropColumn('technician_comment');
            }
            if (Schema::hasColumn('case_patients', 'laboratory_comment')) {
                $table->dropColumn('laboratory_comment');
            }
        });
    }
};
