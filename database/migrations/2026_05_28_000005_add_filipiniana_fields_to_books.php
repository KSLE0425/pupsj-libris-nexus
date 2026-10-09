<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->boolean('is_filipino_author')->default(false)->after('is_donation');
            $table->boolean('is_ph_published')->default(false)->after('is_filipino_author');
            $table->boolean('is_ph_subject')->default(false)->after('is_ph_published');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['is_filipino_author', 'is_ph_published', 'is_ph_subject']);
        });
    }
};
