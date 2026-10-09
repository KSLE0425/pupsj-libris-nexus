<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
                $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->string('status')->default('pending');
                $table->timestamp('reserved_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('fulfilled_at')->nullable();
                $table->integer('position')->default(1);
                $table->timestamps();
                $table->index(['book_id', 'status']);
                $table->index(['student_id', 'status']);
                $table->index(['faculty_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
