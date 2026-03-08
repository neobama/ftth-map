<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    protected $fillable = [
        'name',
        'address',
        'pppoe_username',
        'pppoe_password',
        'ont_sn',
        'service_package',
        'odp_id',
        'latitude',
        'longitude',
        'route_type',
        'waypoints',
        'is_online',
        'last_checked_at',
    ];

    protected $hidden = [
        'pppoe_password',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'waypoints' => 'array',
        'is_online' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class);
    }
}
