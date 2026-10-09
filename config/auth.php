<?php

use App\Models\Librarian;
use App\Models\Student;
use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'librarian' => [
            'driver' => 'session',
            'provider' => 'librarians',
        ],

        'student' => [
            'driver' => 'session',
            'provider' => 'students',
        ],

        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
        'faculty' => [
            'driver' => 'session',
            'provider' => 'faculties',
        ],

        'staff' => [
            'driver' => 'session',
            'provider' => 'staffs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        'students' => [
            'driver' => 'eloquent',
            'model' => Student::class,
        ],

        'librarians' => [
            'driver' => 'eloquent',
            'model' => Librarian::class,
        ],
        
        'faculties' => [
            'driver' => 'eloquent',
            'model' => App\Models\Faculty::class,
        ],

        'staffs' => [
            'driver' => 'eloquent',
            'model' => App\Models\Staff::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
