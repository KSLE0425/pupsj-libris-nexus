<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Librarian extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'librarians';

    protected $fillable = ['librarian_number', 'first_name', 'last_name', 'email', 'password'];

    protected $hidden = ['password'];
}
