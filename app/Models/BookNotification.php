<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookNotification extends Model
{
    protected $fillable = ['book_id', 'student_id', 'faculty_id'];

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

    public function getNotifierEmailAttribute()
    {
        if ($this->student_id && $this->student) {
            return $this->student->email;
        }
        if ($this->faculty_id && $this->faculty) {
            return $this->faculty->email;
        }
        return null;
    }

    public function getNotifierNameAttribute()
    {
        if ($this->student_id && $this->student) {
            return $this->student->first_name . ' ' . $this->student->last_name;
        }
        if ($this->faculty_id && $this->faculty) {
            return $this->faculty->first_name . ' ' . $this->faculty->last_name;
        }
        return 'Unknown';
    }
}