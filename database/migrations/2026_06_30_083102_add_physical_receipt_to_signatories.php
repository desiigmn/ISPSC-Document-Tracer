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
            // 0 = In Transit (Not yet received), 1 = Physically Received
            $table->boolean('is_physically_received')->default(0);
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
