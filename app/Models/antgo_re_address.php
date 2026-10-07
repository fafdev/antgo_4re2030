<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class antgo_re_address extends Model
{
    protected $fillable = [
        'street',
        'number',
        'building',
        'floor',
        'door',
        'city',
        'postal_code',
        'country',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
