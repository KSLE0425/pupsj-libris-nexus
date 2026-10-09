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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->string('subject')->nullable();
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->integer('pages')->nullable();

            $table->string('location'); // Shelf / Section
            $table->string('status')->default('available');
            // available, in_use, maintenance

            $table->integer('copies')->default(1);

            $table->text('description')->nullable();
            $table->text('table_of_contents')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
