<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Tracks whether this book was archived as a side-effect of its collection type being archived.
            // Used to correctly restore books when the collection type is unarchived.
            $table->boolean('archived_by_collection')->nullable()->default(null)->after('collection_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('archived_by_collection');
        });
    }
};
