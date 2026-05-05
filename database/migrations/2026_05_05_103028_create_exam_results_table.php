<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Menyimpan raw baris dari Excel (1 baris = 1 peserta × 1 kelompok ujian)
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            // participant_id nullable: diisi saat noreg cocok dan peserta hadir
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->string('noreg', 50);                          // dari kolom no_register Excel
            $table->string('kode_paket', 50)->nullable();         // kolom kode_paket
            $table->string('nama_kelompok', 100)->nullable();     // kolom nama_kelompok_ujian
            $table->unsignedSmallInteger('benar')->default(0);
            $table->unsignedSmallInteger('salah')->default(0);
            $table->unsignedSmallInteger('kosong')->default(0);
            $table->datetime('waktu_awal')->nullable();
            $table->datetime('waktu_akhir')->nullable();
            // Skor baris ini (dihitung saat import)
            $table->decimal('row_score', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['event_id', 'noreg']);
        });

        // Aggregated ranking per peserta per event
        Schema::create('event_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->string('noreg', 50);
            $table->decimal('total_score', 10, 2)->default(0);
            $table->unsignedSmallInteger('total_benar')->default(0);
            $table->unsignedSmallInteger('total_salah')->default(0);
            $table->unsignedSmallInteger('total_kosong')->default(0);
            $table->datetime('waktu_akhir')->nullable(); // waktu_akhir paling akhir (max) sebagai acuan
            $table->unsignedSmallInteger('rank')->nullable();
            // Status: valid = hadir & noreg terdaftar, invalid_noreg = noreg tidak terdaftar, absent = tidak hadir
            $table->enum('status', ['valid', 'invalid_noreg', 'absent'])->default('valid');
            $table->timestamps();

            $table->unique(['event_id', 'noreg']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_rankings');
        Schema::dropIfExists('exam_results');
    }
};
