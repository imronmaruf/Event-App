<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = ['name', 'province'];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
