<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_scoring_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            // Poin per jawaban — bisa desimal (misal 0.5)
            $table->decimal('point_benar',  5, 2)->default(2);   // benar  × point ini
            // $table->decimal('point_salah', 5, 2)->default(-1)->change();  // salah  × point ini
            $table->decimal('point_salah', 5, 2)->default(-1);
            $table->decimal('point_kosong', 5, 2)->default(0);   // kosong × point ini
            // Tiebreaker: waktu_akhir lebih awal = lebih baik (true default)
            $table->boolean('tiebreak_by_time')->default(true);
            // Catatan/keterangan aturan penilaian
            $table->string('scoring_note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_scoring_configs');
    }
};
