<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_requisitions', function (Blueprint $table) {
            $table->string('publisher')->nullable()->after('author');
            $table->string('image_path')->nullable()->after('publisher');
            $table->json('justification_checklist')->nullable()->after('justification');
            $table->text('justification_other')->nullable()->after('justification_checklist');
            $table->text('ocr_text')->nullable()->after('justification_other');
        });
    }

    public function down(): void
    {
        Schema::table('book_requisitions', function (Blueprint $table) {
            $table->dropColumn([
                'publisher', 'image_path', 'justification_checklist',
                'justification_other', 'ocr_text',
            ]);
        });
    }
};