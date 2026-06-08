<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();

            $table->string('type'); // choice | input
            $table->string('question');
            $table->json('options')->nullable(); // для choice
            $table->string('answer'); // правильный ответ
            $table->unsignedInteger('xp_reward')->default(10);
            $table->unsignedInteger('coin_reward')->default(3);

            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
