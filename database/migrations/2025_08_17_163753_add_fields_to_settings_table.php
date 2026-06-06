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
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type')->default('text')->after('value');
            }
            if (!Schema::hasColumn('settings', 'group')) {
                $table->string('group')->default('general')->after('type');
            }
            if (!Schema::hasColumn('settings', 'label')) {
                $table->string('label')->after('group');
            }
            if (!Schema::hasColumn('settings', 'description')) {
                $table->text('description')->nullable()->after('label');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['type', 'group', 'label', 'description']);
        });
    }
};
