<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shareable_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->json('report_data');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shareable_reports');
    }
};
