<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryDamageReport extends Model
{
    protected $fillable = [
        'book_usage_id',
        'student_id',
        'faculty_id',
        'book_id',
        'patron_note',
        'status',
        'damage_level',
        'admin_user_id',
        'library_penalty_id',
        'admin_notes',
    ];

    protected $casts = [
        'damage_level' => 'integer',
    ];

    public function bookUsage()
    {
        return $this->belongsTo(BookUsage::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function penalty()
    {
        return $this->belongsTo(LibraryPenalty::class, 'library_penalty_id');
    }
}
