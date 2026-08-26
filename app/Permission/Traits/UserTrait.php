<?php
namespace App\Permission\Traits;

use App\Permission\Models\UserPermission;

trait UserTrait {
    //es: desde aqui
    //en: from here
    public function roles(){
        return $this->belongsToMany('App\Permission\Models\Role')->withTimestamps();
    }

    /** Excepciones de permiso por persona: mandan por encima del rol (incluso full-access). */
    public function permissionOverrides()
    {
        return $this->hasMany(UserPermission::class);
    }

    /** Sucursales explícitas asignadas a esta persona; null = sin restricción (ve todas). */
    public function sucursales()
    {
        return $this->belongsToMany(\App\Models\Sucursal::class, 'sucursal_user')->withTimestamps();
    }

    public function sucursalesPermitidas(): ?array
    {
        $ids = $this->sucursales()->pluck('sucursales.id')->all();

        return empty($ids) ? null : $ids;
    }

    public function havePermission($permission)
    {
        $override = UserPermission::where('user_id', $this->id)
            ->whereHas('permission', fn ($q) => $q->where('slug', $permission))
            ->first();

        if ($override) {
            return $override->tipo === 'otorgado';
        }

        foreach($this->roles as $role){
            if(strtolower($role['full-access']) == "yes"){
                return true;
                // return 'true full access';
            }
            foreach ($role->permissions as $perm) {
                if($perm->slug == $permission){
                    return true;
                    // return 'true por permisos';
                }
            }
        }
        return false;
        // return $this->roles;
    }
}