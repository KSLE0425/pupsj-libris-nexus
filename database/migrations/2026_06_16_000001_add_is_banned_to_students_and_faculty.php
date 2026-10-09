<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'is_banned')) {
            Schema::table('students', function (Blueprint $table) {
                $table->boolean('is_banned')->default(false)->after('damage_warning_count');
            });
        }

        if (! Schema::hasColumn('faculties', 'is_banned')) {
            Schema::table('faculties', function (Blueprint $table) {
                $table->boolean('is_banned')->default(false)->after('damage_warning_count');
            });
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('is_banned');
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->dropColumn('is_banned');
        });
    }
};
