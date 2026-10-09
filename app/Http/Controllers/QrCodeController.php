<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Faculty;
use App\Models\Student;
use App\Services\QrCryptoService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function book(Book $book)
    {
        $payload = QrCryptoService::encrypt('BOOK:' . $book->id);
        $svg = QrCode::format('svg')->size(300)->generate($payload);

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function student(Student $student)
    {
        $payload = QrCryptoService::encrypt('STUDENT:' . $student->id);
        $svg = QrCode::format('svg')->size(300)->generate($payload);

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    public function faculty(Faculty $faculty)
    {
        $payload = QrCryptoService::encrypt('FACULTY:' . $faculty->id);
        $svg = QrCode::format('svg')->size(300)->generate($payload);

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }
}
