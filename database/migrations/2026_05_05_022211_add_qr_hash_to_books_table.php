<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('books', function (Blueprint $table) {
        $table->string('qr_hash', 64)->nullable()->unique()->after('barcode');
    });
}

public function down()
{
    Schema::table('books', function (Blueprint $table) {
        $table->dropColumn('qr_hash');
    });
}
};
