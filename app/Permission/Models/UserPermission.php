<?php

namespace App\Permission\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Override de permiso por persona: manda por encima de lo que otorgue su
 * rol (otorgado agrega, denegado quita), aunque el rol tenga full-access.
 */
class UserPermission extends Model
{
    protected $fillable = ['user_id', 'permission_id', 'tipo'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
