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
            if (!Schema::hasColumn('book_usages', 'acknowledged_by_admin1')) {
                $table->string('acknowledged_by_admin1')->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('book_usages', 'acknowledged_at_admin1')) {
                $table->timestamp('acknowledged_at_admin1')->nullable()->after('acknowledged_by_admin1');
            }
            if (!Schema::hasColumn('book_usages', 'acknowledged_by_admin2')) {
                $table->string('acknowledged_by_admin2')->nullable()->after('acknowledged_at_admin1');
            }
            if (!Schema::hasColumn('book_usages', 'acknowledged_at_admin2')) {
                $table->timestamp('acknowledged_at_admin2')->nullable()->after('acknowledged_by_admin2');
            }
            if (!Schema::hasColumn('book_usages', 'acknowledged_by_admin3')) {
                $table->string('acknowledged_by_admin3')->nullable()->after('acknowledged_at_admin2');
            }
            if (!Schema::hasColumn('book_usages', 'acknowledged_at_admin3')) {
                $table->timestamp('acknowledged_at_admin3')->nullable()->after('acknowledged_by_admin3');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_usages', function (Blueprint $table) {
            $table->dropColumn([
                'acknowledged_by_admin1',
                'acknowledged_at_admin1',
                'acknowledged_by_admin2',
                'acknowledged_at_admin2',
                'acknowledged_by_admin3',
                'acknowledged_at_admin3',
            ]);
        });
    }
};
