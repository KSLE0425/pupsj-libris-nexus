<?php

use App\Models\CollectionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedBigInteger('collection_type_id')->nullable()->after('collection');
            $table->foreign('collection_type_id')->references('id')->on('collection_types')->nullOnDelete();
        });

        // Backfill: match existing collection string to collection_types.name
        $types = CollectionType::pluck('id', 'name');
        foreach ($types as $name => $id) {
            DB::table('books')
                ->where('collection', $name)
                ->whereNull('collection_type_id')
                ->update(['collection_type_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['collection_type_id']);
            $table->dropColumn('collection_type_id');
        });
    }
};
