<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_results', 'exam_number')) {
                $table->unsignedTinyInteger('exam_number')->default(1)->after('user_id');
            }

            if (!Schema::hasColumn('exam_results', 'percent')) {
                $table->unsignedTinyInteger('percent')->default(0)->after('total');
            }

            if (!Schema::hasColumn('exam_results', 'passed')) {
                $table->boolean('passed')->default(false)->after('percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            if (Schema::hasColumn('exam_results', 'passed')) {
                $table->dropColumn('passed');
            }

            if (Schema::hasColumn('exam_results', 'percent')) {
                $table->dropColumn('percent');
            }

            if (Schema::hasColumn('exam_results', 'exam_number')) {
                $table->dropColumn('exam_number');
            }
        });
    }
};
