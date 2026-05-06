<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the type enum to include image, docx, csv, txt (semeller had these in code but not schema)
        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('pdf','text','url','docx','csv','txt','image') NOT NULL");

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('category', ['general', 'medical_report', 'prescription'])
                  ->default('general')
                  ->after('type');
            $table->json('analysis_result')->nullable()->after('chunk_count');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['category', 'analysis_result']);
        });

        DB::statement("ALTER TABLE documents MODIFY COLUMN type ENUM('pdf','text','url') NOT NULL");
    }
};
