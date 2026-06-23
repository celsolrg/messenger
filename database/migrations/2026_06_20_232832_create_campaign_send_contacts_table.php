<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_send_contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_send_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('campaign_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('contact_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('message_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('phone')->nullable();

            $table->string('status')->default('pending');
            // pending, queued, sent, failed, skipped

            $table->text('error')->nullable();

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            $table->index(['campaign_send_id', 'status']);
            $table->index(['campaign_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_send_contacts');
    }
};