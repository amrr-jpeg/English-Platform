<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();

            $table->boolean('is_correct')->default(false);
            $table->string('user_answer')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'exercise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_exercises');
    }
};
