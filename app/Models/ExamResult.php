<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $table = 'exam_results';

    protected $fillable = [
        'event_id',
        'participant_id',
        'noreg',
        'kode_paket',
        'nama_kelompok',
        'benar',
        'salah',
        'kosong',
        'waktu_awal',
        'waktu_akhir',
        'row_score',
    ];

    protected $casts = [
        'waktu_awal'  => 'datetime',
        'waktu_akhir' => 'datetime',
        'row_score'   => 'float',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
