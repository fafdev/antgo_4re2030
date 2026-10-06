<?php

namespace App\Models;

use Database\Factories\AntgoReAdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class antgo_re_admin extends Model
{
    /** @use HasFactory<AntgoReAdminFactory> */
    use HasFactory;

    protected $table = 'antgo_re_admins';

    protected $primaryKey = 'role';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['role', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'admin_role', 'role');
    }
}
