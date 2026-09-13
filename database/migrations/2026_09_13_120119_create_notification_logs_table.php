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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('channel')->index();
            $table->string('provider');
            $table->string('recipient');
            $table->string('status')->default('queued')->index(); // queued, sent, failed
            $table->json('response')->nullable();
            $table->timestamps();

            // Composite indexes for fast analytical/dashboard queries
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
