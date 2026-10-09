<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatronBan extends Model
{
    protected $fillable = [
        'user_type', 'student_id', 'faculty_id',
        'warning_count_at_ban',
        'banned_by', 'banned_at',
        'unbanned_by', 'unbanned_at', 'unban_reason',
    ];

    protected $casts = [
        'banned_at'   => 'datetime',
        'unbanned_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function bannedByAdmin()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function unbannedByAdmin()
    {
        return $this->belongsTo(User::class, 'unbanned_by');
    }

    public function getIsActiveAttribute(): bool
    {
        return is_null($this->unbanned_at);
    }
}
