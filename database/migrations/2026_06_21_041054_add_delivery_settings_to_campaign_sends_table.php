<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_sends', function (Blueprint $table) {

            $table->unsignedInteger('min_delay_seconds')
                ->default(20)
                ->after('delay_seconds');

            $table->unsignedInteger('max_delay_seconds')
                ->default(60)
                ->after('min_delay_seconds');

            $table->unsignedInteger('pause_every')
                ->default(20)
                ->after('max_delay_seconds');

            $table->unsignedInteger('pause_seconds')
                ->default(300)
                ->after('pause_every');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_sends', function (Blueprint $table) {

            $table->dropColumn([
                'min_delay_seconds',
                'max_delay_seconds',
                'pause_every',
                'pause_seconds'
            ]);
        });
    }
};