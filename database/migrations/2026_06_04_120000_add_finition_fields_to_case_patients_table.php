<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_patients', function (Blueprint $table) {
            if (!Schema::hasColumn('case_patients', 'finition_requested_at')) {
                $table->timestamp('finition_requested_at')->nullable()->after('wetransfer_link');
            }
            if (!Schema::hasColumn('case_patients', 'finition_requested_by')) {
                $table->unsignedBigInteger('finition_requested_by')->nullable()->after('finition_requested_at');
            }
            if (!Schema::hasColumn('case_patients', 'finition_request_note')) {
                $table->text('finition_request_note')->nullable()->after('finition_requested_by');
            }
            if (!Schema::hasColumn('case_patients', 'finition_description')) {
                $table->text('finition_description')->nullable()->after('finition_request_note');
            }
            if (!Schema::hasColumn('case_patients', 'finition_completed_at')) {
                $table->timestamp('finition_completed_at')->nullable()->after('finition_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('case_patients', function (Blueprint $table) {
            foreach ([
                'finition_requested_at',
                'finition_requested_by',
                'finition_request_note',
                'finition_description',
                'finition_completed_at',
            ] as $column) {
                if (Schema::hasColumn('case_patients', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
