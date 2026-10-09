<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'damage_warning_count')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedInteger('damage_warning_count')->default(0)->after('id');
            });
        }

        if (! Schema::hasColumn('faculties', 'damage_warning_count')) {
            Schema::table('faculties', function (Blueprint $table) {
                $table->unsignedInteger('damage_warning_count')->default(0)->after('id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('damage_warning_count');
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->dropColumn('damage_warning_count');
        });
    }
};
