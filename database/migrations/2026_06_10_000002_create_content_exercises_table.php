<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('content_lessons')->cascadeOnDelete();
            $table->string('type')->default('text');
            $table->text('question');
            $table->json('options')->nullable();
            $table->string('answer')->nullable();
            $table->string('correct_answer')->nullable();
            $table->unsignedInteger('xp_reward')->default(10);
            $table->unsignedInteger('coins_reward')->default(5);
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_exercises');
    }
};