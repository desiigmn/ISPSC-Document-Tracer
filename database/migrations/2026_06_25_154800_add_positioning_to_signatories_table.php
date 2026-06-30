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
    Schema::table('signatories', function (Blueprint $table) {
        if (!Schema::hasColumn('signatories', 'x_pos')) {
            $table->decimal('x_pos', 8, 4)->nullable();
        }
        if (!Schema::hasColumn('signatories', 'y_pos')) {
            $table->decimal('y_pos', 8, 4)->nullable();
        }
        if (!Schema::hasColumn('signatories', 'page_num')) {
            $table->integer('page_num')->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            //
        });
    }
};
