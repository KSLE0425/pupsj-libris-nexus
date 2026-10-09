<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('condemned_by')->nullable()->after('condemnation_reason');
            $table->text('last_borrower_info')->nullable()->after('condemned_by');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['condemned_by', 'last_borrower_info']);
        });
    }
};