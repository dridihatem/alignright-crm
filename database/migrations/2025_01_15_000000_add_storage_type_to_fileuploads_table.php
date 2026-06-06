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
            $table->string('storage_type')->nullable()->after('wich_rubrique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fileuploads', function (Blueprint $table) {
            $table->dropColumn('storage_type');
        });
    }
};






