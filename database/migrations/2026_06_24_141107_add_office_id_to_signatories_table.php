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
    if (!Schema::hasColumn('signatories', 'office_id')) {
        Schema::table('signatories', function (Blueprint $table) {
            $table->string('office_id')->nullable()->after('document_id');
        });
    }

    // Also check if user_id exists before trying to modify it
    if (Schema::hasColumn('signatories', 'user_id')) {
        Schema::table('signatories', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
}
};
