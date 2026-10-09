<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('research_type')->nullable()->after('collection');
        });
    }

    public function down(): void
    {
        Schema::table('books', fn(Blueprint $t) => $t->dropColumn('research_type'));
    }
};
