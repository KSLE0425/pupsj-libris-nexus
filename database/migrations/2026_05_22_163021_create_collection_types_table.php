<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_built_in')->default(false);
            $table->timestamps();
        });

        // Seed built-in collection types
        DB::table('collection_types')->insert([
            ['name' => 'Filipiniana', 'is_built_in' => true],
            ['name' => 'Library of Congress', 'is_built_in' => true],
            ['name' => 'Thesis Collection', 'is_built_in' => true],
            ['name' => 'Fictions', 'is_built_in' => true],
            ['name' => 'Special Collections', 'is_built_in' => true],
            ['name' => 'General', 'is_built_in' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_types');
    }
};