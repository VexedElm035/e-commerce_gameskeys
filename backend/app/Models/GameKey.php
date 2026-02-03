<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class GameKey extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'game_id', 'state', 'region', 'price', 'tax',
        'delivery_time', 'seller_id', 'platform', 'sale_id', 'key'
    ];

    protected $hidden = [
        'key',
        'created_at',
        'updated_at',
        'sale_id',
        'created_at',
        'updated_at',
        'tax'
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'key_id');
    }

    public function cart(): HasMany
    {
        return $this->hasMany(Cart::class, 'key_id'); 
    }
}

