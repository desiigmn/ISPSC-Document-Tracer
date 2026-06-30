<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <--- MUST ADD THIS

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Handle standard Laravel column changes (Works for both MySQL and SQLite)
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('priority')->nullable()->change();
        });

        // 2. Handle the ENUM change (MySQL Only)
        // SQLite does not support MODIFY COLUMN or ENUMs.
        // During testing, SQLite treats ENUMs as simple strings, so we skip the raw SQL.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('mapping', 'needs_review', 'pending', 'accepted', 'returned') DEFAULT 'mapping'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('priority')->nullable(false)->change();
        });

        if (DB::getDriverName() !== 'sqlite') {
            // Revert to your original enum states
            DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('mapping', 'pending', 'accepted', 'returned') DEFAULT 'mapping'");
        }
    }
};