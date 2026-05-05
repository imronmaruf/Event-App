<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRanking extends Model
{
    protected $table = 'event_rankings';

    protected $fillable = [
        'event_id',
        'participant_id',
        'noreg',
        'total_score',
        'total_benar',
        'total_salah',
        'total_kosong',
        'waktu_akhir',
        'rank',
        'status',
    ];

    protected $casts = [
        'total_score' => 'float',
        'waktu_akhir' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function getMedalAttribute(): string
    {
        return match ($this->rank) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => ''
        };
    }
}
