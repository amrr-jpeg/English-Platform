<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('user_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('xp_reward')->default(0);
            $table->unsignedInteger('coin_reward')->default(0);
            $table->string('source')->default('lesson');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'exercise_id']);
            $table->index(['user_id', 'is_correct']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_attempts');
    }
};
