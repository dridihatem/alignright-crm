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
        Schema::create('treatment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('link');
            $table->string('status');
            $table->string('description');
            $table->foreignId('case_id')->constrained('case_patients');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_types');
    }
};
