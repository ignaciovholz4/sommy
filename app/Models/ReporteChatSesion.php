<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteChatSesion extends Model
{
    protected $table = 'reportes_chat_sesiones';

    protected $fillable = ['user_id', 'titulo'];

    public function mensajes()
    {
        return $this->hasMany(ReporteChatMensaje::class, 'sesion_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
