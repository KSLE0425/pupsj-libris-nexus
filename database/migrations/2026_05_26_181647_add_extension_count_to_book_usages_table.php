<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('book_usages', function (Blueprint $table) {
            $table->unsignedTinyInteger('extension_count')->default(0)->after('is_overdue_flagged');
        });
    }

    public function down(): void
    {
        Schema::table('book_usages', function (Blueprint $table) {
            $table->dropColumn('extension_count');
        });
    }
};
