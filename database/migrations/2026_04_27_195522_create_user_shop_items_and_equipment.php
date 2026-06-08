<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_shop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_key');
            $table->timestamps();

            $table->unique(['user_id', 'item_key']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('equipped_hat')->nullable()->after('skin');
            $table->string('equipped_accessory')->nullable()->after('equipped_hat');
            $table->string('equipped_effect')->nullable()->after('equipped_accessory');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'equipped_hat',
                'equipped_accessory',
                'equipped_effect',
            ]);
        });

        Schema::dropIfExists('user_shop_items');
    }
};