<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('noreg', 50);            // Nomor registrasi
            $table->string('name', 150);            // Nama peserta
            $table->string('class', 30)->nullable(); // Kelas
            $table->string('school', 150)->nullable(); // Asal sekolah
            $table->string('room', 30)->nullable();  // Ruang ujian/lomba
            $table->string('supervisor', 100)->nullable(); // Pengawas ruang
            $table->string('attendance_code', 20)->nullable(); // Kode absensi (digenerate otomatis)
            $table->timestamps();

            // Satu nomor registrasi hanya boleh ada sekali per event
            $table->unique(['event_id', 'noreg'], 'uq_event_noreg');
            // Satu kode absensi hanya boleh ada sekali per event (untuk mencegah bentrok)
            $table->unique(['event_id', 'attendance_code'], 'uq_event_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
