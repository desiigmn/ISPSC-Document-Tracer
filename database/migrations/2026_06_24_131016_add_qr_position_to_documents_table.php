<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'qr_x')) {
                $table->decimal('qr_x', 8, 4)->nullable();
            }
            if (!Schema::hasColumn('documents', 'qr_y')) {
                $table->decimal('qr_y', 8, 4)->nullable();
            }
            if (!Schema::hasColumn('documents', 'qr_page')) {
                $table->integer('qr_page')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Check before dropping to prevent errors
            $columns = [];
            if (Schema::hasColumn('documents', 'qr_x')) $columns[] = 'qr_x';
            if (Schema::hasColumn('documents', 'qr_y')) $columns[] = 'qr_y';
            if (Schema::hasColumn('documents', 'qr_page')) $columns[] = 'qr_page';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};