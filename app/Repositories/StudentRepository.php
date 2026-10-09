<?php

namespace App\Repositories;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentRepository
{
    /**
     * Get all students (active only, ordered by ID ascending).
     */
    public function getAll()
    {
        return Student::orderBy('id', 'asc')->get();
    }

    /**
     * Get a single student by ID.
     */
    public function findById($id)
    {
        return Student::findOrFail($id);
    }

    /**
     * Get soft-deleted (archived) students.
     */
    public function getArchived()
    {
        return Student::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
    }

    /**
     * Create a new student.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'student_number' => [
                'required',
                'regex:/^\d{4}-\d{1,5}-SJ-[01]$/',
                'unique:students,student_number',
                function ($attribute, $value, $fail) {
                    $year = substr($value, 0, 4);
                    if ($year > date('Y')) {
                        $fail('Enrollment year cannot be in the future.');
                    }
                }
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'program' => 'required|string|max:100',
            'year_level' => 'required|string|max:20',
            'email' => 'required|email|unique:students,email',
        ], [
            'student_number.regex' => 'Format must be YYYY-XXXXX-SJ-0 or YYYY-XXXXX-SJ-1',
        ]);

        // Password is not required for admin creation – you can set a default or leave null
        // For simplicity, we set a random password if none is provided (should be changed via profile)
        if (empty($validated['password'])) {
            $validated['password'] = Hash::make('password123'); // temporary, user can change later
        }

        return Student::create($validated);
    }

    /**
     * Update an existing student.
     */
    public function update(Student $student, Request $request)
    {
        $validated = $request->validate([
            'student_number' => [
                'required',
                'regex:/^\d{4}-\d{1,5}-SJ-[01]$/',
                'unique:students,student_number,' . $student->id,
                function ($attribute, $value, $fail) {
                    $year = substr($value, 0, 4);
                    if ($year > date('Y')) {
                        $fail('Enrollment year cannot be in the future.');
                    }
                }
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'program' => 'required|string|max:100',
            'year_level' => 'required|string|max:20',
            'email' => 'required|email|unique:students,email,' . $student->id,
        ], [
            'student_number.regex' => 'Format must be YYYY-XXXXX-SJ-0 or YYYY-XXXXX-SJ-1',
        ]);

        $student->update($validated);
        return $student;
    }

    /**
     * Archive (soft delete) a student.
     */
    public function archive(Student $student)
    {
        $student->delete();  // uses SoftDeletes
        return $student;
    }

    /**
     * Restore an archived student.
     */
    public function restore($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();
        return $student;
    }

    /**
     * Permanently delete a student (force delete).
     */
    public function forceDelete($id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->forceDelete();
    }
}