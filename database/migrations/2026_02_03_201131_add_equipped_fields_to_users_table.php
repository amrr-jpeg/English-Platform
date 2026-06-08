<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('users', function (Blueprint $table) {
      $table->foreignId('equipped_hat_id')->nullable()->constrained('items')->nullOnDelete();
      $table->foreignId('equipped_glasses_id')->nullable()->constrained('items')->nullOnDelete();
    });
  }
  public function down(): void {
    Schema::table('users', function (Blueprint $table) {
      $table->dropConstrainedForeignId('equipped_hat_id');
      $table->dropConstrainedForeignId('equipped_glasses_id');
    });
  }
};
