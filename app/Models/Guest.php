<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $fillable = [
        'name', 'email', 'guest_identifier', 'password', 'is_active',
        'type', 'previous_student_number', 'graduation_year',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeAlumni($query)
    {
        return $query->where('type', 'alumni');
    }

    public function scopeGeneral($query)
    {
        return $query->where('type', 'general');
    }
}