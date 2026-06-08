<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_code', 6)->nullable()->after('password');
            $table->timestamp('registration_code_expires_at')->nullable()->after('registration_code');
            $table->boolean('is_registration_verified')->default(false)->after('registration_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'registration_code',
                'registration_code_expires_at',
                'is_registration_verified',
            ]);
        });
    }
};