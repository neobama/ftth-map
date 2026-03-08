<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    protected $fillable = [
        'name',
        'ip_address',
        'username',
        'password',
        'port',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'port' => 'integer',
    ];

    public function odps(): HasMany
    {
        return $this->hasMany(Odp::class);
    }

    public function cablesFrom(): HasMany
    {
        return $this->hasMany(Cable::class, 'from_id')->where('from_type', 'router');
    }

    public function cablesTo(): HasMany
    {
        return $this->hasMany(Cable::class, 'to_id')->where('to_type', 'router');
    }

    public function cables()
    {
        return Cable::where(function($query) {
            $query->where(function($q) {
                $q->where('from_type', 'router')->where('from_id', $this->id);
            })->orWhere(function($q) {
                $q->where('to_type', 'router')->where('to_id', $this->id);
            });
        });
    }
}
