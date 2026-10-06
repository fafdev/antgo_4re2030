<?php

namespace App\Models;

use Database\Factories\AntgoReEquipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class antgo_re_equipment extends Model
{
    /** @use HasFactory<AntgoReEquipmentFactory> */
    use HasFactory;

    protected $table = 'antgo_re_equipments';

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
