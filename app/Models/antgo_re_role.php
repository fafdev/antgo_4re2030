<?php

namespace App\Models;

use Database\Factories\AntgoReRoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class antgo_re_role extends Model
{
    /** @use HasFactory<AntgoReRoleFactory> */
    use HasFactory;

    protected $table = 'antgo_re_roles';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'inSites',
        'inBuildings',
        'inProperties',
        'inContracts',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'inSites' => 'boolean',
        'inBuildings' => 'boolean',
        'inProperties' => 'boolean',
        'inContracts' => 'boolean',
    ];
}
