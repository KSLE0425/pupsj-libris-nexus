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
            $table->boolean('return_kiosk')->default(false)->after('time_out');
            $table->timestamp('return_acknowledged_at')->nullable()->after('return_kiosk');
        });
    }

    public function down(): void
    {
        Schema::table('book_usages', function (Blueprint $table) {
            $table->dropColumn(['return_kiosk', 'return_acknowledged_at']);
        });
    }
};
