<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'creator_id')) {
                $table->foreignId('creator_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('lessons', 'content_course_id')) {
                $table->foreignId('content_course_id')->nullable()->after('creator_id')->constrained('content_courses')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'content_course_id')) {
                $table->dropConstrainedForeignId('content_course_id');
            }

            if (Schema::hasColumn('lessons', 'creator_id')) {
                $table->dropConstrainedForeignId('creator_id');
            }
        });
    }
};
