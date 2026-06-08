<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. OFFICES (The Primary Key is now a String)
        Schema::create('offices', function (Blueprint $table) {
            $table->string('id', 50)->primary(); // e.g., ISPSC-MC-REG-2026-A8F3K9
            $table->string('office_name');
            $table->timestamps();
        });

        // 2. UPDATE USERS TABLE (Add office_id as a String)
        Schema::table('users', function (Blueprint $table) {
            // Check if columns exist before adding (prevents fresh errors)
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('staff')->after('password');
            }
            if (!Schema::hasColumn('users', 'office_id')) {
                $table->string('office_id', 50)->nullable()->after('role');
                $table->foreign('office_id')->references('id')->on('offices')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'campus_code')) {
                $table->string('campus_code')->nullable()->after('office_id');
            }
        });

        // 3. DOCUMENTS
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_id')->unique();
            $table->string('title');
            $table->string('classification');
            $table->string('file_path');
            $table->foreignId('uploader_id')->constrained('users')->onDelete('cascade');
            $table->string('current_office_id', 50); // Matches Office ID String
            $table->string('target_office_id', 50);  // Matches Office ID String
            $table->integer('current_step')->default(1);
            $table->enum('status', ['pending', 'accepted', 'returned', 'archived'])->default('pending');
            $table->timestamps();
        });

        // 4. SIGNATORIES
        Schema::create('signatories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->float('x_pos')->nullable();
            $table->float('y_pos')->nullable();
            $table->integer('sign_order');
            $table->enum('status', ['pending', 'signed'])->default('pending');
            $table->longText('signature_data')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });

        // 5. DOCUMENT LOGS
        Schema::create('document_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('office_id', 50); // Matches Office ID String
            $table->string('action');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_logs');
        Schema::dropIfExists('signatories');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('offices');
    }
};