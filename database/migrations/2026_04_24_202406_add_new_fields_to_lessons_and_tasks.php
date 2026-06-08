<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'category')) {
                $table->string('category')->nullable()->after('description');
            }

            if (!Schema::hasColumn('lessons', 'level')) {
                $table->string('level')->default('A1')->after('category');
            }

            if (!Schema::hasColumn('lessons', 'theory')) {
                $table->longText('theory')->nullable()->after('level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'theory')) {
                $table->dropColumn('theory');
            }
            if (Schema::hasColumn('lessons', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('lessons', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
