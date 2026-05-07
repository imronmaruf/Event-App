<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringConfig extends Model
{
    protected $table = 'event_scoring_configs';

    protected $fillable = [
        'event_id',
        'point_benar',
        'point_salah',
        'point_kosong',
        'tiebreak_by_time',
        'scoring_note',
    ];

    protected $casts = [
        'point_benar'       => 'float',
        'point_salah'       => 'float',
        'point_kosong'      => 'float',
        'tiebreak_by_time'  => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** Hitung skor satu baris */
    public function calcScore(int $benar, int $salah, int $kosong): float
    {
        return round(
            ($benar  * $this->point_benar)
                + ($salah  * $this->point_salah)
                + ($kosong * $this->point_kosong),
            2
        );
    }
}
