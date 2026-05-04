<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'unit_id',
        'city_id',
        'created_by',
        'description',
        'venue',
        'event_date',
        'event_time',
        'digit_mode',
        'digit_count',
        'digit_position',
        'is_active',
        'is_archived',
        'attendance_token',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_archived' => 'boolean',
        'event_date' => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($event) {
            if (empty($event->attendance_token)) {
                $event->attendance_token = Str::random(48);
            }
            if (empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->name, $event->unit_id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, int $unitId): string
    {
        $unit = Unit::find($unitId);
        $base = Str::slug($name) . '-' . ($unit ? Str::slug($unit->slug) : $unitId);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    // Regenerate token (untuk keamanan jika diperlukan)
    public function regenerateToken(): void
    {
        $this->update(['attendance_token' => Str::random(48)]);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function totalParticipants(): int
    {
        return $this->participants()->count();
    }

    public function totalAttended(): int
    {
        return $this->attendances()->count();
    }

    public function totalAbsent(): int
    {
        return max(0, $this->totalParticipants() - $this->totalAttended());
    }

    public function attendancePercentage(): float
    {
        $total = $this->totalParticipants();
        if ($total === 0) return 0;
        return round(($this->totalAttended() / $total) * 100, 1);
    }

    // URL publik absensi
    public function publicAttendanceUrl(): string
    {
        return route('attendance.show', ['slug' => $this->slug]);
    }

    // Cek apakah event bisa dikelola oleh admin unit tertentu
    public function canBeManagedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->unit_id === $this->unit_id;
    }
}
