<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PupsjAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = Hash::make('LibrisAdmin@2024');

        // PUPSJ Libris Admin user account
        User::updateOrCreate(
            ['email' => 'alvarezvirgilio284@gmail.com'],
            [
                'name'              => 'PUPSJ Libris Admin',
                'password'          => $adminPassword,
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Test student account used for "View as Student" impersonation
        Student::updateOrCreate(
            ['email' => 'pupsjlibrisnexus@gmail.com'],
            [
                'student_number' => 'PUPSJ-ADMIN-S',
                'first_name'     => 'PUPSJ',
                'last_name'      => 'Libris (Test Student)',
                'program'        => 'Library and Information Science',
                'year_level'     => '1',
                'password'       => $adminPassword,
                'status'         => 'approved',
            ]
        );

        // Test faculty account used for "View as Faculty" impersonation
        Faculty::updateOrCreate(
            ['email' => 'pupsjlibrisnexus@gmail.com'],
            [
                'employee_id' => 'PUPSJ-ADMIN-F',
                'first_name'  => 'PUPSJ',
                'last_name'   => 'Libris (Test Faculty)',
                'department'  => 'Library Services',
                'password'    => $adminPassword,
                'status'      => 'approved',
            ]
        );
    }
}
