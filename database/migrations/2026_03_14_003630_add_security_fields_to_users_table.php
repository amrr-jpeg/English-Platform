<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('password');
            $table->unsignedInteger('failed_login_attempts')->default(0)->after('is_blocked');
            $table->timestamp('blocked_until')->nullable()->after('failed_login_attempts');

            $table->string('two_factor_code', 10)->nullable()->after('blocked_until');
            $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_blocked',
                'failed_login_attempts',
                'blocked_until',
                'two_factor_code',
                'two_factor_expires_at',
            ]);
        });
    }
};