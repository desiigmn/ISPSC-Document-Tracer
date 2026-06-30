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
    Schema::table('documents', function (Blueprint $table) {
        // This makes the column optional in the database
        $table->string('target_office_id')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('documents', function (Blueprint $table) {
        $table->string('target_office_id')->nullable(false)->change();
    });
}
};
