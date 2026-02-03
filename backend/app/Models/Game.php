<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;
    protected $fillable = [
        'igdb_id', 'name', 'img', 'description', 'launch_date', 'publisher',
        'available_platforms', 'genre_id'
    ];

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function gameKeys(): HasMany
    {
        return $this->hasMany(GameKey::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}

