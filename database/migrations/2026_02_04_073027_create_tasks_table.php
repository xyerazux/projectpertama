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
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description')->nullable();
        $table->boolean('completed')->default(false); // Kolom ini sudah ada di sini
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    // Hapus foreign key dulu sebelum drop tabel
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropForeign(['category_id']);
    });
    Schema::dropIfExists('tasks');
}
};