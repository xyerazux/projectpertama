<?php

use Illuminate\Database\Eloquent\SoftDeletes;
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

        $table->string('title');
        $table->text('description')->nullable();

        $table->date('deadline')->nullable();

        $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
        $table->enum('status', ['todo', 'progress', 'done'])->default('todo');

        $table->timestamps();
        $table->SoftDeletes();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
