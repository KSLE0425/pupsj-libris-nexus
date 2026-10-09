<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('title_cover_image_path')->nullable()->after('cover_image_path');
            $table->string('toc_image_path')->nullable()->after('title_cover_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['title_cover_image_path', 'toc_image_path']);
        });
    }
};
