<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->integer('total')->default(0);
            $table->integer('warnings')->default(0);
            $table->boolean('auto_finished')->default(false);
            $table->integer('xp_reward')->default(0);
            $table->integer('coin_reward')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};