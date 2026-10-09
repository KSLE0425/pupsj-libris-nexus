<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained()->onDelete('cascade');
                $table->foreignId('student_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('cascade');
                $table->string('status')->default('pending'); // pending, fulfilled, cancelled, expired
                $table->unsignedInteger('position')->default(0);
                $table->timestamp('reserved_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('fulfilled_at')->nullable();
                $table->timestamps();
                $table->index(['book_id', 'status']);
            });
        }

        Schema::create('book_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('cascade');
            $table->string('title');
            $table->string('isbn')->nullable();
            $table->string('author')->nullable();
            $table->text('justification')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, approved, ordered, received, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('library_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_usage_id')->nullable()->constrained('book_usages')->nullOnDelete();
            $table->string('penalty_type')->default('damage'); // damage, late, lost, other
            $table->unsignedTinyInteger('damage_level')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, waived, paid, recorded_cash, disputed
            $table->date('due_date')->nullable();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('patron_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
            $table->index(['faculty_id', 'status']);
        });

        Schema::create('library_damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_usage_id')->constrained('book_usages')->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->text('patron_note')->nullable();
            $table->string('status')->default('pending'); // pending, processed, dismissed
            $table->unsignedTinyInteger('damage_level')->nullable();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('library_penalty_id')->nullable()->constrained('library_penalties')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::table('books', function (Blueprint $table) {
            if (! Schema::hasColumn('books', 'is_condemned')) {
                $table->boolean('is_condemned')->default(false)->after('status');
            }
            if (! Schema::hasColumn('books', 'condemned_at')) {
                $table->timestamp('condemned_at')->nullable()->after('is_condemned');
            }
            if (! Schema::hasColumn('books', 'condemnation_reason')) {
                $table->text('condemnation_reason')->nullable()->after('condemned_at');
            }
        });

        Schema::table('book_usages', function (Blueprint $table) {
            if (! Schema::hasColumn('book_usages', 'usage_context')) {
                $table->string('usage_context')->default('in_library')->after('status');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'borrowing_suspended_until')) {
                $table->timestamp('borrowing_suspended_until')->nullable()->after('status');
            }
        });

        Schema::table('faculties', function (Blueprint $table) {
            if (! Schema::hasColumn('faculties', 'borrowing_suspended_until')) {
                $table->timestamp('borrowing_suspended_until')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('faculties', function (Blueprint $table) {
            if (Schema::hasColumn('faculties', 'borrowing_suspended_until')) {
                $table->dropColumn('borrowing_suspended_until');
            }
        });
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'borrowing_suspended_until')) {
                $table->dropColumn('borrowing_suspended_until');
            }
        });
        Schema::table('book_usages', function (Blueprint $table) {
            if (Schema::hasColumn('book_usages', 'usage_context')) {
                $table->dropColumn('usage_context');
            }
        });
        Schema::table('books', function (Blueprint $table) {
            foreach (['condemnation_reason', 'condemned_at', 'is_condemned'] as $col) {
                if (Schema::hasColumn('books', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('library_damage_reports');
        Schema::dropIfExists('library_penalties');
        Schema::dropIfExists('book_requisitions');
        Schema::dropIfExists('reservations');
    }
};
