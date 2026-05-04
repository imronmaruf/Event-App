<?php

namespace App\Services;

use App\Models\Event;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Facades\Storage;

/**
 * QRCodeService
 *
 * Generate QR code untuk:
 * 1. Link absensi publik per event
 * 2. QR code per peserta (berisi kode absensi peserta)
 */
class QRCodeService
{
    /**
     * Generate QR code untuk link absensi event.
     * Mengembalikan URL gambar QR (menggunakan QR Server API atau library lokal).
     */
    public function generateEventQRUrl(Event $event): string
    {
        $url = $event->publicAttendanceUrl();
        return $this->buildQRImageUrl($url);
    }

    /**
     * Generate QR code URL untuk kode absensi peserta tertentu.
     * QR ini bisa di-scan oleh panitia di halaman absensi.
     */
    public function generateParticipantQRUrl(string $attendanceCode, int $size = 200): string
    {
        return $this->buildQRImageUrl($attendanceCode, $size);
    }

    /**
     * Generate semua QR untuk peserta dalam satu event (untuk cetak kartu).
     * Return: array of ['noreg', 'name', 'code', 'qr_url']
     */
    public function generateParticipantQRSheet(Event $event): array
    {
        return $event->participants()
            ->select(['id', 'noreg', 'name', 'class', 'school', 'room', 'attendance_code'])
            ->get()
            ->map(function ($p) {
                return [
                    'id'     => $p->id,
                    'noreg'  => $p->noreg,
                    'name'   => $p->name,
                    'class'  => $p->class,
                    'school' => $p->school,
                    'room'   => $p->room,
                    'code'   => $p->attendance_code,
                    'qr_url' => $p->attendance_code
                        ? $this->generateParticipantQRUrl($p->attendance_code)
                        : null,
                ];
            })
            ->toArray();
    }

    // ── PRIVATE ─────────────────────────────────────────────────

    /**
     * Bangun URL gambar QR menggunakan API gratis qr-server.com.
     * Bisa diganti dengan library lokal (endroid/qr-code) jika perlu offline.
     */
    private function buildQRImageUrl(string $data, int $size = 300): string
    {
        // Opsi 1: API eksternal (tidak perlu library tambahan)
        return 'https://api.qrserver.com/v1/create-qr-code/?'
            . http_build_query([
                'size'       => "{$size}x{$size}",
                'data'       => $data,
                'ecc'        => 'M',
                'margin'     => 2,
                'format'     => 'png',
            ]);

        // Opsi 2: Library lokal (install: composer require endroid/qr-code)
        // Uncomment jika ingin offline / lebih reliable:

        $qrCode = new QrCode($data);
        $qrCode->setSize($size);
        $qrCode->setMargin(4);
        $writer  = new \Endroid\QrCode\Writer\PngWriter();
        $result  = $writer->write($qrCode);
        $path    = 'qrcodes/' . md5($data) . '.png';
        Storage::disk('public')->put($path, $result->getString());
        return Storage::disk('public')->url($path);
    }
}
