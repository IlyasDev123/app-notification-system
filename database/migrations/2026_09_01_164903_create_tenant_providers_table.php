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
        Schema::create('tenant_providers', function (Blueprint $table) {
           $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('channel'); // 'email', 'sms', 'push'
            $table->string('provider_name'); // 'sendgrid', 'mailgun', 'twilio'
            $table->text('credentials'); // Encrypted JSON payload
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(1);
            $table->timestamps();

            $table->index(['tenant_id', 'channel', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_providers');
    }
};
