<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Faculty;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\QrCryptoService;
use Illuminate\Http\Request;

class GlobalArchiveController extends Controller
{
    /**
     * Look up an item (book, student, faculty) by scanned code, barcode, QR, accession no, student no, employee ID.
     */
    public function scan(Request $request)
    {
        $request->validate(['code' => 'required|string|max:255']);

        $raw = trim($request->code);
        $clean = trim(QrCryptoService::decrypt($raw));

        // 1. Check explicit QR prefixes
        if (preg_match('/^BOOK:\s*(\d+)$/i', $clean, $m)) {
            $book = Book::withTrashed()->find((int)$m[1]);
            if ($book) return $this->processBook($book);
        }

        if (preg_match('/^STUDENT:\s*(\d+)$/i', $clean, $m)) {
            $student = Student::withTrashed()->find((int)$m[1]);
            if ($student) return $this->processStudent($student);
        }

        if (preg_match('/^FACULTY:\s*(\d+)$/i', $clean, $m)) {
            $faculty = Faculty::withTrashed()->find((int)$m[1]);
            if ($faculty) return $this->processFaculty($faculty);
        }

        // 2. Try Books (Accession Number, Barcode, LOC/Call Number, ISBN)
        $book = Book::withTrashed()->where('barcode', $clean)
            ->orWhere('accession_number', $clean)
            ->orWhere('loc_number', $clean)
            ->orWhere('isbn', $clean)
            ->first();

        if ($book) {
            return $this->processBook($book);
        }

        // 3. Try Students (Student Number, Email)
        $student = Student::withTrashed()->where('student_number', $clean)
            ->orWhere('email', $clean)
            ->first();

        if ($student) {
            return $this->processStudent($student);
        }

        // 4. Try Faculty (Employee ID, Email)
        $faculty = Faculty::withTrashed()->where('employee_id', $clean)
            ->orWhere('email', $clean)
            ->first();

        if ($faculty) {
            return $this->processFaculty($faculty);
        }

        // 5. Fallback numeric DB ID search
        if (is_numeric($clean)) {
            $id = (int) $clean;
            $book = Book::withTrashed()->find($id);
            if ($book) return $this->processBook($book);

            $student = Student::withTrashed()->find($id);
            if ($student) return $this->processStudent($student);

            $faculty = Faculty::withTrashed()->find($id);
            if ($faculty) return $this->processFaculty($faculty);
        }

        return response()->json([
            'success' => false,
            'message' => "No book, student, or faculty found matching \"{$raw}\".",
        ], 404);
    }

    /**
     * Process archive/unarchive toggle on Book.
     */
    protected function processBook(Book $book)
    {
        $isArchived = ($book->status === 'archived') || $book->trashed();
        $action = $isArchived ? 'unarchived' : 'archived';

        if ($isArchived) {
            if ($book->trashed()) $book->restore();
            $book->update(['status' => 'available']);
            AuditLogger::log('book_unarchived', "Restored book \"{$book->title}\" from archive", [
                'book_id'          => $book->id,
                'affected_book'    => $book->title,
                'accession_number' => $book->accession_number,
            ], 'books');
        } else {
            $book->update(['status' => 'archived']);
            AuditLogger::log('book_archived', "Archived book \"{$book->title}\"", [
                'book_id'          => $book->id,
                'affected_book'    => $book->title,
                'accession_number' => $book->accession_number,
            ], 'books');
        }

        return response()->json([
            'success'     => true,
            'action'      => $action,
            'type'        => 'Book',
            'id'          => $book->id,
            'title'       => $book->title,
            'subtitle'    => ($book->author ?? 'Unknown') . ($book->accession_number ? " (Acc: {$book->accession_number})" : ''),
            'identifier'  => $book->accession_number ?: ($book->barcode ?: "ID #{$book->id}"),
            'status'      => $book->status,
            'timestamp'   => now('Asia/Manila')->format('h:i:s A'),
            'message'     => "✓ Book {$action}: \"{$book->title}\"",
        ]);
    }

    /**
     * Process archive/unarchive toggle on Student.
     */
    protected function processStudent(Student $student)
    {
        $isArchived = $student->trashed();
        $action = $isArchived ? 'unarchived' : 'archived';
        $name = $student->first_name . ' ' . $student->last_name;

        if ($isArchived) {
            $student->restore();
            AuditLogger::log('user_restored', "Restored student \"{$name}\" from archive", [
                'student_id'     => $student->id,
                'affected_user'  => $name,
                'student_number' => $student->student_number,
            ], 'users');
        } else {
            $student->delete();
            AuditLogger::log('user_archived', "Archived student \"{$name}\"", [
                'student_id'     => $student->id,
                'affected_user'  => $name,
                'student_number' => $student->student_number,
            ], 'users');
        }

        return response()->json([
            'success'     => true,
            'action'      => $action,
            'type'        => 'Student',
            'id'          => $student->id,
            'title'       => $name,
            'subtitle'    => ($student->program ?? 'Student') . ($student->student_number ? " • {$student->student_number}" : ''),
            'identifier'  => $student->student_number ?: "ID #{$student->id}",
            'status'      => $action === 'archived' ? 'archived' : 'active',
            'timestamp'   => now('Asia/Manila')->format('h:i:s A'),
            'message'     => "✓ Student {$action}: \"{$name}\"",
        ]);
    }

    /**
     * Process archive/unarchive toggle on Faculty.
     */
    protected function processFaculty(Faculty $faculty)
    {
        $isArchived = $faculty->trashed();
        $action = $isArchived ? 'unarchived' : 'archived';
        $name = $faculty->first_name . ' ' . $faculty->last_name;

        if ($isArchived) {
            $faculty->restore();
            AuditLogger::log('user_restored', "Restored faculty \"{$name}\" from archive", [
                'faculty_id'    => $faculty->id,
                'affected_user' => $name,
                'employee_id'   => $faculty->employee_id,
            ], 'users');
        } else {
            $faculty->delete();
            AuditLogger::log('user_archived', "Archived faculty \"{$name}\"", [
                'faculty_id'    => $faculty->id,
                'affected_user' => $name,
                'employee_id'   => $faculty->employee_id,
            ], 'users');
        }

        return response()->json([
            'success'     => true,
            'action'      => $action,
            'type'        => 'Faculty',
            'id'          => $faculty->id,
            'title'       => $name,
            'subtitle'    => ($faculty->department ?? 'Faculty') . ($faculty->employee_id ? " • {$faculty->employee_id}" : ''),
            'identifier'  => $faculty->employee_id ?: "ID #{$faculty->id}",
            'status'      => $action === 'archived' ? 'archived' : 'active',
            'timestamp'   => now('Asia/Manila')->format('h:i:s A'),
            'message'     => "✓ Faculty {$action}: \"{$name}\"",
        ]);
    }
}
