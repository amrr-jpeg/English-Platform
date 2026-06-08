<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chest_opens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reward_type');
            $table->integer('reward_amount')->default(0);
            $table->string('reward_label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chest_opens');
    }
};