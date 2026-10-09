<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // REGISTER STUDENT
    public function register(Request $request)
    {

        $request->validate([
            'student_number' => 'required|unique:students',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'program' => 'required|string',
            'year_level' => 'required|string',
            'email' => 'required|email|unique:students',
            'password' => 'required|min:6',
        ]);

        $student = Student::create([
            'student_number' => $request->student_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'program' => $request->program,
            'year_level' => $request->year_level,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Student registered successfully',
            'student' => $student,
        ]);

    }

    // LOGIN STUDENT
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $student = Student::where('email', $request->email)->first();

        if (! $student || ! Hash::check($request->password, $student->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'student' => $student,
        ]);

    }
}
