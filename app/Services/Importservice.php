<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

/**
 * ImportService
 *
 * Menangani import data peserta dari file Excel (.xlsx / .xls / .csv)
 *
 * Format Excel yang diterima (baris 1 = header, baris 2+ = data):
 * Kolom A: NOREG       (wajib)
 * Kolom B: NAMA        (wajib)
 * Kolom C: KELAS       (opsional)
 * Kolom D: ASAL_SEKOLAH(opsional)
 * Kolom E: RUANG       (opsional)
 * Kolom F: PENGAWAS    (opsional)
 */
class ImportService
{
    /** Header yang dikenali (case-insensitive) */
    private const COL_MAP = [
        'noreg'      => ['noreg', 'no reg', 'no. reg', 'nomor registrasi', 'registration', 'no_reg'],
        'name'       => ['nama', 'nama siswa', 'nama peserta', 'name', 'student name'],
        'class'      => ['kelas', 'class', 'tingkat'],
        'school'     => ['sekolah', 'asal sekolah', 'school', 'institusi', 'instansi'],
        'room'       => ['ruang', 'room', 'ruangan', 'nomor ruang', 'kode ruang'],
        'supervisor' => ['pengawas', 'supervisor', 'penjaga', 'invigilator'],
    ];

    public function import(Event $event, UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal membaca file: ' . $e->getMessage()];
        }

        if (count($rows) < 2) {
            return ['success' => false, 'message' => 'File kosong atau hanya berisi header.'];
        }

        // Detect header di baris pertama
        $headerRow  = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $colIndex   = $this->resolveColumnIndexes($headerRow);

        if (!isset($colIndex['noreg']) || !isset($colIndex['name'])) {
            return [
                'success' => false,
                'message' => 'Kolom NOREG dan NAMA wajib ada. Pastikan header baris pertama sesuai format.',
            ];
        }

        $dataRows = array_slice($rows, 1);
        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            foreach ($dataRows as $rowNum => $row) {
                $noreg = trim((string)($row[$colIndex['noreg']] ?? ''));
                $name  = trim((string)($row[$colIndex['name']]  ?? ''));

                if (empty($noreg) || empty($name)) {
                    continue; // skip baris kosong
                }

                // Cek duplikat NOREG dalam event ini
                $exists = Participant::where('event_id', $event->id)
                    ->where('noreg', $noreg)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Participant::create([
                    'event_id'   => $event->id,
                    'noreg'      => $noreg,
                    'name'       => $name,
                    'class'      => isset($colIndex['class'])      ? trim((string)($row[$colIndex['class']] ?? ''))      : null,
                    'school'     => isset($colIndex['school'])     ? trim((string)($row[$colIndex['school']] ?? ''))     : null,
                    'room'       => isset($colIndex['room'])       ? trim((string)($row[$colIndex['room']] ?? ''))       : null,
                    'supervisor' => isset($colIndex['supervisor']) ? trim((string)($row[$colIndex['supervisor']] ?? '')) : null,
                ]);
                $inserted++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error saat menyimpan: ' . $e->getMessage()];
        }

        return [
            'success'  => true,
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'message'  => "Import selesai: {$inserted} data berhasil ditambahkan, {$skipped} dilewati (duplikat).",
        ];
    }

    /**
     * Generate template Excel untuk didownload admin.
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['NOREG', 'NAMA SISWA', 'KELAS', 'ASAL SEKOLAH', 'RUANG', 'PENGAWAS'];
        $examples = [
            ['249996600708', 'Ahmad Fauzi', '5A', 'SDN Sidoarjo 1', 'R-01', 'Budi S.'],
            ['249996600735', 'Budi Santoso', '6B', 'SDN Candi 2', 'R-01', 'Budi S.'],
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray($examples, null, 'A2');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '1565C0']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tmpPath = sys_get_temp_dir() . '/template_peserta_' . time() . '.xlsx';
        $writer  = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tmpPath);

        return $tmpPath;
    }

    // ── PRIVATE ─────────────────────────────────────────────────

    private function resolveColumnIndexes(array $headerRow): array
    {
        $result = [];
        foreach (self::COL_MAP as $field => $aliases) {
            foreach ($headerRow as $idx => $cell) {
                if (in_array($cell, $aliases, true)) {
                    $result[$field] = $idx;
                    break;
                }
            }
        }
        return $result;
    }
}
