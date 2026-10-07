<?php

namespace App\Models;

use Database\Factories\AntgoReContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class antgo_re_contact extends Model
{
    /** @use HasFactory<AntgoReContactFactory> */
    use HasFactory;

    protected $fillable = [
        'id',
        'code',
        'type',
        'taxId',
        'formatedName',
        'name',
        'middleName',
        'lastName',
        'companyName',
        'gender',
        'birthDate',
        'email',
        'phone',
    ];

    protected $table = 'antgo_re_contacts';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $casts = [
        'birthDate' => 'date',
    ];

    public function addresses(): MorphMany
    {
        return $this->morphMany(antgo_re_address::class, 'addressable');
    }
}
