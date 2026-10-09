<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->timestamp('last_borrow_at')->nullable()->after('last_activity_at');
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->timestamp('last_borrow_at')->nullable()->after('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::table('students', fn(Blueprint $t) => $t->dropColumn('last_borrow_at'));
        Schema::table('faculties', fn(Blueprint $t) => $t->dropColumn('last_borrow_at'));
    }
};
