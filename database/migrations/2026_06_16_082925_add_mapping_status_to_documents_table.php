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
    // If it's an ENUM column, we redefine the allowed values
    DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending', 'accepted', 'returned', 'mapping') DEFAULT 'mapping'");
}

public function down(): void
{
    // Revert back to original (adjust based on your original statuses)
    DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending', 'accepted', 'returned') DEFAULT 'pending'");
}
};
