<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('type', ['fixing', 'non_fixing'])->default('non_fixing');
            $table->string('department')->nullable();
            $table->enum('status', ['pending', 'active', 'rejected'])->default('pending');
            $table->string('verification_doc_path')->nullable();
            $table->timestamp('borrowing_suspended_until')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
