<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_sends', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('campaign_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status')->default('pending');
            // pending, running, paused, finished, failed, cancelled

            $table->unsignedInteger('total_contacts')->default(0);
            $table->unsignedInteger('total_pending')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->unsignedInteger('total_failed')->default(0);

            $table->unsignedInteger('delay_seconds')->default(3);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->text('error')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_sends');
    }
};