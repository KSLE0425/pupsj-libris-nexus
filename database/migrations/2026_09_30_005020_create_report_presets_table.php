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
        Schema::create('report_presets', function (Blueprint $table) {
            $table->id();
            $table->string('report_type', 50)->index(); // general, circulation, students, faculty, acquired, condemned, daily, recommendations, seasonal
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->json('options'); // JSON structure holding selected sections/columns/filters
            $table->boolean('is_default')->default(false);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_presets');
    }
};
