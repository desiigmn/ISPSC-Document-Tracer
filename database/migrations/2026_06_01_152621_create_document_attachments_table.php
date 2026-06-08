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
    Schema::create('document_attachments', function (Blueprint $table) {
        $table->id();
        // Links to the main documents table
        $table->foreignId('document_id')->constrained()->onDelete('cascade');
        $table->string('file_path');
        $table->string('file_name');
        $table->string('file_type');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('document_attachments');
}
};
