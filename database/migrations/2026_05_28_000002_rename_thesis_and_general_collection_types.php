<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rename "Thesis Collection" → "Research and Innovation"
        DB::table('collection_types')->where('name', 'Thesis Collection')
            ->update(['name' => 'Research and Innovation']);
        DB::table('books')->where('collection', 'Thesis Collection')
            ->update(['collection' => 'Research and Innovation']);

        // Rename "General" → "Circulation"
        DB::table('collection_types')->where('name', 'General')
            ->update(['name' => 'Circulation']);
        DB::table('books')->where('collection', 'General')
            ->update(['collection' => 'Circulation']);
    }

    public function down(): void
    {
        DB::table('collection_types')->where('name', 'Research and Innovation')
            ->update(['name' => 'Thesis Collection']);
        DB::table('books')->where('collection', 'Research and Innovation')
            ->update(['collection' => 'Thesis Collection']);

        DB::table('collection_types')->where('name', 'Circulation')
            ->update(['name' => 'General']);
        DB::table('books')->where('collection', 'Circulation')
            ->update(['collection' => 'General']);
    }
};
