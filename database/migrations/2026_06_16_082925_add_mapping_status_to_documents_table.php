<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Ensure this is imported

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite (used in tests) does not support "MODIFY COLUMN" or ENUM redefinition.
        // Since SQLite handles types loosely, skipping this won't break your tests.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // This runs only on MySQL
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending', 'accepted', 'returned', 'mapping') DEFAULT 'mapping'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Revert back to original (adjust based on your original statuses)
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending', 'accepted', 'returned') DEFAULT 'pending'");
    }
};