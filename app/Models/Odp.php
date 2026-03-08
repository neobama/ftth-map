<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Odp extends Model
{
    protected $fillable = [
        'name',
        'router_id',
        'latitude',
        'longitude',
        'port_capacity',
        'used_ports',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'port_capacity' => 'integer',
        'used_ports' => 'integer',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function cables()
    {
        return Cable::where(function($query) {
            $query->where(function($q) {
                $q->where('from_type', 'odp')->where('from_id', $this->id);
            })->orWhere(function($q) {
                $q->where('to_type', 'odp')->where('to_id', $this->id);
            });
        });
    }

    public function getCapacityPercentageAttribute(): float
    {
        if ($this->port_capacity == 0) {
            return 0;
        }
        return ($this->used_ports / $this->port_capacity) * 100;
    }

    public function getColorAttribute(): string
    {
        $percentage = $this->capacity_percentage;
        if ($percentage < 50) {
            return 'blue';
        } elseif ($percentage < 80) {
            return 'yellow';
        } else {
            return 'red';
        }
    }
}
