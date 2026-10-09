<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class Staff extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'first_name', 'last_name', 'email', 'password',
        'type', 'department', 'status', 'verification_doc_path',
        'borrowing_suspended_until', 'last_activity_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'borrowing_suspended_until' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($staff) {
            if (!empty($staff->password) && Hash::needsRehash($staff->password)) {
                $staff->password = Hash::make($staff->password);
            }
        });
    }

    public function scopeFixing($query)
    {
        return $query->where('type', 'fixing');
    }

    public function scopeNonFixing($query)
    {
        return $query->where('type', 'non_fixing');
    }
}
