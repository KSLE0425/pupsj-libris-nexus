<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class Faculty extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'first_name', 'last_name', 'email', 'password',
        'department', 'status', 'verification_doc_path',
        'borrowing_suspended_until', 'damage_warning_count', 'is_banned',
        'last_activity_at', 'last_borrow_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'borrowing_suspended_until' => 'datetime',
        'last_activity_at'          => 'datetime',
        'last_borrow_at'            => 'datetime',
        'is_banned'                 => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($faculty) {
            if (!empty($faculty->password) && Hash::needsRehash($faculty->password)) {
                $faculty->password = Hash::make($faculty->password);
            }
        });
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'faculty_specialty');
    }

    public function bookUsages()
    {
        return $this->hasMany(BookUsage::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function bookRequisitions()
    {
        return $this->hasMany(BookRequisition::class);
    }

    public function libraryPenalties()
    {
        return $this->hasMany(LibraryPenalty::class);
    }

    public function patronBans()
    {
        return $this->hasMany(PatronBan::class);
    }
}