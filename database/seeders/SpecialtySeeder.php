<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            'Information Technology & Computer Science',
            'Software Engineering & Web Development',
            'Business Administration & Marketing Management',
            'Office Administration & Management',
            'Education & Pedagogy',
            'Civil Engineering & Infrastructure',
            'General Education & Humanities',
        ];

        foreach ($specialties as $name) {
            Specialty::updateOrCreate(['name' => $name]);
        }
    }
}
