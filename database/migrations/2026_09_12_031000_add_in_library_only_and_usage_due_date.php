<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'in_library_only')) {
                $table->boolean('in_library_only')->default(true)->after('status');
            }
        });

        Schema::table('book_usages', function (Blueprint $table) {
            if (!Schema::hasColumn('book_usages', 'due_date')) {
                $table->date('due_date')->nullable()->after('time_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'in_library_only')) {
                $table->dropColumn('in_library_only');
            }
        });

        Schema::table('book_usages', function (Blueprint $table) {
            if (Schema::hasColumn('book_usages', 'due_date')) {
                $table->dropColumn('due_date');
            }
        });
    }
};
