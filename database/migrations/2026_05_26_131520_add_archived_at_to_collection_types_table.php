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
        Schema::table('collection_types', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('is_built_in');
        });
    }

    public function down(): void
    {
        Schema::table('collection_types', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
