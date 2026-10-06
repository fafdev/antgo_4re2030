<?php

namespace App\Models;

use Database\Factories\AntgoReInfrastructureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class antgo_re_infrastructure extends Model
{
    /** @use HasFactory<AntgoReInfrastructureFactory> */
    use HasFactory;

    protected $table = 'antgo_re_infrastructures';

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
