<?php

namespace App\Models;

use Database\Factories\AntgoReMeasureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class antgo_re_measure extends Model
{
    /** @use HasFactory<AntgoReMeasureFactory> */
    use HasFactory;

    protected $table = 'antgo_re_measures';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
    ];
}
