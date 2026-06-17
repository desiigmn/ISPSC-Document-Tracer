<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('priority')->nullable()->change();
        });
        // Add 'needs_review' to your ENUM if you use one, or just allow the string
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('mapping', 'needs_review', 'pending', 'accepted', 'returned') DEFAULT 'mapping'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
