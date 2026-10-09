<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\Guest;
use App\Models\Librarian;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123!');

        // ==========================================
        // 1. STUDENT TEST ACCOUNTS
        // ==========================================

        // Standard Active Student
        Student::updateOrCreate(
            ['email' => 'student@pupsj.edu.ph'],
            [
                'student_number' => '2024-00001-SJ-0',
                'first_name'     => 'Juan',
                'last_name'      => 'Dela Cruz',
                'program'        => 'Bachelor of Science in Information Technology',
                'year_level'     => '3',
                'password'       => $defaultPassword,
                'status'         => 'approved',
                'pup_email'      => 'jdelacruz@pup.edu.ph',
                'is_banned'      => false,
            ]
        );

        // Pending Registration Student
        Student::updateOrCreate(
            ['email' => 'pending.student@pupsj.edu.ph'],
            [
                'student_number' => '2024-00002-SJ-0',
                'first_name'     => 'Maria',
                'last_name'      => 'Clara',
                'program'        => 'Bachelor of Science in Business Administration',
                'year_level'     => '1',
                'password'       => $defaultPassword,
                'status'         => 'pending',
                'is_banned'      => false,
            ]
        );

        // Banned / Suspended Student
        Student::updateOrCreate(
            ['email' => 'banned.student@pupsj.edu.ph'],
            [
                'student_number' => '2024-00003-SJ-0',
                'first_name'     => 'Pedro',
                'last_name'      => 'Penduko',
                'program'        => 'Bachelor of Science in Civil Engineering',
                'year_level'     => '4',
                'password'       => $defaultPassword,
                'status'         => 'approved',
                'is_banned'      => true,
            ]
        );


        // ==========================================
        // 2. FACULTY TEST ACCOUNTS
        // ==========================================

        // Standard Active Faculty
        Faculty::updateOrCreate(
            ['email' => 'faculty@pupsj.edu.ph'],
            [
                'employee_id' => 'FAC-2024-001',
                'first_name'  => 'Dr. Jose',
                'last_name'   => 'Rizal',
                'department'  => 'Department of Computer Studies',
                'password'    => $defaultPassword,
                'status'      => 'approved',
                'is_banned'   => false,
            ]
        );

        // Pending Registration Faculty
        Faculty::updateOrCreate(
            ['email' => 'pending.faculty@pupsj.edu.ph'],
            [
                'employee_id' => 'FAC-2024-002',
                'first_name'  => 'Prof. Apolinario',
                'last_name'   => 'Mabini',
                'department'  => 'Department of Business Administration',
                'password'    => $defaultPassword,
                'status'      => 'pending',
                'is_banned'   => false,
            ]
        );

        // Banned Faculty Account
        Faculty::updateOrCreate(
            ['email' => 'banned.faculty@pupsj.edu.ph'],
            [
                'employee_id' => 'FAC-2024-003',
                'first_name'  => 'Prof. Andres',
                'last_name'   => 'Bonifacio',
                'department'  => 'Department of Engineering',
                'password'    => $defaultPassword,
                'status'      => 'approved',
                'is_banned'   => true,
            ]
        );


        // ==========================================
        // 3. STAFF TEST ACCOUNTS
        // ==========================================

        // Active Non-Fixing Staff
        Staff::updateOrCreate(
            ['email' => 'staff@pupsj.edu.ph'],
            [
                'employee_id' => 'STF-2024-001',
                'first_name'  => 'Emilio',
                'last_name'   => 'Aguinaldo',
                'type'        => 'non_fixing',
                'department'  => 'Library Front Desk',
                'password'    => $defaultPassword,
                'status'      => 'active',
            ]
        );

        // Active Fixing Staff (Technical / Maintenance)
        Staff::updateOrCreate(
            ['email' => 'fixing.staff@pupsj.edu.ph'],
            [
                'employee_id' => 'STF-2024-002',
                'first_name'  => 'Melchora',
                'last_name'   => 'Aquino',
                'type'        => 'fixing',
                'department'  => 'Book Maintenance & Repair',
                'password'    => $defaultPassword,
                'status'      => 'active',
            ]
        );

        // Pending Staff Account
        Staff::updateOrCreate(
            ['email' => 'pending.staff@pupsj.edu.ph'],
            [
                'employee_id' => 'STF-2024-003',
                'first_name'  => 'Gregorio',
                'last_name'   => 'Del Pilar',
                'type'        => 'non_fixing',
                'department'  => 'Library Administration',
                'password'    => $defaultPassword,
                'status'      => 'pending',
            ]
        );


        // ==========================================
        // 4. LIBRARIAN TEST ACCOUNTS
        // ==========================================

        // Head Librarian
        Librarian::updateOrCreate(
            ['email' => 'librarian@pupsj.edu.ph'],
            [
                'librarian_number' => 'LIB-2024-001',
                'first_name'       => 'Gabriela',
                'last_name'        => 'Silang',
                'password'         => $defaultPassword,
            ]
        );

        // Assistant Librarian
        Librarian::updateOrCreate(
            ['email' => 'assistant.librarian@pupsj.edu.ph'],
            [
                'librarian_number' => 'LIB-2024-002',
                'first_name'       => 'Antonio',
                'last_name'        => 'Luna',
                'password'         => $defaultPassword,
            ]
        );


        // ==========================================
        // 5. GUEST TEST ACCOUNTS
        // ==========================================

        // Alumni Guest Account
        Guest::updateOrCreate(
            ['email' => 'alumni.guest@pupsj.edu.ph'],
            [
                'name'                   => 'Marcelo H. Del Pilar',
                'guest_identifier'       => 'GST-ALUMNI-001',
                'password'               => $defaultPassword,
                'is_active'              => true,
                'type'                   => 'alumni',
                'previous_student_number' => '2020-00123-SJ-0',
                'graduation_year'        => 2024,
            ]
        );

        // General Visitor Guest Account
        Guest::updateOrCreate(
            ['email' => 'general.guest@pupsj.edu.ph'],
            [
                'name'             => 'Graciano Lopez Jaena',
                'guest_identifier' => 'GST-GENERAL-001',
                'password'         => $defaultPassword,
                'is_active'        => true,
                'type'             => 'general',
            ]
        );
    }
}
