<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->timestamp('attended_at');
            $table->string('recorded_by', 100)->nullable(); // nama/IP panitia yang input
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // Satu peserta hanya boleh absen sekali per event
            $table->unique(['event_id', 'participant_id'], 'uq_event_participant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
