<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tiang extends Model
{
    protected $fillable = [
        'name',
        'cable_id',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function cable(): BelongsTo
    {
        return $this->belongsTo(Cable::class);
    }
}
