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
        Schema::table('books', function (Blueprint $table) {
            $table->string('cover_image_path')->nullable()->after('table_of_contents');
            $table->integer('total_copies')->nullable()->after('copies');
            $table->integer('low_stock_threshold')->default(1)->after('total_copies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['cover_image_path', 'total_copies', 'low_stock_threshold']);
        });
    }
};
