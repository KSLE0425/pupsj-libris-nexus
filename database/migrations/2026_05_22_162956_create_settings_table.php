<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            ['key' => 'penalty_per_day', 'value' => '50.00', 'group' => 'penalties'],
            ['key' => 'max_borrow_days_faculty', 'value' => '7', 'group' => 'penalties'],
            ['key' => 'max_borrow_days_student', 'value' => '3', 'group' => 'penalties'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};