<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('koha_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('status');
            $table->unsignedInteger('records_synced')->default(0);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('koha_sync_logs');
    }
};
