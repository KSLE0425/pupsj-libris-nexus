<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Api\AIBookLookupController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookUsageController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\StudentController;
use App\Models\Book;
use Illuminate\Support\Facades\Route;

// ======================
// PUBLIC ROUTES
// ======================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// AI book lookup for requisitions
Route::post('/ai/book-lookup', [AIBookLookupController::class, 'lookup']);

// ======================
// PROTECTED ROUTES
// ======================

// Route::middleware('auth:sanctum')->group(function () {

//    Route::apiResource('books', BookController::class);
//   Route::apiResource('students', StudentController::class);
//
//   Route::post('/book-usage/start', [BookUsageController::class, 'start']);
//   Route::put('/book-usage/end/{id}', [BookUsageController::class, 'end']);

// });

// ======================
// STUDENT SPECIAL ROUTES FIRST
// ======================

Route::get('/students/deleted', [StudentController::class, 'deleted']);
Route::put('/students/restore/{id}', [StudentController::class, 'restore']);
Route::delete('/students/force-delete/{id}', [StudentController::class, 'forceDelete']);

// ======================
// STUDENT RESOURCE ROUTE
// ======================

Route::apiResource('students', StudentController::class);

// ======================
// BOOK SPECIAL ROUTES FIRST
// ======================

Route::get('/books/deleted', [BookController::class, 'deleted']);
Route::put('/books/restore/{id}', [BookController::class, 'restore']);
Route::delete('/books/force-delete/{id}', [BookController::class, 'forceDelete']);

// ======================
// BOOK RESOURCE ROUTE
// ======================

Route::get('/books/search', [BookController::class, 'search']);
Route::apiResource('books', BookController::class);

Route::get('/book-usage', [BookUsageController::class, 'index']);
Route::post('/book-usage/start', [BookUsageController::class, 'start']);
Route::put('/book-usage/end/{id}', [BookUsageController::class, 'end']);

Route::get('/reservations', [ReservationController::class, 'index']);
Route::post('/reservations', [ReservationController::class, 'store']);
Route::put('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);

Route::get('/activity-logs', [ActivityLogController::class, 'index']);

Route::get('/qrcode/books/{book}', [QrCodeController::class, 'book']);
Route::get('/qrcode/students/{student}', [QrCodeController::class, 'student']);
Route::get('/qrcode/faculties/{faculty}', [QrCodeController::class, 'faculty']);

Route::post('/book-usage/return', [BookUsageController::class, 'returnBook']);

Route::get('/books/barcode/{barcode}', function ($barcode) {

    $book = Book::where('barcode', $barcode)->first();

    if (! $book) {
        return response()->json([
            'message' => 'Book not found',
        ], 404);
    }

    return response()->json($book);

});
