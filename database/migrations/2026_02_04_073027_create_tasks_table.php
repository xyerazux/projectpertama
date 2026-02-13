<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
   Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description')->nullable(); // Sudah ada di sini
        $table->string('link_attachment')->nullable(); // Tambahkan baris ini di sini
        $table->string('priority')->default('medium'); 
        $table->string('status')->default('pending'); 
        $table->dateTime('deadline')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
