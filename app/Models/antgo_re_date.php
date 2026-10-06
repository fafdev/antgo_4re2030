<?php

namespace App\Models;

use Database\Factories\AntgoReDateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class antgo_re_date extends Model
{
    /** @use HasFactory<AntgoReDateFactory> */
    use HasFactory;

    protected $table = 'antgo_re_dates';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
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

    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
