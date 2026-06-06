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
        Schema::create('tooth_problem_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('case_patients');
            $table->string('tooth_number');
            $table->foreignId('tooth_problem_id')->constrained('tooth_problems');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tooth_problem_cases');
    }
};
