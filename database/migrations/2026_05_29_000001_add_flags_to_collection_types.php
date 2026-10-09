<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_types', function (Blueprint $table) {
            $table->boolean('has_loc_classification')->default(false)->after('is_built_in');
            $table->boolean('has_research_type')->default(false)->after('has_loc_classification');
        });

        // Set flags for known collection types
        DB::table('collection_types')->where('name', 'Circulation')->update(['has_loc_classification' => true]);
        DB::table('collection_types')->where('name', 'Filipiniana')->update(['has_loc_classification' => true]);
        DB::table('collection_types')->whereIn('name', ['Research and Innovation', 'Research & Innovation', 'Thesis Collection'])
            ->update(['has_research_type' => true]);

        // Archive the redundant "Library of Congress" collection type
        DB::table('collection_types')->where('name', 'Library of Congress')
            ->update(['archived_at' => now()]);
    }

    public function down(): void
    {
        DB::table('collection_types')->where('name', 'Library of Congress')
            ->update(['archived_at' => null]);

        Schema::table('collection_types', function (Blueprint $table) {
            $table->dropColumn(['has_loc_classification', 'has_research_type']);
        });
    }
};
