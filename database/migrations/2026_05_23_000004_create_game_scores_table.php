<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('game_scores')) {
            return;
        }

        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('game');
            $table->string('level')->default('easy');
            $table->string('source')->default('learned');
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('coins')->default(0);
            $table->boolean('is_rewarded')->default(false);
            $table->boolean('is_best')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'game', 'level']);
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_scores');
    }
};
