<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('items', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('type'); // hat | glasses
      $table->unsignedInteger('price')->default(50);
      $table->string('emoji')->default('⭐'); // простая “иконка”
      $table->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('items'); }
};
