<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('content_manager_subscriptions')) {
            Schema::create('content_manager_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('content_manager_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'content_manager_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_manager_subscriptions');
    }
};
