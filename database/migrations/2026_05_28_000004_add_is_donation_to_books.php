<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->boolean('is_donation')->default(false)->after('is_new_acquisition');
        });
    }

    public function down(): void
    {
        Schema::table('books', fn(Blueprint $t) => $t->dropColumn('is_donation'));
    }
};
