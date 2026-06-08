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
    Schema::create('signature_positions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('document_id')->constrained()->onDelete('cascade');
        $table->string('signatory_name');
        $table->integer('page_number')->default(1);
        $table->float('x_pos'); // Percentage from left (0-100)
        $table->float('y_pos'); // Percentage from top (0-100)
        $table->timestamps();
    });
}};
