<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookUsage;
use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Database\Seeder;

class BookUsageSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::where('email', 'student@pupsj.edu.ph')->first();
        $faculty = Faculty::where('email', 'faculty@pupsj.edu.ph')->first();
        $bookCS = Book::where('barcode', 'PUP-CS-001')->first();
        $bookFil = Book::where('barcode', 'PUP-FIL-001')->first();
        $bookBus = Book::where('barcode', 'PUP-BUS-001')->first();

        if ($student && $bookCS) {
            BookUsage::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'book_id'    => $bookCS->id,
                    'status'     => 'active',
                ],
                [
                    'time_in'       => now()->subDays(2),
                    'usage_context' => 'home_borrow',
                    'remarks'       => 'Borrowed for CS algorithms research',
                ]
            );
        }

        if ($student && $bookFil) {
            BookUsage::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'book_id'    => $bookFil->id,
                    'status'     => 'completed',
                ],
                [
                    'time_in'       => now()->subDays(10),
                    'time_out'      => now()->subDays(3),
                    'usage_context' => 'home_borrow',
                    'remarks'       => 'Returned on time',
                ]
            );
        }

        if ($faculty && $bookBus) {
            BookUsage::updateOrCreate(
                [
                    'faculty_id' => $faculty->id,
                    'book_id'    => $bookBus->id,
                    'status'     => 'active',
                ],
                [
                    'time_in'       => now()->subDays(1),
                    'usage_context' => 'in_library',
                    'remarks'       => 'In-library reading session',
                ]
            );
        }
    }
}
