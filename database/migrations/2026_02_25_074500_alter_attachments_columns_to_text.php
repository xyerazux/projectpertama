<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Change file_path and file_name from VARCHAR(255) to TEXT
     * to prevent truncation errors with long file paths on Railway.
     */
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->text('file_path')->change();
            $table->text('file_name')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('file_path')->change();
            $table->string('file_name')->change();
        });
    }
};
