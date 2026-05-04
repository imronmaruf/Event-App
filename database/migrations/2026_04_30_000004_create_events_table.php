<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('slug', 200)->unique();  // URL-safe slug unik untuk link absensi publik
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('venue', 200)->nullable();    // lokasi/tempat pelaksanaan
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();

            // ── Konfigurasi kode absensi ─────────────────────────
            // digit_mode: 'auto' = sistem otomatis deteksi, 'manual' = admin tentukan sendiri
            $table->enum('digit_mode', ['auto', 'manual'])->default('auto');
            // digit_count: jumlah digit kode absensi (null = belum dihitung, diisi saat auto-detect)
            $table->unsignedTinyInteger('digit_count')->nullable();
            // digit_position: ambil dari 'suffix' (belakang) atau 'prefix' (depan)
            $table->enum('digit_position', ['suffix', 'prefix'])->default('suffix');

            // ── Status event ─────────────────────────────────────
            $table->boolean('is_active')->default(false);   // absensi aktif/nonaktif
            $table->boolean('is_archived')->default(false); // event diarsipkan
            $table->string('attendance_token', 64)->unique(); // token unik untuk link publik
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
