<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        // 1. Disable Foreign Keys to allow ID changes
        Schema::disableForeignKeyConstraints();

        // 2. Change column types to VARCHAR(50) in all tables
        Schema::table('offices', function (Blueprint $table) { $table->string('id', 50)->change(); });
        Schema::table('users', function (Blueprint $table) { $table->string('office_id', 50)->nullable()->change(); });
        Schema::table('documents', function (Blueprint $table) {
            $table->string('current_office_id', 50)->change();
            $table->string('target_office_id', 50)->change();
        });
        Schema::table('document_logs', function (Blueprint $table) { $table->string('office_id', 50)->change(); });

        // 3. TRANSFORM DATA
        $offices = DB::table('offices')->get();

        foreach ($offices as $office) {
            $oldId = $office->id;
            
            // Create a code from the name (e.g. Registrar -> REG)
            $words = explode(' ', $office->office_name);
            $code = strtoupper(substr($words[0], 0, 3)); 
            
            // Generate: ISPSC-MC-{CODE}-{YEAR}-{RANDOM}
            $newId = "ISPSC-MC-{$code}-" . date('Y') . "-" . strtoupper(Str::random(6));

            // Update Primary Key in 'offices'
            DB::table('offices')->where('id', $oldId)->update(['id' => $newId]);

            // Update Foreign Keys everywhere else to match
            DB::table('users')->where('office_id', $oldId)->update(['office_id' => $newId]);
            DB::table('documents')->where('current_office_id', $oldId)->update(['current_office_id' => $newId]);
            DB::table('documents')->where('target_office_id', $oldId)->update(['target_office_id' => $newId]);
            DB::table('document_logs')->where('office_id', $oldId)->update(['office_id' => $newId]);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        // No easy way to reverse random IDs, so we keep it empty or drop everything
    }
};