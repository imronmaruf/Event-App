<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRanking;
use App\Models\ExamResult;
use App\Models\Participant;
use App\Models\ScoringConfig;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment, Border};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * RankingService
 *
 * Alur:
 * 1. Baca Excel hasil ujian
 * 2. Untuk setiap no_register:
 *    - Cek apakah terdaftar di tabel participants (event yang sama)
 *    - Cek apakah memiliki catatan hadir di tabel attendances
 * 3. Hitung skor total berdasarkan ScoringConfig
 * 4. Urutkan: skor DESC, waktu_akhir ASC (tiebreaker)
 * 5. Assign rank (dense rank — skor sama = rank sama)
 * 6. Simpan ke event_rankings
 */
class RankingService
{
    // ── REQUIRED EXCEL COLUMNS ──────────────────────────────────
    private const COL_MAP = [
        'noreg'       => ['no_register', 'noreg', 'no reg', 'nomor registrasi'],
        'kode_paket'  => ['kode_paket', 'kode paket', 'paket'],
        'kelompok'    => ['nama_kelompok_ujian', 'nama kelompok ujian', 'kelompok', 'mata ujian', 'mapel'],
        'benar'       => ['benar', 'jawaban benar', 'correct'],
        'salah'       => ['salah', 'jawaban salah', 'wrong', 'incorrect'],
        'kosong'      => ['kosong', 'tidak dijawab', 'empty', 'blank'],
        'waktu_awal'  => ['waktu_awal', 'waktu awal', 'start_time', 'mulai'],
        'waktu_akhir' => ['waktu_akhir', 'waktu akhir', 'end_time', 'selesai', 'finish'],
    ];

    // Warna tema (konsisten di semua sheet)
    private const COLOR_RED       = 'C62828';
    private const COLOR_RED_LIGHT = 'FFEBEE';
    private const COLOR_GOLD      = 'F9A825';
    private const COLOR_GOLD_LIGHT = 'FFF9C4';
    private const COLOR_SILVER    = '78909C';
    private const COLOR_SILVER_LT = 'F5F5F5';
    private const COLOR_BRONZE    = 'A1887F';
    private const COLOR_BRONZE_LT = 'FBE9E7';
    private const COLOR_HEADER_FG = 'FFFFFF';
    private const COLOR_MUTED     = '888888';

    // ════════════════════════════════════════════════════════════
    //  IMPORT + PROCESS
    // ════════════════════════════════════════════════════════════
    public function import(Event $event, UploadedFile $file): array
    {
        // ── Baca file ──
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Gagal membaca file: ' . $e->getMessage()];
        }

        if (count($rows) < 2) {
            return ['success' => false, 'message' => 'File kosong atau hanya berisi header.'];
        }

        // ── Detect kolom ──
        $header = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $cols   = $this->resolveColumns($header);

        if (!isset($cols['noreg']) || !isset($cols['benar'])) {
            return ['success' => false, 'message' => 'Kolom no_register dan benar wajib ada. Periksa format file.'];
        }

        // ── Ambil scoring config ──
        $config = ScoringConfig::firstOrCreate(
            ['event_id' => $event->id],
            ['point_benar' => 2, 'point_salah' => 1, 'point_kosong' => 0, 'tiebreak_by_time' => true]
        );

        // ── Ambil semua peserta event (hanya yang hadir) ──
        $presentNoregs = Participant::where('event_id', $event->id)
            ->whereHas('attendance')
            ->pluck('id', 'noreg')
            ->toArray();

        // ── Ambil semua peserta event (terdaftar, tidak harus hadir) ──
        $registeredNoregs = Participant::where('event_id', $event->id)
            ->pluck('id', 'noreg')
            ->toArray();

        DB::beginTransaction();
        try {
            ExamResult::where('event_id', $event->id)->delete();
            EventRanking::where('event_id', $event->id)->delete();

            $dataRows   = array_slice($rows, 1);
            $rawBuckets = [];
            $skipped    = 0;
            $invalid    = [];
            $absent     = [];

            foreach ($dataRows as $row) {
                $noreg = trim((string)($row[$cols['noreg']] ?? ''));
                if (!$noreg) continue;

                $benar  = (int)($row[$cols['benar']]  ?? 0);
                $salah  = (int)($row[$cols['salah']]  ?? 0);
                $kosong = (int)($row[$cols['kosong']] ?? 0);
                $ka     = isset($cols['waktu_akhir']) ? trim((string)($row[$cols['waktu_akhir']] ?? '')) : null;
                $kaw    = isset($cols['waktu_awal'])  ? trim((string)($row[$cols['waktu_awal']]  ?? '')) : null;
                $kode   = isset($cols['kode_paket'])  ? trim((string)($row[$cols['kode_paket']]  ?? '')) : null;
                $kelp   = isset($cols['kelompok'])    ? trim((string)($row[$cols['kelompok']]    ?? '')) : null;

                $rowScore      = $config->calcScore($benar, $salah, $kosong);
                $participantId = $presentNoregs[$noreg] ?? null;

                $status = 'valid';
                if (!isset($registeredNoregs[$noreg])) {
                    $status = 'invalid_noreg';
                } elseif (!isset($presentNoregs[$noreg])) {
                    $status = 'absent';
                }

                ExamResult::create([
                    'event_id'       => $event->id,
                    'participant_id' => $presentNoregs[$noreg] ?? ($registeredNoregs[$noreg] ?? null),
                    'noreg'          => $noreg,
                    'kode_paket'     => $kode,
                    'nama_kelompok'  => $kelp,
                    'benar'          => $benar,
                    'salah'          => $salah,
                    'kosong'         => $kosong,
                    'waktu_awal'     => $kaw ? $this->parseDateTime($kaw) : null,
                    'waktu_akhir'    => $ka  ? $this->parseDateTime($ka)  : null,
                    'row_score'      => $rowScore,
                ]);

                if (!isset($rawBuckets[$noreg])) {
                    $rawBuckets[$noreg] = [
                        'status'         => $status,
                        'participant_id' => $presentNoregs[$noreg] ?? ($registeredNoregs[$noreg] ?? null),
                        'rows'           => [],
                    ];
                }
                $rawBuckets[$noreg]['rows'][] = [
                    'benar'       => $benar,
                    'salah'       => $salah,
                    'kosong'      => $kosong,
                    'row_score'   => $rowScore,
                    'waktu_akhir' => $ka,
                ];
            }

            // ── Aggregasi per noreg ──
            $rankings = [];
            foreach ($rawBuckets as $noreg => $bucket) {
                $rows_   = $bucket['rows'];
                $tBenar  = array_sum(array_column($rows_, 'benar'));
                $tSalah  = array_sum(array_column($rows_, 'salah'));
                $tKosong = array_sum(array_column($rows_, 'kosong'));
                $tScore  = round(array_sum(array_column($rows_, 'row_score')), 2);

                $times    = array_filter(array_column($rows_, 'waktu_akhir'));
                $maxWaktu = $times ? max($times) : null;

                $rankings[] = [
                    'event_id'       => $event->id,
                    'participant_id' => $bucket['participant_id'],
                    'noreg'          => $noreg,
                    'total_score'    => $tScore,
                    'total_benar'    => $tBenar,
                    'total_salah'    => $tSalah,
                    'total_kosong'   => $tKosong,
                    'waktu_akhir'    => $maxWaktu ? $this->parseDateTime($maxWaktu) : null,
                    'status'         => $bucket['status'],
                    'rank'           => null,
                ];
            }

            // ── Sort & assign rank (hanya status=valid) ──
            $valid   = array_filter($rankings, fn($r) => $r['status'] === 'valid');
            $invalid = array_filter($rankings, fn($r) => $r['status'] !== 'valid');

            usort($valid, function ($a, $b) use ($config) {
                if ($a['total_score'] !== $b['total_score']) {
                    return $b['total_score'] <=> $a['total_score'];
                }
                if ($config->tiebreak_by_time && $a['waktu_akhir'] && $b['waktu_akhir']) {
                    return strcmp($a['waktu_akhir'], $b['waktu_akhir']);
                }
                return 0;
            });

            // Dense rank
            $rank      = 0;
            $prevScore = null;
            $prevWaktu = null;
            foreach ($valid as &$r) {
                $sameScore = ($r['total_score'] === $prevScore);
                $sameWaktu = ($config->tiebreak_by_time ? $r['waktu_akhir'] === $prevWaktu : true);
                if (!$sameScore || !$sameWaktu) $rank++;
                $r['rank'] = $rank;
                $prevScore = $r['total_score'];
                $prevWaktu = $r['waktu_akhir'];
            }
            unset($r);

            foreach (array_merge(array_values($valid), array_values($invalid)) as $r) {
                EventRanking::create($r);
            }

            DB::commit();

            $validCount   = count($valid);
            $invalidCount = count(array_filter($invalid, fn($r) => $r['status'] === 'invalid_noreg'));
            $absentCount  = count(array_filter($invalid, fn($r) => $r['status'] === 'absent'));

            return [
                'success'       => true,
                'total_rows'    => count($dataRows),
                'valid'         => $validCount,
                'invalid_noreg' => $invalidCount,
                'absent'        => $absentCount,
                'message'       => "Import berhasil! {$validCount} peserta valid diranking. " .
                    ($invalidCount ? "{$invalidCount} NOREG tidak terdaftar. " : '') .
                    ($absentCount  ? "{$absentCount} peserta tidak hadir diabaikan." : ''),
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // ════════════════════════════════════════════════════════════
    //  GENERATE TEMPLATE
    // ════════════════════════════════════════════════════════════
    public function generateTemplate(Event $event): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Ujian');

        $headers = [
            'no_register',
            'kode_paket',
            'nama_kelompok_ujian',
            'benar',
            'salah',
            'kosong',
            'waktu_awal',
            'waktu_akhir',
        ];
        $sheet->fromArray([$headers], null, 'A1');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => self::COLOR_HEADER_FG], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::COLOR_RED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_HEADER_FG]]],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $participants = Participant::where('event_id', $event->id)->limit(5)->get();
        $row = 2;
        foreach ($participants as $p) {
            $sheet->fromArray([[
                $p->noreg,
                'TO-XXXXX',
                'MATEMATIKA',
                0,
                0,
                0,
                now()->format('Y-m-d H:i:s'),
                now()->format('Y-m-d H:i:s'),
            ]], null, 'A' . $row);
            $row++;
        }

        $instrRow = $row + 1;
        $sheet->setCellValue('A' . $instrRow, '📋 PETUNJUK PENGISIAN:');
        $sheet->getStyle('A' . $instrRow)->getFont()->setBold(true)->setColor(
            new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLOR_RED)
        );

        $instruksi = [
            'no_register : Nomor registrasi peserta (harus terdaftar dan hadir di event ini)',
            'kode_paket  : Kode paket soal (opsional)',
            'nama_kelompok_ujian : Nama mata ujian / kelompok soal (boleh kosong)',
            'benar  : Jumlah jawaban benar (angka)',
            'salah  : Jumlah jawaban salah (angka)',
            'kosong : Jumlah soal tidak dijawab (angka)',
            'waktu_awal  : Waktu mulai ujian (format: YYYY-MM-DD HH:MM:SS)',
            'waktu_akhir : Waktu selesai ujian (format: YYYY-MM-DD HH:MM:SS) — dipakai sebagai tiebreaker',
            '',
            'CATATAN: Satu peserta bisa memiliki lebih dari 1 baris (untuk beberapa mata ujian).',
            'Skor total = jumlah semua baris milik peserta tersebut.',
        ];
        foreach ($instruksi as $i => $ins) {
            $sheet->setCellValue('A' . ($instrRow + $i + 1), $ins);
            $sheet->getStyle('A' . ($instrRow + $i + 1))->getFont()
                ->setSize(10)->setItalic(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
        }

        $widths = ['A' => 18, 'B' => 14, 'C' => 24, 'D' => 8, 'E' => 8, 'F' => 8, 'G' => 22, 'H' => 22];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $infoSheet = $spreadsheet->createSheet();
        $infoSheet->setTitle('Info Event');
        $infoSheet->setCellValue('A1', 'Event');
        $infoSheet->setCellValue('B1', $event->name);
        $infoSheet->setCellValue('A2', 'Unit');
        $infoSheet->setCellValue('B2', $event->unit->name);
        $infoSheet->setCellValue('A3', 'Tanggal');
        $infoSheet->setCellValue('B3', $event->event_date?->format('d/m/Y') ?? '-');
        $infoSheet->setCellValue('A4', 'Total Peserta');
        $infoSheet->setCellValue('B4', $event->participants()->count());
        $infoSheet->setCellValue('A5', 'Template dibuat');
        $infoSheet->setCellValue('B5', now()->format('d/m/Y H:i'));

        $tmpPath = sys_get_temp_dir() . '/template_hasil_' . $event->id . '_' . time() . '.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmpPath);
        return $tmpPath;
    }

    // ════════════════════════════════════════════════════════════
    //  EXPORT RANKING  (multi-sheet: Ringkasan + 1 sheet/kelas)
    // ════════════════════════════════════════════════════════════
    public function exportRanking(Event $event): string
    {
        $config = ScoringConfig::where('event_id', $event->id)->first();

        // Ambil semua ranking valid beserta relasi participant
        $rankings = EventRanking::with('participant')
            ->where('event_id', $event->id)
            ->where('status', 'valid')
            ->orderBy('rank')
            ->get();

        $spreadsheet = new Spreadsheet();

        // ── Sheet 1: Ranking Keseluruhan ─────────────────────────────
        $sheetAll = $spreadsheet->getActiveSheet();
        $sheetAll->setTitle('Ranking Keseluruhan');
        $this->writeRankingSheet(
            sheet: $sheetAll,
            rows: $rankings,
            title: 'RANKING KESELURUHAN — ' . strtoupper($event->name),
            subtitle: $event->unit->name . '  ·  Dicetak: ' . now()->format('d/m/Y H:i'),
            colDefs: $this->allRankingColumns(),
            buildRow: fn($r) => $this->buildAllRankRow($r),
            config: $config,
        );

        // ── Kelompokkan per kelas (natural sort) ─────────────────────
        $byClass = $rankings
            ->filter(fn($r) => !empty($r->participant?->class))
            ->groupBy(fn($r) => $r->participant->class)
            ->sortKeys(SORT_NATURAL);   // VII < VIII < IX, bukan leksikografis

        // ── Sheet per kelas ──────────────────────────────────────────
        foreach ($byClass as $className => $classRows) {
            // Assign class rank (dense rank, sama dengan logika di controller)
            $classRanked = $this->assignClassRank($classRows, $config);

            // Nama sheet: "Kelas VII" dst., maksimal 31 karakter (batasan Excel)
            $sheetTitle = mb_substr('Kelas ' . $className, 0, 31);

            $classSheet = $spreadsheet->createSheet();
            $classSheet->setTitle($sheetTitle);

            $this->writeRankingSheet(
                sheet: $classSheet,
                rows: $classRanked,
                title: 'RANKING KELAS ' . strtoupper($className) . ' — ' . strtoupper($event->name),
                subtitle: $event->unit->name
                    . '  ·  ' . count($classRanked) . ' peserta'
                    . '  ·  Dicetak: ' . now()->format('d/m/Y H:i'),
                colDefs: $this->classRankingColumns(),
                buildRow: fn($r) => $this->buildClassRankRow($r),
                config: $config,
            );
        }

        // ── Sheet terakhir: Ringkasan per Kelas ──────────────────────
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Ringkasan Per Kelas');
        $this->writeSummarySheet($summarySheet, $byClass, $event, $config);

        // Aktifkan sheet pertama saat file dibuka
        $spreadsheet->setActiveSheetIndex(0);

        $tmpPath = sys_get_temp_dir() . '/ranking_' . $event->slug . '_' . time() . '.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmpPath);
        return $tmpPath;
    }

    // ════════════════════════════════════════════════════════════
    //  PRIVATE — Sheet Writers
    // ════════════════════════════════════════════════════════════

    /**
     * Tulis sebuah sheet ranking dengan baris judul, header, data, dan format.
     *
     * @param Worksheet $sheet
     * @param iterable  $rows       Collection atau array entry ranking
     * @param string    $title      Baris judul besar (merge)
     * @param string    $subtitle   Baris sub-judul
     * @param array     $colDefs    [['label'=>'...', 'width'=>N], ...]
     * @param callable  $buildRow   fn($entry) => array nilai sel
     * @param ScoringConfig|null $config
     */
    private function writeRankingSheet(
        Worksheet  $sheet,
        iterable   $rows,
        string     $title,
        string     $subtitle,
        array      $colDefs,
        callable   $buildRow,
        mixed      $config,
    ): void {
        $colCount  = count($colDefs);
        $lastColLt = $this->colLetter($colCount);   // misal "K"

        // ── Baris 1: Judul ──
        $sheet->mergeCells("A1:{$lastColLt}1");
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::COLOR_RED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Baris 2: Subtitle ──
        $sheet->mergeCells("A2:{$lastColLt}2");
        $sheet->setCellValue('A2', $subtitle);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => self::COLOR_MUTED]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFF3E0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(16);

        // ── Baris 3: Header kolom ──
        $headerLabels = array_column($colDefs, 'label');
        $sheet->fromArray([$headerLabels], null, 'A3');
        $sheet->getStyle("A3:{$lastColLt}3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => self::COLOR_HEADER_FG], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '424242']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '666666']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(20);

        // ── Baris data ──
        $excelRow = 4;
        foreach ($rows as $entry) {
            $values = $buildRow($entry);
            $sheet->fromArray([$values], null, 'A' . $excelRow);

            // Ambil rank dari entri (bisa overall rank atau class rank)
            $rank = $entry['_rank'] ?? ($entry->rank ?? null);

            // Highlight top 3
            $bgColor = match ($rank) {
                1       => self::COLOR_GOLD_LIGHT,
                2       => self::COLOR_SILVER_LT,
                3       => self::COLOR_BRONZE_LT,
                default => ($excelRow % 2 === 0 ? 'FAFAFA' : 'FFFFFF'),
            };

            $sheet->getStyle("A{$excelRow}:{$lastColLt}{$excelRow}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                'font'    => ($rank <= 3) ? ['bold' => true] : [],
            ]);

            // Kolom skor (selalu bold + biru)
            $scoreCol = $this->colLetter($this->scoreColumnIndex($colDefs));
            $sheet->getStyle("{$scoreCol}{$excelRow}")->getFont()
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1565C0'));

            $excelRow++;
        }

        // Freeze pane di bawah header
        $sheet->freezePane('A4');

        // ── Lebar kolom ──
        foreach ($colDefs as $i => $def) {
            $sheet->getColumnDimension($this->colLetter($i + 1))->setWidth($def['width']);
        }

        // ── Auto-filter pada baris header ──
        $sheet->setAutoFilter("A3:{$lastColLt}3");
    }

    /**
     * Sheet "Ringkasan Per Kelas": satu baris per kelas berisi statistik + juara 1.
     */
    private function writeSummarySheet(
        Worksheet $sheet,
        iterable  $byClass,
        Event     $event,
        mixed     $config,
    ): void {
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'RINGKASAN RANKING PER KELAS — ' . strtoupper($event->name));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::COLOR_RED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $headers = [
            'Kelas',
            'Jml Peserta',
            'Skor Max',
            'Skor Min',
            'Rata-rata',
            'Juara 1 (Nama)',
            'Juara 1 (NOREG)',
            'Juara 1 (Sekolah)',
            'Juara 1 (Skor)',
        ];
        $sheet->fromArray([$headers], null, 'A2');
        $sheet->getStyle('A2:I2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => self::COLOR_HEADER_FG], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '424242']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '666666']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $row = 3;
        foreach ($byClass as $className => $classRows) {
            $ranked  = $this->assignClassRank($classRows, $config);
            $scores  = array_column($ranked, 'total_score');
            $winner  = collect($ranked)->firstWhere('_rank', 1);

            $sheet->fromArray([[
                'Kelas ' . $className,
                count($ranked),
                $scores ? max($scores) : 0,
                $scores ? min($scores) : 0,
                $scores ? round(array_sum($scores) / count($scores), 2) : 0,
                $winner['name']         ?? '-',
                $winner['noreg']        ?? '-',
                $winner['school']       ?? '-',
                $winner['total_score']  ?? 0,
            ]], null, 'A' . $row);

            // Warna zebra
            $bg = ($row % 2 === 0) ? 'F5F5F5' : 'FFFFFF';
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
            ]);

            // Bold + warna juara 1
            $sheet->getStyle("F{$row}:I{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLOR_RED));

            $row++;
        }

        // Total baris di bawah
        $totalRow = $row;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->setCellValue('B' . $totalRow, '=SUM(B3:B' . ($row - 1) . ')');
        $sheet->getStyle("A{$totalRow}:B{$totalRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => self::COLOR_HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::COLOR_RED]],
        ]);

        $widths = ['A' => 14, 'B' => 14, 'C' => 12, 'D' => 12, 'E' => 12, 'F' => 28, 'G' => 16, 'H' => 28, 'I' => 12];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A3');
        $sheet->setAutoFilter('A2:I2');
    }

    // ════════════════════════════════════════════════════════════
    //  PRIVATE — Column Definitions & Row Builders
    // ════════════════════════════════════════════════════════════

    /** Definisi kolom sheet "Ranking Keseluruhan" */
    private function allRankingColumns(): array
    {
        return [
            ['label' => 'Rank',        'width' => 7,  'key' => 'rank'],
            ['label' => '🏅',          'width' => 5,  'key' => 'medal'],
            ['label' => 'NOREG',       'width' => 16, 'key' => 'noreg'],
            ['label' => 'Nama',        'width' => 26, 'key' => 'name'],
            ['label' => 'Kelas',       'width' => 10, 'key' => 'class'],
            ['label' => 'Sekolah',     'width' => 26, 'key' => 'school'],
            ['label' => 'Ruang',       'width' => 10, 'key' => 'room'],
            ['label' => 'Skor Total',  'width' => 12, 'key' => 'score'],
            ['label' => 'Benar',       'width' => 8,  'key' => 'benar'],
            ['label' => 'Salah',       'width' => 8,  'key' => 'salah'],
            ['label' => 'Kosong',      'width' => 8,  'key' => 'kosong'],
            ['label' => 'Waktu Akhir', 'width' => 20, 'key' => 'waktu'],
        ];
    }

    /** Definisi kolom sheet "Kelas XXX" */
    private function classRankingColumns(): array
    {
        return [
            ['label' => 'Rank Kelas',    'width' => 12, 'key' => 'class_rank'],
            ['label' => '🏅',            'width' => 5,  'key' => 'medal'],
            ['label' => 'Rank Overall',  'width' => 13, 'key' => 'overall_rank'],
            ['label' => 'NOREG',         'width' => 16, 'key' => 'noreg'],
            ['label' => 'Nama',          'width' => 26, 'key' => 'name'],
            ['label' => 'Sekolah',       'width' => 26, 'key' => 'school'],
            ['label' => 'Ruang',         'width' => 10, 'key' => 'room'],
            ['label' => 'Skor Total',    'width' => 12, 'key' => 'score'],
            ['label' => 'Benar',         'width' => 8,  'key' => 'benar'],
            ['label' => 'Salah',         'width' => 8,  'key' => 'salah'],
            ['label' => 'Kosong',        'width' => 8,  'key' => 'kosong'],
            ['label' => 'Waktu Akhir',   'width' => 20, 'key' => 'waktu'],
        ];
    }

    private function buildAllRankRow(mixed $r): array
    {
        $p = $r->participant;
        return [
            $r->rank,
            $this->medal($r->rank),
            $r->noreg,
            $p?->name   ?? '-',
            $p?->class  ?? '-',
            $p?->school ?? '-',
            $p?->room   ?? '-',
            $r->total_score,
            $r->total_benar,
            $r->total_salah,
            $r->total_kosong,
            $r->waktu_akhir?->format('d/m/Y H:i:s') ?? '-',
        ];
    }

    private function buildClassRankRow(array $r): array
    {
        return [
            $r['_rank'],
            $this->medal($r['_rank']),
            $r['overall_rank'],
            $r['noreg'],
            $r['name'],
            $r['school'],
            $r['room'],
            $r['total_score'],
            $r['total_benar'],
            $r['total_salah'],
            $r['total_kosong'],
            $r['waktu_akhir'],
        ];
    }

    // ════════════════════════════════════════════════════════════
    //  PRIVATE — Helpers
    // ════════════════════════════════════════════════════════════

    /**
     * Dari koleksi EventRanking sebuah kelas, kembalikan array asosiatif
     * yang sudah diberi '_rank' (class rank, dense).
     */
    private function assignClassRank(mixed $classCollection, mixed $config): array
    {
        // Sort: skor DESC, waktu_akhir ASC
        $sorted = collect($classCollection)
            ->sortByDesc('total_score')
            ->when(
                $config?->tiebreak_by_time,
                fn($col) => $col->sortBy([['total_score', 'desc'], ['waktu_akhir', 'asc']])
            )
            ->values();

        $classRank = 0;
        $prevScore = null;
        $prevWaktu = null;
        $result    = [];

        foreach ($sorted as $r) {
            $sameScore = $r->total_score === $prevScore;
            $sameWaktu = $config?->tiebreak_by_time
                ? ((string) $r->waktu_akhir === (string) $prevWaktu)
                : true;

            if (!$sameScore || !$sameWaktu) {
                $classRank++;
            }

            $p = $r->participant;
            $result[] = [
                '_rank'        => $classRank,       // dipakai oleh writeRankingSheet
                'overall_rank' => $r->rank,
                'noreg'        => $r->noreg,
                'name'         => $p?->name   ?? '-',
                'class'        => $p?->class  ?? '-',
                'school'       => $p?->school ?? '-',
                'room'         => $p?->room   ?? '-',
                'total_score'  => $r->total_score,
                'total_benar'  => $r->total_benar,
                'total_salah'  => $r->total_salah,
                'total_kosong' => $r->total_kosong,
                'waktu_akhir'  => $r->waktu_akhir?->format('d/m/Y H:i:s') ?? '-',
            ];

            $prevScore = $r->total_score;
            $prevWaktu = $r->waktu_akhir;
        }

        return $result;
    }

    /** Konversi nomor kolom (1-based) ke huruf Excel: 1→A, 26→Z, 27→AA */
    private function colLetter(int $n): string
    {
        $letter = '';
        while ($n > 0) {
            $n--;
            $letter = chr(65 + ($n % 26)) . $letter;
            $n      = intdiv($n, 26);
        }
        return $letter;
    }

    /** Cari index (1-based) kolom yang memiliki key='score' dalam $colDefs */
    private function scoreColumnIndex(array $colDefs): int
    {
        foreach ($colDefs as $i => $def) {
            if (($def['key'] ?? '') === 'score') {
                return $i + 1;
            }
        }
        return 8; // fallback kolom H
    }

    private function medal(int $rank): string
    {
        return match ($rank) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => '',
        };
    }

    private function resolveColumns(array $header): array
    {
        $result = [];
        foreach (self::COL_MAP as $field => $aliases) {
            foreach ($header as $idx => $cell) {
                if (in_array($cell, $aliases, true)) {
                    $result[$field] = $idx;
                    break;
                }
            }
        }
        return $result;
    }

    private function parseDateTime(string $dt): ?string
    {
        try {
            return \Carbon\Carbon::parse($dt)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
