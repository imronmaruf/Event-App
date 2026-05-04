<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Collection;

/**
 * AttendanceCodeService
 *
 * Bertanggung jawab untuk:
 * 1. Auto-deteksi berapa digit kode absensi yang unik dari semua nomor registrasi
 * 2. Generate kode absensi untuk setiap peserta
 * 3. Validasi kode absensi saat check-in
 */
class AttendanceCodeService
{
    /**
     * Deteksi otomatis berapa digit (dari posisi suffix/prefix)
     * yang bersifat unik untuk semua peserta di suatu event.
     *
     * Algoritma:
     * - Mulai dari 1 digit, naikkan terus sampai semua kode berbeda
     * - Jika sampai panjang NOREG penuh masih ada duplikat, return null (error)
     *
     * @return int|null  Jumlah digit yang cukup, atau null jika tidak bisa unik
     */
    public function detectMinimumDigits(Event $event, string $position = 'suffix'): ?int
    {
        $noreg_list = Participant::where('event_id', $event->id)
            ->pluck('noreg')
            ->map(fn($n) => (string)$n)
            ->filter()
            ->values();

        if ($noreg_list->isEmpty()) return null;

        // Panjang NOREG terpendek jadi batas atas
        $maxLen = $noreg_list->min(fn($n) => strlen($n));

        for ($d = 1; $d <= $maxLen; $d++) {
            $codes = $noreg_list->map(fn($n) => $this->extractCode($n, $d, $position));
            if ($codes->count() === $codes->unique()->count()) {
                return $d; // sudah unik, cukup $d digit
            }
        }

        return null; // tidak bisa unik bahkan dengan full NOREG
    }

    /**
     * Generate kode absensi untuk satu NOREG berdasarkan konfigurasi event.
     */
    public function generateCode(string $noreg, int $digitCount, string $position = 'suffix'): string
    {
        return $this->extractCode($noreg, $digitCount, $position);
    }

    /**
     * Generate dan simpan kode absensi untuk SEMUA peserta di suatu event.
     * Dipanggil setelah import peserta atau setelah konfigurasi digit diubah.
     */
    public function generateCodesForEvent(Event $event): array
    {
        $digitCount = $event->digit_count;
        $position   = $event->digit_position ?? 'suffix';

        if (!$digitCount) {
            // Auto-detect dulu
            $detected = $this->detectMinimumDigits($event, $position);
            if (!$detected) {
                return ['success' => false, 'message' => 'Tidak dapat menentukan digit unik. Periksa data NOREG.'];
            }
            $digitCount = $detected;
            $event->update(['digit_count' => $digitCount]);
        }

        $participants = Participant::where('event_id', $event->id)->get();
        $codes = [];
        $conflicts = [];

        foreach ($participants as $p) {
            $code = $this->generateCode($p->noreg, $digitCount, $position);
            if (isset($codes[$code])) {
                $conflicts[] = $code;
            }
            $codes[$code] = $p->id;
        }

        if (!empty($conflicts)) {
            return [
                'success'   => false,
                'message'   => 'Terjadi konflik kode: ' . implode(', ', array_unique($conflicts)) . '. Coba tambah jumlah digit.',
                'conflicts' => array_unique($conflicts),
            ];
        }

        // Simpan semua kode
        foreach ($participants as $p) {
            $code = $this->generateCode($p->noreg, $digitCount, $position);
            $p->update(['attendance_code' => $code]);
        }

        return [
            'success'     => true,
            'digit_count' => $digitCount,
            'total'       => count($codes),
            'message'     => "Kode absensi ({$digitCount} digit) berhasil digenerate untuk " . count($codes) . " peserta.",
        ];
    }

    /**
     * Cari peserta berdasarkan kode absensi yang diinput.
     *
     * @return array { status: FOUND|NOT_FOUND|AMBIGUOUS|EVENT_INACTIVE, peserta?, sudahHadir?, waktuHadir? }
     */
    public function findParticipantByCode(Event $event, string $inputCode): array
    {
        if (!$event->is_active) {
            return ['status' => 'EVENT_INACTIVE', 'message' => 'Event ini belum diaktifkan atau sudah ditutup.'];
        }

        $inputCode = preg_replace('/\D/', '', trim($inputCode));

        if (empty($inputCode)) {
            return ['status' => 'ERROR', 'message' => 'Kode tidak boleh kosong.'];
        }

        $digitCount = $event->digit_count;
        if ($digitCount && strlen($inputCode) !== $digitCount) {
            return ['status' => 'ERROR', 'message' => "Masukkan tepat {$digitCount} digit angka."];
        }

        $matches = Participant::where('event_id', $event->id)
            ->where('attendance_code', $inputCode)
            ->with('attendance')
            ->get();

        if ($matches->isEmpty()) {
            return ['status' => 'NOT_FOUND', 'message' => "Kode \"{$inputCode}\" tidak terdaftar."];
        }

        if ($matches->count() > 1) {
            return ['status' => 'AMBIGUOUS', 'message' => "Kode \"{$inputCode}\" cocok dengan {$matches->count()} peserta. Hubungi panitia."];
        }

        $peserta = $matches->first();
        $attendance = $peserta->attendance;

        return [
            'status'      => 'FOUND',
            'peserta'     => $peserta,
            'sudahHadir'  => (bool)$attendance,
            'waktuHadir'  => $attendance ? $attendance->attended_at->format('d/m/Y H:i:s') : null,
        ];
    }

    // ── PRIVATE HELPERS ─────────────────────────────────────────

    private function extractCode(string $noreg, int $digits, string $position): string
    {
        // Ambil hanya angka dari NOREG
        $numOnly = preg_replace('/\D/', '', $noreg);
        if ($position === 'prefix') {
            return substr($numOnly, 0, $digits);
        }
        // Default: suffix (dari belakang)
        return substr($numOnly, -$digits);
    }
}
