<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    // Desactivamos updated_at porque solo nos importa el momento del intento
    public $timestamps = false;

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'identifier',
        'ip_address',
        'status',
        'user_agent',
        'created_at',
    ];
}