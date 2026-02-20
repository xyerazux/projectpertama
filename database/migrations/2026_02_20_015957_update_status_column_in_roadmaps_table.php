<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update data lama yang mungkin conflict (opsional)
        DB::table('roadmaps')->where('status', 'on_hold')->update(['status' => 'planned']);
        
        // Modify column status
        Schema::table('roadmaps', function (Blueprint $table) {
            $table->enum('status', ['planned', 'in_progress', 'completed', 'on_hold'])
                  ->default('planned')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('roadmaps', function (Blueprint $table) {
            $table->enum('status', ['planned', 'in_progress', 'completed'])
                  ->default('planned')
                  ->change();
        });
    }
};