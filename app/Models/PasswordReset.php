<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = [
        'email',
        'guard',
        'token',
        'created_at',
        'expires_at',
    ];

    public $timestamps = false;
}