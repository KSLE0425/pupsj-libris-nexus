<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'book_id', 'student_id', 'faculty_id', 'status', 'fulfilled_at', 'position', 'expires_at', 'reserved_at',
    ];

    protected $casts = [
        'fulfilled_at' => 'datetime',
        'expires_at' => 'datetime',
        'reserved_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    // Get the reserver's email and name irrespective of type
    public function getReserverEmailAttribute()
    {
        if ($this->student_id) {
            return $this->student->email;
        }
        if ($this->faculty_id) {
            return $this->faculty->email;
        }
        return null;
    }

    public function getReserverNameAttribute()
    {
        if ($this->student_id) {
            return $this->student->first_name . ' ' . $this->student->last_name;
        }
        if ($this->faculty_id) {
            return $this->faculty->first_name . ' ' . $this->faculty->last_name;
        }
        return 'Unknown';
    }
}