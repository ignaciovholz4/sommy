<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Permission\Traits\UserTrait;


class User extends Authenticatable implements MustVerifyEmailContract
{
    use Notifiable, UserTrait;

    /**
     * Mail de restablecimiento de contraseña en español (marca Sommy).
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\SommyResetPassword($token));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'estatus', 'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
    ];

    //es: desde aqui
    //en: from here 
    /**function que se ejecuta desde el seeder*/
    public function roles(){
        return $this->belongsToMany('App\Permission\Models\Role')->withTimestamps();
    }

    public function hasRole($roleName) {
        return $this->roles()->where('name', $roleName)->exists();
    }

    // public function havePermission($permission){
    //     //return $this->roles;
    //     foreach($this->roles as $role){
    //         if($role['full-access'] == "yes"){
    //             return 'true full access';
    //         }
    //         foreach ($role->permissions as $perm) {
    //             if($perm->slug == $permission){
    //                 return 'true por permisos';
    //             }
    //         }
    //     }
    //     return 'false';
    // }
}
