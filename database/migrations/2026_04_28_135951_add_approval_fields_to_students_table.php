<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('students', function (Blueprint $table) {
        if (!Schema::hasColumn('students', 'status')) {
            $table->string('status')->default('active');
        }
        if (!Schema::hasColumn('students', 'pup_email')) {
            $table->string('pup_email')->nullable()->unique();
        }
        if (!Schema::hasColumn('students', 'cor_file_path')) {
            $table->string('cor_file_path')->nullable();
        }
    });
}

public function down()
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropColumn(['status', 'pup_email', 'cor_file_path']);
    });
}
};
