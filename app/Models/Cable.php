<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cable extends Model
{
    protected $fillable = [
        'name',
        'from_type',
        'from_id',
        'to_type',
        'to_id',
        'route_type',
        'waypoints',
        'core_count',
        'length',
    ];

    protected $casts = [
        'waypoints' => 'array',
        'core_count' => 'integer',
        'length' => 'decimal:2',
    ];

    public function getFromNodeAttribute()
    {
        if ($this->from_type === 'router') {
            return Router::find($this->from_id);
        } elseif ($this->from_type === 'odp') {
            return Odp::find($this->from_id);
        }
        return null;
    }

    public function getToNodeAttribute()
    {
        if ($this->to_type === 'router') {
            return Router::find($this->to_id);
        } elseif ($this->to_type === 'odp') {
            return Odp::find($this->to_id);
        }
        return null;
    }

    public function tiangs(): HasMany
    {
        return $this->hasMany(Tiang::class);
    }
}
