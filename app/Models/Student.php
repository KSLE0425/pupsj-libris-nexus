<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model implements AuthenticatableContract
{
    use Authenticatable, SoftDeletes;

protected $fillable = [
    'student_number',
    'first_name',
    'last_name',
    'program',
    'year_level',
    'email',
    'password',
    'pup_email',
    'cor_file_path',
    'status',
    'in_library_only',
    'borrowing_suspended_until',
    'damage_warning_count',
    'is_banned',
    'last_activity_at',
    'last_borrow_at',
];

// Optional: scope for pending
public function scopePending($query)
{
    return $query->where('status', 'pending');
}

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'borrowing_suspended_until' => 'datetime',
        'last_activity_at'          => 'datetime',
        'last_borrow_at'            => 'datetime',
        'is_banned'                 => 'boolean',
        'in_library_only'           => 'boolean',
    ];

    public function usages()
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
