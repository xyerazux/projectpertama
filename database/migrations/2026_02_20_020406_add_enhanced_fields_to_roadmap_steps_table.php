<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->string('priority', 20)->default('medium')->after('title'); // high, medium, low
            $table->date('due_date')->nullable()->after('priority');
            $table->text('description')->nullable()->after('due_date');
            $table->string('category', 50)->nullable()->after('description'); // Design, Development, Testing, etc.
            $table->integer('progress')->default(0)->after('category'); // 0-100
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->dropColumn(['priority', 'due_date', 'description', 'category', 'progress']);
        });
    }
};