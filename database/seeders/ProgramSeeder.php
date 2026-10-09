<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSBA-MM', 'name' => 'Bachelor of Science in Business Administration major in Marketing Management'],
            ['code' => 'BSED-ENG', 'name' => 'Bachelor of Secondary Education major in English'],
            ['code' => 'BSOA', 'name' => 'Bachelor of Science in Office Administration'],
            ['code' => 'DOMT', 'name' => 'Diploma in Office Management Technology'],
            ['code' => 'BSCE', 'name' => 'Bachelor of Science in Civil Engineering'],
        ];

        foreach ($programs as $prog) {
            Course::updateOrCreate(
                ['code' => $prog['code']],
                ['name' => $prog['name']]
            );
        }
    }
}
