<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Drop columns that are no longer needed
            $table->dropColumn([
                'location',
                'pages',
                'description',
                'table_of_contents',
                'cover_image_path',
                'total_copies',
                'low_stock_threshold',
                'issn',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Re-add columns if rolling back (with nullable)
            $table->string('location')->nullable()->after('author');
            $table->integer('pages')->nullable()->after('publication_year');
            $table->text('description')->nullable()->after('copies');
            $table->text('table_of_contents')->nullable()->after('description');
            $table->string('cover_image_path')->nullable()->after('table_of_contents');
            $table->integer('total_copies')->nullable()->after('copies');
            $table->integer('low_stock_threshold')->default(1)->after('total_copies');
            $table->string('issn')->nullable()->after('isbn');
        });
    }
};
