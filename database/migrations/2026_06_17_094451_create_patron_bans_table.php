<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patron_bans', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['student', 'faculty']);
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('faculty_id')->nullable();
            $table->unsignedInteger('warning_count_at_ban')->default(3);
            $table->unsignedBigInteger('banned_by')->comment('admin user id');
            $table->timestamp('banned_at');
            $table->unsignedBigInteger('unbanned_by')->nullable();
            $table->timestamp('unbanned_at')->nullable();
            $table->text('unban_reason')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            $table->foreign('faculty_id')->references('id')->on('faculties')->nullOnDelete();
            $table->foreign('banned_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('unbanned_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['user_type', 'student_id']);
            $table->index(['user_type', 'faculty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patron_bans');
    }
};
