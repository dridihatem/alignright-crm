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
        Schema::table('fileuploads', function (Blueprint $table) {
            $table->boolean('included_in_zip')->default(false)->after('storage_type')->comment('Whether this file is included in the case ZIP archive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fileuploads', function (Blueprint $table) {
            $table->dropColumn('included_in_zip');
        });
    }
};
