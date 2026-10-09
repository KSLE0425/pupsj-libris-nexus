<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookRequisition extends Model
{
    protected $fillable = [
        'student_id',
        'faculty_id',
        'title',
        'isbn',
        'author',
        'publisher',
        'image_path',
        'justification',
        'justification_checklist',
        'justification_other',
        'ocr_text',
        'status',
        'admin_notes',
        'handled_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getRequesterNameAttribute(): string
    {
        if ($this->student_id && $this->student) {
            return $this->student->first_name.' '.$this->student->last_name;
        }
        if ($this->faculty_id && $this->faculty) {
            return $this->faculty->first_name.' '.$this->faculty->last_name;
        }

        return 'Unknown';
    }
}
