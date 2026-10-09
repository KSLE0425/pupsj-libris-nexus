<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryPenalty extends Model
{
    protected $fillable = [
        'student_id',
        'faculty_id',
        'book_id',
        'book_usage_id',
        'penalty_type',
        'damage_level',
        'amount',
        'status',
        'due_date',
        'admin_user_id',
        'patron_note',
        'admin_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'damage_level' => 'integer',
    ];

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

    public function bookUsage()
    {
        return $this->belongsTo(BookUsage::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function scopeBlockingBorrow($query)
    {
        return $query->where('status', 'pending');
    }
}
