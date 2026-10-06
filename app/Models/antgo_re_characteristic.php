<?php

namespace App\Models;

use Database\Factories\AntgoReCharacteristicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class antgo_re_characteristic extends Model
{
    /** @use HasFactory<AntgoReCharacteristicFactory> */
    use HasFactory;

    protected $table = 'antgo_re_characteristics';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'inSites',
        'inBuildings',
        'inProperties',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'inSites' => 'boolean',
        'inBuildings' => 'boolean',
        'inProperties' => 'boolean',
    ];
}
