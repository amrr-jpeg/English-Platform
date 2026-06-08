<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('coins')->default(0);
            $table->string('skin')->default('blue'); // blue | pink | green
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['xp', 'level', 'coins', 'skin']);
        });
    }
};
