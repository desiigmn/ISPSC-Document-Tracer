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
        // Add priority column: 1=Normal, 2=Urgent, 3=Extremely Urgent
        $table->integer('priority')->default(1)->after('classification');
    });
}

public function down(): void
{
    Schema::table('documents', function (Blueprint $table) {
        $table->dropColumn('priority');
    });
}
};
