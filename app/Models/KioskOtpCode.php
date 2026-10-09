<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KioskOtpCode extends Model
{
    protected $fillable = ['email', 'code', 'guard', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];
}
