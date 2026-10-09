<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->enum('type', ['alumni', 'general'])->default('general')->after('is_active');
            $table->string('previous_student_number')->nullable()->after('type');
            $table->unsignedSmallInteger('graduation_year')->nullable()->after('previous_student_number');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['type', 'previous_student_number', 'graduation_year']);
        });
    }
};
