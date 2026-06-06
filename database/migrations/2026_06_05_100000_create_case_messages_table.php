<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id');
            // Conversation channel: admin_doctor | doctor_technician | doctor_laboratory | technician_laboratory
            $table->string('channel')->index();
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_role')->nullable();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['case_id', 'channel']);
            $table->index('sender_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_messages');
    }
};
