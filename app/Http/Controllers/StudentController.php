<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Repositories\StudentRepository;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentRepo;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepo = $studentRepo;
    }

    // ==============================
    // API: Show active students only
    // ==============================
    public function index()
    {
        return response()->json($this->studentRepo->getAll());
    }

    // ==============================
    // API: Show deleted (archived) students
    // ==============================
    public function deleted()
    {
        return response()->json($this->studentRepo->getArchived());
    }

    // ==============================
    // API: Store new student
    // ==============================
    public function store(Request $request)
    {
        $student = $this->studentRepo->create($request);
        $name = $student->first_name . ' ' . $student->last_name;

        AuditLogger::log('user_created', 'Created student record for ' . $name . ' (' . ($student->student_number ?? $student->email) . ')', [
            'affected_user'  => $name,
            'student_id'     => $student->id,
            'student_number' => $student->student_number,
            'email'          => $student->email,
        ], 'users');

        return response()->json([
            'message' => 'Student created successfully',
            'data' => $student,
        ], 201);
    }

    // ==============================
    // API: Show one student
    // ==============================
    public function show(Student $student)
    {
        return response()->json($student);
    }

    // ==============================
    // API: Update student
    // ==============================
    public function update(Request $request, Student $student)
    {
        $updatedStudent = $this->studentRepo->update($student, $request);
        $name = $updatedStudent->first_name . ' ' . $updatedStudent->last_name;

        AuditLogger::log('user_updated', 'Updated student record for ' . $name . ' (' . ($updatedStudent->student_number ?? $updatedStudent->email) . ')', [
            'affected_user'  => $name,
            'student_id'     => $updatedStudent->id,
            'student_number' => $updatedStudent->student_number,
        ], 'users');

        return response()->json([
            'message' => 'Student updated successfully',
            'data' => $updatedStudent,
        ]);
    }

    // ==============================
    // API: Soft delete (archive) student
    // ==============================
    public function destroy(Student $student)
    {
        $name = $student->first_name . ' ' . $student->last_name;
        $num = $student->student_number;
        $id = $student->id;

        $this->studentRepo->archive($student);

        AuditLogger::log('user_archived', 'Archived student: ' . $name . ' (' . ($num ?? 'ID: ' . $id) . ')', [
            'affected_user'  => $name,
            'student_id'     => $id,
            'student_number' => $num,
        ], 'users');

        return response()->json([
            'message' => 'Student soft deleted successfully',
        ]);
    }

    // ==============================
    // API: Restore student
    // ==============================
    public function restore($id)
    {
        $student = $this->studentRepo->restore($id);
        $name = $student ? ($student->first_name . ' ' . $student->last_name) : 'Student #' . $id;

        AuditLogger::log('user_restored', 'Restored student: ' . $name, [
            'affected_user'  => $name,
            'student_id'     => $id,
        ], 'users');

        return response()->json([
            'message' => 'Student restored successfully',
        ]);
    }

    // ==============================
    // API: Force delete student (permanent)
    // ==============================
    public function forceDelete($id)
    {
        $this->studentRepo->forceDelete($id);

        AuditLogger::log('user_deleted', 'Permanently deleted student #' . $id, [
            'student_id' => $id,
        ], 'users');

        return response()->json([
            'message' => 'Student permanently deleted',
        ]);
    }
}