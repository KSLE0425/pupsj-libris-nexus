<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('courses', 'programs');

        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('course', 'program');
        });
    }

    public function down(): void
    {
        Schema::rename('programs', 'courses');

        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('program', 'course');
        });
    }
};
